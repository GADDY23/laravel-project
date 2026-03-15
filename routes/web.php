<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\CourseStrandController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::resource('users', UserController::class);
        
        // Rooms
        Route::resource('rooms', RoomController::class);
        
        // Sections
        Route::resource('sections', SectionController::class);
        
        // Subjects
        Route::resource('subjects', SubjectController::class);
        
        // Curricula
        Route::resource('curricula', CurriculumController::class);

        // Course/Strands
        Route::resource('course-strands', CourseStrandController::class);
        
        // Terms
        Route::patch('terms/{term}/status', [TermController::class, 'updateStatus'])->name('terms.update-status');
        Route::resource('terms', TermController::class);
        
        // Schedules - Define specific routes BEFORE resource route to avoid conflicts
        Route::get('schedules/configure', [ScheduleController::class, 'configure'])->name('schedules.configure');
        Route::get('schedules/timetable/view', [ScheduleController::class, 'timetableView'])->name('schedules.timetable-view');
        Route::get('schedules/timetable/edit', [ScheduleController::class, 'timetableEdit'])->name('schedules.timetable-edit');
        Route::get('schedules/timetable', [ScheduleController::class, 'timetable'])->name('schedules.timetable');
        Route::post('schedules/store-from-timetable', [ScheduleController::class, 'storeFromTimetable'])->name('schedules.store-from-timetable');
        Route::post('schedules/check-conflicts', [ScheduleController::class, 'checkConflictsAjax'])->name('schedules.check-conflicts');
        Route::post('schedules/save-draft', [ScheduleController::class, 'saveDraftWeek'])->name('schedules.save-draft');
        Route::delete('schedules/delete-group', [ScheduleController::class, 'destroyGroup'])->name('schedules.destroy-group');
        Route::post('schedules/publish-week', [ScheduleController::class, 'publishWeek'])->name('schedules.publish-week');
        Route::resource('schedules', ScheduleController::class);
        
    });
});

require __DIR__.'/auth.php';
