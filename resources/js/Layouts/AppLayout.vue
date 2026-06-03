<script setup>
import { ref, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const mobileMenuOpen = ref(false);
const searchQuery = ref('');
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref('success');

let searchTimeout = null;

const user = page.props.auth?.user;

const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (searchQuery.value.trim()) {
            router.get('/search', { q: searchQuery.value.trim() }, { preserveState: true });
        }
    }, 500);
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success || flash?.error) {
        flashMessage.value = flash.success || flash.error;
        flashType.value = flash.success ? 'success' : 'error';
        showFlash.value = true;
        setTimeout(() => { showFlash.value = false; }, 3000);
    }
}, { immediate: true, deep: true });

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-paper dark:bg-ink-950 text-ink-900 dark:text-ink-50">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-y-[-100%] opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-[-100%] opacity-0"
        >
            <div
                v-if="showFlash"
                :class="[
                    'fixed top-4 left-1/2 -translate-x-1/2 z-50 px-5 py-2.5 border-2 font-mono text-sm shadow-hard',
                    flashType === 'success'
                        ? 'bg-brand-100 border-brand-700 text-brand-900 dark:bg-brand-900/40 dark:border-brand-400 dark:text-brand-100'
                        : 'bg-red-100 border-red-700 text-red-900 dark:bg-red-900/40 dark:border-red-400 dark:text-red-100'
                ]"
            >
                <span class="mr-1">{{ flashType === 'success' ? '[ok]' : '[err]' }}</span>
                {{ flashMessage }}
            </div>
        </Transition>

        <div class="hidden md:block bg-ink-900 dark:bg-ink-950 text-brand-300 font-mono text-[11px] border-b-2 border-ink-900 dark:border-brand-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-6 flex items-center justify-between">
                <span>&gt; encyclopedia.local — status: online</span>
                <span class="text-ink-200">uptime: {{ new Date().toLocaleDateString('ru-RU') }}</span>
            </div>
        </div>

        <header class="sticky top-0 z-40 bg-paper/95 dark:bg-ink-950/95 backdrop-blur border-b-2 border-ink-900 dark:border-brand-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-8">
                        <Link href="/" class="flex items-center gap-2 font-serif text-xl font-bold text-ink-900 dark:text-paper group">
                            <span class="w-8 h-8 bg-brand-700 dark:bg-brand-600 border-2 border-ink-900 dark:border-paper flex items-center justify-center text-paper shadow-hard-sm group-hover:-translate-y-0.5 transition-transform">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </span>
                            <span class="hidden sm:inline">encyclopedia</span>
                            <span class="hidden sm:inline font-mono text-xs text-brand-700 dark:text-brand-400 font-normal">v1.0</span>
                        </Link>

                        <nav class="hidden md:flex items-center gap-1 font-mono text-sm">
                            <Link
                                href="/"
                                class="px-2 py-1 text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2 transition-colors"
                            >
                                [home]
                            </Link>
                            <Link
                                href="/articles"
                                class="px-2 py-1 text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2 transition-colors"
                            >
                                [articles]
                            </Link>
                            <Link
                                href="/categories"
                                class="px-2 py-1 text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2 transition-colors"
                            >
                                [categories]
                            </Link>
                            <Link
                                href="/search"
                                class="px-2 py-1 text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2 transition-colors"
                            >
                                [search]
                            </Link>
                            <Link
                                v-if="user"
                                href="/favorites"
                                class="px-2 py-1 text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2 transition-colors"
                            >
                                [favorites]
                            </Link>
                        </nav>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden sm:block relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 font-mono text-xs text-brand-700 dark:text-brand-400 pointer-events-none">&gt;</span>
                            <input
                                v-model="searchQuery"
                                @input="doSearch"
                                type="text"
                                placeholder="search..."
                                class="w-48 lg:w-64 pl-6 pr-3 py-1.5 font-mono text-sm border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-ink-50 placeholder-ink-200 dark:placeholder-ink-700 focus:ring-0 focus:border-brand-700 dark:focus:border-brand-400 shadow-hard-sm focus:shadow-[3px_3px_0_0_rgba(15,118,110,1)] transition-shadow"
                            />
                        </div>

                        <div class="hidden md:flex items-center gap-3 font-mono text-sm">
                            <template v-if="user">
                                <Link
                                    v-if="user.role === 'admin' || user.role === 'editor'"
                                    href="/admin/articles"
                                    class="text-brand-700 dark:text-brand-400 hover:underline underline-offset-4 decoration-2"
                                >
                                    [admin]
                                </Link>
                                <Link href="/dashboard" class="flex items-center gap-2 text-ink-700 dark:text-ink-100 hover:text-ink-900 dark:hover:text-paper">
                                    <span>{{ user.name }}</span>
                                    <span
                                        :class="[
                                            'px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border',
                                            user.role === 'admin' ? 'bg-red-100 text-red-800 border-red-700 dark:bg-red-900/40 dark:text-red-300 dark:border-red-500' :
                                            user.role === 'editor' ? 'bg-amber-100 text-amber-800 border-amber-700 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-500' :
                                            'bg-ink-50 text-ink-700 border-ink-700 dark:bg-ink-800 dark:text-ink-100 dark:border-ink-200'
                                        ]"
                                    >
                                        {{ user.role }}
                                    </span>
                                </Link>
                                <button
                                    @click="logout"
                                    class="text-ink-700 dark:text-ink-100 hover:text-red-700 dark:hover:text-red-400 hover:underline underline-offset-4 decoration-2"
                                >
                                    [logout]
                                </button>
                            </template>
                            <template v-else>
                                <Link
                                    href="/login"
                                    class="text-ink-700 dark:text-ink-100 hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-4 decoration-2"
                                >
                                    [login]
                                </Link>
                                <Link
                                    href="/register"
                                    class="px-3 py-1 bg-brand-700 text-paper border-2 border-ink-900 dark:border-paper shadow-hard-sm hover:-translate-y-0.5 hover:shadow-hard transition-all"
                                >
                                    [register]
                                </Link>
                            </template>
                        </div>

                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 shadow-hard-sm"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div v-if="mobileMenuOpen" class="md:hidden border-t-2 border-ink-900 dark:border-brand-800 bg-paper dark:bg-ink-900 px-4 py-4 space-y-1 font-mono text-sm">
                    <div class="relative sm:hidden mb-3">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-brand-700 dark:text-brand-400">&gt;</span>
                        <input
                            v-model="searchQuery"
                            @input="doSearch"
                            type="text"
                            placeholder="search..."
                            class="w-full pl-6 pr-3 py-2 text-sm border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-ink-50 focus:ring-0 focus:border-brand-700"
                        />
                    </div>

                    <Link href="/" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-brand-700 dark:hover:text-brand-400" @click="mobileMenuOpen = false">[home]</Link>
                    <Link href="/articles" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-brand-700 dark:hover:text-brand-400" @click="mobileMenuOpen = false">[articles]</Link>
                    <Link href="/categories" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-brand-700 dark:hover:text-brand-400" @click="mobileMenuOpen = false">[categories]</Link>
                    <Link href="/search" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-brand-700 dark:hover:text-brand-400" @click="mobileMenuOpen = false">[search]</Link>
                    <Link v-if="user" href="/favorites" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-brand-700 dark:hover:text-brand-400" @click="mobileMenuOpen = false">[favorites]</Link>

                    <div class="border-t-2 border-dashed border-ink-200 dark:border-ink-700 pt-3 mt-3">
                        <template v-if="user">
                            <div class="px-3 py-2 flex items-center gap-2 text-ink-700 dark:text-ink-100">
                                <span>{{ user.name }}</span>
                                <span
                                    :class="[
                                        'px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border',
                                        user.role === 'admin' ? 'bg-red-100 text-red-800 border-red-700 dark:bg-red-900/40 dark:text-red-300 dark:border-red-500' :
                                        user.role === 'editor' ? 'bg-amber-100 text-amber-800 border-amber-700 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-500' :
                                        'bg-ink-50 text-ink-700 border-ink-700 dark:bg-ink-800 dark:text-ink-100 dark:border-ink-200'
                                    ]"
                                >
                                    {{ user.role }}
                                </span>
                            </div>
                            <Link v-if="user.role === 'admin' || user.role === 'editor'" href="/admin/articles" class="block px-3 py-2 text-brand-700 dark:text-brand-400 hover:bg-ink-50 dark:hover:bg-ink-800" @click="mobileMenuOpen = false">[admin-panel]</Link>
                            <Link href="/dashboard" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800" @click="mobileMenuOpen = false">[dashboard]</Link>
                            <button @click="logout" class="w-full text-left px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-red-700 dark:hover:text-red-400">[logout]</button>
                        </template>
                        <template v-else>
                            <Link href="/login" class="block px-3 py-2 text-ink-700 dark:text-ink-100 hover:bg-ink-50 dark:hover:bg-ink-800" @click="mobileMenuOpen = false">[login]</Link>
                            <Link href="/register" class="block px-3 py-2 text-brand-700 dark:text-brand-400 hover:bg-ink-50 dark:hover:bg-ink-800" @click="mobileMenuOpen = false">[register]</Link>
                        </template>
                    </div>
                </div>
            </Transition>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t-2 border-ink-900 dark:border-brand-800 bg-ink-900 dark:bg-ink-950 text-ink-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="font-mono text-xs text-brand-400 mb-4 select-none">
                    ═══════════════════════════════════════════════════════════════
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 font-serif text-lg text-paper">
                            <span class="font-bold">encyclopedia</span>
                            <span class="font-mono text-xs text-brand-400 font-normal">// knowledge base</span>
                        </div>
                        <p class="font-mono text-xs text-ink-200">
                            &copy; {{ new Date().getFullYear() }} — все права защищены. powered by ai &amp; humans.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 font-mono text-sm">
                        <Link href="/articles" class="text-ink-100 hover:text-brand-400 hover:underline underline-offset-4 decoration-2">[articles]</Link>
                        <Link href="/categories" class="text-ink-100 hover:text-brand-400 hover:underline underline-offset-4 decoration-2">[categories]</Link>
                        <Link href="/feed" class="text-ink-100 hover:text-brand-400 hover:underline underline-offset-4 decoration-2">[rss]</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
