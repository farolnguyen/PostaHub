@php
    /** @var \App\Models\Post $post */
    $semanticRedirectTo = $semanticRedirectTo ?? 'edit';
@endphp
@if($post->semanticIndexTrackingApplicable())
    <div class="card border-info mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
                <div class="mb-2 mb-md-0 pr-md-3">
                    <strong>Semantic search</strong>
                    @php $semanticBadge = $post->semanticIndexBadge(); @endphp
                    @if($semanticBadge)
                        <span class="badge {{ $semanticBadge['class'] }} align-middle ml-1">{{ $semanticBadge['label'] }}</span>
                    @endif
                    @if($post->semantic_index_status === \App\Models\Post::SEMANTIC_STATUS_FAILED && $post->semantic_index_last_error)
                        <div class="small text-danger mt-2">{{ \Illuminate\Support\Str::limit($post->semantic_index_last_error, 280) }}</div>
                    @endif
                    @if($post->semantic_index_status === \App\Models\Post::SEMANTIC_STATUS_INDEXED && $post->semantic_indexed_at)
                        <div class="small text-muted mt-2">Index lần cuối: {{ $post->semantic_indexed_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
                <form method="post" action="{{ route('admin.post.semantic-reindex', $post) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ $semanticRedirectTo }}">
                    <button type="submit" class="btn btn-sm btn-outline-info">Reindex semantic cho bài này</button>
                </form>
            </div>
            <p class="small text-muted mb-0 mt-2">Chạy ngay trên server (embedding + Qdrant). Dùng khi queue chưa xử lý hoặc cần đồng bộ tức thì.</p>
        </div>
    </div>
@endif
