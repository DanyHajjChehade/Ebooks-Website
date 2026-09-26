<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Email verification is available (routes + views) but not enforced: buying
 * never depends on SMTP. Implement Illuminate\Contracts\Auth\MustVerifyEmail
 * here to send verification mail on registration.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * `is_admin` is deliberately NOT mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * The customer's library (books they may download).
     *
     * @return BelongsToMany<Book, $this>
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)
            ->withPivot('order_id')
            ->withTimestamps();
    }

    public function ownsBook(Book|int $book): bool
    {
        $bookId = $book instanceof Book ? $book->getKey() : $book;

        return $this->books()->withTrashed()->whereKey($bookId)->exists();
    }

    /**
     * IDs of every book in the user's library.
     *
     * @return list<int>
     */
    public function ownedBookIds(): array
    {
        return $this->books()->withTrashed()->pluck('books.id')->map(fn ($id) => (int) $id)->all();
    }
}
