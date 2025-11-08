<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostalCodeController;
use App\Http\Controllers\Api\CountyController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\ZipCodeController;

// Publikus végpontok - nincs hitelesítés
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{postalCode}', [PostalCodeController::class, 'show']);
Route::get('/postal-codes/search/{code}', [PostalCodeController::class, 'searchByCode']);
Route::get('counties', [CountyController::class, 'index']);
Route::get('counties/{county}', [CountyController::class, 'show']);
Route::get('settlements', [SettlementController::class, 'index']);
Route::get('settlements/{settlement}', [SettlementController::class, 'show']);
Route::get('zip-codes', [ZipCodeController::class, 'index']);
Route::get('zip-codes/{zipCode}', [ZipCodeController::class, 'show']);

// Védett végpontok - hitelesítés szükséges
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{postalCode}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{postalCode}', [PostalCodeController::class, 'destroy']);
});

// Védett végpontok (POST, PUT, DELETE) - csak hitelesített felhasználóknak
Route::middleware('auth:sanctum')->group(function () {
    Route::post('counties', [CountyController::class, 'store']);
    Route::put('counties/{county}', [CountyController::class, 'update']);
    Route::delete('counties/{county}', [CountyController::class, 'destroy']);

    Route::post('settlements', [SettlementController::class, 'store']);
    Route::put('settlements/{settlement}', [SettlementController::class, 'update']);
    Route::delete('settlements/{settlement}', [SettlementController::class, 'destroy']);

    Route::post('zip-codes', [ZipCodeController::class, 'store']);
    Route::put('zip-codes/{zipCode}', [ZipCodeController::class, 'update']);
    Route::delete('zip-codes/{zipCode}', [ZipCodeController::class, 'destroy']);
});