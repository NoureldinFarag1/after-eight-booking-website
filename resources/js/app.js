import './bootstrap';

// Collapsible sidebar toggle and persistence
document.addEventListener('DOMContentLoaded', () => {
	const KEY = 'ae.sidebar.collapsed';
	const body = document.body;
	const btn = document.getElementById('sidebarToggleBtn');
	const icon = document.getElementById('sidebarToggleIcon');
	const MIN_DESKTOP_COLLAPSE = 1200; // px

	function applyTooltips(enable) {
		// When collapsed, show tooltips using label text as title
		const elements = document.querySelectorAll('.sidebar .nav-link, .sidebar .guest-actions .btn');
		elements.forEach(el => {
			const label = el.querySelector('.label-text');
			if (!label) return;
			// Always dispose any existing tooltip to avoid duplicates
			if (window.bootstrap && window.bootstrap.Tooltip) {
				const existing = window.bootstrap.Tooltip.getInstance(el);
				if (existing) existing.dispose();
			}
			if (enable) {
				el.setAttribute('data-bs-toggle', 'tooltip');
				el.setAttribute('data-bs-placement', 'right');
				el.setAttribute('title', label.textContent.trim());
				// Initialize tooltip
				if (window.bootstrap && window.bootstrap.Tooltip) {
					new window.bootstrap.Tooltip(el, { trigger: 'hover focus', delay: { show: 150, hide: 0 } });
				}
			} else {
				el.removeAttribute('data-bs-toggle');
				el.removeAttribute('data-bs-placement');
				el.removeAttribute('title');
				// Dispose tooltip if exists
				if (window.bootstrap && window.bootstrap.Tooltip) {
					const tip = window.bootstrap.Tooltip.getInstance(el);
					if (tip) tip.dispose();
				}
			}
		});
	}

	function updateResponsiveCollapse() {
		const shouldCollapse = window.innerWidth < MIN_DESKTOP_COLLAPSE;
		body.classList.toggle('sidebar-collapsed-responsive', shouldCollapse);
		applyTooltips(shouldCollapse || body.classList.contains('sidebar-collapsed'));
		updateToggleIcon();
	}

	function isCollapsed() {
		return body.classList.contains('sidebar-collapsed') || body.classList.contains('sidebar-collapsed-responsive');
	}

	function updateToggleIcon() {
		// No-op: rotation handled via CSS on body class (#sidebarToggleIcon transform)
		return;
	}

	// Apply persisted state
	try {
		const saved = localStorage.getItem(KEY);
		if (saved === '1') body.classList.add('sidebar-collapsed');
	} catch {}

	// Ensure icon reflects initial state
	updateToggleIcon();

	if (btn) {
		btn.addEventListener('click', () => {
			body.classList.toggle('sidebar-collapsed');
			// Persist
			try {
				localStorage.setItem(KEY, body.classList.contains('sidebar-collapsed') ? '1' : '0');
			} catch {}
			applyTooltips(body.classList.contains('sidebar-collapsed') || body.classList.contains('sidebar-collapsed-responsive'));
			updateToggleIcon();
		});
	}

	// Initial responsive state and tooltips
	updateResponsiveCollapse();
	// Debounced resize handler
	let resizeTimeout;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(updateResponsiveCollapse, 100);
	});
});
