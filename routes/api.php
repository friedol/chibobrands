<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HikvisionAttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by bootstrap/app.php with the 'api' prefix
| and the 'hikvision.auth' middleware guards the integration endpoint.
*/

Route::prefix('hikvision')
    ->middleware('hikvision.auth')
    ->group(function () {
        Route::post('attendance', [HikvisionAttendanceController::class, 'store'])
            ->name('api.hikvision.attendance');
    });
