import './bootstrap';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

// Collapsible sidebar toggle and persistence
document.addEventListener('DOMContentLoaded', () => {
	/* =============================
	   Sidebar Submenu Functionality
	   ============================= */
	const SUBMENU_KEY = 'ae.submenu.state';
	
	// Load saved submenu states
	function loadSubmenuStates() {
		try {
			const saved = localStorage.getItem(SUBMENU_KEY);
			return saved ? JSON.parse(saved) : {};
		} catch {
			return {};
		}
	}
	
	// Save submenu states
	function saveSubmenuStates() {
		try {
			const states = {};
			document.querySelectorAll('.has-submenu').forEach(item => {
				const toggle = item.querySelector('.submenu-toggle');
				if (toggle) {
					const submenuId = toggle.getAttribute('data-submenu');
					states[submenuId] = item.classList.contains('submenu-open');
				}
			});
			localStorage.setItem(SUBMENU_KEY, JSON.stringify(states));
		} catch {}
	}
	
	// Apply saved states on load (called after offcanvas is ready)
	function applySavedSubmenuStates() {
		const savedStates = loadSubmenuStates();
		Object.entries(savedStates).forEach(([submenuId, isOpen]) => {
			const toggle = document.querySelector(`[data-submenu="${submenuId}"]`);
			if (toggle) {
				const parent = toggle.closest('.has-submenu');
				if (parent) {
					if (isOpen) {
						parent.classList.add('submenu-open');
						toggle.setAttribute('aria-expanded', 'true');
					} else {
						parent.classList.remove('submenu-open');
						toggle.setAttribute('aria-expanded', 'false');
					}
				}
			}
		});
	}
	
	// Handle submenu toggle clicks using event delegation
	document.addEventListener('click', (e) => {
		const toggle = e.target.closest('.submenu-toggle');
		if (!toggle) return;
		
		e.preventDefault();
		e.stopPropagation();
		
		const parent = toggle.closest('.has-submenu');
		if (!parent) return;
		
		const isOpen = parent.classList.contains('submenu-open');
		
		// Toggle state
		parent.classList.toggle('submenu-open');
		toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
		
		// Save state
		saveSubmenuStates();
	});
	
	// Apply saved states initially
	applySavedSubmenuStates();	/* =============================
	   Modern Toast Notifications
	   ============================= */
	if (!window.__notyfInstance) {
		window.__notyfInstance = new Notyf({
			position: { x: 'right', y: window.innerWidth < 768 ? 'bottom' : 'top' },
			duration: 4200,
			dismissible: true,
			// We keep backgrounds transparent-ish and handle gradient in CSS via modifier classes
			types: [
				{ type: 'success', background: 'rgba(25,135,84,0.85)', icon: { className: 'bi bi-check-circle', tagName: 'i' } },
				{ type: 'error', background: 'rgba(220,53,69,0.85)', icon: { className: 'bi bi-exclamation-octagon', tagName: 'i' } },
				{ type: 'warning', background: 'rgba(255,193,7,0.90)', icon: { className: 'bi bi-exclamation-triangle text-dark', tagName: 'i' } },
				{ type: 'info', background: 'rgba(13,202,240,0.90)', icon: { className: 'bi bi-info-circle text-dark', tagName: 'i' } }
			]
		});
	}
	const notyf = window.__notyfInstance;

	// Suppress trivial auth messages (login/register/logout welcomes) & if we are on auth pages
	const authRoutePatterns = [/\/login$/, /\/register$/, /\/forgot-password/, /\/reset-password/];
	const onAuthPage = authRoutePatterns.some(r => r.test(window.location.pathname));
	const trivialPhrases = [
		'Welcome back,',
		'You have been logged out',
		'Welcome to After Eight Events'
	];

	function isTrivial(message) {
		return trivialPhrases.some(p => message.startsWith(p));
	}

	if (window.__FLASH__ && !onAuthPage) {
		Object.entries(window.__FLASH__).forEach(([type, message]) => {
			if (!message) return;
			if (isTrivial(message)) return; // skip UX noise
			if (['success','error','warning','info'].includes(type)) {
				const toast = notyf.open({ type, message });
				// Attach a progress bar element for visual lifetime (CSS anim handles width)
				try {
					const bar = document.createElement('div');
					bar.className = 'notyf-progress';
					toast.el.appendChild(bar);
				} catch {}
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
		
		// Apply submenu states when offcanvas is shown
		mobileSidebar.addEventListener('shown.bs.offcanvas', () => {
			applySavedSubmenuStates();
		});
		
		mobileSidebar.addEventListener('click', (e) => {
			const link = e.target.closest('a.nav-link, a.btn');
			if (!link) return;
			
			// Skip if it's a submenu toggle
			if (link.classList.contains('submenu-toggle')) return;
			
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

	/* =============================
	   AJAX Publish Toggle (Events Index)
	   ============================= */
	const publishForms = document.querySelectorAll('.publish-toggle-form');
	publishForms.forEach(form => {
		form.addEventListener('submit', async (ev) => {
			ev.preventDefault();
			const btn = form.querySelector('[data-publish-btn]');
			if (!btn) return form.submit(); // fallback
			if (btn.getAttribute('data-loading') === '1') return; // guard double click
			const spinner = btn.querySelector('.spinner-border');
			const labelSpan = btn.querySelector('.btn-label');
			const badge = form.closest('.card').querySelector('.status-badge');
			const origHTML = labelSpan ? labelSpan.innerHTML : '';
			btn.setAttribute('data-loading','1');
			btn.disabled = true;
			if (spinner) spinner.classList.remove('d-none');
			try {
				const resp = await fetch(form.action, {
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
					},
					body: new URLSearchParams({ _method: 'PATCH' })
				});
				const data = await resp.json();
				if (!resp.ok) {
					throw new Error(data.message || 'Failed to update');
				}
				// Update badge
				if (badge) {
					badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
					badge.className = 'badge status-badge ' + (data.badge_class || 'bg-secondary');
				}
				// Update button visual state
				btn.classList.remove('btn-success','btn-outline-warning');
				if (data.status === 'draft') {
					btn.classList.add('btn-success');
					if (labelSpan) labelSpan.innerHTML = '<i class="bi bi-upload me-1"></i>Publish';
					btn.setAttribute('aria-label','Publish event');
				} else if (data.status === 'published') {
					btn.classList.add('btn-outline-warning');
					if (labelSpan) labelSpan.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Revert';
					btn.setAttribute('aria-label','Revert event to draft');
				}
				btn.removeAttribute('data-loading');
				btn.disabled = false;
				if (spinner) spinner.classList.add('d-none');
				if (window.__notyfInstance) window.__notyfInstance.success(data.message || 'Status updated');
			} catch (err) {
				if (labelSpan) labelSpan.innerHTML = origHTML;
				btn.removeAttribute('data-loading');
				btn.disabled = false;
				if (spinner) spinner.classList.add('d-none');
				if (window.__notyfInstance) window.__notyfInstance.error(err.message || 'Failed to update');
			}
		});
	});
});
