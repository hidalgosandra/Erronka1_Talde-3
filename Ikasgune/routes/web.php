<?php

Route::post('/idioma', \App\Http\Controllers\LocaleController::class)->name('locale.update');

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('inicio');
Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/forgot-password', [PasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
    Route::post('/register/reenviar', [RegistrationController::class, 'resend'])->middleware('throttle:1,1')->name('register.resend');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/register/verificar', [RegistrationController::class, 'verify'])->name('register.verify');
    Route::post('/register/verificar', [RegistrationController::class, 'verifyStore'])->middleware('throttle:10,1')->name('register.verify.store');
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/mis-cursos', [EnrollmentController::class, 'index'])->name('courses.mine');
    Route::get('/cursos/{course}/inscribirme', [EnrollmentController::class, 'create'])->name('courses.join');
    Route::post('/cursos/{course}/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::post('/admin/cursos', [AdminCourseController::class, 'store'])->middleware('can:access-admin')->name('admin.courses.store');
    Route::put('/admin/cursos/{course}', [AdminCourseController::class, 'update'])->middleware('can:access-admin')->name('admin.courses.update');
    Route::delete('/admin/cursos/{course}', [AdminCourseController::class, 'destroy'])->middleware('can:access-admin')->name('admin.courses.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->middleware('can:access-admin')->name('admin.index');
    Route::post('/admin/usuarios', [AdminUserController::class, 'store'])->middleware('can:access-admin')->name('admin.users.store');
    Route::put('/admin/usuarios/{user}', [AdminUserController::class, 'update'])->middleware('can:access-admin')->name('admin.users.update');
    Route::delete('/admin/usuarios/{user}', [AdminUserController::class, 'destroy'])->middleware('can:access-admin')->name('admin.users.destroy');
    Route::get('/mi-espacio', DashboardController::class)->name('dashboard');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
