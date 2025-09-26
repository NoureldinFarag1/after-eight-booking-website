import './bootstrap';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

// Collapsible sidebar toggle and persistence
document.addEventListener('DOMContentLoaded', () => {
	/* =============================
	   Modern Toast Notifications
	   ============================= */
	if (!window.__notyfInstance) {
		window.__notyfInstance = new Notyf({
			position: { x: 'right', y: 'top' },
			duration: 4500,
			dismissible: true,
			types: [
				{ type: 'success', background: '#198754', icon: { className: 'bi bi-check-circle me-1', tagName: 'i' } },
				{ type: 'error', background: '#dc3545', icon: { className: 'bi bi-exclamation-octagon me-1', tagName: 'i' } },
				{ type: 'warning', background: '#ffc107', icon: { className: 'bi bi-exclamation-triangle me-1 text-dark', tagName: 'i' } },
				{ type: 'info', background: '#0dcaf0', icon: { className: 'bi bi-info-circle me-1 text-dark', tagName: 'i' } }
			]
		});
	}
	const notyf = window.__notyfInstance;
	if (window.__FLASH__) {
		Object.entries(window.__FLASH__).forEach(([type, message]) => {
			if (!message) return;
			if (['success','error','warning','info'].includes(type)) {
				notyf.open({ type, message });
			}
		});
	}
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

	/* =============================
	   Password Visibility Toggles
	   ============================= */
	document.querySelectorAll('.toggle-password').forEach(toggle => {
		toggle.addEventListener('click', () => {
			const selector = toggle.getAttribute('data-target');
			if (!selector) return;
			const input = document.querySelector(selector);
			if (!input) return;
			const isPassword = input.getAttribute('type') === 'password';
			input.setAttribute('type', isPassword ? 'text' : 'password');
			const iconEl = toggle.querySelector('i');
			if (iconEl) {
				iconEl.classList.toggle('bi-eye', !isPassword);
				iconEl.classList.toggle('bi-eye-slash', isPassword);
			}
			toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
		});
	});

	/* =============================
	   Auth Card Entrance Animation
	   ============================= */
	const authCard = document.querySelector('.auth-card');
	if (authCard) {
		requestAnimationFrame(() => authCard.classList.add('enter'));
	}

    // Theme toggle removed – app now defaults to single dark theme.

	/* =============================
	   Mobile Offcanvas Nav Autoclose
	   ============================= */
	const mobileSidebar = document.getElementById('mobileSidebar');
	if (mobileSidebar && window.bootstrap) {
		let navTapLocked = false;
		const pageLoader = document.getElementById('navPageLoader');
		function showPageLoader() {
			if (!pageLoader) return;
			pageLoader.classList.remove('d-none');
		}
		function hidePageLoader() {
			if (!pageLoader) return;
			pageLoader.classList.add('d-none');
		}
		window.addEventListener('pageshow', hidePageLoader);
		mobileSidebar.addEventListener('click', (e) => {
			const link = e.target.closest('a.nav-link, a.btn');
			if (!link) return;
			const href = link.getAttribute('href');
			if (!href || href.startsWith('#') || link.getAttribute('target') === '_blank') return;
			if (navTapLocked) { e.preventDefault(); return; }
			navTapLocked = true;
			showPageLoader();
			const oc = window.bootstrap.Offcanvas.getInstance(mobileSidebar) || new window.bootstrap.Offcanvas(mobileSidebar);
			requestAnimationFrame(() => oc.hide());
			// Safety unlock after 2s if navigation prevented (edge case)
			setTimeout(() => { navTapLocked = false; }, 2000);
		});
	}

	// Also apply page loader + debounce for any top-level layout nav links (desktop)
	document.addEventListener('click', (e) => {
		const link = e.target.closest('a.nav-link');
		if (!link) return;
		if (link.closest('#mobileSidebar')) return; // already handled above
		const href = link.getAttribute('href');
		if (!href || href.startsWith('#') || link.getAttribute('target') === '_blank') return;
		showPageLoader();
	});
});
