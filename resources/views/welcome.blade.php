<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name','After Eight Events') }}</title>
    <meta name="color-scheme" content="dark">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-animated-red-black">
    <div class="home-nav-shell home-nav-shell--home">
        @include('partials.pill-nav')
    </div>

    <div class="home-container">
            <!-- Hero banner -->
            @if(isset($featured) && $featured)
            <section class="mb-5">
                <div class="home-hero shadow-red-lg">
                    @if($featured->image_url)
                        <img src="{{ Storage::url($featured->image_url) }}" alt="{{ $featured->title }}" class="home-hero__img" />
                    @endif
                    <div class="position-absolute bottom-0 start-0 end-0 p-4 p-md-5" style="background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.6) 60%, rgba(0,0,0,.75) 100%);">
                        <div class="text-white">
                            <h1 class="h2 fw-bold mb-2">{{ $featured->title }}</h1>
                            <div class="d-flex flex-wrap gap-3 text-white-50 mb-3">
                                <span><i data-lucide="calendar"></i> {{ optional($featured->event_date)->format('M d, Y') }}</span>
                                <span><i data-lucide="clock"></i> {{ optional($featured->event_time)->format('h:i A') }}</span>
                                @if($featured->location)
                                    <span><i data-lucide="map-pin"></i> {{ $featured->location }}</span>
                                @endif
                            </div>
                            <a href="{{ route('events.show', $featured) }}" class="btn btn-primary btn-lg">
                                <i data-lucide="ticket"></i> View Event
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Hot Month section -->
            @if($hotMonth->count())
            <section class="events-section">
                <h2>Hot Events in {{ $currentMonthName }} <span class="section-meta">| {{ $hotMonth->count() }} Events</span></h2>
                <div class="events-scroller-container">
                    <button class="scroller-btn prev" data-scroll-target="#hot-scroller" aria-label="Scroll left"><i data-lucide="chevron-left"></i></button>
                    <div id="hot-scroller" class="events-scroller">
                        @foreach($hotMonth as $event)
                        <a href="{{ route('events.show',$event) }}" class="home-event-card lg">
                            @if($event->image_url)
                                <img class="bg" src="{{ Storage::url($event->image_url) }}" alt="{{ $event->title }}" />
                            @endif
                            <div class="overlay">
                                <div class="title">{{ $event->title }}</div>
                                <div class="meta">
                                    <span><i data-lucide="calendar"></i> {{ $event->event_date->format('M d') }}</span>
                                    <span><i data-lucide="clock"></i> {{ $event->event_time->format('h:i A') }}</span>
                                    <span><i data-lucide="map-pin"></i> {{ $event->location }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    <button class="scroller-btn next" data-scroll-target="#hot-scroller" aria-label="Scroll right"><i data-lucide="chevron-right"></i></button>
                </div>
            </section>
            @endif

            <!-- Today section -->
            @if($today->count())
            <section class="events-section">
                <h2>Today</h2>
                <div class="events-scroller-container">
                    <button class="scroller-btn prev" data-scroll-target="#today-scroller" aria-label="Scroll left"><i data-lucide="chevron-left"></i></button>
                    <div id="today-scroller" class="events-scroller">
                        @foreach($today as $event)
                        <a href="{{ route('events.show',$event) }}" class="home-event-card">
                            @if($event->image_url)
                                <img class="bg" src="{{ Storage::url($event->image_url) }}" alt="{{ $event->title }}" />
                            @endif
                            <div class="overlay">
                                <div class="title">{{ $event->title }}</div>
                                <div class="meta">
                                    <span><i data-lucide="calendar"></i> {{ $event->event_date->format('M d') }}</span>
                                    <span><i data-lucide="clock"></i> {{ $event->event_time->format('h:i A') }}</span>
                                    <span><i data-lucide="map-pin"></i> {{ $event->location }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    <button class="scroller-btn next" data-scroll-target="#today-scroller" aria-label="Scroll right"><i data-lucide="chevron-right"></i></button>
                </div>
            </section>
            @endif

            <!-- Tomorrow section -->
            @if($tomorrow->count())
            <section class="events-section">
                <h2>Tomorrow</h2>
                <div class="events-scroller-container">
                    <button class="scroller-btn prev" data-scroll-target="#tomorrow-scroller" aria-label="Scroll left"><i data-lucide="chevron-left"></i></button>
                    <div id="tomorrow-scroller" class="events-scroller">
                        @foreach($tomorrow as $event)
                        <a href="{{ route('events.show',$event) }}" class="home-event-card">
                            @if($event->image_url)
                                <img class="bg" src="{{ Storage::url($event->image_url) }}" alt="{{ $event->title }}" />
                            @endif
                            <div class="overlay">
                                <div class="title">{{ $event->title }}</div>
                                <div class="meta">
                                    <span><i data-lucide="calendar"></i> {{ $event->event_date->format('M d') }}</span>
                                    <span><i data-lucide="clock"></i> {{ $event->event_time->format('h:i A') }}</span>
                                    <span><i data-lucide="map-pin"></i> {{ $event->location }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    <button class="scroller-btn next" data-scroll-target="#tomorrow-scroller" aria-label="Scroll right"><i data-lucide="chevron-right"></i></button>
                </div>
            </section>
            @endif


            <!-- Footer -->
            <footer class="mt-5 text-center text-muted small">
                <div>© {{ date('Y') }} After Eight. All rights reserved.</div>
                <div>
                    <a href="{{ route('events.index') }}" class="text-decoration-none me-3">Browse Events</a>
                    <a href="mailto:support@aftereight.com" class="text-decoration-none">Contact Support</a>
                </div>
            </footer>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
