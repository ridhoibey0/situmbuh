<?php

use App\Exports\RekapExport;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AgeCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Testimonial;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $testimoni = Testimonial::with('user')->latest()->take(10)->get();
    return view('welcome', compact('testimoni'));
});

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard/rekap', [DashboardController::class, 'rekap'])->name('admin.rekap');
        Route::get('/children', [App\Http\Controllers\Admin\ChildAdminController::class, 'index'])->name('admin.children.index');
        Route::get('/children/{child}', [App\Http\Controllers\Admin\ChildAdminController::class, 'show'])->whereNumber('child')->name('admin.children.show');
        Route::post('/children/{child}/staff', [App\Http\Controllers\Admin\ChildAdminController::class, 'assignStaff'])->name('admin.children.staff.assign');
        Route::delete('/children/{child}/staff/{user}', [App\Http\Controllers\Admin\ChildAdminController::class, 'removeStaff'])->name('admin.children.staff.remove');
        Route::put('/children/{child}/parent', [App\Http\Controllers\Admin\ChildAdminController::class, 'linkParent'])->name('admin.children.parent.link');
        Route::delete('/children/{child}/parent', [App\Http\Controllers\Admin\ChildAdminController::class, 'unlinkParent'])->name('admin.children.parent.unlink');
        Route::get('/stunting', [App\Http\Controllers\Admin\StuntingController::class, 'index'])->name('admin.stunting.index');
        Route::get('/stunting/{child}', [App\Http\Controllers\Admin\StuntingController::class, 'show'])->name('admin.stunting.show');
        Route::resource('age-category', AgeCategoryController::class);
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::resource('questions', App\Http\Controllers\Admin\KpspQuestionController::class);
        Route::resource('questions-category', App\Http\Controllers\Admin\QuestionCategoryController::class);
        Route::resource('blogs', App\Http\Controllers\Admin\BlogController::class);
        Route::resource('answers', App\Http\Controllers\Admin\AnswersController::class);
    });

Route::prefix('users')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Users\HomeController::class, 'index']);
        Route::resource('profile', App\Http\Controllers\Users\ProfileController::class);
        Route::get('/children', [App\Http\Controllers\Users\ChildController::class, 'index'])->name('children.index');
        Route::get('/children/create', [App\Http\Controllers\Users\ChildController::class, 'create'])->name('children.create');
        Route::post('/children', [App\Http\Controllers\Users\ChildController::class, 'store'])->name('children.store');
        Route::post('/children/{child}/select', [App\Http\Controllers\Users\ChildController::class, 'select'])->name('children.select');
        Route::get('/questioner', [App\Http\Controllers\Users\KpspController::class, 'index'])
            ->middleware('check.profile')
            ->name('questioner.index');
        Route::get('/questioner/{id}', [App\Http\Controllers\Users\KpspController::class, 'question'])
            ->middleware('check.profile')
            ->name('question.index');
        Route::post('/questioner', [App\Http\Controllers\Users\KpspController::class, 'store'])
            ->middleware('check.profile')
            ->name('kpsp.store');
        Route::get('/questioner/result/{id}', [App\Http\Controllers\Users\KpspController::class, 'result'])
            ->middleware('check.profile')
            ->name('kpsp.result');
        Route::get('/measurement', [App\Http\Controllers\Users\MeasurementController::class, 'index'])
            ->middleware('check.profile')
            ->name('measurement.create');
        Route::post('/measurement', [App\Http\Controllers\Users\MeasurementController::class, 'store'])
            ->middleware('check.profile')
            ->name('measurement.store');
        Route::get('/growth-monitoring', [App\Http\Controllers\Users\GrowthController::class, 'index'])
            ->middleware('check.profile')
            ->name('growth.index');
        Route::get('/artikel/{slug}', [App\Http\Controllers\Users\BlogController::class, 'show'])->name('detail.blog');
        Route::get('/artikel', [App\Http\Controllers\Users\BlogController::class, 'index'])->name('blog.index');
        Route::post('/testimonials', [App\Http\Controllers\Users\TestimonialController::class, 'store'])->name('testimoni.create');
        Route::post('/chat/send', [App\Http\Controllers\Api\ChatController::class, 'send'])->name('chat.send');
        Route::get('/chat', [App\Http\Controllers\Api\ChatController::class, 'getSession'])->name('chat.session');
        Route::get('/chat-ai', [App\Http\Controllers\Api\ChatController::class, 'page'])->name('chat.page');
    });
Route::prefix('kader')
    ->middleware(['auth', 'role:kader,nakes'])
    ->name('kader.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Kader\ChildController::class, 'index'])->name('children.index');
        Route::get('/children/{child}', [App\Http\Controllers\Kader\ChildController::class, 'show'])->whereNumber('child')->name('children.show');

        Route::get('/follow-ups', [App\Http\Controllers\Kader\FollowUpController::class, 'index'])->name('follow-ups.index');
        // Policy menentukan siapa yang boleh mengubah status (penanggung jawab, kader, admin).
        Route::patch('/follow-ups/{followUp}', [App\Http\Controllers\Kader\FollowUpController::class, 'update'])
            ->whereNumber('followUp')
            ->name('follow-ups.update');

        // Nakes hanya meninjau; pencatatan dibatasi kader (dan admin).
        Route::middleware('role:kader')->group(function () {
            Route::post('/children/{child}/follow-ups', [App\Http\Controllers\Kader\FollowUpController::class, 'store'])
                ->whereNumber('child')
                ->name('children.follow-ups.store');
            Route::get('/children/create', [App\Http\Controllers\Kader\ChildController::class, 'create'])->name('children.create');
            Route::post('/children', [App\Http\Controllers\Kader\ChildController::class, 'store'])->name('children.store');
            Route::post('/children/{child}/measurements', [App\Http\Controllers\Kader\ChildController::class, 'storeMeasurement'])
                ->whereNumber('child')
                ->name('children.measurements.store');
        });
    });

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// routes/web.php
Route::post('/export-rekap', [DashboardController::class, 'export'])->name('export');
