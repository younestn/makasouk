<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['name' => config('app.name'), 'status' => 'ok']));

Route::view('/app/{any?}', 'spa')->where('any', '.*');
