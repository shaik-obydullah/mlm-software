<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ setting('site_name', 'MLM Software') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-brand-900">
    <div class="min-h-full flex flex-col lg:flex-row">
        <div class="flex-1 hidden lg:flex flex-col justify-center px-16 py-12 text-white bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700">
            <div class="max-w-md mx-auto">
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-xl font-bold mb-6">M</div>
                <h1 class="text-3xl font-bold leading-tight">{{ setting('site_name', 'MLM Software') }}</h1>
                <p class="mt-3 text-brand-200">Binary MLM platform with multi-level commissions, CV packages, PIN activation and automated wallet payouts.</p>
                <ul class="mt-8 space-y-3 text-sm text-brand-100">
                    <li class="flex gap-2"><span class="text-emerald-400">✓</span> Binary tree with auto-placement</li>
                    <li class="flex gap-2"><span class="text-emerald-400">✓</span> Sponsor, matching &amp; 7-level commissions</li>
                    <li class="flex gap-2"><span class="text-emerald-400">✓</span> CV packages &amp; PIN activation</li>
                    <li class="flex gap-2"><span class="text-emerald-400">✓</span> Wallet, withdrawals &amp; monthly interest</li>
                </ul>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center p-6 bg-slate-100 lg:rounded-l-3xl">
            <div class="w-full max-w-sm">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
