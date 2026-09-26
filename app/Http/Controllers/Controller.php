<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Checkout is a POST, so Laravel can't remember it as the intended URL;
     * the cart links to login/register with ?return=cart instead. Allow-listed
     * value only, so this can't become an open redirect.
     */
    protected function rememberCartReturn(Request $request): void
    {
        if ($request->query('return') === 'cart') {
            redirect()->setIntendedUrl(route('cart.index'));
        }
    }
}
