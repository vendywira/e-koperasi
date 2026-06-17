<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    editing: boolean;
    user: any;
}>();

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
    role: props.user?.role || 'editor',
    password: '',
    password_confirmation: '',
});

const pageTitle = computed(() => props.editing ? 'Edit User' : 'Tambah User');

function submit() {
    if (props.editing) {
        form.put('/admin/users/' + props.user.id, {
            onError: () => {},
        });
    } else {
        form.post('/admin/users', {
            onError: () => {},
        });
    }
}
</script>

<template>
    <AdminLayout :title="pageTitle">
        <Head :title="pageTitle + ' - CMS Admin'" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-2xl space-y-6">
            <Link
                href="/admin/users"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Daftar User
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-6">{{ pageTitle }}</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Nama</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-lg border text-sm"
                            :class="form.errors.name ? 'border-red-300 dark:border-red-700' : 'border-neutral-300 dark:border-neutral-700'"
                            placeholder="Nama lengkap"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-2.5 rounded-lg border text-sm"
                            :class="form.errors.email ? 'border-red-300 dark:border-red-700' : 'border-neutral-300 dark:border-neutral-700'"
                            placeholder="email@example.com"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Telepon (opsional)</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm"
                            placeholder="08xxx"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Role</label>
                        <select
                            v-model="form.role"
                            class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm"
                        >
                            <option value="admin">Admin — akses penuh</option>
                            <option value="editor">Editor — edit konten saja</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Password {{ editing ? '(biarkan kosong jika tidak diganti)' : '' }}
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                :required="!editing"
                                class="w-full px-4 py-2.5 rounded-lg border text-sm"
                                :class="form.errors.password ? 'border-red-300 dark:border-red-700' : 'border-neutral-300 dark:border-neutral-700'"
                                placeholder="Min. 8 karakter"
                            />
                            <p v-if="form.errors.password" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Konfirmasi Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                :required="!editing && !!form.password"
                                class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm"
                                placeholder="Ulangi password"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah User' }}
                        </button>
                        <Link
                            href="/admin/users"
                            class="px-6 py-2.5 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors text-center"
                        >
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
