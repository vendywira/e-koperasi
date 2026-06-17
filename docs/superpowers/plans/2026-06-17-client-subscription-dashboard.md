# Client Subscription Dashboard — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build client subscription dashboard — koperasi pelanggan login, lihat status subscription, riwayat pembayaran, dan invoice. Admin kelola client, subscription, dan input pembayaran manual.

**Architecture:** Satu aplikasi, prefix `/client/*` untuk client pages, `/admin/clients/*` untuk admin management. Inertia + Vue 3. Client punya layout sendiri (`ClientLayout.vue`) dengan sidebar navigasi.

**Tech Stack:** Laravel 11 + Inertia + Vue 3 + TypeScript + Tailwind CSS

---

## File Structure

### New Files (18 files)

```
database/migrations/
├── 2026_06_17_000001_add_phone_to_users_table.php
├── 2026_06_17_000002_create_subscriptions_table.php
└── 2026_06_17_000003_create_payments_table.php

app/Models/
├── Subscription.php
└── Payment.php

app/Http/Controllers/
├── Client/
│   ├── DashboardController.php
│   ├── SubscriptionController.php
│   └── PaymentController.php
└── Admin/
    ├── ClientController.php
    └── ClientPaymentController.php

resources/js/
├── Layouts/ClientLayout.vue
├── Pages/
│   ├── Auth/ClientLogin.vue
│   ├── Client/
│   │   ├── Dashboard.vue
│   │   ├── Subscription.vue
│   │   ├── Payments.vue
│   │   └── PaymentDetail.vue
│   └── Admin/
│       └── Client/
│           ├── Index.vue
│           └── Show.vue
```

### Modified Files (4 files)

```
app/Models/User.php                        → +phone, subscription relation
app/Http/Controllers/AuthController.php    → +showClientLogin, clientLogin
app/Http/Middleware/HandleInertiaRequests.php → +subscription for client
routes/web.php                             → +client routes
routes/cms.php                             → +admin client management routes
```

---

### Task 1: Migration — Add `phone` to users table

**Files:**
- Create: `database/migrations/2026_06_17_000001_add_phone_to_users_table.php`

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```
Expected: `2026_06_17_000001_add_phone_to_users_table ..................... DONE`

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_06_17_000001_add_phone_to_users_table.php
git commit -m "feat: add phone column to users table"
```

---

### Task 2: Migration — Create `subscriptions` table

**Files:**
- Create: `database/migrations/2026_06_17_000002_create_subscriptions_table.php`

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan'); // starter, premium, enterprise
            $table->string('status')->default('active'); // active, expired, cancelled, trialing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_06_17_000002_create_subscriptions_table.php
git commit -m "feat: create subscriptions table"
```

---

### Task 3: Migration — Create `payments` table

**Files:**
- Create: `database/migrations/2026_06_17_000003_create_payments_table.php`

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 0);
            $table->string('status')->default('paid'); // paid, pending, failed
            $table->string('payment_method')->default('manual_transfer');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_06_17_000003_create_payments_table.php
git commit -m "feat: create payments table"
```

---

### Task 4: Model — Subscription

**Files:**
- Create: `app/Models/Subscription.php`

- [ ] **Step 1: Create model + relationships**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'started_at',
        'trial_ends_at',
        'ends_at',
        'renewed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
            'renewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function daysRemaining(): int
    {
        if (!$this->ends_at) return 365;
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function usagePercent(): int
    {
        if (!$this->ends_at || !$this->started_at) return 0;
        $total = $this->started_at->diffInDays($this->ends_at);
        if ($total <= 0) return 100;
        $elapsed = $this->started_at->diffInDays(now());
        return min(100, max(0, (int) round(($elapsed / $total) * 100)));
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/Subscription.php
git commit -m "feat: add Subscription model with relations and helpers"
```

---

### Task 5: Model — Payment

**Files:**
- Create: `app/Models/Payment.php`

- [ ] **Step 1: Create model + relationships**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'status',
        'payment_method',
        'paid_at',
        'notes',
        'receipt_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function amountFormatted(): string
    {
        return 'Rp' . number_format($this->amount, 0, ',', '.');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/Payment.php
git commit -m "feat: add Payment model"
```

---

### Task 6: Modify User model — add phone + subscription relation

**Files:**
- Modify: `app/Models/User.php`

- [ ] **Step 1: Add `phone` to $fillable and add subscription relation**

```php
// Inside $fillable, add 'phone':
protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'role',
];

// Add import:
use Illuminate\Database\Eloquent\Relations\HasOne;

// Add method:
public function subscription(): HasOne
{
    return $this->hasOne(Subscription::class);
}
```

Edit file:

```php
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];
```

Add after `sendPasswordResetNotification` method:

```php
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
```

Add import at top (after `use App\Notifications\ResetPasswordNotification;`):

```php
use Illuminate\Database\Eloquent\Relations\HasOne;
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/User.php
git commit -m "feat: add phone field and subscription relation to User"
```

---

### Task 7: AuthController — add client login methods

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`

- [ ] **Step 1: Add showClientLogin method**

```php
public function showClientLogin(): Response
{
    if (Auth::check() && Auth::user()->role === 'client') {
        return redirect()->route('client.dashboard');
    }

    return Inertia::render('Auth/ClientLogin');
}

public function clientLogin(Request $request): \Illuminate\Http\RedirectResponse
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();

        if ($user->role !== 'client') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses ke portal client.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('client.dashboard'));
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->onlyInput('email');
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/AuthController.php
git commit -m "feat: add client login methods to AuthController"
```

---

### Task 8: Client Login Page

**Files:**
- Create: `resources/js/Pages/Auth/ClientLogin.vue`

- [ ] **Step 1: Create ClientLogin page**

```vue
<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/client/login', {
        onError: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login Client - e-Koperasi" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary-50 via-white to-emerald-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950 px-4">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 mb-8 group">
            <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-10 w-10 rounded-xl shadow-sm" />
            <div>
                <span class="text-xl font-bold text-neutral-900 dark:text-white">e-Koperasi</span>
                <span class="block text-xs text-neutral-500 dark:text-neutral-400 -mt-0.5">Client Portal</span>
            </div>
        </Link>

        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl dark:shadow-neutral-950/80 border border-neutral-200 dark:border-neutral-800 p-8">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-1">Login Client</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Masuk untuk melihat dashboard langganan koperasi Anda.</p>

                <!-- Error message -->
                <div v-if="form.errors.email" class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                            placeholder="koperasi@email.com"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                            placeholder="Password"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Ingat saya</span>
                        </label>
                        <Link href="/forgot-password" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                            Lupa password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow-md active:scale-[0.98]"
                    >
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-neutral-500 dark:text-neutral-400">
                <Link href="/" class="text-primary-600 dark:text-primary-400 hover:underline">&larr; Kembali ke website</Link>
            </p>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Auth/ClientLogin.vue
git commit -m "feat: add client login page"
```

---

### Task 9: Client Layout

**Files:**
- Create: `resources/js/Layouts/ClientLayout.vue`

- [ ] **Step 1: Create ClientLayout.vue**

```vue
<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useTheme } from '@/composables/useTheme';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const subscription = computed(() => (page.props as any).auth?.user?.subscription);
const { theme, toggleTheme } = useTheme();
const sidebarOpen = ref(false);

function logout() {
    router.post('/logout', {}, {
        onFinish: () => window.location.href = '/client/login',
    });
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') sidebarOpen.value = false;
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

const navItems = [
    { key: 'dashboard', label: 'Beranda', href: '/client/dashboard', icon: 'home' },
    { key: 'subscription', label: 'Langganan', href: '/client/subscription', icon: 'clipboard' },
    { key: 'payments', label: 'Pembayaran', href: '/client/payments', icon: 'credit-card' },
];

const isActive = (href: string) => page.url === href || page.url.startsWith(href + '/');
</script>

<template>
    <div class="min-h-screen flex bg-neutral-50 dark:bg-neutral-950">
        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
        </Transition>

        <!-- Sidebar -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="sidebarOpen"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex flex-col lg:static lg:z-auto lg:translate-x-0"
            >
                <SidebarContent :user :subscription :navItems :isActive :theme :toggleTheme :logout :closeSidebar="() => sidebarOpen = false" />
            </aside>
        </Transition>

        <!-- Desktop sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex-shrink-0">
            <SidebarContent :user :subscription :navItems :isActive :theme :toggleTheme :logout :closeSidebar="() => {}" />
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile header -->
            <header class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30 lg:hidden">
                <div class="flex items-center gap-3 px-4 py-3">
                    <button
                        @click="sidebarOpen = true"
                        class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-base font-bold text-neutral-900 dark:text-white truncate">{{ title || 'Dashboard' }}</h1>
                </div>
            </header>
            <!-- Desktop header -->
            <header class="hidden lg:block bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 px-6 py-4">
                <h1 class="text-lg font-bold text-neutral-900 dark:text-white">{{ title || 'Dashboard' }}</h1>
            </header>
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const SidebarContent = defineComponent({
    props: ['user', 'subscription', 'navItems', 'isActive', 'theme', 'toggleTheme', 'logout', 'closeSidebar'],
    setup(props) {
        const icons: Record<string, any> = {
            home: 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            clipboard: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'credit-card': 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        };

        return () => h('div', { class: 'flex flex-col h-full' }, [
            // Logo
            h('div', { class: 'px-6 py-5 border-b border-neutral-200 dark:border-neutral-800' }, [
                h('div', { class: 'flex items-center justify-between' }, [
                    h(Link, { href: '/client/dashboard', class: 'flex items-center gap-2.5 group', onClick: props.closeSidebar }, () => [
                        h('div', { class: 'w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm' }, 'eK'),
                        h('div', {}, [
                            h('span', { class: 'font-bold text-sm text-neutral-900 dark:text-white' }, 'e-Koperasi'),
                            h('span', { class: 'block text-[10px] text-neutral-400 dark:text-neutral-500 -mt-0.5' }, 'Client Portal'),
                        ]),
                    ]),
                    // Close button mobile
                    h('button', {
                        class: 'lg:hidden p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors',
                        onClick: props.closeSidebar,
                    }, [
                        h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M6 18L18 6M6 6l12 12' }),
                        ]),
                    ]),
                ]),
            ]),

            // Nav items
            h('nav', { class: 'flex-1 px-3 py-4 space-y-1' }, [
                h('p', { class: 'px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500' }, 'Menu'),
                ...props.navItems.map((item: any) =>
                    h(Link, {
                        href: item.href,
                        class: 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ' +
                            (props.isActive(item.href)
                                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                                : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'),
                        onClick: props.closeSidebar,
                    }, [
                        h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: icons[item.icon] }),
                        ]),
                        item.label,
                    ])
                ),
            ]),

            // Subscription status mini
            h('div', { class: 'px-3 py-3 border-t border-neutral-200 dark:border-neutral-800' }, [
                h('div', { class: 'px-3 py-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50' }, [
                    h('p', { class: 'text-[10px] font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500' }, 'Status Langganan'),
                    h('div', { class: 'flex items-center gap-2 mt-1' }, [
                        h('span', {
                            class: 'w-2 h-2 rounded-full ' +
                                (props.subscription?.is_active ? 'bg-emerald-500' : 'bg-red-500'),
                        }),
                        h('span', { class: 'text-sm font-medium text-neutral-900 dark:text-white' },
                            props.subscription?.plan
                                ? (props.subscription.plan.charAt(0).toUpperCase() + props.subscription.plan.slice(1)) + ' · ' + (props.subscription.is_active ? 'Aktif' : 'Tidak Aktif')
                                : 'Belum ada'
                        ),
                    ]),
                ]),
            ]),

            // Theme + Logout
            h('div', { class: 'px-3 py-3 border-t border-neutral-200 dark:border-neutral-800 space-y-1' }, [
                h('button', {
                    onClick: props.toggleTheme,
                    class: 'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white',
                }, [
                    props.theme === 'dark'
                        ? h('svg', { class: 'w-5 h-5 text-amber-400', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z' }),
                        ])
                        : h('svg', { class: 'w-5 h-5 text-indigo-500', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z' }),
                        ]),
                    h('span', { class: 'flex-1 text-left' }, props.theme === 'dark' ? 'Mode Terang' : 'Mode Gelap'),
                ]),
                h('button', {
                    onClick: props.logout,
                    class: 'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors',
                }, [
                    h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' }),
                    ]),
                    'Logout',
                ]),
            ]),
        ]);
    },
});
</script>
```

Note: The sidebar content is defined as a child component (inline) to keep template clean while using render function for the sidebar (avoids prop drilling repetition between mobile/desktop sidebars).

- [ ] **Step 2: Commit**

```bash
git add resources/js/Layouts/ClientLayout.vue
git commit -m "feat: add ClientLayout with sidebar navigation"
```

---

### Task 10: Client Dashboard Page

**Files:**
- Create: `resources/js/Pages/Client/Dashboard.vue`

- [ ] **Step 1: Create Dashboard page**

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    subscription: any;
    recentPayments: any[];
}>();

const planLabel = computed(() => {
    const labels: Record<string, string> = {
        starter: 'Starter',
        premium: 'Premium',
        enterprise: 'Enterprise',
    };
    return labels[props.subscription?.plan] || props.subscription?.plan || '-';
});

const priceLabel = computed(() => {
    const prices: Record<string, string> = {
        starter: 'Rp499.000 / bln',
        premium: 'Rp1.500.000 / bln',
        enterprise: 'Custom',
    };
    return prices[props.subscription?.plan] || '-';
});

const statusColor = computed(() => {
    if (!props.subscription) return 'bg-neutral-400';
    return props.subscription.is_active ? 'bg-emerald-500' : 'bg-red-500';
});

const statusLabel = computed(() => {
    if (!props.subscription) return 'Belum Aktif';
    return props.subscription.is_active ? 'Aktif' : 'Tidak Aktif';
});
</script>

<template>
    <ClientLayout title="Beranda">
        <Head title="Dashboard - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl">
            <!-- Welcome -->
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">
                    Selamat datang, {{ $page.props.auth?.user?.name || 'Client' }}
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Ringkasan langganan koperasi Anda.</p>
            </div>

            <!-- Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Status Langganan</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span :class="statusColor" class="w-3 h-3 rounded-full inline-block"></span>
                                <span class="text-lg font-bold text-neutral-900 dark:text-white">{{ statusLabel }}</span>
                            </div>
                        </div>
                        <span class="text-3xl opacity-10">📋</span>
                    </div>

                    <div v-if="subscription" class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-500 dark:text-neutral-400">Sisa Masa Aktif</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ subscription.days_remaining }} hari</span>
                            </div>
                            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="subscription.is_active ? 'bg-emerald-500' : 'bg-red-400'"
                                    :style="{ width: Math.min(100, subscription.usage_percent || 0) + '%' }"
                                />
                            </div>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
                                {{ subscription.started_at ?? '-' }} — {{ subscription.ends_at ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada langganan aktif. Hubungi admin.</p>
                    </div>
                </div>

                <!-- Package Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Paket</p>
                            <h3 class="text-xl font-bold text-neutral-900 dark:text-white mt-1">{{ planLabel }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ priceLabel }}</p>
                        </div>
                        <span class="text-3xl opacity-10">📦</span>
                    </div>
                    <Link
                        href="/client/subscription"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                    >
                        Lihat Detail Paket
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Riwayat Pembayaran</h3>
                    <Link
                        v-if="recentPayments.length > 0"
                        href="/client/payments"
                        class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                    >
                        Lihat Semua
                    </Link>
                </div>

                <div v-if="recentPayments.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Invoice</th>
                                <th class="text-right px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Jumlah</th>
                                <th class="text-center px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="payment in recentPayments" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 sm:px-6 py-3 text-neutral-700 dark:text-neutral-300">{{ payment.paid_at ?? payment.created_at }}</td>
                                <td class="px-5 sm:px-6 py-3">
                                    <Link :href="'/client/payments/' + payment.id" class="text-emerald-600 dark:text-emerald-400 hover:underline font-mono text-xs">
                                        {{ payment.receipt_number }}
                                    </Link>
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-right font-medium text-neutral-900 dark:text-white">
                                    Rp{{ Number(payment.amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'"
                                    >
                                        {{ payment.status === 'paid' ? 'Lunas' : payment.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 sm:px-6 py-8 text-center text-sm text-neutral-400 dark:text-neutral-500">
                    <p class="text-2xl mb-2">💳</p>
                    <p>Belum ada pembayaran tercatat.</p>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Client/Dashboard.vue
git commit -m "feat: add client dashboard page"
```

---

### Task 11: Client Subscription Detail Page

**Files:**
- Create: `resources/js/Pages/Client/Subscription.vue`

- [ ] **Step 1: Create Subscription page**

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    subscription: any;
    planFeatures: string[];
}>();

const planLabel = computed(() => {
    const labels: Record<string, string> = { starter: 'Starter', premium: 'Premium', enterprise: 'Enterprise' };
    return labels[props.subscription?.plan] || '-';
});

const priceLabel = computed(() => {
    const prices: Record<string, string> = { starter: 'Rp499.000 / bln', premium: 'Rp1.500.000 / bln', enterprise: 'Custom' };
    return prices[props.subscription?.plan] || '-';
});

const isActive = computed(() => props.subscription?.is_active ?? false);
</script>

<template>
    <ClientLayout title="Detail Langganan">
        <Head title="Langganan - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl space-y-6">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Detail Langganan</h2>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-5 sm:p-6 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ planLabel }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ priceLabel }}</p>
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
                        :class="isActive ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                    >
                        {{ isActive ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <!-- Info -->
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Mulai</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.started_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Berakhir</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.ends_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Sisa Hari</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.days_remaining ?? '-' }} hari</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Terakhir Diperpanjang</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.renewed_at ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div v-if="subscription">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-neutral-500 dark:text-neutral-400">Masa berlaku</span>
                            <span class="text-neutral-900 dark:text-white font-medium">{{ subscription.usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="isActive ? 'bg-emerald-500' : 'bg-neutral-400'"
                                :style="{ width: Math.min(100, subscription.usage_percent || 0) + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div v-if="planFeatures.length > 0" class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Fitur Paket</h4>
                    <ul class="space-y-2">
                        <li v-for="(feature, idx) in planFeatures" :key="idx" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                </div>
            </div>

            <Link
                href="/client/dashboard"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Dashboard
            </Link>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Client/Subscription.vue
git commit -m "feat: add client subscription detail page"
```

---

### Task 12: Client Payments Pages

**Files:**
- Create: `resources/js/Pages/Client/Payments.vue`
- Create: `resources/js/Pages/Client/PaymentDetail.vue`

- [ ] **Step 1: Create Payments list page**

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

defineProps<{
    payments: any[];
}>();
</script>

<template>
    <ClientLayout title="Riwayat Pembayaran">
        <Head title="Pembayaran - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Riwayat Pembayaran</h2>
            </div>

            <div v-if="payments.length > 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">#</th>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Invoice</th>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-right px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Jumlah</th>
                                <th class="text-center px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="(payment, idx) in payments" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 sm:px-6 py-3.5 text-neutral-500 dark:text-neutral-400">{{ idx + 1 }}</td>
                                <td class="px-5 sm:px-6 py-3.5">
                                    <Link :href="'/client/payments/' + payment.id" class="text-emerald-600 dark:text-emerald-400 hover:underline font-mono text-xs">
                                        {{ payment.receipt_number }}
                                    </Link>
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-neutral-700 dark:text-neutral-300">{{ payment.paid_at ?? payment.created_at }}</td>
                                <td class="px-5 sm:px-6 py-3.5 text-right font-medium text-neutral-900 dark:text-white font-mono">
                                    Rp{{ Number(payment.amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-center">
                                    <span
                                        class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'"
                                    >
                                        {{ payment.status === 'paid' ? 'Lunas' : payment.status === 'pending' ? 'Pending' : 'Gagal' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center">
                <p class="text-3xl mb-3">💳</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada riwayat pembayaran.</p>
            </div>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 2: Create Payment Detail page**

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    payment: any;
    subscription: any;
}>();

const statusBadge = (status: string) => {
    const map: Record<string, { class: string; label: string }> = {
        paid: { class: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300', label: 'Lunas' },
        pending: { class: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300', label: 'Pending' },
        failed: { class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300', label: 'Gagal' },
    };
    return map[status] || { class: 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400', label: status };
};
</script>

<template>
    <ClientLayout title="Detail Pembayaran">
        <Head title="Detail Pembayaran - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-2xl space-y-6">
            <Link
                href="/client/payments"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Riwayat
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Invoice Header -->
                <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Invoice</p>
                            <h2 class="text-xl font-bold text-neutral-900 dark:text-white mt-1 font-mono">{{ payment.receipt_number }}</h2>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="statusBadge(payment.status).class"
                        >
                            {{ statusBadge(payment.status).label }}
                        </span>
                    </div>
                </div>

                <!-- Detail -->
                <div class="p-6 sm:p-8 space-y-4">
                    <div class="grid grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Tanggal</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ payment.paid_at ?? payment.created_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Metode</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">
                                {{ payment.payment_method === 'manual_transfer' ? 'Transfer Manual' : payment.payment_method }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Paket</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1 capitalize">{{ subscription?.plan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Periode</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ payment.paid_at ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div v-if="payment.notes" class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1">Catatan</p>
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ payment.notes }}</p>
                    </div>

                    <!-- Total -->
                    <div class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Total Pembayaran</span>
                            <span class="text-xl font-bold text-neutral-900 dark:text-white">Rp{{ Number(payment.amount).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Client/Payments.vue resources/js/Pages/Client/PaymentDetail.vue
git commit -m "feat: add client payment history and detail pages"
```

---

### Task 13: Client Controllers (Backend)

**Files:**
- Create: `app/Http/Controllers/Client/DashboardController.php`
- Create: `app/Http/Controllers/Client/SubscriptionController.php`
- Create: `app/Http/Controllers/Client/PaymentController.php`

- [ ] **Step 1: Create DashboardController**

```php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\SiteConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        $recentPayments = $subscription
            ? $subscription->payments()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
            : [];

        return Inertia::render('Client/Dashboard', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'started_at' => $subscription->started_at?->format('d M Y'),
                'ends_at' => $subscription->ends_at?->format('d M Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'usage_percent' => $subscription->usagePercent(),
            ] : null,
            'recentPayments' => $recentPayments,
        ]);
    }
}
```

- [ ] **Step 2: Create SubscriptionController**

```php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\SiteConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        $planFeatures = [];
        if ($subscription) {
            $tiers = SiteConfig::get('pricing.tiers', []);
            $planFeatures = $tiers[$subscription->plan]['features'] ?? [];
            // Flatten the features (they come as [{feature: "..."}] from repeater)
            $planFeatures = array_map(function ($f) {
                return is_array($f) ? ($f['feature'] ?? $f) : $f;
            }, $planFeatures);
        }

        return Inertia::render('Client/Subscription', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'started_at' => $subscription->started_at?->format('d M Y'),
                'ends_at' => $subscription->ends_at?->format('d M Y'),
                'renewed_at' => $subscription->renewed_at?->format('d M Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'usage_percent' => $subscription->usagePercent(),
            ] : null,
            'planFeatures' => $planFeatures,
        ]);
    }
}
```

- [ ] **Step 3: Create PaymentController**

```php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $payments = $user->subscription
            ? $user->subscription->payments()
                ->orderBy('created_at', 'desc')
                ->paginate(15)
            : collect([]);

        return Inertia::render('Client/Payments', [
            'payments' => $payments,
        ]);
    }

    public function show(int $id): Response
    {
        $user = auth()->user();
        $payment = Payment::where('id', $id)
            ->whereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        return Inertia::render('Client/PaymentDetail', [
            'payment' => $payment,
            'subscription' => $payment->subscription,
        ]);
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Client/
git commit -m "feat: add client controllers (dashboard, subscription, payment)"
```

---

### Task 14: Client Routes

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add client routes (sebelum require cms.php)**

```php
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\SubscriptionController;
use App\Http\Controllers\Client\PaymentController;

// ...

// Client Portal
Route::prefix('client')->name('client.')->group(function () {
    Route::get('/login', [AuthController::class, 'showClientLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'clientLogin'])->middleware('throttle:5,1')->name('login.submit');

    Route::middleware(['auth', 'role:client'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
    });
});
```

- [ ] **Step 2: Commit**

```bash
git add routes/web.php
git commit -m "feat: add client portal routes"
```

---

### Task 15: HandleInertiaRequests — share subscription for client

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

- [ ] **Step 1: Add subscription data for authenticated client users**

Add inside the `share` method, after the existing auth user share:

```php
'role' => $request->user()->role ?? 'editor',
'subscription' => $request->user()->role === 'client' && $request->user()->subscription
    ? [
        'id' => $request->user()->subscription->id,
        'plan' => $request->user()->subscription->plan,
        'status' => $request->user()->subscription->status,
        'is_active' => $request->user()->subscription->isActive(),
        'days_remaining' => $request->user()->subscription->daysRemaining(),
        'usage_percent' => $request->user()->subscription->usagePercent(),
        'ends_at' => $request->user()->subscription->ends_at?->format('d M Y'),
    ]
    : null,
```

Find the existing `'role' => $request->user()->role ?? 'editor'` line and replace it to add the subscription data.

- [ ] **Step 2: Commit**

```bash
git add app/Http/Middleware/HandleInertiaRequests.php
git commit -m "feat: share subscription data for client users in Inertia"
```

---

### Task 16: Admin — Client Management Controller

**Files:**
- Create: `app/Http/Controllers/Admin/ClientController.php`
- Create: `app/Http/Controllers/Admin/ClientPaymentController.php`

- [ ] **Step 1: Create ClientController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = User::where('role', 'client')
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Client/Index', [
            'clients' => $clients,
        ]);
    }

    public function show(int $id): Response
    {
        $client = User::where('role', 'client')
            ->with(['subscription.payments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);

        $planLabels = [
            'starter' => ['name' => 'Starter', 'price' => 'Rp499.000/bln'],
            'premium' => ['name' => 'Premium', 'price' => 'Rp1.500.000/bln'],
            'enterprise' => ['name' => 'Enterprise', 'price' => 'Custom'],
        ];

        return Inertia::render('Admin/Client/Show', [
            'client' => $client,
            'planLabels' => $planLabels,
        ]);
    }

    public function updateSubscription(Request $request, int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'plan' => 'required|in:starter,premium,enterprise',
            'status' => 'required|in:active,expired,cancelled,trialing',
            'started_at' => 'required|date',
            'ends_at' => 'nullable|date|after:started_at',
            'trial_ends_at' => 'nullable|date',
        ]);

        $subscription = $client->subscription;

        if ($subscription) {
            $subscription->update($validated);
        } else {
            $subscription = Subscription::create(array_merge($validated, ['user_id' => $client->id]));
        }

        return redirect()->back()->with('success', 'Subscription berhasil diperbarui.');
    }
}
```

- [ ] **Step 2: Create ClientPaymentController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function store(Request $request, int $subscriptionId): RedirectResponse
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:paid,pending,failed',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Generate receipt number
        $lastReceipt = Payment::where('receipt_number', 'like', 'INV-' . date('Ym') . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNum = (int) substr($lastReceipt->receipt_number, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        $receiptNumber = 'INV-' . date('Ym') . '-' . $newNum;

        $payment = $subscription->payments()->create([
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'payment_method' => 'manual_transfer',
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ?? null,
            'receipt_number' => $receiptNumber,
        ]);

        // If payment is paid, update subscription
        if ($validated['status'] === 'paid') {
            $pricing = SiteConfig::get('pricing.tiers.' . $subscription->plan, []);
            $subscription->update([
                'status' => 'active',
                'renewed_at' => now(),
                // Perpanjang ends_at jika sudah expired atau mendekati
                'ends_at' => $subscription->ends_at && $subscription->ends_at->isFuture()
                    ? $subscription->ends_at->addMonth()
                    : now()->addMonth(),
                'started_at' => $subscription->started_at ?? now(),
            ]);
        }

        return redirect()->back()->with('success', "Pembayaran {$receiptNumber} berhasil dicatat.");
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/ClientController.php app/Http/Controllers/Admin/ClientPaymentController.php
git commit -m "feat: add admin client management controllers"
```

---

### Task 17: Admin — Client Management Pages

**Files:**
- Create: `resources/js/Pages/Admin/Client/Index.vue`
- Create: `resources/js/Pages/Admin/Client/Show.vue`

- [ ] **Step 1: Create Admin Client Index page**

```vue
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    clients: any;
}>();

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AdminLayout title="Daftar Client">
        <Head title="Client - CMS Admin" />

        <div class="p-4 sm:p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Daftar Client / Koperasi</h2>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Koperasi</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Email</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Paket</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Bergabung</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white">{{ client.name }}</td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400">{{ client.email }}</td>
                                <td class="px-5 py-3 capitalize text-neutral-700 dark:text-neutral-300">{{ client.subscription?.plan ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span
                                        v-if="client.subscription"
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="client.subscription.isActive() ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                                    >
                                        {{ client.subscription.isActive() ? 'Aktif' : client.subscription.status }}
                                    </span>
                                    <span v-else class="text-xs text-neutral-400 dark:text-neutral-500">Belum ada</span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs">{{ formatDate(client.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link
                                        :href="'/admin/clients/' + client.id"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors"
                                    >
                                        Kelola
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="clients.total > clients.per_page" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">Menampilkan {{ clients.from }}–{{ clients.to }} dari {{ clients.total }}</span>
                    <div class="flex gap-2">
                        <Link
                            v-if="clients.prev_page_url"
                            :href="clients.prev_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Sebelumnya
                        </Link>
                        <Link
                            v-if="clients.next_page_url"
                            :href="clients.next_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 2: Create Admin Client Show page**

```vue
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    client: any;
    planLabels: Record<string, { name: string; price: string }>;
}>();

const form = useForm({
    plan: props.client.subscription?.plan || 'starter',
    status: props.client.subscription?.status || 'active',
    started_at: props.client.subscription?.started_at?.split(' ')[0] || new Date().toISOString().split('T')[0],
    ends_at: props.client.subscription?.ends_at?.split(' ')[0] || '',
    trial_ends_at: props.client.subscription?.trial_ends_at?.split(' ')[0] || '',
});

const paymentForm = useForm({
    amount: 0,
    status: 'paid',
    paid_at: new Date().toISOString().split('T')[0],
    notes: '',
});

function saveSubscription() {
    form.put('/admin/clients/' + props.client.id + '/subscription', {
        onSuccess: () => {
            // Reload client data
            router.reload({ only: ['client'] });
        },
    });
}

function addPayment() {
    if (!props.client.subscription) {
        alert('Client belum memiliki subscription. Buat subscription dulu.');
        return;
    }
    paymentForm.post('/admin/clients/' + props.client.subscription.id + '/payments', {
        onSuccess: () => {
            paymentForm.reset();
            router.reload({ only: ['client'] });
        },
    });
}

const formatDate = (dt: string | null) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AdminLayout :title="'Client: ' + client.name">
        <Head :title="client.name + ' - Client Detail'" />

        <div class="p-4 sm:p-6 space-y-6 max-w-5xl">
            <Link
                href="/admin/clients"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Daftar Client
            </Link>

            <!-- Client Info -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Info Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Informasi Koperasi</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Nama</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Email</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Telepon</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.phone || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Bergabung</dt>
                            <dd class="text-sm text-neutral-900 dark:text-white">{{ formatDate(client.created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Subscription Form -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Kelola Subscription</h3>
                    <form @submit.prevent="saveSubscription" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Paket</label>
                            <select v-model="form.plan" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                                <option v-for="(label, key) in planLabels" :key="key" :value="key">{{ label.name }} ({{ label.price }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                            <select v-model="form.status" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                                <option value="active">Aktif</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Dibatalkan</option>
                                <option value="trialing">Trial</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Mulai</label>
                                <input v-model="form.started_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Berakhir</label>
                                <input v-model="form.ends_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Subscription' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Riwayat Pembayaran</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Invoice</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Tanggal</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Jumlah</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Status</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="payment in client.subscription?.payments ?? []" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs text-primary-600 dark:text-primary-400">{{ payment.receipt_number }}</td>
                                <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ formatDate(payment.paid_at) }}</td>
                                <td class="px-5 py-3 text-right font-medium text-neutral-900 dark:text-white">Rp{{ Number(payment.amount).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700'"
                                    >{{ payment.status === 'paid' ? 'Lunas' : payment.status }}</span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs max-w-[150px] truncate">{{ payment.notes || '-' }}</td>
                            </tr>
                            <tr v-if="!client.subscription?.payments?.length">
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-neutral-400">Belum ada pembayaran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Payment Form -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Input Pembayaran Manual</h3>
                <form @submit.prevent="addPayment" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Jumlah (Rp)</label>
                        <input v-model.number="paymentForm.amount" type="number" min="0" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                        <select v-model="paymentForm.status" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                            <option value="paid">Lunas</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Gagal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Tanggal Bayar</label>
                        <input v-model="paymentForm.paid_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 transition-colors"
                        >
                            {{ paymentForm.processing ? 'Menyimpan...' : 'Tambah Pembayaran' }}
                        </button>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Catatan</label>
                        <input v-model="paymentForm.notes" type="text" placeholder="Catatan pembayaran (opsional)" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Client/
git commit -m "feat: add admin client management pages"
```

---

### Task 18: Admin Client Management Routes

**Files:**
- Modify: `routes/cms.php`

- [ ] **Step 1: Add admin client routes** (before the closing `});` of the admin group)

```php
// Client Management
Route::prefix('clients')->name('client.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ClientController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Admin\ClientController::class, 'show'])->name('show');
    Route::put('/{id}/subscription', [\App\Http\Controllers\Admin\ClientController::class, 'updateSubscription'])->name('subscription.update');
    Route::post('/{subscriptionId}/payments', [\App\Http\Controllers\Admin\ClientPaymentController::class, 'store'])->name('payments.store');
});
```

Add imports at the top of `routes/cms.php`:

```php
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientPaymentController;
```

- [ ] **Step 2: Commit**

```bash
git add routes/cms.php
git commit -m "feat: add admin client management routes"
```

---

## Self-Review Checklist

### 1. Spec Coverage
- ✅ Data model migrations (subscriptions + payments tables) → Task 2-3
- ✅ Models (Subscription + Payment + User relations) → Task 4-6
- ✅ Client login page + controller → Task 7-8
- ✅ Client layout with sidebar → Task 9
- ✅ Dashboard page (status + summary) → Task 10
- ✅ Subscription detail page → Task 11
- ✅ Payment history + invoice detail → Task 12
- ✅ Client controllers (Dashboard, Subscription, Payment) → Task 13
- ✅ Client routes → Task 14
- ✅ HandleInertiaRequests share → Task 15
- ✅ Admin client management (list + detail + subscription edit) → Task 16-17
- ✅ Admin payment input (manual) → Task 17-18
- ✅ Admin routes → Task 18

### 2. Placeholder Scan
- ✅ No TODOs, TBDs, or "implement later" in code
- ✅ UI labels use actual Indonesian text
- ✅ Date formatting uses `.format()` / `Intl` consistently
- ✅ Route names use consistent `client.*` and `admin.client.*` pattern

### 3. Type Consistency
- ✅ `subscription.plan` values match config/site.php (starter, premium, enterprise)
- ✅ Payment statuses: paid, pending, failed
- ✅ Subscription statuses: active, expired, cancelled, trialing
- ✅ Receipt format: INV-YYYYMM-XXXX everywhere

### 4. Scope Check
- ✅ Focused on v1 per spec
- ❌ PDF download (v2) — excluded from plan
- ❌ Email notification (v2) — excluded
- ❌ Auto-expire scheduler (v2) — excluded
- ❌ Payment gateway (v2) — excluded
