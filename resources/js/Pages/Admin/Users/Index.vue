<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');

function applyFilters() {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
    }, { preserveState: true, replace: true });
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
}

function setRole(role) {
    roleFilter.value = role;
    applyFilters();
}

function confirmDelete(user) {
    if (confirm(`Are you sure you want to delete "${user.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
}

function toggleAdmin(user) {
    const action = user.is_admin ? 'remove admin from' : 'make admin';
    if (confirm(`Are you sure you want to ${action} "${user.name}"?`)) {
        router.post(route('admin.users.toggle-admin', user.id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AppLayout title="User Management">
        <Head title="User Management" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                        / Users
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <input
                    v-model="search"
                    @input="onSearchInput"
                    type="text"
                    placeholder="Search by name or email..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm focus:ring-purple-500 focus:border-purple-500"
                />
                <div class="flex gap-2">
                    <button v-for="role in ['all', 'admin', 'user']" :key="role"
                        @click="setRole(role)"
                        class="px-3 py-2 text-sm rounded-lg font-medium transition"
                        :class="roleFilter === role ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    >
                        {{ role === 'all' ? 'All' : role === 'admin' ? 'Admins' : 'Users' }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quizzes</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Games Hosted</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Joined</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-3">
                                    <Link :href="route('admin.users.show', user.id)" class="font-medium text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                        {{ user.name }}
                                    </Link>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ user.quizzes_count }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ user.hosted_game_sessions_count }}</td>
                                <td class="px-5 py-3">
                                    <span v-if="user.is_admin" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Admin</span>
                                    <span v-else class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">User</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(user.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="route('admin.users.show', user.id)" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">View</Link>
                                        <button @click="toggleAdmin(user)" class="text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400">
                                            {{ user.is_admin ? 'Remove Admin' : 'Make Admin' }}
                                        </button>
                                        <button @click="confirmDelete(user)" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No users found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ users.from }}-{{ users.to }} of {{ users.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link v-for="link in users.links" :key="link.label"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded-lg transition"
                            :class="link.active ? 'bg-purple-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                            v-html="link.label"
                            :preserve-state="true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
