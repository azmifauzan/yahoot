import { ref, watch } from 'vue';

const collapsed = ref(localStorage.getItem('sidebar_collapsed') === 'true');
const mobileOpen = ref(false);

watch(collapsed, (value) => {
    localStorage.setItem('sidebar_collapsed', value ? 'true' : 'false');
});

export function useSidebar() {
    function toggleCollapsed() {
        collapsed.value = !collapsed.value;
    }

    function toggleMobile() {
        mobileOpen.value = !mobileOpen.value;
    }

    function closeMobile() {
        mobileOpen.value = false;
    }

    return {
        collapsed,
        mobileOpen,
        toggleCollapsed,
        toggleMobile,
        closeMobile,
    };
}
