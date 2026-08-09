@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900">Welcome back</h2>
        <p class="text-sm text-slate-500 mt-1">Sign in to your member account.</p>

        @if($errors->any())
            <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('web.login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input type="password" name="password" required
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5 transition-colors">
                Sign in
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            New member?
            <a href="{{ route('web.register') }}" class="text-brand-600 hover:text-brand-700 font-medium">Create an account</a>
        </p>
    </div>
@endsection
