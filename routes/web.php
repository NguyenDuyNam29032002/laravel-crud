<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/check-database-connection', function () {
    try {
        DB::connection()->getPdo();

        return "<h1>Connected to the database!</h1>";
    } catch (\Exception $e) {
        return "<h1>" . "Unable to connect to the database. Error: " . $e->getMessage() . "</h1>";
    }
});
Route::get('/check-redis-connection', function () {
    try {
        \Illuminate\Support\Facades\Redis::connection();

        return "<h1>" . "Connected to Redis!" . "</h1>";
    } catch (\Exception $e) {
        return "<h1>" . "Unable to connect to Redis. Error: " . $e->getMessage() . "</h1>";
    }
});

Route::get('php-info', function () {
    phpinfo();
});
