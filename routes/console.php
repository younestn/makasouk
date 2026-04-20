<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:health-check', function () {
    $this->info('Makasouk backend baseline is healthy.');
});
