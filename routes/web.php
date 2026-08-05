<?php

use App\Http\Controllers\VerificacionCertificadoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::get('/verificar/{codigo}', VerificacionCertificadoController::class)
    ->name('certificados.verificar');
Route::get('/qr/{codigo}', [VerificacionCertificadoController::class, 'qr'])
    ->name('certificados.qr');
