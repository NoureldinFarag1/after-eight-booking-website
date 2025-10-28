import './bootstrap';
import { Notyf } from 'notyf';
import { createIcons, icons } from 'lucide';
import 'notyf/notyf.min.css';

// Call once as early as possible to render any icons already in DOM
try { createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}

// Collapsible sidebar toggle and persistence
document.addEventListener('DOMContentLoaded', () => {
	/* =============================
	   Lucide Icons: auto-replace
	   ============================= */
	// Temporary shim: convert common Bootstrap Icons <i class="bi bi-*"></i> to Lucide attributes for inline icons
	function replaceBootstrapIcons(scope = document) {
		const map = {
			'arrow-left': 'arrow-left',
			'arrow-right': 'arrow-right',
			'box-arrow-in-right': 'log-in',
			'box-arrow-up-right': 'external-link',
			'person-plus': 'user-plus',
			'person': 'user',
			'person-fill': 'user',
			'person-gear': 'user-cog',
			'people': 'users',
			'shield-lock': 'shield',
			'shield-check': 'shield-check',
			'check-circle': 'check-circle',
			'check2': 'check',
			'check-lg': 'check',
			'x-circle': 'x-circle',
			'x-lg': 'x',
			'x': 'x',
			'pencil': 'pencil',
			'gear': 'settings',
			'gear-fill': 'settings',
			'calendar': 'calendar',
			'calendar-event': 'calendar',
			'calendar-x': 'calendar-x',
			'plus-circle': 'plus-circle',
			'plus': 'plus',
			'funnel': 'filter',
			'search': 'search',
			'arrow-counterclockwise': 'rotate-ccw',
			'image': 'image',
			'eye': 'eye',
			'eye-slash': 'eye-off',
			'graph-down': 'trending-down',
			'graph-up': 'trending-up',
			'geo-alt': 'map-pin',
			'clock': 'clock',
			'exclamation-triangle': 'triangle-alert',
			'info-circle': 'info',
			'trash': 'trash-2',
			'upload': 'upload',
			'download': 'download',
			'share': 'share-2',
			'qr-code': 'qr-code',
			'qr-code-scan': 'scan',
			'ticket-perforated': 'ticket',
			'ticket-detailed': 'ticket',
			'cash-coin': 'banknote',
			'cash-stack': 'banknote',
			'music-note-list': 'music',
			'journal-text': 'book-open',
			'envelope-open': 'mail',
			'envelope': 'mail',
			'envelope-paper': 'mail',
			'envelope-plus': 'mail-plus',
			'cart-plus': 'shopping-cart',
			'lightbulb': 'lightbulb',
		};
		const candidates = scope.querySelectorAll('i.bi');
		candidates.forEach(el => {
			if (el.hasAttribute('data-lucide')) return; // already converted
			// Limit to inline/button/nav icons to avoid affecting large hero icons
			const isInline = el.classList.contains('me-1') || el.classList.contains('me-2') || el.closest('.btn') || el.closest('.nav-link') || el.closest('.badge');
			if (!isInline) return;
			const biClass = Array.from(el.classList).find(c => c.startsWith('bi-'));
			if (!biClass) return;
			const name = biClass.replace('bi-', '');
			const lucideName = map[name];
			if (!lucideName) return; // keep original Bootstrap icon when no Lucide equivalent
			el.setAttribute('data-lucide', lucideName);
			el.classList.remove('bi');
			el.classList.remove(biClass);
		});
	}

	try { replaceBootstrapIcons(); createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}

	/* =============================
	   Homepage: horizontal scrollers
	   ============================= */
	(function initHomeScrollers(){
		const btns = document.querySelectorAll('.scroller-btn');
		btns.forEach(btn => {
			btn.addEventListener('click', () => {
				const targetSel = btn.getAttribute('data-scroll-target');
				const scroller = targetSel ? document.querySelector(targetSel) : null;
				if (!scroller) return;
				const dir = btn.classList.contains('prev') ? -1 : 1;
				const amount = Math.min(480, scroller.clientWidth * 0.9);
				scroller.scrollBy({ left: dir * amount, behavior: 'smooth' });
			});
		});
		// drag-to-scroll (optional)
		document.querySelectorAll('.events-scroller').forEach(scroller => {
			let isDown = false, startX = 0, scrollLeft = 0;
			scroller.addEventListener('mousedown', (e)=>{ isDown = true; startX = e.pageX - scroller.offsetLeft; scrollLeft = scroller.scrollLeft; scroller.classList.add('dragging'); });
			document.addEventListener('mouseup', ()=>{ isDown = false; scroller.classList.remove('dragging'); });
			scroller.addEventListener('mouseleave', ()=>{ isDown = false; scroller.classList.remove('dragging'); });
			scroller.addEventListener('mousemove', (e)=>{ if(!isDown) return; e.preventDefault(); const x = e.pageX - scroller.offsetLeft; const walk = (x - startX) * 1.1; scroller.scrollLeft = scrollLeft - walk; });
		});
	})();
	/* =============================
	   Color Scheme hint for UA controls
	   ============================= */
	try {
		// Force single dark theme across the app
		document.documentElement.style.colorScheme = 'dark';
	} catch {}
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
	applySavedSubmenuStates();

	/* Collapsed sidebar flyout enhancement: hide submenu when leaving flyout */
	function isSidebarCollapsed() {
		return document.body.classList.contains('sidebar-collapsed') || document.body.classList.contains('sidebar-collapsed-responsive');
	}

	// Hover intent management to prevent flicker when moving diagonally
	let submenuHideTimers = new WeakMap();

	document.querySelectorAll('.sidebar .has-submenu').forEach(parent => {
		parent.addEventListener('mouseenter', () => {
			if (!isSidebarCollapsed()) return;
			const t = submenuHideTimers.get(parent);
			if (t) { clearTimeout(t); submenuHideTimers.delete(parent); }
			const toggle = parent.querySelector('.submenu-toggle');
			if (toggle) toggle.setAttribute('aria-expanded', 'true');
		});
		parent.addEventListener('mouseleave', () => {
			if (!isSidebarCollapsed()) return;
			// Set delay before allowing CSS to transition to hidden (by removing :hover state)
			const timer = setTimeout(() => {
				const toggle = parent.querySelector('.submenu-toggle');
				if (toggle) toggle.setAttribute('aria-expanded', 'false');
				submenuHideTimers.delete(parent);
			}, 160); // matches CSS hide delay (~120ms + buffer)
			submenuHideTimers.set(parent, timer);
		});
	});
	/* =============================
	   Modern Toast Notifications (Monochromatic Theme)
	   ============================= */
	if (!window.__notyfInstance) {
		window.__notyfInstance = new Notyf({
			position: { x: 'right', y: window.innerWidth < 768 ? 'bottom' : 'top' },
			duration: 4200,
			dismissible: true,
			// Monochromatic red/black/white theme for toasts
			types: [
				{
					type: 'success',
					background: 'linear-gradient(135deg, #7f1d1d, #450a0a)'
				},
				{
					type: 'error',
					background: 'linear-gradient(135deg, #262626, #0a0a0a)'
				},
				{
					type: 'warning',
					background: 'linear-gradient(135deg, #a3a3a3, #525252)'
				},
				{
					type: 'info',
					background: 'linear-gradient(135deg, #b91c1c, #7f1d1d)'
				}
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
	const MANUAL_EXPAND_CLASS = 'sidebar-expanded-manual';

	function applyTooltips(enable) {
		// When collapsed, show tooltips using label text as title
		const elements = document.querySelectorAll('.sidebar .nav-link, .sidebar .guest-actions .btn');
		elements.forEach(el => {
			const label = el.querySelector('.label-text');
			const userInitials = el.querySelector('.user-initials-sidebar');
			if (!label && !userInitials) return;

			// Always dispose any existing tooltip to avoid duplicates
			if (window.bootstrap && window.bootstrap.Tooltip) {
				const existing = window.bootstrap.Tooltip.getInstance(el);
				if (existing) existing.dispose();
			}
			if (enable) {
				el.setAttribute('data-bs-toggle', 'tooltip');
				el.setAttribute('data-bs-placement', 'right');
				// Use appropriate title text
				if (userInitials) {
					el.setAttribute('title', 'My Profile');
				} else if (label) {
					el.setAttribute('title', label.textContent.trim());
				}
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
		if (window.innerWidth >= MIN_DESKTOP_COLLAPSE) {
			body.classList.remove('sidebar-collapsed-responsive');
			body.classList.remove(MANUAL_EXPAND_CLASS);
			applyTooltips(body.classList.contains('sidebar-collapsed'));
			updateToggleIcon();
			return;
		}

		const manualExpand = body.classList.contains(MANUAL_EXPAND_CLASS);
		if (manualExpand) {
			body.classList.remove('sidebar-collapsed-responsive');
		} else {
			body.classList.add('sidebar-collapsed-responsive');
		}
		applyTooltips(body.classList.contains('sidebar-collapsed') || body.classList.contains('sidebar-collapsed-responsive'));
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
			if (window.innerWidth < MIN_DESKTOP_COLLAPSE) {
				if (body.classList.contains('sidebar-collapsed')) {
					body.classList.remove(MANUAL_EXPAND_CLASS);
				} else {
					body.classList.add(MANUAL_EXPAND_CLASS);
				}
			} else {
				body.classList.remove(MANUAL_EXPAND_CLASS);
			}
			updateResponsiveCollapse();
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
			// Re-select icon each time to support both <i data-lucide> and rendered <svg data-lucide>
			const iconEl = toggle.querySelector('svg[data-lucide]') || toggle.querySelector('i[data-lucide]') || toggle.querySelector('i');
			if (iconEl) {
				const next = isPassword ? 'eye-off' : 'eye';
				iconEl.setAttribute('data-lucide', next);
				try { createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}
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
	const pageLoader = document.getElementById('navPageLoader');
	let mobileSidebarHydrated = false;
	function showPageLoader() { if (pageLoader) pageLoader.classList.remove('d-none'); }
	function hidePageLoader() { if (pageLoader) pageLoader.classList.add('d-none'); }
	if (mobileSidebar && window.bootstrap) {
		let navTapLocked = false;
		window.addEventListener('pageshow', hidePageLoader);

		// On first open, clone the desktop sidebar nav and bottom actions into the mobile offcanvas
		mobileSidebar.addEventListener('show.bs.offcanvas', () => {
			if (mobileSidebarHydrated) return;
			try {
				const mount = mobileSidebar.querySelector('#mobileSidebarMount');
				const desktopAside = document.querySelector('aside.sidebar.sidebar-fixed');
				if (mount && desktopAside) {
					// Clone nav
					const desktopNav = desktopAside.querySelector('nav.nav');
					const bottom = desktopAside.querySelector('.mt-auto');
					if (desktopNav) {
						const navClone = desktopNav.cloneNode(true);
						mount.appendChild(navClone);
					}
					if (bottom) {
						const bottomClone = bottom.cloneNode(true);
						mount.appendChild(bottomClone);
					}
					// Re-bind any delegated events or tooltip behavior for the cloned content
					applySavedSubmenuStates();
					applyTooltips(isSidebarCollapsed());
					try { replaceBootstrapIcons(mount); createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}
					mobileSidebarHydrated = true;
				}
			} catch {}
		});

		// Apply submenu states when offcanvas is fully shown
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
					if (labelSpan) {
						labelSpan.innerHTML = '<i data-lucide="upload" class="me-1"></i>Publish';
						try { createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}
					}
					btn.setAttribute('aria-label','Publish event');
				} else if (data.status === 'published') {
					btn.classList.add('btn-outline-warning');
					if (labelSpan) {
						labelSpan.innerHTML = '<i data-lucide="undo-2" class="me-1"></i>Revert';
						try { createIcons({ icons, attrs: { class: 'icon', 'aria-hidden': 'true' } }); } catch {}
					}
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
