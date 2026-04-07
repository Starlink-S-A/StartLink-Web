document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebarMenu');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (!sidebar) return;

    const STORAGE_KEY = 'startlink_sidebar_collapsed';
    const mobileMedia = window.matchMedia('(max-width: 992px)');

    // Read persisted state
    function getPersistedState() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'true') return true;
        if (stored === 'false') return false;
        return null; // no saved preference
    }

    function setCollapsed(collapsed) {
        sidebar.classList.toggle('is-collapsed', collapsed);
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        // Persist to localStorage (only for desktop)
        if (!mobileMedia.matches) {
            localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');
        }
    }

    // Initialize state
    function initSidebar() {
        if (mobileMedia.matches) {
            // On mobile, always start collapsed
            sidebar.classList.remove('is-collapsed');
            sidebar.classList.remove('is-mobile-open');
            document.body.classList.add('sidebar-collapsed');
        } else {
            // On desktop, restore persisted state
            const persisted = getPersistedState();
            if (persisted === true) {
                setCollapsed(true);
            } else {
                setCollapsed(false);
            }
        }
    }

    // Sidebar toggler (hamburger inside sidebar header)
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (mobileMedia.matches) {
                sidebar.classList.toggle('is-mobile-open');
            } else {
                setCollapsed(!sidebar.classList.contains('is-collapsed'));
            }
        });
    }

    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function (e) {
        if (mobileMedia.matches && sidebar.classList.contains('is-mobile-open')) {
            if (!sidebar.contains(e.target)) {
                sidebar.classList.remove('is-mobile-open');
            }
        }
    });

    // Handle resize
    mobileMedia.addEventListener('change', function () {
        if (mobileMedia.matches) {
            sidebar.classList.remove('is-mobile-open');
            sidebar.classList.remove('is-collapsed');
            document.body.classList.add('sidebar-collapsed');
        } else {
            initSidebar();
        }
    });

    // Initial setup
    initSidebar();
});
