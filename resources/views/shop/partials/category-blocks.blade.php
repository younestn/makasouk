@if($categories->isEmpty())
    @include('shop.partials.empty-state', [
        'title' => __('shop.empty.no_categories_title'),
        'message' => __('shop.empty.no_categories_message'),
    ])
@else
    <div class="grid grid-4 shop-categories-grid">
        @foreach($categories as $category)
            <a class="shop-category-card" href="{{ route('shop.category', $category->slug) }}">
                <div class="shop-category-media">
                    @if($category->image_path)
                        @php
                            $categoryImage = \Illuminate\Support\Str::startsWith($category->image_path, ['http://', 'https://', '/'])
                                ? $category->image_path
                                : \Illuminate\Support\Facades\Storage::url($category->image_path);
                        @endphp
                        <img src="{{ $categoryImage }}" alt="{{ $category->display_name }}" loading="lazy">
                    @else
                        <div class="shop-category-placeholder">{{ \Illuminate\Support\Str::of($category->display_name)->substr(0, 2)->upper() }}</div>
                    @endif
                </div>
                <div class="stack" style="gap:0.35rem;">
                    <strong>{{ $category->display_name }}</strong>
                    <p class="small">{{ __('shop.sections.category_products_count', ['count' => $category->products_count]) }}</p>
                </div>
            </a>
        @endforeach
    </div>
@endif

