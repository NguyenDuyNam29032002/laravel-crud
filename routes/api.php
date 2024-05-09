<?php

use App\Http\Controllers\Api\Version1\V1Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/', [V1Controller::class, 'index']);
    Route::post('/', [V1Controller::class, 'store']);
    Route::get('/{id}/detail-entity', [V1Controller::class, 'show']);
    Route::patch('/{id}/update-entity', [V1Controller::class, 'update']);
    Route::delete('/{id}/delete-entity', [V1Controller::class, 'delete']);
    Route::delete('delete-by-ids', [V1Controller::class, 'deleteByIds']);
    Route::post('test-traits', [V1Controller::class, 'test']);
});
