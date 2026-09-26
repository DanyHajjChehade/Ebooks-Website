{{-- Book page (DESIGN.md §5.1 books.show): cover stage, CTA by state (A9), details, author, reviews, related, JSON-LD. --}}
@use('Illuminate\Support\Number')
@use('Illuminate\Support\Str')
@php
    $author = $book->author;
    $category = $book->category;
    $authorLive = $author && ! $author->trashed();
    $categoryLive = $category && ! $category->trashed();
    $format = strtoupper((string) $book->file_format);
    $size = Number::fileSize((int) $book->file_size, maxPrecision: 1);
    $count = (int) ($book->reviews_count ?? $reviews->total());
    $avg = $book->reviews_avg_rating !== null ? round((float) $book->reviews_avg_rating, 1) : null;
    $save = $book->isOnSale() && $book->price_cents > 0 ? (int) floor(($book->price_cents - $book->effective_price_cents) / $book->price_cents * 100) : 0;
    // preg_split returns false on invalid UTF-8 (N2): fall back to one paragraph instead of failing the page.
    $paragraphs = collect(preg_split('/\R\s*\R/u', trim((string) $book->description)) ?: [trim((string) $book->description)])->map(fn ($p) => trim($p))->filter();
    $crumbs = array_values(array_filter([
        ['Books', route('books.index')],
        $category ? [$category->name, $categoryLive ? route('categories.show', $category) : null] : null,
        [$book->title],
    ]));

    $ld = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        'name' => $book->title,
        'url' => route('books.show', $book),
        'description' => Str::limit(trim((string) $book->description), 300),
        'image' => $book->cover_url ?? asset('og-default.png'),
        'author' => $author ? ['@type' => 'Person', 'name' => $author->name, 'url' => $authorLive ? route('authors.show', $author) : null] : null,
        'bookFormat' => 'https://schema.org/EBook',
        'genre' => $category?->name,
        'isbn' => $book->isbn ?: null,
        'numberOfPages' => $book->page_count ?: null,
        'datePublished' => $book->published_at?->toDateString(),
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($book->effective_price_cents / 100, 2, '.', ''),
            'priceCurrency' => \App\Support\Money::currency(),
            'availability' => 'https://schema.org/InStock',
            'url' => route('books.show', $book),
        ],
        'aggregateRating' => $count > 0 && $avg !== null ? [
            '@type' => 'AggregateRating',
            'ratingValue' => $avg,
            'reviewCount' => $count,
            'bestRating' => 5,
            'worstRating' => 1,
        ] : null,
    ], fn ($v) => $v !== null);
    $ldCrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => array_filter([
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $c[0],
            'item' => $c[1] ?? route('books.show', $book),
        ]))->all(),
    ];
    $json = fn ($data) => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    $reviewErrors = $errors->hasAny(['rating', 'body']);
@endphp
<x-layouts.app :title="$book->title.($author ? ' by '.$author->name : '')" :description="Str::limit(trim((string) $book->description), 155)" og-type="book" :og-image="$book->cover_url" :canonical="route('books.show', $book)">
    <x-slot:head>
        <script type="application/ld+json">{!! $json($ld) !!}</script>
        <script type="application/ld+json">{!! $json($ldCrumbs) !!}</script>
    </x-slot:head>

    <div class="container-page pb-section-sm pt-6 lg:pt-10">
        <x-breadcrumbs class="mb-6 lg:mb-10" :items="$crumbs"/>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-5">
                <div class="cover-stage mx-auto w-3/5 max-w-60 lg:w-full lg:max-w-[22rem]">
                    <x-book-cover :book="$book" :eager="true" :alt="'Cover of '.$book->title"/>
                </div>
            </div>

            <div class="grid content-start gap-4 lg:col-span-6 lg:col-start-7">
                @if ($category)
                    <p class="eyebrow">@if ($categoryLive)<a class="link-quiet" href="{{ route('categories.show', $category) }}">{{ $category->name }}</a>@else{{ $category->name }}@endif</p>
                @endif
                <h1 class="h1">{{ $book->title }}</h1>
                @if ($author)
                    <p class="font-serif text-lg">by @if ($authorLive)<a class="link" href="{{ route('authors.show', $author) }}">{{ $author->name }}</a>@else{{ $author->name }}@endif</p>
                @endif
                @if ($count > 0)
                    <a class="inline-flex min-h-11 items-center gap-2 justify-self-start text-sm link-quiet" href="#reviews">
                        <x-stars :value="$avg ?? 0"/>
                        <span>@if ($avg !== null)<strong>{{ number_format($avg, 1) }}</strong> · @endif{{ $count }} {{ Str::plural('review', $count) }}</span>
                    </a>
                @else
                    <p class="text-sm text-muted">No reviews yet</p>
                @endif

                <div class="mt-2 grid gap-5">
                    @unless ($owned)
                        <div class="flex flex-wrap items-center gap-3">
                            <x-price :book="$book" size="lg"/>
                            @if ($save > 0)<span class="badge badge-accent">Save {{ $save }}%</span>@endif
                        </div>
                    @endunless

                    <x-book-cta :book="$book" :owned="$owned" :in-cart="$inCart" group="book" data-cta/>

                    <ul class="grid gap-2 text-sm text-muted">
                        <li class="flex items-start gap-2"><x-icon name="lock" class="icon-sm mt-0.5"/>Secure checkout with Stripe</li>
                        <li class="flex items-start gap-2"><x-icon name="download" class="icon-sm mt-0.5"/>Instant download, and any time later from your library</li>
                        <li class="flex items-start gap-2"><x-icon name="file-text" class="icon-sm mt-0.5"/>{{ $format }} · {{ $size }}@if ($book->page_count) · {{ number_format($book->page_count) }} pages @endif</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-8">
            <section class="lg:col-span-7" aria-labelledby="about-h">
                <h2 class="h3 mb-4" id="about-h">About this book</h2>
                <div class="prose-book prose-book--dropcap">
                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </section>

            <div class="grid content-start gap-12 lg:col-span-4 lg:col-start-9">
                <section aria-labelledby="details-h">
                    <h2 class="h3 mb-4" id="details-h">Details</h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-muted">Format</dt><dd class="font-semibold">{{ $format }}</dd></div>
                        <div><dt class="text-muted">File size</dt><dd class="font-semibold">{{ $size }}</dd></div>
                        @if ($book->page_count)<div><dt class="text-muted">Pages</dt><dd class="font-semibold">{{ number_format($book->page_count) }}</dd></div>@endif
                        @if ($book->isbn)<div><dt class="text-muted">ISBN</dt><dd class="font-semibold break-all">{{ $book->isbn }}</dd></div>@endif
                        @if ($book->published_at)<div><dt class="text-muted">Published</dt><dd class="font-semibold"><time datetime="{{ $book->published_at->toDateString() }}">{{ $book->published_at->format('M j, Y') }}</time></dd></div>@endif
                        @if ($category)<div><dt class="text-muted">Category</dt><dd class="font-semibold">{{ $category->name }}</dd></div>@endif
                    </dl>
                </section>

                @if ($author)
                    <section aria-labelledby="author-h">
                        <h2 class="h3 mb-4" id="author-h">About the author</h2>
                        <div class="flex items-center gap-4">
                            <x-avatar :name="$author->name" :id="$author->id" size="lg" :photo="$author->photo_url"/>
                            @if ($authorLive)
                                <a class="font-serif text-lg link-quiet" href="{{ route('authors.show', $author) }}">{{ $author->name }}</a>
                            @else
                                <span class="font-serif text-lg">{{ $author->name }}</span>
                            @endif
                        </div>
                        @if ($author->bio)
                            <p class="prose-book mt-4 line-clamp-4 text-base">{{ $author->bio }}</p>
                        @endif
                        @if ($authorLive)
                            <a class="link mt-3 inline-flex min-h-11 items-center gap-1 text-sm font-semibold" href="{{ route('authors.show', $author) }}">More from {{ $author->name }}<x-icon name="arrow-right" class="icon-sm"/></a>
                        @endif
                    </section>
                @endif
            </div>
        </div>

        <section class="mt-section-sm border-t border-line pt-section-sm" id="reviews" aria-labelledby="reviews-h">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="grid content-start gap-6 lg:col-span-4">
                    <h2 class="h2" id="reviews-h">Reviews</h2>
                    @if ($count > 0 && $avg !== null)
                        <div class="flex items-center gap-4">
                            <p class="font-serif text-3xl leading-none">{{ number_format($avg, 1) }}</p>
                            <div class="grid gap-1">
                                <x-stars :value="$avg" size="lg"/>
                                <p class="text-sm text-muted">{{ $count }} {{ Str::plural('review', $count) }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($userReview)
                        <div class="grid gap-4" x-data="{ editing: {{ $reviewErrors ? 'true' : 'false' }} }">
                            <div class="card grid gap-3 p-5" x-show="!editing">
                                <p class="eyebrow">Your review</p>
                                <x-stars :value="$userReview->rating"/>
                                <p class="prose-book text-base">{{ $userReview->body }}</p>
                                <p class="text-sm text-muted"><time datetime="{{ $userReview->created_at->toDateString() }}">{{ $userReview->created_at->format('M j, Y') }}</time></p>
                                <div class="flex flex-wrap gap-2">
                                    <button class="btn btn-ghost btn-sm js-only" type="button" x-on:click="editing = true; $nextTick(() => $refs.body.focus())"><x-icon name="pencil"/>Edit</button>
                                    <form method="POST" action="{{ route('reviews.destroy', $userReview) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger-quiet btn-sm" type="submit" data-confirm="Delete your review?" data-confirm-body="It disappears from this page. You can write a new one any time." data-confirm-ok="Delete review" data-confirm-cancel="Keep review" data-confirm-busy="Deleting…"><x-icon name="trash-2"/>Delete</button>
                                    </form>
                                </div>
                            </div>
                            <form class="card grid gap-5 p-5" method="POST" action="{{ route('reviews.update', $userReview) }}" x-show="editing" x-cloak>
                                @csrf
                                @method('PATCH')
                                <x-rating-input :value="$userReview->rating"/>
                                <x-textarea name="body" label="Your review" :value="$userReview->body" rows="5" maxlength="2000" hint="Up to 2,000 characters." required x-ref="body"/>
                                <div class="flex flex-wrap gap-3">
                                    <button class="btn btn-primary" type="submit" data-busy-label="Saving…">Save review</button>
                                    <button class="btn btn-ghost js-only" type="button" x-on:click="editing = false">Cancel</button>
                                </div>
                            </form>
                        </div>
                    @elseif ($canReview)
                        <form class="grid gap-5" method="POST" action="{{ route('reviews.store', $book) }}">
                            @csrf
                            <x-rating-input/>
                            <x-textarea name="body" label="Your review" rows="5" maxlength="2000" hint="Up to 2,000 characters." required/>
                            <button class="btn btn-primary justify-self-start" type="submit" data-busy-label="Posting…">Post review</button>
                        </form>
                    @elseif (auth()->check())
                        <p class="text-muted">Only readers who bought this book can review it.</p>
                    @else
                        <p class="text-muted"><a class="link" href="{{ route('login') }}">Log in</a> to review books you own.</p>
                    @endif
                </div>

                <div class="lg:col-span-7 lg:col-start-6">
                    @forelse ($reviews as $review)
                        <article class="grid gap-3 border-b border-line py-6 first:pt-0 last:border-b-0">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$review->user?->name" :id="$review->user?->id"/>
                                <div class="grid gap-0.5">
                                    <p class="font-semibold"><x-public-name :name="$review->user?->name"/></p>
                                    <p class="flex flex-wrap items-center gap-2 text-sm text-muted"><x-stars :value="$review->rating"/><time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('F Y') }}</time></p>
                                </div>
                            </div>
                            <div class="prose-book">
                                @foreach (preg_split('/\R\s*\R/u', trim((string) $review->body)) ?: [trim((string) $review->body)] as $p)
                                    <p>{{ $p }}</p>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <div class="grid gap-1 py-2">
                            <p class="font-serif text-xl">No reviews yet</p>
                            <p class="text-muted">Readers who buy this book can leave the first one.</p>
                        </div>
                    @endforelse
                    <x-pagination class="mt-8" :paginator="$reviews"/>
                </div>
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section class="border-t border-line pt-section-sm mt-section-sm" aria-labelledby="related-h">
                <h2 class="h2 mb-8" id="related-h">More like this</h2>
                <div class="book-grid shelf-row" data-reveal-grid>
                    @foreach ($related->take(5) as $rel)
                        <x-book-card :book="$rel"/>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <div class="buy-bar" hidden data-buy-bar>
        <div class="min-w-0 flex-1">
            <p class="truncate font-serif">{{ $book->title }}</p>
            @unless ($owned)<x-price :book="$book" class="text-sm"/>@endunless
        </div>
        <x-book-cta :book="$book" :owned="$owned" :in-cart="$inCart" variant="bar" group="book"/>
    </div>
</x-layouts.app>
