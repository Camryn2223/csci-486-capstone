<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\OrganizationInviteController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationUserPermissionController;
use App\Http\Controllers\TemplateFieldController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));

// Public Job Board: Guests can view the list of positions
Route::get('organizations/{organization}/job-positions', [JobPositionController::class, 'index'])
    ->name('organizations.job-positions.index');

// Public Application Flow
Route::get(
    'organizations/{organization}/job-positions/{jobPosition}/apply',
    [ApplicationController::class, 'create']
)->name('applications.create');

Route::post(
    'organizations/{organization}/job-positions/{jobPosition}/apply',
    [ApplicationController::class, 'store']
)->name('applications.store');

Route::post(
    'applications/{application}/documents',
    [DocumentController::class, 'store']
)->name('documents.store');

// Registration
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'create'])->name('register');
    Route::post('register', [AuthController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Private Routes (Auth & Verified Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();
        $orgs = $user->isChairman()
            ? $user->ownedOrganizations()
            : $user->organizations();

        if ($orgs->count() === 1) {
            return redirect()->route('organizations.show', $orgs->first());
        }

        return view('dashboard');
    })->name('dashboard');
    Route::redirect('/home', '/dashboard');

    /*
    |----------------------------------------------------------------------
    | Organizations Management
    |----------------------------------------------------------------------
    */
    Route::resource('organizations', OrganizationController::class);

    Route::prefix('organizations/{organization}')->name('organizations.')->group(function () {

        Route::get('members', [OrganizationController::class, 'members'])->name('members');
        Route::post('members', [OrganizationController::class, 'addMember'])->name('members.add');
        Route::delete('members/{user}', [OrganizationController::class, 'removeMember'])->name('members.remove');

        Route::get('permissions', [OrganizationUserPermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{user}', [OrganizationUserPermissionController::class, 'show'])->name('permissions.show');
        Route::put('permissions/{user}', [OrganizationUserPermissionController::class, 'sync'])->name('permissions.sync');
        Route::patch('members/{user}/role', [OrganizationUserPermissionController::class, 'updateRole'])->name('members.role');

        Route::get('invites', [OrganizationInviteController::class, 'index'])->name('invites.index');
        Route::post('invites', [OrganizationInviteController::class, 'store'])->name('invites.store');
        Route::delete('invites/{invite}', [OrganizationInviteController::class, 'destroy'])->name('invites.destroy');

        /*
        |----------------------------------------------------------------------
        | Job Position Management
        |----------------------------------------------------------------------
        */
        Route::get('job-positions/create', [JobPositionController::class, 'create'])->name('job-positions.create');
        Route::post('job-positions', [JobPositionController::class, 'store'])->name('job-positions.store');
        Route::get('job-positions/{jobPosition}/edit', [JobPositionController::class, 'edit'])->name('job-positions.edit');
        Route::put('job-positions/{jobPosition}', [JobPositionController::class, 'update'])->name('job-positions.update');
        Route::delete('job-positions/{jobPosition}', [JobPositionController::class, 'destroy'])->name('job-positions.destroy');

        Route::post('application-templates/preview', [ApplicationTemplateController::class, 'preview'])->name('application-templates.preview');
        Route::resource('application-templates', ApplicationTemplateController::class);

        Route::prefix('application-templates/{applicationTemplate}')->name('application-templates.fields.')->group(function () {
            Route::post('fields', [TemplateFieldController::class, 'store'])->name('store');
            
            Route::patch('fields/reorder', [TemplateFieldController::class, 'reorder'])->name('reorder');
            
            Route::patch('fields/{templateField}', [TemplateFieldController::class, 'update'])->name('update');
            Route::delete('fields/{templateField}', [TemplateFieldController::class, 'destroy'])->name('destroy');
        });

        Route::get('interviews', [InterviewController::class, 'index'])->name('interviews.index');
    });

    /*
    |----------------------------------------------------------------------
    | Staff Review: Applications & Documents
    |----------------------------------------------------------------------
    */
    Route::get(
        'organizations/{organization}/job-positions/{jobPosition}/applications',
        [ApplicationController::class, 'index']
    )->name('applications.index');

    Route::get(
        '/organizations/{organization}/applications', 
        [\App\Http\Controllers\OrganizationController::class, 'applications']
    )->name('organizations.applications');

    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::patch('applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('applications.status');
    Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    /*
    |----------------------------------------------------------------------
    | Interview Scheduling & Feedback
    |----------------------------------------------------------------------
    */
    Route::get('applications/{application}/interviews/create', [InterviewController::class, 'create'])->name('interviews.create');
    Route::post('applications/{application}/interviews', [InterviewController::class, 'store'])->name('interviews.store');
    Route::get('interviews/{interview}', [InterviewController::class, 'show'])->name('interviews.show');
    Route::get('interviews/{interview}/edit', [InterviewController::class, 'edit'])->name('interviews.edit');
    Route::patch('interviews/{interview}', [InterviewController::class, 'update'])->name('interviews.update');
    Route::patch('interviews/{interview}/cancel', [InterviewController::class, 'cancel'])->name('interviews.cancel');
    Route::patch('interviews/{interview}/complete', [InterviewController::class, 'complete'])->name('interviews.complete');
    Route::patch('interviews/{interview}/feedback', [InterviewController::class, 'submitFeedback'])->name('interviews.feedback');

    /*
    |----------------------------------------------------------------------
    | User Security
    |----------------------------------------------------------------------
    */
    Route::prefix('user/two-factor')->name('two-factor.')->group(function () {
        Route::get('/', [TwoFactorController::class, 'show'])->name('show');
        Route::post('/', [TwoFactorController::class, 'store'])->name('store');
        Route::post('confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
        Route::put('recovery-codes', [TwoFactorController::class, 'regenerateCodes'])->name('regenerate');
        Route::delete('/', [TwoFactorController::class, 'destroy'])->name('destroy');
    });
});

// Final catch-all for public routes
Route::get('organizations/{organization}/job-positions/{jobPosition}', [JobPositionController::class, 'show'])
    ->name('organizations.job-positions.show');