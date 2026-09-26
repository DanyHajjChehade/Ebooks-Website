{{-- Home (DESIGN.md §5.1): hero · featured spotlight · new releases · shelves · readers · authors · how buying works. --}}
@php
    $stack = $featured->concat($newReleases)->unique('id')->take(3)->values();
    $front = $stack->get(1) ?? $stack->first();
    $spotlight = $featured->first();
    $moreFeatured = $featured->slice(1)->take(5)->values();
    $shelves = $categories->where('books_count', '>', 0)->sortByDesc('books_count')->take(12)->values();
    $testimonials = $reviews->take(3);
    $spotOwned = $spotlight && in_array((int) $spotlight->id, $ownedBookIds, true);
    $spotInCart = $spotlight && ! $spotOwned && in_array((int) $spotlight->id, $cartBookIds, true);
@endphp
<x-layouts.app :description="($settings->tagline ? $settings->tagline.' ' : '').'Novels, essays and poetry as EPUB and PDF. Pay once and download on any device.'">
    {{-- 1. Hero: pure CSS entrance (.rise, .hero-stack) --}}
    <section class="container-page pb-16 pt-10 lg:pb-24 lg:pt-20" aria-labelledby="hero-title">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
            <div class="grid justify-items-start gap-6 lg:col-span-7">
                <p class="eyebrow rise">{{ $settings->tagline ?: 'An independent ebook shop' }}</p>
                <h1 class="h-display rise rise-1" id="hero-title">Books worth <em class="font-normal italic">staying up</em> for.</h1>
                <p class="lede rise rise-2">Novels, essays and poetry as EPUB and PDF. Pay once and your books stay in your library, ready to download on any device.</p>
                <div class="rise rise-3 flex flex-wrap gap-3">
                    <x-button variant="primary" size="lg" :href="route('books.index')" trailing="arrow-right">Browse the books</x-button>
                    <x-button variant="ghost" size="lg" :href="route('authors.index')">Meet the authors</x-button>
                </div>
                <p class="rise rise-4 flex flex-wrap gap-x-3 gap-y-1 text-sm text-muted">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="lock" class="icon-sm"/>Secure checkout with Stripe</span><span aria-hidden="true">·</span><span>Instant download</span><span aria-hidden="true">·</span><span>EPUB &amp; PDF</span>
                </p>
            </div>
            @if ($stack->count() >= 3)
                <div class="grid justify-items-center gap-4 lg:col-span-5">
                    <div class="hero-stack" aria-hidden="true">
                        @foreach ($stack as $i => $b)
                            <x-book-cover :book="$b" :eager="$i === 1"/>
                        @endforeach
                    </div>
                    <p class="text-center text-sm text-muted">Featured: <a class="link" href="{{ route('books.show', $front) }}"><em class="font-serif text-base">{{ $front->title }}</em></a> by {{ $front->author->name }}</p>
                </div>
            @elseif ($front)
                <div class="grid justify-items-center gap-4 lg:col-span-5">
                    <a class="block w-3/5 max-w-60" href="{{ route('books.show', $front) }}" aria-label="{{ $front->title }} by {{ $front->author->name }}"><x-book-cover :book="$front" :eager="true"/></a>
                </div>
            @endif
        </div>
    </section>

    {{-- 2. Featured spotlight --}}
    @if ($spotlight)
        <section class="border-t border-line py-section" aria-labelledby="spotlight-title">
            <div class="container-page grid gap-section">
                <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12 lg:gap-8" data-reveal>
                    <a class="mx-auto block w-3/5 max-w-80 lg:col-span-4 lg:w-full" href="{{ route('books.show', $spotlight) }}" tabindex="-1" aria-hidden="true">
                        <x-book-cover :book="$spotlight"/>
                    </a>
                    <div class="grid justify-items-start gap-4 lg:col-span-7 lg:col-start-6">
                        <p class="eyebrow">Featured @if ($spotlight->category)· <a class="link-quiet" href="{{ route('categories.show', $spotlight->category) }}">{{ $spotlight->category->name }}</a>@endif</p>
                        <h2 class="h2" id="spotlight-title"><a class="link-quiet" href="{{ route('books.show', $spotlight) }}">{{ $spotlight->title }}</a></h2>
                        <p class="font-serif text-lg text-muted">by <a class="link-quiet text-ink" href="{{ route('authors.show', $spotlight->author) }}">{{ $spotlight->author->name }}</a></p>
                        <p class="prose-book line-clamp-3">{{ $spotlight->description }}</p>
                        @unless ($spotOwned)<x-price :book="$spotlight" size="lg"/>@endunless
                        <x-book-cta :book="$spotlight" :owned="$spotOwned" :in-cart="$spotInCart" group="spotlight" class="w-full">
                            <x-slot:extra>
                                <a class="btn btn-ghost btn-lg max-sm:w-full" href="{{ route('books.show', $spotlight) }}">Read more</a>
                            </x-slot:extra>
                        </x-book-cta>
                    </div>
                </div>

                @if ($moreFeatured->isNotEmpty())
                    <div>
                        <h2 class="sr-only">More featured books</h2>
                        <div class="book-grid" data-reveal-grid>
                            @foreach ($moreFeatured as $book)
                                <x-book-card :book="$book" :class="$loop->iteration === 5 ? 'hidden xl:flex' : ''"/>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- 3. New releases --}}
    @if ($newReleases->isNotEmpty())
        <section class="border-t border-line py-section" aria-labelledby="new-h">
            <div class="container-page">
                <x-section-head id="new-h" eyebrow="Just in" title="New releases" :href="route('books.index', ['sort' => 'newest'])"/>
                <div class="book-grid shelf-row" data-reveal-grid>
                    @foreach ($newReleases->take(10) as $book)
                        <x-book-card :book="$book"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Shelves --}}
    @if ($shelves->isNotEmpty())
        <section class="border-t border-line py-section" aria-labelledby="shelves-h">
            <div class="container-page">
                <x-section-head id="shelves-h" eyebrow="Browse by shelf" title="Shelves"/>
                <ul class="grid grid-cols-1 gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($shelves as $category)
                        <li>
                            <a class="group flex min-h-14 items-baseline justify-between gap-4 border-b border-line py-4 text-ink no-underline" href="{{ route('categories.show', $category) }}">
                                <span class="font-serif text-xl group-hover:underline group-hover:decoration-1 group-hover:underline-offset-4">{{ $category->name }}</span>
                                <span class="tabular text-sm text-muted">{{ $category->books_count }} <span class="sr-only">{{ \Illuminate\Support\Str::plural('book', $category->books_count) }}</span></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <a class="link mt-6 inline-flex min-h-11 items-center gap-1 text-sm font-semibold" href="{{ route('books.index') }}">Browse all books<x-icon name="arrow-right" class="icon-sm"/></a>
            </div>
        </section>
    @endif

    {{-- 5. From our readers (A9 testimonials) --}}
    @if ($testimonials->isNotEmpty())
        <section class="border-t border-line py-section" aria-labelledby="readers-h">
            <div class="container-page">
                <x-section-head id="readers-h" eyebrow="Reviews" title="From our readers"/>
                <div class="grid grid-cols-1 lg:grid-cols-3 lg:divide-x lg:divide-line">
                    @foreach ($testimonials as $review)
                        <figure class="grid content-start gap-4 border-b border-line py-6 first:pt-0 last:border-b-0 lg:border-b-0 lg:px-8 lg:py-0 lg:first:pl-0 lg:last:pr-0">
                            <x-stars :value="$review->rating"/>
                            <blockquote class="font-serif text-lg">“{{ \Illuminate\Support\Str::limit(trim($review->body), 220, '…') }}”</blockquote>
                            <figcaption class="flex items-center gap-3">
                                <x-avatar :name="$review->user?->name" :id="$review->user?->id"/>
                                <span class="text-sm leading-snug">
                                    <strong><x-public-name :name="$review->user?->name"/></strong> on <a class="link" href="{{ route('books.show', $review->book) }}"><em class="font-serif text-base">{{ $review->book->title }}</em></a><br>
                                    <time class="text-muted" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('F Y') }}</time>
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 6. Authors --}}
    @if ($authors->isNotEmpty())
        <section class="border-t border-line py-section" aria-labelledby="authors-h">
            <div class="container-page">
                <x-section-head id="authors-h" eyebrow="The writers" title="Authors" :href="route('authors.index')" link="All authors"/>
                <ul class="shelf-row grid gap-6 sm:grid-cols-3 lg:grid-cols-6" data-reveal-grid>
                    @foreach ($authors->take(6) as $author)
                        <li class="relative grid content-start justify-items-center gap-2 text-center">
                            <x-avatar :name="$author->name" :id="$author->id" size="xl" :photo="$author->photo_url"/>
                            <a class="font-serif text-lg leading-tight text-ink no-underline after:absolute after:inset-0 hover:underline" href="{{ route('authors.show', $author) }}">{{ $author->name }}</a>
                            <span class="text-sm text-muted">{{ $author->books_count }} {{ \Illuminate\Support\Str::plural('book', $author->books_count) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- 7. How buying works --}}
    <section class="border-t border-line py-section" aria-labelledby="how-h">
        <div class="container-page">
            <x-section-head id="how-h" eyebrow="Simple and safe" title="How buying works"/>
            <ol class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach (['Pay securely with Stripe.', 'Find the book in your library straight away.', 'Download the EPUB or PDF and read on any device.'] as $i => $step)
                    <li class="grid content-start gap-3">
                        <span class="font-serif text-[3rem] font-light italic leading-none text-muted" aria-hidden="true">{{ $i + 1 }}</span>
                        <p class="font-serif text-lg">{{ $step }}</p>
                    </li>
                @endforeach
            </ol>
            <x-button class="mt-10" variant="primary" :href="route('books.index')" trailing="arrow-right">Browse the books</x-button>
        </div>
    </section>
</x-layouts.app>
