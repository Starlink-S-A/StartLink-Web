document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebarMenu');

    if (!toggle || !sidebar) return;

    const mobileMedia = window.matchMedia('(max-width: 992px)');

    const setCollapsed = function (collapsed) {
        sidebar.classList.toggle('is-collapsed', collapsed);
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        toggle.classList.toggle('is-active', !collapsed);
    };

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        setCollapsed(!sidebar.classList.contains('is-collapsed'));
    });

    document.addEventListener('click', function (e) {
        if (mobileMedia.matches && !sidebar.contains(e.target) && !toggle.contains(e.target) && !sidebar.classList.contains('is-collapsed')) {
            setCollapsed(true);
        }
    });

    mobileMedia.addEventListener('change', function () {
        setCollapsed(mobileMedia.matches);
    });

    setCollapsed(mobileMedia.matches);
});
