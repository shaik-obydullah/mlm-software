<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ setting('site_name', 'MLM Software') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity
             class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
             @click="sidebarOpen = false"></div>

        <aside x-show="sidebarOpen || window.innerWidth >= 1024"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-brand-900 text-white lg:translate-x-0 lg:static"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10">
                <div class="w-9 h-9 rounded-lg bg-brand-500 flex items-center justify-center font-bold text-sm">M</div>
                <div>
                    <div class="font-semibold text-sm leading-tight">{{ setting('site_name', 'MLM Software') }}</div>
                    <div class="text-[11px] text-brand-300">Member Portal</div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm" x-data>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('web.tree') }}" class="nav-link {{ request()->routeIs('web.tree') ? 'active' : '' }}">Binary Tree</a>
                <a href="{{ route('web.cv') }}" class="nav-link {{ request()->routeIs('web.cv') ? 'active' : '' }}">CV Packages</a>
                <a href="{{ route('web.commissions') }}" class="nav-link {{ request()->routeIs('web.commissions') ? 'active' : '' }}">Commissions</a>
                <a href="{{ route('web.wallet') }}" class="nav-link {{ request()->routeIs('web.wallet') ? 'active' : '' }}">Wallet</a>
                <a href="{{ route('web.reports') }}" class="nav-link {{ request()->routeIs('web.reports') ? 'active' : '' }}">Reports</a>
                <a href="{{ route('web.tickets') }}" class="nav-link {{ request()->routeIs('web.tickets*') ? 'active' : '' }}">Support</a>

                @if(auth()->user()->isAdmin())
                    <div class="pt-4 pb-1 text-[11px] uppercase tracking-wider text-brand-300 px-3">Administration</div>
                    <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Members</a>
                    <a href="{{ route('admin.pins') }}" class="nav-link">PIN Management</a>
                    <a href="{{ route('admin.withdrawals') }}" class="nav-link">Withdrawals</a>
                    <a href="{{ route('admin.kyc') }}" class="nav-link">KYC Review</a>
                    <a href="{{ route('admin.settings') }}" class="nav-link">Settings</a>
                @endif
            </nav>

            <div class="p-4 border-t border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-400 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-brand-300 truncate">{{ auth()->user()->unique_id }}</div>
                    </div>
                    <form method="POST" action="{{ route('web.logout') }}">
                        @csrf
                        <button class="text-brand-300 hover:text-white" title="Logout">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center gap-4 px-4 sm:px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-base font-semibold text-slate-900">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('web.profile') }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900">
                        <span class="hidden sm:inline">{{ auth()->user()->unique_id }}</span>
                        <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </a>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         x-transition class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         x-transition class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <style>
        .nav-link {
            @apply flex items-center gap-2 px-3 py-2 rounded-lg text-brand-100 hover:bg-white/10 hover:text-white transition-colors;
        }
        .nav-link.active {
            @apply bg-white/10 text-white font-medium;
        }
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
