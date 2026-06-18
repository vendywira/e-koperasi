# Ticket Issue System — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete ticket issue system inside e-koperasi where clients can raise tickets and IT-Ops/Admin can manage, reply, and resolve them.

**Architecture:** Full stack inside e-koperasi — Laravel models/controllers/mail, Polymorphic attachments, Inertia + Vue.js frontend with AdminLayout and ClientLayout. Queue-based email notifications.

**Tech Stack:** Laravel 12, Vue 3 (Composition API), Inertia.js, Tailwind CSS v4, Laravel Mail + Queue (database), Ziggy TypeScript routes

---

### Task 1: Database Migrations (Tickets, Replies, Attachments, Role)

**Files:**
- Create: `database/migrations/2026_06_18_000001_create_tickets_table.php`
- Create: `database/migrations/2026_06_18_000002_create_ticket_replies_table.php`
- Create: `database/migrations/2026_06_18_000003_create_attachments_table.php`
- Create: `database/migrations/2026_06_18_000004_add_itops_role_to_users.php`

- [ ] **Step 1: Create tickets migration**

```php
<?php
// database/migrations/2026_06_18_000001_create_tickets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number', 20)->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 255);
            $table->text('description');
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

- [ ] **Step 2: Create ticket_replies migration**

```php
<?php
// database/migrations/2026_06_18_000002_create_ticket_replies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
```

- [ ] **Step 3: Create polymorphic attachments migration**

```php
<?php
// database/migrations/2026_06_18_000003_create_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('attachable');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
```

- [ ] **Step 4: Add it-ops role migration** (role column is string, not DB enum)

```php
<?php
// database/migrations/2026_06_18_000004_add_itops_role_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No schema change needed — role column already exists as string
        // This migration ensures the role value 'it-ops' is recognized
        // Seeder can be added if needed
    }

    public function down(): void
    {
        //
    }
};
```

- [ ] **Step 5: Run migrations**

Run: `php artisan migrate`
Expected: 4 new tables created (tickets, ticket_replies, attachments) + jobs table if not exists

- [ ] **Step 6: Commit**

```bash
git add database/migrations/
git commit -m "feat: create tickets, ticket_replies, attachments tables"
```

---

### Task 2: Eloquent Models (Ticket, TicketReply, Attachment)

**Files:**
- Create: `app/Models/Ticket.php`
- Create: `app/Models/TicketReply.php`
- Create: `app/Models/Attachment.php`

- [ ] **Step 1: Create Ticket model**

```php
<?php
// app/Models/Ticket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'description',
        'status',
        'priority',
        'assigned_to',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT-';
        $last = static::withTrashed()
            ->where('ticket_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('ticket_number');

        if ($last) {
            $num = (int) substr($last, 4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
```

- [ ] **Step 2: Create TicketReply model**

```php
<?php
// app/Models/TicketReply.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TicketReply extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
```

- [ ] **Step 3: Create Attachment model (polymorphic)**

```php
<?php
// app/Models/Attachment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

- [ ] **Step 4: Add relationships to User model**

Add to `app/Models/User.php`:

```php
// Inside the class body:

public function tickets(): HasMany
{
    return $this->hasMany(Ticket::class, 'user_id');
}

public function assignedTickets(): HasMany
{
    return $this->hasMany(Ticket::class, 'assigned_to');
}

public function ticketReplies(): HasMany
{
    return $this->hasMany(TicketReply::class, 'user_id');
}
```

Also add `use HasMany;` import if not already present.

- [ ] **Step 5: Commit**

```bash
git add app/Models/
git commit -m "feat: add Ticket, TicketReply, Attachment models"
```

---

### Task 3: Mail Classes + Email Templates

**Files:**
- Create: `app/Mail/TicketCreatedMail.php`
- Create: `app/Mail/TicketRepliedMail.php`
- Create: `app/Mail/TicketStatusChangedMail.php`
- Create: `resources/views/emails/ticket-created.blade.php`
- Create: `resources/views/emails/ticket-reply.blade.php`
- Create: `resources/views/emails/ticket-status.blade.php`

- [ ] **Step 1: TicketCreatedMail**

```php
<?php
// app/Mail/TicketCreatedMail.php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $recipientType, // 'client' or 'staff'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'client'
            ? 'Ticket #' . $this->ticket->ticket_number . ' - Berhasil Dibuat'
            : 'Ticket Baru: #' . $this->ticket->ticket_number . ' - ' . $this->ticket->subject;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
            with: [
                'ticket' => $this->ticket,
                'recipientType' => $this->recipientType,
                'url' => $this->recipientType === 'client'
                    ? url('/tickets/' . $this->ticket->id)
                    : url('/admin/tickets/' . $this->ticket->id),
            ],
        );
    }
}
```

- [ ] **Step 2: TicketRepliedMail**

```php
<?php
// app/Mail/TicketRepliedMail.php

namespace App\Mail;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketReply $reply,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Balasan baru pada Ticket #' . $this->reply->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-reply',
            with: [
                'reply' => $this->reply,
                'ticket' => $this->reply->ticket,
                'url' => url('/tickets/' . $this->reply->ticket->id),
            ],
        );
    }
}
```

- [ ] **Step 3: TicketStatusChangedMail**

```php
<?php
// app/Mail/TicketStatusChangedMail.php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Status Ticket #' . $this->ticket->ticket_number . ' Berubah',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-status',
            with: [
                'ticket' => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'url' => url('/tickets/' . $this->ticket->id),
            ],
        );
    }
}
```

- [ ] **Step 4: Email template — ticket-created.blade.php**

```blade
{{-- resources/views/emails/ticket-created.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #fef3c7; color: #92400e; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Ticket #{{ $ticket->ticket_number }}</h2>
        </div>
        <div class="body">
            @if ($recipientType === 'client')
                <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
                <p>Ticket Anda telah berhasil dibuat dengan detail berikut:</p>
            @else
                <p>Halo Admin/IT-Ops,</p>
                <p>Ticket baru telah dibuat oleh <strong>{{ $ticket->user->name }}</strong>:</p>
            @endif

            <table style="width:100%; border-collapse:collapse; margin:16px 0;">
                <tr><td style="padding:8px; font-weight:600; width:120px">Subject</td><td style="padding:8px">{{ $ticket->subject }}</td></tr>
                <tr><td style="padding:8px; font-weight:600">Status</td><td style="padding:8px"><span class="status-badge">{{ ucfirst($ticket->status) }}</span></td></tr>
                <tr><td style="padding:8px; font-weight:600">Prioritas</td><td style="padding:8px">{{ ucfirst($ticket->priority) }}</td></tr>
            </table>

            <p><strong>Deskripsi:</strong></p>
            <p style="background:#f9fafb; padding:12px; border-radius:6px;">{{ $ticket->description }}</p>

            <p style="text-align:center; margin-top:24px;">
                <a href="{{ $url }}" class="btn">Lihat Ticket</a>
            </p>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 5: Email template — ticket-reply.blade.php**

```blade
{{-- resources/views/emails/ticket-reply.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Balasan Baru — Ticket #{{ $ticket->ticket_number }}</h2>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
            <p>Ticket <strong>#{{ $ticket->ticket_number }}</strong> (<em>{{ $ticket->subject }}</em>) mendapat balasan baru:</p>

            <div style="background:#f0fdf4; padding:16px; border-radius:8px; margin:16px 0; border-left:4px solid #059669;">
                <p style="margin:0 0 8px; font-size:13px; color:#666;">
                    <strong>{{ $reply->user->name }}</strong> ({{ ucfirst($reply->user->role) }}) — {{ $reply->created_at->format('d M Y H:i') }}
                </p>
                <p style="margin:0; white-space:pre-wrap;">{{ $reply->message }}</p>
            </div>

            <p style="text-align:center; margin-top:24px;">
                <a href="{{ $url }}" class="btn">Balas di Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 6: Email template — ticket-status.blade.php**

```blade
{{-- resources/views/emails/ticket-status.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; text-align: center; }
    .status-old { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #6b7280; text-decoration: line-through; }
    .status-new { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #d1fae5; color: #065f46; }
    .arrow { font-size: 24px; margin: 0 12px; color: #9ca3af; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Status Ticket Diperbarui</h2>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
            <p>Status ticket <strong>#{{ $ticket->ticket_number }}</strong> telah berubah:</p>

            <p style="margin:20px 0;">
                <span class="status-old">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">→</span>
                <span class="status-new">{{ ucfirst($newStatus) }}</span>
            </p>

            <p><strong>{{ $ticket->subject }}</strong></p>

            <p style="margin-top:24px;">
                <a href="{{ $url }}" class="btn">Lihat Ticket</a>
            </p>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 7: Commit**

```bash
git add app/Mail/ resources/views/emails/
git commit -m "feat: add ticket email notifications with templates"
```

---

### Task 4: Client-side Ticket Controller (Raise & View Tickets)

**Files:**
- Create: `app/Http/Controllers/TicketController.php`
- Modify: `routes/web.php` (add client ticket routes)

- [ ] **Step 1: Create TicketController for client**

```php
<?php
// app/Http/Controllers/TicketController.php

namespace App\Http\Controllers;

use App\Mail\TicketCreatedMail;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tickets = Ticket::where('user_id', $user->id)
            ->with(['replies' => function ($q) {
                $q->latest();
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Client/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Client/Tickets/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp4,mov,avi',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'pending',
        ]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                Attachment::create([
                    'attachable_id' => $ticket->id,
                    'attachable_type' => Ticket::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Send email to client
        Mail::to($ticket->user->email)->queue(
            new TicketCreatedMail($ticket, 'client')
        );

        // Send email to all admin & it-ops
        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'it-ops'])->get();
        foreach ($staffUsers as $staff) {
            Mail::to($staff->email)->queue(
                new TicketCreatedMail($ticket, 'staff')
            );
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil dibuat.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        // Ensure the ticket belongs to the current user
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        $ticket->load([
            'replies' => function ($q) {
                $q->with('user', 'attachments')->orderBy('created_at', 'asc');
            },
            'attachments',
        ]);

        return Inertia::render('Client/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup. Tidak bisa menambah balasan.']);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp4,mov,avi',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                Attachment::create([
                    'attachable_id' => $reply->id,
                    'attachable_type' => TicketReply::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // No email needed when client replies (they know they replied)
        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function close(Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== request()->user()->id) {
            abort(403);
        }

        if ($ticket->status !== 'solved') {
            return back()->withErrors(['message' => 'Hanya ticket dengan status "solved" yang bisa ditutup.']);
        }

        $ticket->update(['status' => 'close']);

        // Notify staff
        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'it-ops'])->get();
        foreach ($staffUsers as $staff) {
            Mail::to($staff->email)->queue(
                new \App\Mail\TicketStatusChangedMail($ticket, 'solved', 'close')
            );
        }

        return back()->with('success', 'Ticket berhasil ditutup.');
    }
}
```

- [ ] **Step 2: Add client ticket routes to web.php**

Add this after the existing client routes block (around line where `require __DIR__ . '/cms.php';` is):

```php
// Client Ticket Routes (authenticated clients)
Route::middleware(['auth'])->prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TicketController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\TicketController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\TicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [\App\Http\Controllers\TicketController::class, 'reply'])->name('reply');
    Route::put('/{ticket}/close', [\App\Http\Controllers\TicketController::class, 'close'])->name('close');
});
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/TicketController.php routes/web.php
git commit -m "feat: add client ticket controller with create, reply, close"
```

---

### Task 5: Admin/IT-Ops Ticket Controller (Manage All Tickets)

**Files:**
- Create: `app/Http/Controllers/Admin/TicketController.php`
- Modify: `routes/cms.php` (add admin ticket routes)

- [ ] **Step 1: Create Admin TicketController**

```php
<?php
// app/Http/Controllers/Admin/TicketController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketRepliedMail;
use App\Mail\TicketStatusChangedMail;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Ticket::query()->with(['user', 'assignedTo']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'search']),
            'staffUsers' => $staffUsers,
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load([
            'user',
            'assignedTo',
            'replies' => function ($q) {
                $q->with('user', 'attachments')->orderBy('created_at', 'asc');
            },
            'attachments',
        ]);

        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'staffUsers' => $staffUsers,
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup.']);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp4,mov,avi',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                Attachment::create([
                    'attachable_id' => $reply->id,
                    'attachable_type' => TicketReply::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Send email to client
        Mail::to($ticket->user->email)->queue(
            new TicketRepliedMail($reply)
        );

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup. Tidak bisa mengubah status.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,acknowledge,in_progress,solved,close',
        ]);

        $validTransitions = [
            'pending' => ['acknowledge'],
            'acknowledge' => ['in_progress', 'pending'],
            'in_progress' => ['solved', 'acknowledge', 'pending'],
            'solved' => ['close', 'in_progress'],
            'close' => [],
        ];

        $allowed = $validTransitions[$ticket->status] ?? [];
        if (!in_array($validated['status'], $allowed)) {
            return back()->withErrors([
                'status' => 'Tidak bisa mengubah status dari "' . $ticket->status . '" ke "' . $validated['status'] . '".'
            ]);
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        // Send email to client
        Mail::to($ticket->user->email)->queue(
            new TicketStatusChangedMail($ticket, $oldStatus, $validated['status'])
        );

        return back()->with('success', 'Status ticket berhasil diperbarui.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Ticket berhasil di-assign.');
    }

    public function destroyAttachment(Ticket $ticket, Attachment $attachment): RedirectResponse
    {
        if ($attachment->attachable_id !== $ticket->id && $attachment->attachable_type !== Ticket::class) {
            // Check if attachment belongs to a reply of this ticket
            $replyIds = $ticket->replies()->pluck('id')->toArray();
            if (!in_array($attachment->attachable_id, $replyIds)) {
                abort(403);
            }
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
```

- [ ] **Step 2: Add admin ticket routes to cms.php**

Add BEFORE the closing `});` of the admin middleware group (inside the `Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(...)` block):

```php
        // Ticket Management (admin & it-ops)
        Route::middleware('role:admin,it-ops')->prefix('tickets')->name('ticket.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
            Route::put('/{ticket}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('status');
            Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
            Route::put('/{ticket}/assign', [\App\Http\Controllers\Admin\TicketController::class, 'assign'])->name('assign');
            Route::delete('/{ticket}/attachment/{attachment}', [\App\Http\Controllers\Admin\TicketController::class, 'destroyAttachment'])->name('attachment.destroy');
        });
```

Note: The CheckRole middleware accepts `...$roles` — so `role:admin,it-ops` passes both roles.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/TicketController.php routes/cms.php
git commit -m "feat: add admin ticket controller with manage, reply, assign"
```

---

### Task 6: Vue Pages — Client Ticket List & Create

**Files:**
- Create: `resources/js/Pages/Client/Tickets/Index.vue`
- Create: `resources/js/Pages/Client/Tickets/Create.vue`

- [ ] **Step 1: Create Client Tickets/Index.vue (ticket list)**

```vue
<script setup lang="ts">
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    tickets: any;
}>();

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending',
    acknowledge: 'Acknowledge',
    in_progress: 'In Progress',
    solved: 'Solved',
    close: 'Closed',
};

const priorityColors: Record<string, string> = {
    low: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    medium: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <ClientLayout title="Ticket Saya">
        <Head title="Ticket Saya - e-Koperasi" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ticket Saya</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Kelola permintaan bantuan dan laporan masalah Anda.</p>
                </div>
                <Link href="/tickets/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Ticket Baru
                </Link>
            </div>

            <!-- Ticket List -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Ticket</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Subject</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Prioritas</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ ticket.ticket_number }}</td>
                                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white max-w-xs truncate">{{ ticket.subject }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="statusColors[ticket.status] || ''">
                                        {{ statusLabels[ticket.status] || ticket.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="priorityColors[ticket.priority] || ''">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs">{{ formatDate(ticket.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link :href="'/tickets/' + ticket.id"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium text-xs">
                                        Detail
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!tickets.data?.length">
                                <td colspan="6" class="px-5 py-12 text-center text-neutral-400 dark:text-neutral-500">
                                    <p class="text-lg mb-2">Belum ada ticket</p>
                                    <p class="text-sm">Buat ticket baru untuk melaporkan masalah atau mengajukan bantuan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="tickets.links?.length > 3" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">← Sebelumnya</Link>
                    <span v-else class="text-neutral-400">← Sebelumnya</span>
                    <span class="text-neutral-500 dark:text-neutral-400">Halaman {{ tickets.current_page }} dari {{ tickets.last_page }}</span>
                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">Selanjutnya →</Link>
                    <span v-else class="text-neutral-400">Selanjutnya →</span>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 2: Create Client Tickets/Create.vue**

```vue
<script setup lang="ts">
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    subject: '',
    description: '',
    priority: 'medium',
});

const attachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

function onFileSelect(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    attachmentErrors.value = [];
    const valid: File[] = [];

    Array.from(input.files).forEach((file) => {
        if (file.size > MAX_FILE_SIZE) {
            attachmentErrors.value.push(`"${file.name}" melebihi 10MB.`);
            return;
        }
        if (!ALLOWED_TYPES.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|mp4|mov|avi)$/i)) {
            attachmentErrors.value.push(`"${file.name}" format tidak didukung.`);
            return;
        }
        valid.push(file);
    });

    attachments.value = [...attachments.value, ...valid];
    input.value = '';
}

function removeAttachment(index: number) {
    attachments.value.splice(index, 1);
}

function submit() {
    form.post('/tickets', {
        onSuccess: () => {
            form.reset();
            attachments.value = [];
        },
    });
}

// Upload attachments after ticket is created
function attachFiles(ticketId: string) {
    if (!attachments.value.length) return;
    const data = new FormData();
    attachments.value.forEach((file) => {
        data.append('attachments[]', file);
    });
    router.post(`/tickets/${ticketId}/attachments`, data, {
        preserveScroll: true,
    });
}
</script>

<template>
    <ClientLayout title="Buat Ticket Baru">
        <Head title="Buat Ticket Baru - e-Koperasi" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-3xl">
            <div>
                <Link href="/tickets" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Ticket
                </Link>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Buat Ticket Baru</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Laporkan masalah atau ajukan bantuan teknis.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6 space-y-6">
                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Subject <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.subject"
                        type="text"
                        placeholder="Contoh: Tidak bisa login ke dashboard"
                        class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition-all"
                        :class="form.errors.subject ? 'border-red-400 focus:ring-2 focus:ring-red-500' : 'border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-emerald-500'"
                    />
                    <p v-if="form.errors.subject" class="mt-1 text-xs text-red-500">{{ form.errors.subject }}</p>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Prioritas <span class="text-red-500">*</span></label>
                    <select
                        v-model="form.priority"
                        class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
                    >
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                    <p v-if="form.errors.priority" class="mt-1 text-xs text-red-500">{{ form.errors.priority }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        placeholder="Jelaskan masalah yang Anda alami secara detail..."
                        class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition-all resize-y"
                        :class="form.errors.description ? 'border-red-400 focus:ring-2 focus:ring-red-500' : 'border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-emerald-500'"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                </div>

                <!-- Attachments -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Lampiran (opsional)</label>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 px-4 py-2.5 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-500 dark:text-neutral-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                            </svg>
                            Pilih File (max 10MB)
                            <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                        </label>
                        <span class="text-xs text-neutral-400">PDF, JPG/PNG, MP4/MOV/AVI</span>
                    </div>

                    <!-- File list -->
                    <ul v-if="attachments.length" class="mt-3 space-y-1.5">
                        <li v-for="(file, i) in attachments" :key="i" class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                            <span class="flex-1 truncate">{{ file.name }}</span>
                            <span class="text-xs text-neutral-400">({{ (file.size / 1024 / 1024).toFixed(1) }} MB)</span>
                            <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    </ul>

                    <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="mt-1 text-xs text-red-500">{{ err }}</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Mengirim...' : 'Kirim Ticket' }}
                    </button>
                    <Link href="/tickets" class="px-4 py-2.5 text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors">Batal</Link>
                </div>
            </form>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Client/Tickets/
git commit -m "feat: add client ticket list and create pages"
```

---

### Task 7: Vue Pages — Client Ticket Detail & Reply

**Files:**
- Create: `resources/js/Pages/Client/Tickets/Show.vue`

- [ ] **Step 1: Create Client Tickets/Show.vue**

```vue
<script setup lang="ts">
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    ticket: any;
}>();

const replyForm = useForm({ message: '' });
const replyAttachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending', acknowledge: 'Acknowledge', in_progress: 'In Progress', solved: 'Solved', close: 'Closed',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

function onFileSelect(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    attachmentErrors.value = [];
    Array.from(input.files).forEach((file) => {
        if (file.size > MAX_FILE_SIZE) {
            attachmentErrors.value.push(`"${file.name}" melebihi 10MB.`);
            return;
        }
        if (!ALLOWED_TYPES.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|mp4|mov|avi)$/i)) {
            attachmentErrors.value.push(`"${file.name}" format tidak didukung.`);
            return;
        }
        replyAttachments.value.push(file);
    });
    input.value = '';
}

function removeAttachment(index: number) {
    replyAttachments.value.splice(index, 1);
}

function submitReply() {
    if (!replyForm.message.trim() && !replyAttachments.value.length) return;

    const formData = new FormData();
    formData.append('message', replyForm.message);
    replyAttachments.value.forEach((f) => formData.append('attachments[]', f));

    router.post(`/tickets/${props.ticket.id}/reply`, formData, {
        onSuccess: () => {
            replyForm.reset();
            replyAttachments.value = [];
        },
    });
}

function closeTicket() {
    if (!confirm('Yakin ingin menutup ticket ini?')) return;
    router.put(`/tickets/${props.ticket.id}/close`);
}

function getFileUrl(path: string) {
    return '/storage/' + path;
}

function isImage(mime: string) {
    return mime?.startsWith('image/');
}

function isVideo(mime: string) {
    return mime?.startsWith('video/');
}

function getFileIcon(name: string) {
    if (name?.endsWith('.pdf')) return '📄';
    if (name?.match(/\.(mp4|mov|avi)$/i)) return '🎬';
    return '📎';
}
</script>

<template>
    <ClientLayout :title="'Ticket #' + ticket.ticket_number">
        <Head :title="ticket.ticket_number + ' - e-Koperasi'" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl">
            <!-- Back link -->
            <Link href="/tickets" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Ticket
            </Link>

            <!-- Ticket Header -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-mono text-xs font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.ticket_number }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="statusColors[ticket.status] || ''">{{ statusLabels[ticket.status] || ticket.status }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 capitalize">{{ ticket.priority }} priority</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-neutral-900 dark:text-white">{{ ticket.subject }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Dibuat {{ formatDate(ticket.created_at) }}</p>
                    </div>

                    <!-- Close button (only if solved) -->
                    <button v-if="ticket.status === 'solved'" @click="closeTicket"
                        class="shrink-0 px-4 py-2 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-lg transition-colors">
                        Tutup Ticket
                    </button>
                </div>

                <!-- Assigned to -->
                <p v-if="ticket.assigned_to" class="mt-3 text-xs text-neutral-500 dark:text-neutral-400">
                    Ditangani oleh: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ ticket.assigned_to?.name || '-' }}</span>
                </p>

                <!-- Description -->
                <div class="mt-4 p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <!-- Ticket Attachments -->
                <div v-if="ticket.attachments?.length" class="mt-4 flex flex-wrap gap-2">
                    <template v-for="att in ticket.attachments" :key="att.id">
                        <a v-if="isImage(att.mime_type)" :href="getFileUrl(att.file_path)" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            🖼️ {{ att.file_name }}
                        </a>
                        <a v-else :href="getFileUrl(att.file_path)" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            {{ getFileIcon(att.file_name) }} {{ att.file_name }}
                        </a>
                    </template>
                </div>
            </div>

            <!-- Replies Thread -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Percakapan ({{ ticket.replies?.length || 0 }})</h3>

                <!-- Initial ticket as first message -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                            {{ (ticket.user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ticket.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(ticket.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <!-- Replies -->
                <div v-for="reply in ticket.replies" :key="reply.id"
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5"
                    :class="reply.user_id !== ticket.user_id ? 'border-l-4 border-l-emerald-500' : ''">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-400 text-sm font-bold">
                            {{ (reply.user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ reply.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(reply.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ reply.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ reply.message }}</p>

                    <!-- Reply Attachments -->
                    <div v-if="reply.attachments?.length" class="mt-3 flex flex-wrap gap-2">
                        <a v-for="att in reply.attachments" :key="att.id"
                            :href="getFileUrl(att.file_path)" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            {{ isImage(att.mime_type) ? '🖼️' : isVideo(att.mime_type) ? '🎬' : '📎' }} {{ att.file_name }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reply Form (not if closed) -->
            <div v-if="ticket.status !== 'close'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-4">Tambah Balasan</h3>

                <form @submit.prevent="submitReply">
                    <textarea
                        v-model="replyForm.message"
                        rows="4"
                        placeholder="Tulis balasan Anda..."
                        class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-y"
                    ></textarea>

                    <!-- Attachments -->
                    <div class="mt-3">
                        <label class="inline-flex items-center gap-2 px-4 py-2 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-500 dark:text-neutral-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                            </svg>
                            Lampirkan File
                            <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                        </label>
                    </div>

                    <ul v-if="replyAttachments.length" class="mt-2 space-y-1">
                        <li v-for="(file, i) in replyAttachments" :key="i" class="flex items-center gap-2 text-sm text-neutral-500">
                            <span>{{ file.name }}</span>
                            <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700 text-xs">hapus</button>
                        </li>
                    </ul>
                    <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="mt-1 text-xs text-red-500">{{ err }}</p>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" :disabled="replyForm.processing || (!replyForm.message.trim() && !replyAttachments.length)"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                            <svg v-if="replyForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ replyForm.processing ? 'Mengirim...' : 'Kirim Balasan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Closed notice -->
            <div v-else class="bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Ticket ini sudah ditutup. Tidak dapat menambah balasan.</p>
            </div>
        </div>
    </ClientLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Client/Tickets/Show.vue
git commit -m "feat: add client ticket detail page with reply thread"
```

---

### Task 8: Vue Pages — Admin/IT-Ops Ticket List

**Files:**
- Create: `resources/js/Pages/Admin/Tickets/Index.vue`
- Modify: `resources/js/Layouts/AdminLayout.vue` (add ticket nav link)

- [ ] **Step 1: Create Admin/Tickets/Index.vue**

```vue
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    tickets: any;
    filters: { status?: string; priority?: string; assigned_to?: string; search?: string };
    staffUsers: { id: string; name: string }[];
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const priorityFilter = ref(props.filters?.priority || '');
const assignedFilter = ref(props.filters?.assigned_to || '');

function applyFilters() {
    router.get('/admin/tickets', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        priority: priorityFilter.value || undefined,
        assigned_to: assignedFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 400);
});

watch([statusFilter, priorityFilter, assignedFilter], applyFilters);

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending', acknowledge: 'Acknowledge', in_progress: 'In Progress', solved: 'Solved', close: 'Closed',
};

const priorityColors: Record<string, string> = {
    low: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    medium: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const statusCounts = computed(() => {
    const counts: Record<string, number> = { pending: 0, acknowledge: 0, in_progress: 0, solved: 0, close: 0 };
    if (props.tickets?.data) {
        props.tickets.data.forEach((t: any) => {
            counts[t.status] = (counts[t.status] || 0) + 1;
        });
    }
    return counts;
});
</script>

<template>
    <AdminLayout title="Ticket Management">
        <Head title="Ticket Management - e-Koperasi CMS" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ticket Management</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Kelola semua ticket dari client.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input v-model="search" type="text" placeholder="Cari ticket, client..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 outline-none focus:ring-2 focus:ring-emerald-500 transition-all" />
                </div>
                <select v-model="statusFilter"
                    class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="acknowledge">Acknowledge</option>
                    <option value="in_progress">In Progress</option>
                    <option value="solved">Solved</option>
                    <option value="close">Closed</option>
                </select>
                <select v-model="priorityFilter"
                    class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Prioritas</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                <select v-model="assignedFilter"
                    class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Assignee</option>
                    <option value="unassigned">Unassigned</option>
                    <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                </select>
            </div>

            <!-- Ticket Table -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Ticket</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Client</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Subject</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Prioritas</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Assignee</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs font-medium text-neutral-400">{{ ticket.ticket_number }}</td>
                                <td class="px-5 py-3">
                                    <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ ticket.user?.name }}</div>
                                    <div class="text-xs text-neutral-400">{{ ticket.user?.email }}</div>
                                </td>
                                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white max-w-xs truncate">{{ ticket.subject }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold whitespace-nowrap" :class="statusColors[ticket.status] || ''">{{ statusLabels[ticket.status] || ticket.status }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="priorityColors[ticket.priority] || ''">{{ ticket.priority }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-neutral-500 dark:text-neutral-400">{{ ticket.assigned_to?.name || '-' }}</td>
                                <td class="px-5 py-3 text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(ticket.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link :href="'/admin/tickets/' + ticket.id"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium text-xs">
                                        Detail
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!tickets.data?.length">
                                <td colspan="8" class="px-5 py-12 text-center text-neutral-400 dark:text-neutral-500">
                                    <p class="text-lg mb-2">Tidak ada ticket</p>
                                    <p class="text-sm">Belum ada ticket yang masuk.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="tickets.links?.length > 3" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">← Sebelumnya</Link>
                    <span v-else class="text-neutral-400">← Sebelumnya</span>
                    <span class="text-neutral-500 dark:text-neutral-400">Halaman {{ tickets.current_page }} dari {{ tickets.last_page }}</span>
                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">Selanjutnya →</Link>
                    <span v-else class="text-neutral-400">Selanjutnya →</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 2: Add ticket nav to AdminLayout.vue**

In `AdminLayout.vue`, add after the User Management link (around line 150-160 area). Find the existing nav list and add:

```html
                    <!-- Ticket Management (admin & it-ops) -->
                    <p v-if="user?.role === 'admin' || user?.role === 'it-ops'"
                       class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        Support
                    </p>
                    <Link
                        v-if="user?.role === 'admin' || user?.role === 'it-ops'"
                        href="/admin/tickets"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url.startsWith('/admin/tickets')
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        Tickets
                    </Link>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Tickets/Index.vue resources/js/Layouts/AdminLayout.vue
git commit -m "feat: add admin ticket list page with filters and sidebar nav"
```

---

### Task 9: Vue Pages — Admin/IT-Ops Ticket Detail

**Files:**
- Create: `resources/js/Pages/Admin/Tickets/Show.vue`

- [ ] **Step 1: Create Admin/Tickets/Show.vue**

```vue
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    ticket: any;
    staffUsers: { id: string; name: string }[];
}>();

const replyMessage = ref('');
const replyAttachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending', acknowledge: 'Acknowledge', in_progress: 'In Progress', solved: 'Solved', close: 'Closed',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

function onFileSelect(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    attachmentErrors.value = [];
    Array.from(input.files).forEach((file) => {
        if (file.size > MAX_FILE_SIZE) {
            attachmentErrors.value.push(`"${file.name}" melebihi 10MB.`);
            return;
        }
        if (!ALLOWED_TYPES.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|mp4|mov|avi)$/i)) {
            attachmentErrors.value.push(`"${file.name}" format tidak didukung.`);
            return;
        }
        replyAttachments.value.push(file);
    });
    input.value = '';
}

function removeAttachment(index: number) {
    replyAttachments.value.splice(index, 1);
}

function changeStatus(status: string) {
    const form = new FormData();
    form.append('_method', 'PUT');
    form.append('status', status);
    router.post(`/admin/tickets/${props.ticket.id}/status`, form, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['ticket'] }),
    });
}

function assignUser(userId: string) {
    router.put(`/admin/tickets/${props.ticket.id}/assign`, { assigned_to: userId || null }, {
        preserveScroll: true,
    });
}

function submitReply() {
    if (!replyMessage.value.trim() && !replyAttachments.value.length) return;

    const formData = new FormData();
    formData.append('message', replyMessage.value);
    replyAttachments.value.forEach((f) => formData.append('attachments[]', f));

    router.post(`/admin/tickets/${props.ticket.id}/reply`, formData, {
        onSuccess: () => {
            replyMessage.value = '';
            replyAttachments.value = [];
        },
        preserveScroll: true,
    });
}

function getFileUrl(path: string) {
    return '/storage/' + path;
}

function isImage(mime: string) { return mime?.startsWith('image/'); }
function isVideo(mime: string) { return mime?.startsWith('video/'); }
function getFileIcon(name: string) {
    if (name?.endsWith('.pdf')) return '📄';
    if (name?.match(/\.(mp4|mov|avi)$/i)) return '🎬';
    return '📎';
}

// Available next statuses based on current status
const nextStatuses: Record<string, { value: string; label: string }[]> = {
    pending: [{ value: 'acknowledge', label: 'Acknowledge' }],
    acknowledge: [{ value: 'in_progress', label: 'Proses' }, { value: 'pending', label: 'Kembali ke Pending' }],
    in_progress: [{ value: 'solved', label: 'Selesai' }, { value: 'acknowledge', label: 'Kembali ke Acknowledge' }],
    solved: [{ value: 'close', label: 'Tutup Ticket' }, { value: 'in_progress', label: 'Buka Kembali' }],
    close: [],
};

const availableStatuses = nextStatuses[props.ticket.status] || [];
</script>

<template>
    <AdminLayout :title="'Ticket #' + ticket.ticket_number">
        <Head :title="ticket.ticket_number + ' - Admin'" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl">
            <!-- Back link -->
            <Link href="/admin/tickets" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Ticket
            </Link>

            <!-- Ticket Header -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <span class="font-mono text-xs font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.ticket_number }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="statusColors[ticket.status] || ''">{{ statusLabels[ticket.status] || ticket.status }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 capitalize">{{ ticket.priority }} priority</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-neutral-900 dark:text-white">{{ ticket.subject }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Oleh {{ ticket.user?.name }} ({{ ticket.user?.email }}) — {{ formatDate(ticket.created_at) }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Status buttons -->
                        <button v-for="s in availableStatuses" :key="s.value" @click="changeStatus(s.value)"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                            :class="s.value === 'close' ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' :
                                   s.value === 'solved' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                   'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300'">
                            {{ s.label }}
                        </button>
                    </div>
                </div>

                <!-- Assignee -->
                <div class="mt-4 flex items-center gap-3">
                    <label class="text-xs text-neutral-500 dark:text-neutral-400">Ditangani oleh:</label>
                    <select @change="assignUser(($event.target as HTMLSelectElement).value)"
                        class="px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Pilih Staff —</option>
                        <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id" :selected="ticket.assigned_to?.id === staff.id">
                            {{ staff.name }}
                        </option>
                    </select>
                    <span v-if="ticket.assigned_to" class="text-xs text-neutral-400">({{ ticket.assigned_to?.name }})</span>
                </div>

                <!-- Description -->
                <div class="mt-4 p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-wider">Deskripsi</p>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <!-- Attachments -->
                <div v-if="ticket.attachments?.length" class="mt-4 flex flex-wrap gap-2">
                    <a v-for="att in ticket.attachments" :key="att.id"
                        :href="getFileUrl(att.file_path)" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                        {{ isImage(att.mime_type) ? '🖼️' : isVideo(att.mime_type) ? '🎬' : getFileIcon(att.file_name) }} {{ att.file_name }}
                    </a>
                </div>
            </div>

            <!-- Replies Thread -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Percakapan ({{ ticket.replies?.length || 0 }})</h3>

                <!-- Initial ticket -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                            {{ (ticket.user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ticket.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(ticket.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <!-- Replies -->
                <div v-for="reply in ticket.replies" :key="reply.id"
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5"
                    :class="reply.user_id !== ticket.user_id ? 'border-l-4 border-l-emerald-500' : ''">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full"
                            :class="reply.user_id !== ticket.user_id ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
                            :class="{'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400': reply.user_id !== ticket.user_id}">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ reply.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(reply.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ reply.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ reply.message }}</p>

                    <div v-if="reply.attachments?.length" class="mt-3 flex flex-wrap gap-2">
                        <a v-for="att in reply.attachments" :key="att.id"
                            :href="getFileUrl(att.file_path)" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            {{ isImage(att.mime_type) ? '🖼️' : isVideo(att.mime_type) ? '🎬' : getFileIcon(att.file_name) }} {{ att.file_name }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reply Form -->
            <div v-if="ticket.status !== 'close'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-4">Balas Ticket</h3>

                <form @submit.prevent="submitReply">
                    <textarea v-model="replyMessage" rows="4" placeholder="Tulis balasan Anda..."
                        class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-y"></textarea>

                    <div class="mt-3">
                        <label class="inline-flex items-center gap-2 px-4 py-2 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-500 dark:text-neutral-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                            </svg>
                            Lampirkan File
                            <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                        </label>
                    </div>

                    <ul v-if="replyAttachments.length" class="mt-2 space-y-1">
                        <li v-for="(file, i) in replyAttachments" :key="i" class="flex items-center gap-2 text-sm text-neutral-500">
                            <span>{{ file.name }}</span>
                            <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700 text-xs">hapus</button>
                        </li>
                    </ul>
                    <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="mt-1 text-xs text-red-500">{{ err }}</p>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" :disabled="!replyMessage.trim() && !replyAttachments.length"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                            Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>

            <div v-else class="bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Ticket ini sudah ditutup.</p>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Admin/Tickets/Show.vue
git commit -m "feat: add admin ticket detail page with reply & status management"
```

---

### Task 10: Dashboard Stats & Mail Configuration

**Files:**
- Modify: `resources/js/Pages/Client/Dashboard.vue` (add ticket stats)
- Modify: `resources/js/Pages/Admin/Dashboard.vue` (add ticket stats)
- Create: `app/Http/Controllers/Admin/DashboardController.php` (add ticket stats to admin dashboard)
- Modify: `app/Http/Controllers/Client/DashboardController.php` (add ticket stats)
- Modify: `routes/cms.php` (if needed)

- [ ] **Step 1: Add ticket stats to Admin DashboardController**

In `app/Http/Controllers/Admin/ClientController.php`, in the `dashboard()` method, add after existing stats:

```php
        // Ticket statistics
        $ticketStats = [
            'total' => \App\Models\Ticket::count(),
            'pending' => \App\Models\Ticket::where('status', 'pending')->count(),
            'in_progress' => \App\Models\Ticket::where('status', 'in_progress')->count(),
            'solved' => \App\Models\Ticket::where('status', 'solved')->count(),
            'close' => \App\Models\Ticket::where('status', 'close')->count(),
        ];
```

And add `'ticketStats' => $ticketStats` to the returned Inertia render array.

- [ ] **Step 2: Add ticket stats to Client DashboardController**

In `app/Http/Controllers/Client/DashboardController.php`, add:

```php
        $userId = $request->user()->id;
        $ticketStats = [
            'total' => \App\Models\Ticket::where('user_id', $userId)->count(),
            'pending' => \App\Models\Ticket::where('user_id', $userId)->where('status', 'pending')->count(),
            'in_progress' => \App\Models\Ticket::where('user_id', $userId)->where('status', 'in_progress')->count(),
            'solved' => \App\Models\Ticket::where('user_id', $userId)->where('status', 'solved')->count(),
            'close' => \App\Models\Ticket::where('user_id', $userId)->where('status', 'close')->count(),
        ];
```

And add `'ticketStats' => $ticketStats` to the returned Inertia render array.

- [ ] **Step 3: Add ticket section to Client Dashboard.vue**

In `resources/js/Pages/Client/Dashboard.vue`, add after the subscription section or payments section:

```html
            <!-- Ticket Stats -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Ticket Saya</h3>
                    <Link href="/tickets" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Lihat Semua</Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ ticketStats?.total || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Total</p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ ticketStats?.pending || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Pending</p>
                    </div>
                    <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ ticketStats?.in_progress || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Diproses</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ ticketStats?.solved || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Selesai</p>
                    </div>
                </div>
            </div>
```

- [ ] **Step 4: Add ticket stats to Admin Dashboard.vue**

Add ticket stat cards to `resources/js/Pages/Admin/Dashboard.vue` after the existing stat cards or as a new row:

```html
            <!-- Ticket Stats -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Ticket Support</h3>
                    <Link href="/admin/tickets" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Kelola Ticket</Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-xl font-bold text-neutral-900 dark:text-white">{{ ticketStats?.total || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Total</p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ ticketStats?.pending || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Pending</p>
                    </div>
                    <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ ticketStats?.in_progress || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Diproses</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ ticketStats?.solved || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Selesai</p>
                    </div>
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-xl font-bold text-neutral-600 dark:text-neutral-400">{{ ticketStats?.close || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Closed</p>
                    </div>
                </div>
            </div>
```

- [ ] **Step 5: Add Client ticket nav in ClientLayout.vue**

In `ClientLayout.vue`, add to `navItems` array (around line 30-33):

```js
    { key: 'tickets', label: 'Ticket', href: '/tickets', icon: 'ticket' },
```

And add the ticket SVG icon to the `icons` record in the `SidebarContent` component (around line 133-137):

```js
            ticket: 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
```

- [ ] **Step 6: Setup mail config queue**

Run: `php artisan queue:table`
Run: `php artisan migrate`

Add `.env` mail configuration instructions:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@e-koperasi.com"
MAIL_FROM_NAME="e-Koperasi Support"
```

- [ ] **Step 7: Run queue worker for email delivery**

Run: `php artisan queue:work --queue=default`

Note: This should be run as a background process in production (supervisor).

- [ ] **Step 8: Create storage symlink (if not already)**

Run: `php artisan storage:link`

- [ ] **Step 9: Commit**

```bash
git add resources/js/Pages/Client/Dashboard.vue resources/js/Pages/Admin/Dashboard.vue resources/js/Layouts/ClientLayout.vue
git commit -m "feat: add ticket stats to dashboards and client nav"
```

---

### Task 11: Generate Laravel Wayfinder Route Definitions

**Files:**
- Create/Modify: `resources/js/routes/client/tickets.ts` (auto-generated by wayfinder)
- Create/Modify: `resources/js/routes/admin/tickets.ts` (auto-generated by wayfinder)

The project uses **Laravel Wayfinder** for typed TypeScript routes. Wayfinder auto-generates route definitions from Laravel named routes — the generated files live under `resources/js/routes/` and follow the project's existing pattern.

- [ ] **Step 1: Regenerate Wayfinder definitions for all new routes**

The new routes added in Tasks 4-5 (`tickets.*` and `admin.ticket.*`) are Laravel named routes — Wayfinder picks them up automatically when regenerated.

Run: `php artisan wayfinder:generate`

This will create (or update):
- `resources/js/routes/client/tickets.ts` — for client ticket routes (index, create, store, show, reply, close)
- `resources/js/routes/admin/tickets.ts` — for admin ticket routes (index, show, status, reply, assign)

If the tool generates them as part of existing files (e.g. appended to `client/index.ts` and `admin/index.ts`), that's fine — follow whatever Wayfinder generates.

- [ ] **Step 2: Verify all routes work**

Run: `php artisan route:list | grep ticket`
Expected output showing all ticket routes with correct middleware (auth, role:admin/it-ops where applicable).

- [ ] **Step 3: Commit**

```bash
git add resources/js/routes/
git commit -m "feat: add wayfinder route definitions for ticket routes"
```
