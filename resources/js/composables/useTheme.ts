import { ref, watch, onMounted } from 'vue';

type Theme = 'light' | 'dark';

const STORAGE_KEY = 'ekoperasi-theme';

const theme = ref<Theme>('light');

function applyTheme(value: Theme) {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    if (value === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
}

function getStoredTheme(): Theme | null {
    if (typeof window === 'undefined') return null;
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);
        if (stored === 'light' || stored === 'dark') return stored;
    } catch {
        return null;
    }
    return null;
}

function getSystemTheme(): Theme {
    if (typeof window === 'undefined') return 'light';
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function useTheme() {
    onMounted(() => {
        const stored = getStoredTheme();
        const initial = stored ?? getSystemTheme();
        theme.value = initial;
        applyTheme(initial);
    });

    watch(theme, (value) => {
        applyTheme(value);
        try {
            window.localStorage.setItem(STORAGE_KEY, value);
        } catch {
            // ignore quota/privacy errors
        }
    });

    function setTheme(value: Theme) {
        theme.value = value;
    }

    function toggleTheme() {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
    }

    return {
        theme,
        setTheme,
        toggleTheme,
    };
}
