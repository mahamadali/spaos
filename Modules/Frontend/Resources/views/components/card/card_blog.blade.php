        <div class="blog-card rounded-3" data-url="{{ route('blog_detail', ['id' => $blog->id]) }}"  style="cursor: pointer;">
        <div class="blog-image-wrapper">
            <input type="hidden" value="{{$blog->id}}">
            <img src="{{ asset($blog->image ?? product_feature_image()) }}" alt="blog-image" class="blog-image img-fluid">
        </div>
        <div class="blog-details p-4">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                <ul class="list-inline m-0 p-0 d-flex align-items-center row-gap-1 column-gap-3 flex-wrap category-list">
                    <li>
                        <a href="#">{{ __('frontend.by') }} :  {{ $blog->user ? $blog->user->first_name . ' ' . $blog->user->last_name : default_user_name() }}
                        </a>
                    </li>
                </ul>
                <span class="date flex-shrink-0">{{ \Carbon\Carbon::parse($blog->created_at)->translatedFormat('F d, Y') ?? '-' }}</span>
            </div>
            <h6 class="blog-title line-count-1 mb-0">{{ $blog->title ?? '-' }}</h6>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const blogCards = document.querySelectorAll('.blog-card');

    blogCards.forEach(card => {
        card.addEventListener('click', function () {
            const url = card.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
});

    </script>
