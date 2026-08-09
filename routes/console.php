<?php

use App\Jobs\ProcessInterestAccrual;
use App\Services\PinService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mlm:expire-pins', function (PinService $pinService) {
    $expired = $pinService->expire();
    $this->info("Expired {$expired} PIN(s).");
})->purpose('Expire activation PINs that are past their expiry date')->daily();

Artisan::command('mlm:interest {period?}', function (?string $period = null) {
    $period = $period ?? now()->format('Y-m');
    ProcessInterestAccrual::dispatchSync($period);
    $this->info("Interest processed for {$period}.");
})->purpose('Credit monthly interest on active CV packages');

Schedule::command('mlm:expire-pins')->dailyAt('01:00');

Schedule::command('mlm:interest')->monthlyOn(1, '02:00');
