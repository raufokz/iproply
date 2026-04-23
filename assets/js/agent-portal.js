(() => {
    const body = document.body;
    if (!body || !body.classList.contains('agent-portal')) return;

    const sidebar = document.getElementById('agentSidebar') || document.querySelector('.sidebar');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!sidebar || !overlay || !toggle) return;

    const setExpanded = (isExpanded) => {
        toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    };

    const openSidebar = () => {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('sidebar-open');
        setExpanded(true);
    };

    const closeSidebar = () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('sidebar-open');
        setExpanded(false);
    };

    const toggleSidebar = () => {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
            return;
        }
        openSidebar();
    };

    toggle.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });

    sidebar.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link) return;
        if (window.matchMedia('(max-width: 1024px)').matches) closeSidebar();
    });

    const desktopMql = window.matchMedia('(min-width: 1025px)');
    const onDesktopChange = (event) => {
        if (event.matches) closeSidebar();
    };

    if (typeof desktopMql.addEventListener === 'function') {
        desktopMql.addEventListener('change', onDesktopChange);
    } else if (typeof desktopMql.addListener === 'function') {
        desktopMql.addListener(onDesktopChange);
    }
})();

