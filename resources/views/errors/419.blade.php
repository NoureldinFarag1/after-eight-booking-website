@extends('layouts.app')

@section('title', 'Page Expired')

@section('content')
    <div class="mx-auto max-w-xl text-center py-16">
        <h1 class="text-6xl font-extrabold text-yellow-400 mb-6">419</h1>
        <h2 class="text-2xl font-semibold mb-4">Page Expired</h2>
        <p class="text-gray-300 mb-6">Your session has expired for security reasons (usually after being idle or due to a token mismatch).</p>
        <p class="text-gray-400 mb-8">Please refresh or log in again to continue.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <form method="GET" action="{{ url()->current() }}">
                <button class="px-6 py-3 rounded bg-indigo-600 hover:bg-indigo-500 transition font-medium">Refresh Page</button>
            </form>
            @auth
                <a href="{{ route('home') }}" class="px-6 py-3 rounded bg-gray-700 hover:bg-gray-600 transition font-medium">Go Home</a>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="px-6 py-3 rounded bg-gray-700 hover:bg-gray-600 transition font-medium">Login Again</a>
            @endguest
        </div>
    </div>
@endsection
