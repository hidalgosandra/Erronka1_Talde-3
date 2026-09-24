<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'inicio')->name('inicio');
Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/mis-cursos', [EnrollmentController::class, 'index'])->name('courses.mine');
    Route::get('/cursos/{course}/inscribirme', [EnrollmentController::class, 'create'])->name('courses.join');
    Route::post('/cursos/{course}/inscripciones', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::post('/admin/cursos', [AdminCourseController::class, 'store'])->middleware('can:access-admin')->name('admin.courses.store');
    Route::get('/admin', [AdminController::class, 'index'])->middleware('can:access-admin')->name('admin.index');
    Route::view('/mi-espacio', 'dashboard')->name('dashboard');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
