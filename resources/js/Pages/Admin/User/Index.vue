<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    users: any;
}>();

const deleteConfirm = ref<number | null>(null);

const formatDate = (dt: string | null) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

function confirmDelete(id: number) {
    deleteConfirm.value = id;
}

function cancelDelete() {
    deleteConfirm.value = null;
}

function executeDelete(id: number) {
    router.delete('/admin/users/' + id, {
        onSuccess: () => {
            deleteConfirm.value = null;
        },
    });
}

const roleBadge = (role: string) => {
    const map: Record<string, { class: string; label: string }> = {
        admin: { class: 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400', label: 'Admin' },
        editor: { class: 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400', label: 'Editor' },
    };
    return map[role] || { class: 'bg-neutral-100 text-neutral-600', label: role };
};
</script>

<template>
    <AdminLayout title="Kelola User Internal">
        <Head title="User Internal - CMS Admin" />

        <div class="p-4 sm:p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-white">User Internal</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Kelola admin dan editor CMS.</p>
                </div>
                <Link
                    href="/admin/users/create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah User
                </Link>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Nama</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Email</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Role</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Bergabung</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 text-sm font-semibold">
                                            {{ u.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-neutral-900 dark:text-white">{{ u.name }}</p>
                                            <p v-if="u.phone" class="text-xs text-neutral-400 dark:text-neutral-500">{{ u.phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400">{{ u.email }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" :class="roleBadge(u.role).class">
                                        {{ roleBadge(u.role).label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(u.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="'/admin/users/' + u.id + '/edit'"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            v-if="u.id !== $page.props.auth?.user?.id"
                                            @click="confirmDelete(u.id)"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.total > users.per_page" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">Menampilkan {{ users.from }}–{{ users.to }} dari {{ users.total }}</span>
                    <div class="flex gap-2">
                        <Link
                            v-if="users.prev_page_url"
                            :href="users.prev_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Sebelumnya
                        </Link>
                        <Link
                            v-if="users.next_page_url"
                            :href="users.next_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="deleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="cancelDelete">
                <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl dark:shadow-neutral-950/80 p-6 max-w-sm w-full mx-auto border border-neutral-200 dark:border-neutral-800">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Hapus User?</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">User yang dihapus tidak bisa dikembalikan. Lanjutkan?</p>
                    <div class="flex gap-3 justify-end">
                        <button @click="cancelDelete" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">Batal</button>
                        <button @click="executeDelete(deleteConfirm)" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
