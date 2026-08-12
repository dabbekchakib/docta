<?php

use App\Http\Controllers\LaboratoryReportController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\PrescriptionVerificationController;
use App\Http\Controllers\ProfileController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()?->canAccessAdminPanel()) {
        return redirect(Filament::getPanel('admin')->getUrl());
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/documents-medicaux/{medicalDocument}/telecharger', [MedicalDocumentController::class, 'download'])
        ->name('medical-documents.download');

    Route::get('/comptes-rendus-laboratoire/{laboratoryReport}/telecharger', [LaboratoryReportController::class, 'download'])
        ->name('laboratory-reports.download');

    Route::get('/ordonnances/verification/{token}', [PrescriptionVerificationController::class, 'show'])
        ->name('prescriptions.verify');
});

require __DIR__.'/auth.php';
