@php
    $authUser = auth()->user();
    $userInitials = null;
    $userFirstName = null;

    if ($authUser) {
        $nameParts = preg_split('/\s+/', trim($authUser->name));
        $firstNamePart = $nameParts[0] ?? $authUser->name;
        $secondNamePart = $nameParts[1] ?? '';
        $userFirstName = $firstNamePart ?: $authUser->name;

        $initials = strtoupper(substr($firstNamePart, 0, 1) . ($secondNamePart ? substr($secondNamePart, 0, 1) : ''));
        $userInitials = $initials !== '' ? $initials : strtoupper(substr($authUser->name, 0, 1));

        // Light-weight count of user's requests needing attention (pending or awaiting payment)
        try {
            $myRequestsAttentionCount = \App\Models\EventRequest::where('user_id', $authUser->id)
                ->whereIn('status', ['pending','awaiting_payment'])
                ->count();
        } catch (Throwable $e) {
            $myRequestsAttentionCount = 0;
        }
    }
@endphp
<div class="home-nav home-nav--sticky mb-4">
    <div class="home-nav__top">
        <div class="brand">
            <a href="{{ route('home') }}" class="d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight logo" />
            </a>
        </div>

        <div class="home-search d-none d-lg-flex">
            <i class="search-icon" data-lucide="search"></i>
            <form action="{{ route('events.index') }}" method="get" class="w-100">
                <input type="search" name="q" placeholder="Search for events..." class="form-control" />
            </form>
        </div>

        <div class="home-nav__actions ms-auto d-flex align-items-center gap-3">
            <div class="nav-links d-none d-lg-flex align-items-center">
                <a href="{{ route('events.index') }}">
                    <i data-lucide="globe" class="me-1"></i>Events
                </a>

                @if($authUser)
                    <a href="{{ route('bookings.index') }}">
                        <i data-lucide="calendar" class="me-1"></i>Bookings
                    </a>
                    <a href="{{ route('user.profile.index') }}">
                        <i data-lucide="settings" class="me-1"></i>Preferences
                    </a>
                @else
                    <a href="{{ route('login') }}">
                        <i data-lucide="log-in" class="me-1"></i>Login
                    </a>
                @endif
            </div>

            @if($authUser)
                <!-- Requests icon-only button (desktop) -->
                <a href="{{ route('event_requests.index') }}" class="d-none d-lg-inline-flex align-items-center position-relative nav-link-button p-0" title="Requests" aria-label="Requests">
                    <i data-lucide="bell"></i>
                    @if(($myRequestsAttentionCount ?? 0) > 0)
                        <span class="badge rounded-pill bg-danger position-absolute" style="top:-6px; right:-6px; font-size:10px; line-height:1;">{{ $myRequestsAttentionCount }}</span>
                    @endif
                </a>
            @endif

            @if($authUser)
                <div class="dropdown d-none d-lg-block" style="position: relative; overflow: visible;">
                    <button
                        class="user-initials-avatar home-nav__initial-chip dropdown-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-offset="0,8"
                        data-bs-boundary="viewport"
                        data-bs-display="static"
                        aria-expanded="false"
                        aria-label="User menu"
                    >{{ $userInitials }}</button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        @include('partials.user-menu-items', ['authUser' => $authUser])
                    </ul>
                </div>
            @endif

            <button
                type="button"
                class="home-nav__mobile-toggle d-lg-none"
                id="mobileNavToggle"
                aria-label="Toggle navigation"
                aria-expanded="false"
                aria-controls="mobileNavContent"
            >
                <svg class="icon icon-menu" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 6h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>
    <div class="home-nav__mobile-content d-lg-none" id="mobileNavContent">
        <div class="home-nav__mobile-inner">
            <div class="home-search mobile">
                <i class="search-icon" data-lucide="search"></i>
                <form action="{{ route('events.index') }}" method="get" class="w-100">
                    <input type="search" name="q" placeholder="Search for events..." class="form-control" />
                </form>
            </div>

            <div class="nav-links mobile">
                <a href="{{ route('events.index') }}" class="nav-link-mobile">
                    <i data-lucide="globe"></i>
                    <span>Events</span>
                </a>

                @if($authUser)
                    <a href="{{ route('bookings.index') }}" class="nav-link-mobile">
                        <i data-lucide="calendar"></i>
                        <span>Bookings</span>
                    </a>
                    <a href="{{ route('event_requests.index') }}" class="nav-link-mobile position-relative">
                        <i data-lucide="bell"></i>
                        <span>Requests</span>
                        @if(($myRequestsAttentionCount ?? 0) > 0)
                            <span class="badge bg-danger ms-2">{{ $myRequestsAttentionCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('user.profile.index') }}" class="nav-link-mobile">
                        <i data-lucide="settings"></i>
                        <span>Preferences</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="nav-link-mobile-form">
                        @csrf
                        <button type="submit" class="nav-link-mobile">
                            <i data-lucide="log-out"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link-mobile">
                        <i data-lucide="log-in"></i>
                        <span>Login</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const initMobileNav = () => {
    const toggle = document.getElementById('mobileNavToggle');
    const content = document.getElementById('mobileNavContent');
    const contentInner = content ? content.querySelector('.home-nav__mobile-inner') : null;
    const navShell = document.querySelector('.home-nav');

    if (toggle && content && contentInner) {
        let closeTransitionHandler = null;

        const setMaxHeight = (value) => {
            content.style.maxHeight = typeof value === 'number' ? `${value}px` : value;
        };

        const openMenu = () => {
            if (content.classList.contains('is-open') && !content.classList.contains('is-closing')) {
                return;
            }

            if (closeTransitionHandler) {
                content.removeEventListener('transitionend', closeTransitionHandler);
                closeTransitionHandler = null;
            }

            toggle.setAttribute('aria-expanded', 'true');
            navShell?.classList.add('home-nav--expanded');

            const targetHeight = contentInner.scrollHeight;
            content.classList.add('is-open');
            content.classList.remove('is-closing');
            setMaxHeight(0);

            requestAnimationFrame(() => {
                setMaxHeight(targetHeight);
            });

            const handleOpenEnd = (event) => {
                if (event.propertyName !== 'max-height') {
                    return;
                }
                setMaxHeight('none');
                content.removeEventListener('transitionend', handleOpenEnd);
            };

            content.addEventListener('transitionend', handleOpenEnd, { once: true });
        };

        const closeMenu = () => {
            if (!content.classList.contains('is-open') || content.classList.contains('is-closing')) {
                return;
            }

            toggle.setAttribute('aria-expanded', 'false');

            const currentHeight = contentInner.scrollHeight;
            setMaxHeight(currentHeight);
            content.classList.add('is-closing');

            requestAnimationFrame(() => {
                setMaxHeight(0);
            });

            closeTransitionHandler = (event) => {
                if (event.propertyName !== 'max-height') {
                    return;
                }
                content.classList.remove('is-open', 'is-closing');
                setMaxHeight('');
                navShell?.classList.remove('home-nav--expanded');
                content.removeEventListener('transitionend', closeTransitionHandler);
                closeTransitionHandler = null;
            };

            content.addEventListener('transitionend', closeTransitionHandler);
        };

        toggle.addEventListener('click', () => {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            isExpanded ? closeMenu() : openMenu();
        });

        content.querySelectorAll('a.nav-link-mobile, button.nav-link-mobile').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keyup', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileNav, { once: true });
    } else {
        initMobileNav();
    }
})();
</script>

<style>
/* Ensure dropdowns are not clipped by the pill nav shell */
.home-nav,
.home-nav__top,
.home-nav__actions {
    overflow: visible !important;
    /* Defuse any clipping primitives that may be applied for the pill effect */
    clip-path: none !important;
    -webkit-clip-path: none !important;
    mask: none !important;
    -webkit-mask: none !important;
    /* Avoid layout containment from clipping positioned children */
    contain: none !important;
}

/* Keep the dropdown positioned relative to the avatar and above surrounding layers */
.dropdown { position: relative !important; }
.dropdown-menu { z-index: 2000 !important; }
</style>
