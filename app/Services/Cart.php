<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Session cart for ebooks. Stores book IDs only (every ebook is quantity 1).
 * Books that are unpublished, deleted or already in the customer's library
 * are ignored on add and pruned on read, so prices always come from the DB.
 */
class Cart
{
    public const SESSION_KEY = 'cart.book_ids';

    public const MAX_ITEMS = 50;

    /**
     * Per-request memo (stored on the current Request so it can never leak
     * between requests, even when this service is reused in tests/Octane).
     */
    private const MEMO_KEY = 'cart.items';

    public function __construct(
        private readonly Session $session,
        private readonly AuthFactory $auth,
    ) {}

    /**
     * Add a book. Returns one of: added, exists, owned, unavailable, full.
     */
    public function add(Book $book): string
    {
        if (! $book->isPubliclyVisible()) {
            return 'unavailable';
        }

        if ($this->user()?->ownsBook($book)) {
            return 'owned';
        }

        $ids = $this->ids();

        if (in_array($book->getKey(), $ids, true)) {
            return 'exists';
        }

        if (count($ids) >= self::MAX_ITEMS) {
            return 'full';
        }

        $ids[] = (int) $book->getKey();
        $this->store($ids);

        return 'added';
    }

    public function remove(int $bookId): void
    {
        $this->store(array_values(array_diff($this->ids(), [$bookId])));
    }

    /**
     * @param  iterable<int|null>  $bookIds
     */
    public function removeMany(iterable $bookIds): void
    {
        $remove = collect($bookIds)->filter()->map(fn ($id) => (int) $id)->all();

        $this->store(array_values(array_diff($this->ids(), $remove)));
    }

    public function clear(): void
    {
        $this->store([]);
    }

    public function has(Book|int $book): bool
    {
        $id = $book instanceof Book ? (int) $book->getKey() : $book;

        return in_array($id, $this->ids(), true);
    }

    /**
     * Raw book IDs held in the session.
     *
     * @return list<int>
     */
    public function ids(): array
    {
        $ids = $this->session->get(self::SESSION_KEY, []);

        return is_array($ids)
            ? array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))))
            : [];
    }

    /**
     * Purchasable books in the cart, in the order they were added. Invalid IDs
     * are pruned from the session as a side effect.
     *
     * @return Collection<int, Book>
     */
    public function items(): Collection
    {
        $ids = $this->ids();
        $signature = implode(',', $ids).'|'.($this->user()?->getKey() ?? 'guest');
        $memo = request()->attributes->get(self::MEMO_KEY);

        if (is_array($memo) && $memo['signature'] === $signature) {
            return $memo['items'];
        }

        if ($ids === []) {
            return $this->remember($signature, new Collection);
        }

        $owned = $this->user()?->ownedBookIds() ?? [];

        $books = Book::query()
            ->published()
            ->with(['author', 'category'])
            ->whereKey(array_diff($ids, $owned))
            ->get()
            ->keyBy('id');

        $valid = array_values(array_filter($ids, fn (int $id) => $books->has($id)));

        if ($valid !== $ids) {
            $this->session->put(self::SESSION_KEY, $valid);
            $signature = implode(',', $valid).'|'.($this->user()?->getKey() ?? 'guest');
        }

        return $this->remember($signature, collect($valid)->map(fn (int $id) => $books->get($id))->values());
    }

    /**
     * @param  Collection<int, Book>  $items
     * @return Collection<int, Book>
     */
    private function remember(string $signature, Collection $items): Collection
    {
        request()->attributes->set(self::MEMO_KEY, ['signature' => $signature, 'items' => $items]);

        return $items;
    }

    /**
     * Forget the memoised items (e.g. after the customer's library changed).
     */
    public function refresh(): void
    {
        request()->attributes->remove(self::MEMO_KEY);
    }

    public function count(): int
    {
        return $this->ids() === [] ? 0 : $this->items()->count();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function subtotalCents(): int
    {
        return (int) $this->items()->sum(fn (Book $book) => $book->effective_price_cents);
    }

    /**
     * @param  list<int>  $ids
     */
    private function store(array $ids): void
    {
        $this->session->put(self::SESSION_KEY, $ids);
        $this->refresh();
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }
}
