<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'public-site');
Route::view('/how-it-works', 'public-site');
Route::view('/for-customers', 'public-site');
Route::view('/for-tailors', 'public-site');
Route::view('/faq', 'public-site');
Route::view('/contact', 'public-site');

Route::view('/app/{any?}', 'spa')->where('any', '.*');
