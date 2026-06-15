<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { ArrowRight, Copy, Check, Eye, EyeOff, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { ref, computed, nextTick, watch } from 'vue';

const props = defineProps<{
    demoAccounts: { role: string; email: string; pin: string }[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);

const form = useForm({
    name: '',
    role: '',
    cooperative_name: '',
    member_count: '',
    whatsapp: '',
    message: '',
});

const errorSummaryRef = ref<HTMLDivElement | null>(null);
const fieldRefs = ref<Record<string, HTMLElement | null>>({});

function setFieldRef(name: string) {
    return (el: Element | null) => {
        fieldRefs.value[name] = el as HTMLElement | null;
    };
}

const submit = () => {
    form.post('/demo', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            nextTick(() => {
                const firstError = Object.keys(form.errors)[0];
                if (firstError && fieldRefs.value[firstError]) {
                    fieldRefs.value[firstError]?.focus();
                } else if (errorSummaryRef.value) {
                    errorSummaryRef.value.focus();
                }
            });
        },
    });
};

const errorCount = computed(() => Object.keys(form.errors).length);
const errorMessages = computed(() => Object.entries(form.errors));

const showCredentials = ref(false);
const copiedIdx = ref<number | null>(null);

function copy(text: string, idx: number) {
    navigator.clipboard.writeText(text);
    copiedIdx.value = idx;
    setTimeout(() => (copiedIdx.value = null), 1500);
}
</script>

<template>
    <SiteLayout title="Coba Demo e-Koperasi - Eksplorasi Sandbox Gratis">
        <!-- Hero -->
        <section class="bg-gradient-to-b from-primary-50 to-white dark:from-primary-900/20 dark:to-neutral-950">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold mb-4">
                    GRATIS · TANPA KARTU KREDIT
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900 dark:text-white">
                    Coba e-Koperasi Sekarang
                </h1>
                <p class="mt-6 text-lg text-neutral-600 dark:text-neutral-300">
                    Eksplorasi sandbox demo dengan 6 akun role berbeda. Atau request demo 1-on-1 dengan tim kami.
                </p>
            </div>
        </section>

        <!-- Sandbox Quick Access -->
        <section class="py-16 bg-white dark:bg-neutral-950">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-8 lg:p-12">
                    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Sandbox Demo</h2>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">Login dengan salah satu akun di bawah untuk explore.</p>
                        </div>
                    </div>

                    <button
                        @click="showCredentials = !showCredentials"
                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium flex items-center gap-1 mb-4 cursor-pointer"
                    >
                        <component :is="showCredentials ? EyeOff : Eye" class="h-4 w-4" />
                        {{ showCredentials ? 'Sembunyikan' : 'Tampilkan' }} kredensial
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div
                            v-for="(acc, idx) in demoAccounts"
                            :key="acc.email"
                            class="flex items-center justify-between p-3 rounded-lg border border-neutral-100 dark:border-neutral-700 hover:border-primary-200 dark:hover:border-primary-500 transition"
                        >
                            <div>
                                <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">{{ acc.role }}</p>
                                <p class="text-sm font-mono text-neutral-700 dark:text-neutral-200 mt-1">{{ acc.email }}</p>
                                <p v-if="showCredentials" class="text-sm font-mono text-neutral-500 dark:text-neutral-400">PIN: {{ acc.pin }}</p>
                            </div>
                            <button
                                @click="copy(acc.email, idx)"
                                class="p-2 rounded-md hover:bg-neutral-50 transition"
                                :aria-label="'Copy ' + acc.email"
                            >
                                <Check v-if="copiedIdx === idx" class="h-4 w-4 text-green-600" />
                                <Copy v-else class="h-4 w-4 text-neutral-400" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Consultation Form -->
        <section id="konsultasi" class="py-16 bg-neutral-50 dark:bg-neutral-900">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                        Request Demo 1-on-1
                    </h2>
                    <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                        Tim kami akan menghubungi Anda dalam 1x24 jam via WhatsApp.
                    </p>
                </div>

                <div v-if="flashSuccess" role="alert" class="mb-6 p-4 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3">
                    <CheckCircle2 class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <span>{{ flashSuccess }}</span>
                </div>

                <form @submit.prevent="submit" novalidate class="bg-white dark:bg-neutral-800 rounded-xl p-8 border border-neutral-100 dark:border-neutral-700 space-y-4">
                    <div
                        v-if="errorCount > 0"
                        ref="errorSummaryRef"
                        tabindex="-1"
                        role="alert"
                        aria-live="assertive"
                        class="p-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3"
                    >
                        <AlertCircle class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <div class="text-sm">
                            <p class="font-semibold">
                                Mohon perbaiki {{ errorCount }} kesalahan di bawah ini:
                            </p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                <li v-for="[field, msg] in errorMessages" :key="field">
                                    <a
                                        :href="'#field-' + field"
                                        class="underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
                                    >{{ msg }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="field-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                                Nama Lengkap <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input
                                :ref="setFieldRef('name')"
                                id="field-name"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                :aria-invalid="!!form.errors.name"
                                :aria-describedby="form.errors.name ? 'error-name' : undefined"
                                class="w-full px-3 py-2 rounded-md border focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="form.errors.name ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800'"
                            />
                            <p v-if="form.errors.name" id="error-name" class="text-xs text-red-700 mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" aria-hidden="true" />
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div>
                            <label for="field-role" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                                Jabatan <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input
                                :ref="setFieldRef('role')"
                                id="field-role"
                                v-model="form.role"
                                type="text"
                                required
                                placeholder="cth: Ketua, Admin, dll"
                                autocomplete="organization-title"
                                :aria-invalid="!!form.errors.role"
                                :aria-describedby="form.errors.role ? 'error-role' : undefined"
                                class="w-full px-3 py-2 rounded-md border focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="form.errors.role ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800'"
                            />
                            <p v-if="form.errors.role" id="error-role" class="text-xs text-red-700 mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" aria-hidden="true" />
                                {{ form.errors.role }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="field-cooperative_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                            Nama Koperasi <span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <input
                            :ref="setFieldRef('cooperative_name')"
                            id="field-cooperative_name"
                            v-model="form.cooperative_name"
                            type="text"
                            required
                            autocomplete="organization"
                            :aria-invalid="!!form.errors.cooperative_name"
                            :aria-describedby="form.errors.cooperative_name ? 'error-cooperative_name' : undefined"
                            class="w-full px-3 py-2 rounded-md border focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            :class="form.errors.cooperative_name ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800'"
                        />
                        <p v-if="form.errors.cooperative_name" id="error-cooperative_name" class="text-xs text-red-700 mt-1 flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" aria-hidden="true" />
                            {{ form.errors.cooperative_name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="field-member_count" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                                Jumlah Anggota <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <select
                                :ref="setFieldRef('member_count')"
                                id="field-member_count"
                                v-model="form.member_count"
                                required
                                :aria-invalid="!!form.errors.member_count"
                                :aria-describedby="form.errors.member_count ? 'error-member_count' : undefined"
                                class="w-full px-3 py-2 rounded-md border focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="form.errors.member_count ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800'"
                            >
                                <option value="">Pilih range</option>
                                <option value="< 100">&lt; 100</option>
                                <option value="100-500">100 - 500</option>
                                <option value="500-1000">500 - 1000</option>
                                <option value="1000-5000">1000 - 5000</option>
                                <option value="> 5000">&gt; 5000</option>
                            </select>
                            <p v-if="form.errors.member_count" id="error-member_count" class="text-xs text-red-700 mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" aria-hidden="true" />
                                {{ form.errors.member_count }}
                            </p>
                        </div>
                        <div>
                            <label for="field-whatsapp" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                                WhatsApp <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input
                                :ref="setFieldRef('whatsapp')"
                                id="field-whatsapp"
                                v-model="form.whatsapp"
                                type="tel"
                                required
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="0812-3456-7890"
                                :aria-invalid="!!form.errors.whatsapp"
                                :aria-describedby="form.errors.whatsapp ? 'error-whatsapp' : undefined"
                                class="w-full px-3 py-2 rounded-md border focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="form.errors.whatsapp ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800'"
                            />
                            <p v-if="form.errors.whatsapp" id="error-whatsapp" class="text-xs text-red-700 mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" aria-hidden="true" />
                                {{ form.errors.whatsapp }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="field-message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                            Pesan (opsional)
                        </label>
                        <textarea
                            id="field-message"
                            v-model="form.message"
                            rows="4"
                            placeholder="Ceritakan kebutuhan koperasi Anda..."
                            class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <span class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin" aria-hidden="true" />
                            Mengirim...
                        </span>
                        <span v-else class="flex items-center gap-2">
                            Kirim Request
                            <ArrowRight class="h-4 w-4" />
                        </span>
                    </button>

                    <p class="text-xs text-neutral-500 text-center">
                        Dengan mengirim form ini, Anda setuju dengan
                        <Link href="/privacy" class="underline hover:text-primary-600">kebijakan privasi</Link> kami.
                    </p>
                </form>
            </div>
        </section>
    </SiteLayout>
</template>
