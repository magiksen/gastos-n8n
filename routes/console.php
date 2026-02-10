<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bcv:actualizar-tasa')->dailyAt('23:00');
Schedule::command('gastos:resumen-diario')->dailyAt('08:00');
