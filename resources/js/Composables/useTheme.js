import { ref, watch } from 'vue';

const theme = ref(localStorage.getItem('theme') || 'light');

function applyTheme(value) {
    if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Apply on load
applyTheme(theme.value);

watch(theme, (value) => {
    localStorage.setItem('theme', value);
    applyTheme(value);
});

export function useTheme() {
    function toggleTheme() {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
    }

    function setTheme(value) {
        theme.value = value;
    }

    return {
        theme,
        toggleTheme,
        setTheme,
        isDark: () => theme.value === 'dark',
    };
}
