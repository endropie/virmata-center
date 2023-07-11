<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware(['web'])->group(function () {
    Route::get('/csrf_token/faker', function (\Illuminate\Http\Request $request) {
        $user = $request->get('auth')
            ? \App\Models\User::find($request->get('auth'))
            : \App\Models\User::first();

        /** @var \App\Models\User $user */
        $token = $user->token();
        return response()->json(["token" => $token]);
    });

    Route::middleware('auth')->get('/csrf_token', function () {
        $user = auth()->user();
        /** @var \App\Models\User $user */
        $token = $user->token();
        return response()->json(["token" => $token]);
    });

    Route::get('/', function () {
        return 'This is account application.';
    });
});


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
