<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Models\Book;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        return view('cart.index', [
            'items' => $cart->items(),
            'subtotalCents' => $cart->subtotalCents(),
        ]);
    }

    public function store(CartStoreRequest $request, Cart $cart): RedirectResponse|JsonResponse
    {
        $book = Book::query()->findOrFail($request->integer('book_id'));

        $result = $cart->add($book);

        $message = match ($result) {
            'added' => "Added “{$book->title}” to your cart.",
            'exists' => "“{$book->title}” is already in your cart.",
            'owned' => 'You already own this book. It’s in your library.',
            'full' => 'Your cart is full. Check out or remove a book first.',
            default => 'This book is not available right now.',
        };

        $ok = in_array($result, ['added', 'exists'], true);

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $cart->count(),
                'message' => $message,
                'added' => $result === 'added',
                'result' => $result,
            ]);
        }

        return back()->with($ok ? 'status' : 'error', $message);
    }

    public function destroy(Request $request, Cart $cart, int $book): RedirectResponse|JsonResponse
    {
        $cart->remove($book);

        $title = Book::withTrashed()->whereKey($book)->value('title');
        $message = $title !== null ? "Removed “{$title}” from your cart." : 'Removed from your cart.';

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $cart->count(),
                'subtotalCents' => $cart->subtotalCents(),
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
