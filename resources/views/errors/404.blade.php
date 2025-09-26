@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
    <div class="mx-auto max-w-xl text-center py-16">
        <h1 class="text-6xl font-extrabold text-red-500 mb-6">404</h1>
        <h2 class="text-2xl font-semibold mb-4">Page Not Found</h2>
        <p class="text-gray-300 mb-8">The page you're looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
            <a href="{{ route('home') }}" class="px-6 py-3 rounded bg-indigo-600 hover:bg-indigo-500 transition font-medium">Go Home</a>
            <button onclick="history.back()" class="px-6 py-3 rounded bg-gray-700 hover:bg-gray-600 transition font-medium">Go Back</button>
        </div>
        <div class="max-w-md mx-auto">
            <form action="{{ url('/') }}" method="GET" onsubmit="return false;" class="relative">
                <input disabled type="text" placeholder="Search (future enhancement)" class="w-full px-4 py-3 rounded bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-500 focus:outline-none" />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">🔍</span>
            </form>
        </div>
    </div>
@endsection
