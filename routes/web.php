<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PublicDashboardController;

Route::get('/', [PublicDashboardController::class, 'landing'])->name('public.landing');
Route::get('/data', [PublicDashboardController::class, 'data'])->name('public.data');
Route::get('/data/fullscreen', [PublicDashboardController::class, 'fullscreen'])->name('public.data.fullscreen');
Route::get('/data/download/pdf', [PublicDashboardController::class, 'downloadTablePdf'])->name('public.data.download.pdf');
Route::get('/data/download/excel', [PublicDashboardController::class, 'downloadTableExcel'])->name('public.data.download.excel');
Route::get('/grafik', [PublicDashboardController::class, 'charts'])->name('public.charts');
Route::get('/grafik/fullscreen', [PublicDashboardController::class, 'chartsFullscreen'])->name('public.charts.fullscreen');
Route::get('/grafik/download/pdf', [PublicDashboardController::class, 'downloadChartPdf'])->name('public.charts.download.pdf');
Route::get('/compare', [PublicDashboardController::class, 'compare'])->name('public.compare');
Route::get('/compare/fullscreen', [PublicDashboardController::class, 'compareFullscreen'])->name('public.compare.fullscreen');
Route::get('/compare/download/pdf', [PublicDashboardController::class, 'downloadComparePdf'])->name('public.compare.download.pdf');
Route::get('/terms', [PublicDashboardController::class, 'terms'])->name('public.terms');

Route::get('/dashboard', function () {
    return redirect()->route('public.data');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::redirect('/admin', '/serdadu/admin');

Route::middleware(['auth', 'admin'])->group(function () {
    // Admin Dashboard Routes
    Route::get('/serdadu/admin', [App\Http\Controllers\AdminDashboardController::class, 'landing'])->name('admin.landing');
    Route::get('/serdadu/admin/data', [App\Http\Controllers\AdminDashboardController::class, 'data'])->name('admin.data');
    Route::get('/serdadu/admin/data/fullscreen', [App\Http\Controllers\AdminDashboardController::class, 'fullscreen'])->name('admin.data.fullscreen');
    Route::get('/serdadu/admin/grafik', [App\Http\Controllers\AdminDashboardController::class, 'charts'])->name('admin.charts');
    Route::get('/serdadu/admin/grafik/fullscreen', [App\Http\Controllers\AdminDashboardController::class, 'chartsFullscreen'])->name('admin.charts.fullscreen');
    Route::get('/serdadu/admin/compare', [App\Http\Controllers\AdminDashboardController::class, 'compare'])->name('admin.compare');
    Route::get('/serdadu/admin/compare/fullscreen', [App\Http\Controllers\AdminDashboardController::class, 'compareFullscreen'])->name('admin.compare.fullscreen');
    Route::get('/serdadu/admin/compare/download/pdf', [App\Http\Controllers\AdminDashboardController::class, 'downloadComparePdf'])->name('admin.compare.download.pdf');

    Route::get('/serdadu/admin/data/download/pdf', [App\Http\Controllers\AdminDashboardController::class, 'downloadTablePdf'])->name('admin.data.download.pdf');
    Route::get('/serdadu/admin/data/download/excel', [App\Http\Controllers\AdminDashboardController::class, 'downloadTableExcel'])->name('admin.data.download.excel');
    
    Route::get('/serdadu/admin/grafik/download/pdf', [App\Http\Controllers\AdminDashboardController::class, 'downloadChartPdf'])->name('admin.charts.download.pdf');
    
    // Admin Features
    Route::get('/serdadu/admin/import', [App\Http\Controllers\AdminDashboardController::class, 'import'])->name('admin.import');
    Route::post('/serdadu/admin/import', [ImportController::class, 'store'])->name('import.store');
    Route::post('/serdadu/admin/import/reset', [ImportController::class, 'reset'])->name('import.reset');
    
    Route::get('/serdadu/admin/download-logs', [App\Http\Controllers\AdminDashboardController::class, 'downloadLogs'])->name('admin.download-logs');
    Route::get('/serdadu/admin/download-logs/count', [App\Http\Controllers\AdminDashboardController::class, 'getUnseenCount'])->name('admin.download-logs.count');
    
    Route::get('/serdadu/admin/account', [App\Http\Controllers\AdminDashboardController::class, 'account'])->name('admin.account');
    Route::patch('/serdadu/admin/account', [App\Http\Controllers\AdminDashboardController::class, 'updateAccount'])->name('admin.account.update');
});



require __DIR__.'/auth.php';
