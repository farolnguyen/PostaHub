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
use App\Http\Controllers\CommentLikeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\Mypage\LikeController as MypageLikeController;
use App\Http\Controllers\Mypage\PostController as MypagePostController;
use App\Http\Controllers\Mypage\ProfileController as MypageProfileController;
use App\Http\Controllers\PostDetailController;
use App\Http\Controllers\PostSlugPreviewController;
use App\Http\Controllers\SearchController;
use App\Models\Post;
use App\Support\SiteCache;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/search', [SearchController::class, 'index'])
    ->middleware('throttle:40,1')
    ->name('search.index');

Route::get('/', function () {
    $page = max(1, (int) request()->integer('page', 1));
    $cacheKey = sprintf('home:feed:v%d:p%d', SiteCache::homeVersion(), $page);
    $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () {
        $paginator = Post::query()->latest()->paginate(10);

        return [
            'ids' => $paginator->pluck('id')->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    });

    $ids = collect($payload['ids'] ?? [])->map(fn ($id) => (int) $id)->all();
    $items = Post::query()
        ->with(['user', 'media'])
        ->withCount(['likes', 'comments'])
        ->whereIn('id', $ids)
        ->get()
        ->keyBy('id');
    $ordered = collect($ids)->map(fn ($id) => $items->get($id))->filter()->values();

    $posts = new LengthAwarePaginator(
        $ordered,
        (int) ($payload['total'] ?? $ordered->count()),
        (int) ($payload['per_page'] ?? 10),
        (int) ($payload['current_page'] ?? $page),
        ['path' => request()->url(), 'pageName' => 'page']
    );

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
    Route::post('/login', [UserAuthController::class, 'login'])->middleware('throttle:5,1')->name('user.login.submit');
    Route::get('/register', [UserRegisterController::class, 'showRegistrationForm'])->name('user.register.form');
    Route::post('/register', [UserRegisterController::class, 'register'])->name('user.register.submit');
    Route::get('/forget_password', [UserForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forget_password', [UserForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [UserForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [UserForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
});

Route::middleware('auth:web,admin')->group(function () {
    Route::get('/email/verify', function (Request $request) {
        if ($request->user('web') || $request->user('admin')) {
            return view('auth.verify-email');
        }

        return redirect()->route('login');
    })->name('verification.notice');
});

Route::middleware('auth:web')->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('user.index')->with('status', 'Xác thực email thành công.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user('web')->hasVerifiedEmail()) {
            return redirect()->route('user.index');
        }

        $request->user('web')->sendEmailVerificationNotification();

        return back()->with('status', 'Đã gửi lại email xác thực.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/post/detail/{url}', [PostDetailController::class, 'show'])->name('post.detail');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
        Route::get('/register', [AdminRegisterController::class, 'showRegisterForm'])->name('register.form');
        Route::post('/register', [AdminRegisterController::class, 'register'])->name('register.submit');
        Route::get('/forget_password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forget_password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AdminForgotPasswordController::class, 'reset'])->name('password.update');
    });
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();

            return redirect()->route('admin.dashboard')->with('status', 'Xác thực email admin thành công.');
        })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

        Route::post('/email/verification-notification', function (Request $request) {
            if ($request->user('admin')->hasVerifiedEmail()) {
                return redirect()->route('admin.dashboard');
            }

            $request->user('admin')->sendEmailVerificationNotification();

            return back()->with('status', 'Đã gửi lại email xác thực cho admin.');
        })->middleware('throttle:6,1')->name('verification.send');
    });
});

Route::middleware(['auth:web,admin'])->group(function () {
    Route::get('/mypage', function () {
        return view('mypage.index');
    })->name('user.index');

    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/post', [MypagePostController::class, 'index'])->name('post.index');
        Route::get('/post/create', [MypagePostController::class, 'create'])->name('post.create');
        Route::post('/post', [MypagePostController::class, 'store'])->middleware('throttle:20,1')->name('post.store');
        Route::get('/post/{post}/edit', [MypagePostController::class, 'edit'])->name('post.edit');
        Route::put('/post/{post}', [MypagePostController::class, 'update'])->middleware('throttle:20,1')->name('post.update');
        Route::delete('/post/{post}', [MypagePostController::class, 'destroy'])->name('post.destroy');
        Route::get('/like', [MypageLikeController::class, 'index'])->name('like.index');
        Route::get('/profile', [MypageProfileController::class, 'index'])->name('profile.index');
    });

    Route::prefix('comment')->name('comment.')->group(function () {
        Route::post('/post/{post}', [CommentController::class, 'storeForPost'])->middleware('throttle:30,1')->name('store.post');
        Route::post('/reply/{comment}', [CommentController::class, 'storeReply'])->middleware('throttle:30,1')->name('store.reply');
        Route::get('/{comment}/edit', [CommentController::class, 'edit'])->name('edit');
        Route::put('/{comment}', [CommentController::class, 'update'])->middleware('throttle:30,1')->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    });

    Route::post('/like/post/{post}', [LikeController::class, 'toggle'])->name('like.toggle');
    Route::post('/like/comment/{comment}', [CommentLikeController::class, 'toggle'])->middleware('throttle:60,1')->name('like.comment.toggle');
});

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::prefix('post')->name('post.')->group(function () {
        Route::get('/', [AdminPostController::class, 'index'])->name('index');
        Route::get('/create', [AdminPostController::class, 'create'])->name('create');
        Route::post('/', [AdminPostController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::get('/{post}/edit', [AdminPostController::class, 'edit'])->name('edit');
        Route::post('/{post}/semantic-reindex', [AdminPostController::class, 'semanticReindex'])
            ->middleware('throttle:15,1')
            ->name('semantic-reindex');
        Route::put('/{post}', [AdminPostController::class, 'update'])->middleware('throttle:20,1')->name('update');
        Route::delete('/{post}', [AdminPostController::class, 'destroy'])->name('destroy');
        Route::get('/detail/{post}', [AdminPostController::class, 'show'])->name('detail');
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [AdminMediaController::class, 'index'])->name('index');
        Route::get('/upload', [AdminMediaController::class, 'create'])->name('upload');
        Route::post('/upload', [AdminMediaController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::get('/detail/{media}', [AdminMediaController::class, 'show'])->name('detail');
        Route::get('/edit/{media}', [AdminMediaController::class, 'edit'])->name('edit');
        Route::put('/edit/{media}', [AdminMediaController::class, 'update'])->middleware('throttle:20,1')->name('update');
        Route::delete('/delete/{media}', [AdminMediaController::class, 'destroy'])->name('delete');
    });

    Route::prefix('comment')->name('comment.')->group(function () {
        Route::get('/', [AdminCommentController::class, 'index'])->name('index');
        Route::get('/create', [AdminCommentController::class, 'create'])->name('create');
        Route::post('/create', [AdminCommentController::class, 'store'])->middleware('throttle:30,1')->name('store');
        Route::get('/edit/{comment}', [AdminCommentController::class, 'edit'])->name('edit');
        Route::put('/edit/{comment}', [AdminCommentController::class, 'update'])->middleware('throttle:30,1')->name('update');
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
        Route::post('/users', [AdminImportController::class, 'usersStore'])->middleware('throttle:10,1')->name('users.store');
        Route::get('/users/template.csv', [AdminImportController::class, 'usersTemplateCsv'])->name('users.template');
    });
});
