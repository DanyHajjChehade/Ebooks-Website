<?php

namespace App\View\Composers;

use App\Models\Setting;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shares `$settings` (App\Models\Setting, cached) and `$cartCount` (int) with
 * every view. Values are computed once per request.
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
        $shared = $this->request->attributes->get(self::MEMO_KEY);

        if (! is_array($shared)) {
            $shared = [
                'settings' => Setting::current(),
                'cartCount' => $this->request->hasSession() ? $this->cart->count() : 0,
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
