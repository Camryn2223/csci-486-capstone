<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationUserPermissionController;
use App\Http\Controllers\TemplateFieldController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::redirect('/home', '/dashboard');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
| Login and logout are handled by Fortify. We only define register here
| so we can assign the default interviewer role on account creation.
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'create'])->name('register');
    Route::post('register', [AuthController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Settings
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('user/two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [TwoFactorController::class, 'show'])->name('show');
    Route::post('/', [TwoFactorController::class, 'store'])->name('store');
    Route::post('confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
    Route::put('recovery-codes', [TwoFactorController::class, 'regenerateCodes'])->name('regenerate');
    Route::delete('/', [TwoFactorController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Public application submission - no authentication required
|--------------------------------------------------------------------------
*/
Route::get(
    'organizations/{organization}/job-positions/{jobPosition}/apply',
    [ApplicationController::class, 'create']
)->name('applications.create');

Route::post(
    'organizations/{organization}/job-positions/{jobPosition}/apply',
    [ApplicationController::class, 'store']
)->name('applications.store');

/*
|--------------------------------------------------------------------------
| Public document upload - part of the application submission flow
|--------------------------------------------------------------------------
*/
Route::post(
    'applications/{application}/documents',
    [DocumentController::class, 'store']
)->name('documents.store');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Organizations
    |--------------------------------------------------------------------------
    */
    Route::resource('organizations', OrganizationController::class);

    Route::prefix('organizations/{organization}')->name('organizations.')->group(function () {

        Route::get('members', [OrganizationController::class, 'members'])
            ->name('members');

        Route::post('members', [OrganizationController::class, 'addMember'])
            ->name('members.add');

        Route::delete('members/{user}', [OrganizationController::class, 'removeMember'])
            ->name('members.remove');

        /*
        |----------------------------------------------------------------------
        | Permissions
        |----------------------------------------------------------------------
        */
        Route::get('permissions', [OrganizationUserPermissionController::class, 'index'])
            ->name('permissions.index');

        Route::post('permissions', [OrganizationUserPermissionController::class, 'store'])
            ->name('permissions.store');

        Route::delete('permissions/{permission}', [OrganizationUserPermissionController::class, 'destroy'])
            ->name('permissions.destroy');

        Route::patch('members/{user}/role', [OrganizationUserPermissionController::class, 'updateRole'])
            ->name('members.role');

        /*
        |----------------------------------------------------------------------
        | Job Positions
        |----------------------------------------------------------------------
        */
        Route::resource('job-positions', JobPositionController::class)
            ->shallow();

        /*
        |----------------------------------------------------------------------
        | Application Templates
        |----------------------------------------------------------------------
        */
        Route::resource('application-templates', ApplicationTemplateController::class)
            ->shallow();

        Route::prefix('application-templates/{applicationTemplate}')->name('application-templates.fields.')->group(function () {

            Route::post('fields', [TemplateFieldController::class, 'store'])
                ->name('store');

            Route::patch('fields/{templateField}', [TemplateFieldController::class, 'update'])
                ->name('update');

            Route::delete('fields/{templateField}', [TemplateFieldController::class, 'destroy'])
                ->name('destroy');

            Route::post('fields/reorder', [TemplateFieldController::class, 'reorder'])
                ->name('reorder');
        });

        /*
        |----------------------------------------------------------------------
        | Interviews (organization-level calendar view)
        |----------------------------------------------------------------------
        */
        Route::get('interviews', [InterviewController::class, 'index'])
            ->name('interviews.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Applications (staff review)
    |--------------------------------------------------------------------------
    */
    Route::get(
        'organizations/{organization}/job-positions/{jobPosition}/applications',
        [ApplicationController::class, 'index']
    )->name('applications.index');

    Route::get('applications/{application}', [ApplicationController::class, 'show'])
        ->name('applications.show');

    Route::patch('applications/{application}/status', [ApplicationController::class, 'updateStatus'])
        ->name('applications.status');

    /*
    |--------------------------------------------------------------------------
    | Documents (staff actions)
    |--------------------------------------------------------------------------
    */
    Route::get('documents/{document}', [DocumentController::class, 'show'])
        ->name('documents.show');

    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');

    /*
    |--------------------------------------------------------------------------
    | Interviews
    |--------------------------------------------------------------------------
    */
    Route::get('applications/{application}/interviews/create', [InterviewController::class, 'create'])
        ->name('interviews.create');

    Route::post('applications/{application}/interviews', [InterviewController::class, 'store'])
        ->name('interviews.store');

    Route::get('interviews/{interview}', [InterviewController::class, 'show'])
        ->name('interviews.show');

    Route::get('interviews/{interview}/edit', [InterviewController::class, 'edit'])
        ->name('interviews.edit');

    Route::patch('interviews/{interview}', [InterviewController::class, 'update'])
        ->name('interviews.update');

    Route::patch('interviews/{interview}/cancel', [InterviewController::class, 'cancel'])
        ->name('interviews.cancel');

    Route::patch('interviews/{interview}/complete', [InterviewController::class, 'complete'])
        ->name('interviews.complete');

    Route::patch('interviews/{interview}/feedback', [InterviewController::class, 'submitFeedback'])
        ->name('interviews.feedback');
});