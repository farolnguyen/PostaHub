<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Auth\AdminForgotPasswordController;
use App\Http\Controllers\Admin\Auth\AdminRegisterController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\RuleController as AdminRuleController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserForgotPasswordController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\Mypage\LikeController as MypageLikeController;
use App\Http\Controllers\Mypage\PostController as MypagePostController;
use App\Http\Controllers\Mypage\ProfileController as MypageProfileController;
use App\Http\Controllers\PostDetailController;
use App\Http\Controllers\PostSlugPreviewController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $posts = Post::query()
        ->with(['user', 'media'])
        ->withCount(['likes', 'comments'])
        ->latest()
        ->paginate(10);

    return view('welcome', compact('posts'));
})->name('home');

Route::redirect('/welcome', '/')->name('welcome.page');

// Alias for middleware that expects route('login')
Route::get('/auth/login', function () {
    return redirect()->route('user.login.form');
})->name('login');

Route::get('/post/slug-preview', PostSlugPreviewController::class)->name('post.slug-preview');

Route::middleware('guest:web')->group(function () {
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('user.login.form');
    Route::post('/login', [UserAuthController::class, 'login'])->name('user.login.submit');
    Route::get('/register', [UserRegisterController::class, 'showRegistrationForm'])->name('user.register.form');
    Route::post('/register', [UserRegisterController::class, 'register'])->name('user.register.submit');
    Route::get('/forget_password', [UserForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forget_password', [UserForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [UserForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [UserForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
    Route::get('/mypage', function () {
        return view('mypage.index');
    })->name('user.index');

    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/post', [MypagePostController::class, 'index'])->name('post.index');
        Route::get('/post/create', [MypagePostController::class, 'create'])->name('post.create');
        Route::post('/post', [MypagePostController::class, 'store'])->name('post.store');
        Route::get('/post/{post}/edit', [MypagePostController::class, 'edit'])->name('post.edit');
        Route::put('/post/{post}', [MypagePostController::class, 'update'])->name('post.update');
        Route::delete('/post/{post}', [MypagePostController::class, 'destroy'])->name('post.destroy');
        Route::get('/like', [MypageLikeController::class, 'index'])->name('like.index');
        Route::get('/profile', [MypageProfileController::class, 'index'])->name('profile.index');
    });

    Route::prefix('comment')->name('comment.')->group(function () {
        Route::post('/post/{post}', [CommentController::class, 'storeForPost'])->name('store.post');
        Route::post('/reply/{comment}', [CommentController::class, 'storeReply'])->name('store.reply');
        Route::get('/{comment}/edit', [CommentController::class, 'edit'])->name('edit');
        Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    });

    Route::post('/like/post/{post}', [LikeController::class, 'toggle'])->name('like.toggle');
});

Route::get('/post/detail/{url}', [PostDetailController::class, 'show'])->name('post.detail');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [AdminRegisterController::class, 'showRegisterForm'])->name('register.form');
        Route::post('/register', [AdminRegisterController::class, 'register'])->name('register.submit');
        Route::get('/forget_password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forget_password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AdminForgotPasswordController::class, 'reset'])->name('password.update');
    });
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::prefix('post')->name('post.')->group(function () {
            Route::get('/', [AdminPostController::class, 'index'])->name('index');
            Route::get('/create', [AdminPostController::class, 'create'])->name('create');
            Route::post('/', [AdminPostController::class, 'store'])->name('store');
            Route::get('/{post}/edit', [AdminPostController::class, 'edit'])->name('edit');
            Route::put('/{post}', [AdminPostController::class, 'update'])->name('update');
            Route::delete('/{post}', [AdminPostController::class, 'destroy'])->name('destroy');
            Route::get('/detail/{post}', [AdminPostController::class, 'show'])->name('detail');
        });

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [AdminMediaController::class, 'index'])->name('index');
            Route::get('/upload', [AdminMediaController::class, 'create'])->name('upload');
            Route::post('/upload', [AdminMediaController::class, 'store'])->name('store');
            Route::get('/detail/{media}', [AdminMediaController::class, 'show'])->name('detail');
            Route::get('/edit/{media}', [AdminMediaController::class, 'edit'])->name('edit');
            Route::put('/edit/{media}', [AdminMediaController::class, 'update'])->name('update');
            Route::delete('/delete/{media}', [AdminMediaController::class, 'destroy'])->name('delete');
        });

        Route::prefix('comment')->name('comment.')->group(function () {
            Route::get('/', [AdminCommentController::class, 'index'])->name('index');
            Route::get('/create', [AdminCommentController::class, 'create'])->name('create');
            Route::post('/create', [AdminCommentController::class, 'store'])->name('store');
            Route::get('/edit/{comment}', [AdminCommentController::class, 'edit'])->name('edit');
            Route::put('/edit/{comment}', [AdminCommentController::class, 'update'])->name('update');
            Route::delete('/delete/{comment}', [AdminCommentController::class, 'destroy'])->name('delete');
        });

        Route::prefix('rule')->name('rule.')->group(function () {
            Route::get('/', [AdminRuleController::class, 'index'])->name('index');
            Route::put('/{user}', [AdminRuleController::class, 'update'])->name('update');
        });

        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/users.csv', [AdminExportController::class, 'usersCsv'])->name('users.csv');
            Route::get('/users.xlsx', [AdminExportController::class, 'usersXlsx'])->name('users.xlsx');
            Route::get('/posts.csv', [AdminExportController::class, 'postsCsv'])->name('posts.csv');
            Route::get('/posts.xlsx', [AdminExportController::class, 'postsXlsx'])->name('posts.xlsx');
        });

        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/users', [AdminImportController::class, 'usersForm'])->name('users.form');
            Route::post('/users', [AdminImportController::class, 'usersStore'])->name('users.store');
            Route::get('/users/template.csv', [AdminImportController::class, 'usersTemplateCsv'])->name('users.template');
        });
    });
});
