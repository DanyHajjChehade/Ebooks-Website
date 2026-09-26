<?php

namespace App\Providers;

use App\Models\User;
use App\Payments\PaymentGateway;
use App\Payments\StripePaymentGateway;
use App\Services\Cart;
use App\View\Composers\SiteComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class);

        $this->app->bind(PaymentGateway::class, function () {
            $secret = config('services.stripe.secret');

            return new StripePaymentGateway(filled($secret) ? new StripeClient($secret) : null);
        });
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureAuthorization();
        $this->configureRateLimiting();

        View::composer('*', SiteComposer::class);

        if ($proxies = config('app.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8));

        DB::prohibitDestructiveCommands($this->app->isProduction());
    }

    private function configureModels(): void
    {
        $strict = ! $this->app->isProduction();

        Model::preventSilentlyDiscardingAttributes($strict);
        Model::preventLazyLoading($strict);

        if ($this->app->isLocal()) {
            // Surface N+1 queries in the log during development instead of crashing the page.
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
                logger()->warning(sprintf('Lazy loaded [%s] on [%s].', $relation, $model::class));
            });
        }
    }

    private function configureAuthorization(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(10)->by('login-ip:'.$request->ip()),
            Limit::perMinute(5)->by('login:'.Str::lower((string) $request->input('email')).'|'.$request->ip()),
        ]);

        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(5)->by('password-reset-ip:'.$request->ip()),
            Limit::perHour(10)->by('password-reset:'.Str::lower((string) $request->input('email'))),
        ]);

        RateLimiter::for('downloads', fn (Request $request) => Limit::perMinute(20)
            ->by('downloads:'.($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)
            ->by('checkout:'.($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(10)
            ->by('reviews:'.($request->user()?->getKey() ?? $request->ip())));

        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(60)
            ->by('cart:'.($request->user()?->getKey() ?? $request->ip())));
    }
}
