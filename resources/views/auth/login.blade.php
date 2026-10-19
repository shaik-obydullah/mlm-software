@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="bg-white rounded-2xl shadow-xl shadow-brand-900/5 border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-8 py-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-lg font-bold">M</div>
                <div>
                    <h2 class="text-lg font-bold leading-tight">Welcome back</h2>
                    <p class="text-sm text-brand-100">Sign in to your member account</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @if($errors->any())
                <div class="mb-6 flex items-start gap-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('web.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4.5 h-4.5" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com"
                               class="w-full rounded-xl border-slate-300 bg-slate-50/50 py-3.5 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4.5 h-4.5" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                               class="w-full rounded-xl border-slate-300 bg-slate-50/50 py-3.5 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-600 select-none">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Remember me
                    </label>
                    <a href="#" class="text-brand-600 hover:text-brand-700 font-medium">Forgot password?</a>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold py-3 shadow-md shadow-brand-600/25 transition-all hover:shadow-lg hover:shadow-brand-600/30 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                New member?
                <a href="{{ route('web.register') }}" class="text-brand-600 hover:text-brand-700 font-medium">Create an account</a>
            </p>

            <div class="mt-6 rounded-xl bg-slate-50 border border-dashed border-slate-300 px-4 py-3 text-xs text-slate-500">
                <span class="font-semibold text-slate-600">Demo:</span>
                <code class="text-slate-700">admin@mlm.local</code> / <code class="text-slate-700">password</code>
            </div>
        </div>
    </div>
@endsection
