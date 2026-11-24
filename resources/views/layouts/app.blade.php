<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Daily Reporting System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#F7F8FA]">
    <div class="min-h-full flex flex-col md:flex-row" x-data="{ mobileMenuOpen: false }">
        <!-- Mobile Header Bar -->
        <div class="md:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="h-8 w-8 rounded-lg bg-teal-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                    DR
                </div>
                <div>
                    <h1 class="text-sm font-semibold tracking-wide">Daily Report</h1>
                    <p class="text-[11px] text-slate-400 truncate max-w-[180px]">
                        {{ auth()->user()->hotel?->name ?? 'Hospitality Portal' }}
                    </p>
                </div>
            </div>
            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-slate-400 hover:text-white focus:outline-none p-1.5 rounded-md hover:bg-slate-800" aria-label="Toggle Navigation">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Off-Canvas Backdrop -->
        <div x-show="mobileMenuOpen"
             x-cloak
             @click="mobileMenuOpen = false"
             class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs md:hidden"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <!-- Sidebar Navigation (Desktop + Mobile Slideover) -->
        <aside :class="mobileMenuOpen ? 'fixed inset-y-0 left-0 z-50 flex' : 'hidden md:flex'"
               class="w-64 bg-slate-900 flex-shrink-0 flex-col border-r border-slate-800">
            <!-- App Branding (Desktop) -->
            <div class="h-16 hidden md:flex items-center px-6 bg-slate-950/60 border-b border-slate-800/80">
                <div class="flex items-center space-x-3">
                    <div class="h-9 w-9 rounded-lg bg-teal-600 flex items-center justify-center text-white font-bold shadow-sm">
                        DR
                    </div>
                    <div>
                        <h1 class="text-sm font-semibold text-white tracking-wide">Daily Report</h1>
                        <p class="text-xs text-slate-400 truncate max-w-[150px]">
                            {{ auth()->user()->hotel?->name ?? 'Hospitality Portal' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Header Close -->
            <div class="h-14 flex md:hidden items-center justify-between px-4 bg-slate-950/80 border-b border-slate-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Navigation</span>
                <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-teal-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('reports.index') }}" 
                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reports.index') ? 'bg-teal-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('reports.index') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Daily Reports
                </a>

                @if(auth()->user()->isAdmin())
                <div class="pt-4 pb-1">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Administration</p>
                </div>

                <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors opacity-80 cursor-default">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Hotels & Settings
                </a>

                <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors opacity-80 cursor-default">
                    <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users & Roles
                </a>
                @endif
            </nav>

            <!-- User footer profile -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between px-2 py-1.5">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-teal-800 flex items-center justify-center text-white text-xs font-semibold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-teal-900/60 text-teal-300 border border-teal-700/50">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="text-slate-400 hover:text-rose-400 transition-colors p-1 rounded-md hover:bg-slate-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase hidden sm:inline">Property:</span>
                    <span class="text-xs sm:text-sm font-semibold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200 truncate max-w-[200px] sm:max-w-none">
                        {{ auth()->user()->hotel?->name ?? 'Default Property' }}
                    </span>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-xs text-slate-500 font-medium">
                        {{ now()->format('l, d F Y') }}
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
