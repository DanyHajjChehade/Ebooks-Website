<?php

namespace App\View\Composers;

use App\Models\Setting;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shares with every view (computed once per request):
 *  - $settings      App\Models\Setting (cached; unsaved defaults if no row yet)
 *  - $cartCount     int
 *  - $cartBookIds   list<int>  purchasable books in the cart
 *  - $ownedBookIds  list<int>  books in the signed-in user's library ([] for guests)
 */
class SiteComposer
{
    private const MEMO_KEY = 'view.shared';

    public function __construct(
        private readonly Request $request,
        private readonly Cart $cart,
    ) {}

    public function compose(View $view): void
    {
        // Error pages (errors::404, errors.layout, …) must render without the database,
        // settings cache or session: a 500/503 may be the database failing.
        if (str_starts_with($view->name(), 'errors')) {
            return;
        }

        $shared = $this->request->attributes->get(self::MEMO_KEY);

        if (! is_array($shared)) {
            $cartBookIds = $this->request->hasSession()
                ? $this->cart->items()->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [];

            $user = $this->request->user();

            $shared = [
                'settings' => Setting::current(),
                'cartCount' => count($cartBookIds),
                'cartBookIds' => $cartBookIds,
                'ownedBookIds' => $user instanceof User ? $user->ownedBookIds() : [],
            ];

            $this->request->attributes->set(self::MEMO_KEY, $shared);
        }

        $view->with($shared);
    }

    /**
     * Drop the memo so the next render recomputes (after cart/settings changes).
     */
    public static function forget(Request $request): void
    {
        $request->attributes->remove(self::MEMO_KEY);
    }
}
