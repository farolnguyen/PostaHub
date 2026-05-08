<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Observers\PostObserver;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use App\Services\SemanticSearch\EmbeddingHttpClient;
use App\Services\SemanticSearch\PostSemanticIndexer;
use App\Services\SemanticSearch\QdrantHttpClient;
use App\Services\SemanticSearch\SemanticSearchService;
use App\Support\ActorUserResolver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EmbeddingHttpClient::class, fn () => new EmbeddingHttpClient);
        $this->app->singleton(QdrantHttpClient::class, fn () => QdrantHttpClient::fromConfig());
        $this->app->singleton(PostSemanticIndexer::class, fn ($app) => new PostSemanticIndexer(
            $app->make(EmbeddingHttpClient::class),
            $app->make(QdrantHttpClient::class),
        ));
        $this->app->singleton(SemanticSearchService::class, fn ($app) => new SemanticSearchService(
            $app->make(EmbeddingHttpClient::class),
            $app->make(QdrantHttpClient::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Du an dung Bootstrap 4.5, can buoc paginator render theo bootstrap
        // de tranh fallback tailwind (mui ten SVG to bat thuong).
        Paginator::useBootstrapFour();

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        Post::observe(PostObserver::class);

        // Chia sẻ unread count, 5 notify gần nhất, và userId tới site-header
        View::composer('partials.site-header', function ($view) {
            $actor = ActorUserResolver::current();
            $view->with('unreadNotificationCount', $actor?->unreadNotifications()->count() ?? 0);
            $view->with('recentNotifications', $actor?->notifications()->latest()->limit(5)->get() ?? collect());
            // notificationUserId chỉ truyền cho web user (admin dùng guard riêng,
            // không authenticate được với Pusher private channel qua web guard).
            $view->with('notificationUserId', auth('web')->id());
        });
    }
}
