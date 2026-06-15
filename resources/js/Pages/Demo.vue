<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { ArrowRight, Copy, Check, Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    demoAccounts: { role: string; email: string; pin: string }[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).errors?.submission);

const form = useForm({
    name: '',
    role: '',
    cooperative_name: '',
    member_count: '',
    whatsapp: '',
    message: '',
});

const submit = () => {
    form.post('/demo', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

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
        <section class="bg-gradient-to-b from-primary-50 to-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold mb-4">
                    GRATIS · TANPA KARTU KREDIT
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900">
                    Coba e-Koperasi Sekarang
                </h1>
                <p class="mt-6 text-lg text-neutral-600">
                    Eksplorasi sandbox demo dengan 6 akun role berbeda. Atau request demo 1-on-1 dengan tim kami.
                </p>
            </div>
        </section>

        <!-- Sandbox Quick Access -->
        <section class="py-16 bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl border border-neutral-100 p-8 lg:p-12">
                    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-neutral-900">Sandbox Demo</h2>
                            <p class="mt-1 text-sm text-neutral-600">Login dengan salah satu akun di bawah untuk explore.</p>
                        </div>
                    </div>

                    <button
                        @click="showCredentials = !showCredentials"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1 mb-4"
                    >
                        <component :is="showCredentials ? EyeOff : Eye" class="h-4 w-4" />
                        {{ showCredentials ? 'Sembunyikan' : 'Tampilkan' }} kredensial
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div
                            v-for="(acc, idx) in demoAccounts"
                            :key="acc.email"
                            class="flex items-center justify-between p-3 rounded-lg border border-neutral-100 hover:border-primary-200 transition"
                        >
                            <div>
                                <p class="text-xs font-semibold text-primary-600 uppercase tracking-wider">{{ acc.role }}</p>
                                <p class="text-sm font-mono text-neutral-700 mt-1">{{ acc.email }}</p>
                                <p v-if="showCredentials" class="text-sm font-mono text-neutral-500">PIN: {{ acc.pin }}</p>
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
        <section id="konsultasi" class="py-16 bg-neutral-50">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                        Request Demo 1-on-1
                    </h2>
                    <p class="mt-4 text-lg text-neutral-600">
                        Tim kami akan menghubungi Anda dalam 1x24 jam via WhatsApp.
                    </p>
                </div>

                <div v-if="flashSuccess" class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
                    {{ flashSuccess }}
                </div>
                <div v-if="flashError" class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 text-red-800">
                    {{ flashError }}
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-xl p-8 border border-neutral-100 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Nama Lengkap *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Jabatan *</label>
                            <input
                                v-model="form.role"
                                type="text"
                                required
                                placeholder="cth: Ketua, Admin, dll"
                                class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="{ 'border-red-300': form.errors.role }"
                            />
                            <p v-if="form.errors.role" class="text-xs text-red-600 mt-1">{{ form.errors.role }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Nama Koperasi *</label>
                        <input
                            v-model="form.cooperative_name"
                            type="text"
                            required
                            class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            :class="{ 'border-red-300': form.errors.cooperative_name }"
                        />
                        <p v-if="form.errors.cooperative_name" class="text-xs text-red-600 mt-1">{{ form.errors.cooperative_name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Jumlah Anggota *</label>
                            <select
                                v-model="form.member_count"
                                required
                                class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            >
                                <option value="">Pilih range</option>
                                <option value="< 100">&lt; 100</option>
                                <option value="100-500">100 - 500</option>
                                <option value="500-1000">500 - 1000</option>
                                <option value="1000-5000">1000 - 5000</option>
                                <option value="> 5000">&gt; 5000</option>
                            </select>
                            <p v-if="form.errors.member_count" class="text-xs text-red-600 mt-1">{{ form.errors.member_count }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">WhatsApp *</label>
                            <input
                                v-model="form.whatsapp"
                                type="tel"
                                required
                                placeholder="0812-3456-7890"
                                class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="{ 'border-red-300': form.errors.whatsapp }"
                            />
                            <p v-if="form.errors.whatsapp" class="text-xs text-red-600 mt-1">{{ form.errors.whatsapp }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Pesan (opsional)</label>
                        <textarea
                            v-model="form.message"
                            rows="4"
                            placeholder="Ceritakan kebutuhan koperasi Anda..."
                            class="w-full px-3 py-2 rounded-md border border-neutral-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition disabled:opacity-50"
                    >
                        <span v-if="form.processing">Mengirim...</span>
                        <span v-else>Kirim Request</span>
                        <ArrowRight v-if="!form.processing" class="h-4 w-4" />
                    </button>

                    <p class="text-xs text-neutral-500 text-center">
                        Dengan mengirim form ini, Anda setuju dengan kebijakan privasi kami.
                    </p>
                </form>
            </div>
        </section>
    </SiteLayout>
</template>
