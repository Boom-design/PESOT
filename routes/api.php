<?php

/**
 * API Routes Configuration
 *
 * This file defines all API endpoints for the PESO (Public Employment Service Office) backend.
 * Routes are organized into:
 * - Public routes: User authentication (register/login)
 * - Job routes: Job browsing (publicly accessible)
 * - Protected routes: Require Sanctum token authentication
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationFormController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EmployerRequirementController;

// ════════════════════════════════════════════════════════════
// PUBLIC ROUTES - User Authentication
// ════════════════════════════════════════════════════════════

/** User registration endpoint - creates a new user account */
Route::post('/register', [AuthController::class, 'register']);

/** User login endpoint - authenticates user and returns access token */
Route::post('/login',    [AuthController::class, 'login']);

// ════════════════════════════════════════════════════════════
// JOB ROUTES - Public Job Browsing
// ════════════════════════════════════════════════════════════ 

/** Fetch all available job listings */
Route::get('/jobs',        [JobController::class, 'index']);

/** Fetch the 5 most recent job postings */
Route::get('/jobs/latest', [JobController::class, 'latest']);

/** Get the total count of currently open job positions */
Route::get('/jobs/count',  [JobController::class, 'count']);

/** Fetch detailed information for a specific job by ID */
Route::get('/jobs/{id}',   [JobController::class, 'show']);

// ════════════════════════════════════════════════════════════
// PROTECTED ROUTES - Authenticated User Only
// ════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ── AUTH ──
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── NSRP REGISTRATION FORM (Jobseeker) ──
    Route::post('/registration-form', [RegistrationFormController::class, 'store']);
    Route::get('/registration-form',  [RegistrationFormController::class, 'show']);

    // ── NOTIFICATIONS ──
    Route::get('/notifications',              [NotificationController::class, 'userNotifications']);
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);

    // ── EMPLOYER REQUIREMENTS ──
    Route::get('/employer/requirements/status', [EmployerRequirementController::class, 'apiStatus']);
    Route::post('/employer/requirements',       [EmployerRequirementController::class, 'apiStore']);
});