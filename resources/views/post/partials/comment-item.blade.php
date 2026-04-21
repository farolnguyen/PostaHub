@php
    $left = min($depth * 24, 120);
@endphp

<div class="border rounded p-3 mb-3" style="margin-left: {{ $left }}px;">
    <div class="d-flex justify-content-between">
        <div>
            <strong>{{ $comment->user->name ?? 'N/A' }}</strong>
            <span class="text-muted small ml-2">{{ $comment->created_at }}</span>
        </div>
    </div>

    <div class="mt-2">{!! $comment->content !!}</div>
    @if ($comment->image)
        <p class="small text-muted mt-2 mb-0">Image: {{ $comment->image }}</p>
    @endif
    @if ($comment->media->count())
        <div class="row mt-2">
            @foreach($comment->media as $media)
                <div class="col-md-3 mb-2">
                    <img src="{{ $media->path }}" alt="comment media" class="img-fluid rounded border">
                </div>
            @endforeach
        </div>
    @endif

    @auth('web')
        <div class="mt-3">
            @can('create', \App\Models\Comment::class)
                <details>
                    <summary class="small text-primary">Tra loi comment</summary>
                    <form action="{{ route('comment.store.reply', $comment) }}" method="post" class="mt-2" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea name="content" rows="3" class="form-control js-comment-editor" required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <input type="text" name="image" class="form-control" placeholder="Image URL (tuy chon)">
                        </div>
                        <div class="form-group mb-2">
                            <input type="file" name="image_file" class="form-control-file" accept="image/*">
                        </div>
                        <div class="form-group mb-2">
                            <input type="file" name="media_images[]" class="form-control-file" accept="image/*" multiple>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Gui reply</button>
                    </form>
                </details>
            @endcan

            @if (auth('web')->can('update', $comment) || auth('web')->can('delete', $comment))
                <div class="mt-2">
                    @can('update', $comment)
                        <a href="{{ route('comment.edit', $comment) }}" class="btn btn-sm btn-outline-secondary">Sua</a>
                    @endcan
                    @can('delete', $comment)
                        <form action="{{ route('comment.destroy', $comment) }}" method="post" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa comment nay?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
    @endauth
</div>

@foreach($comment->comments as $child)
    @include('post.partials.comment-item', ['comment' => $child, 'depth' => $depth + 1])
@endforeach

