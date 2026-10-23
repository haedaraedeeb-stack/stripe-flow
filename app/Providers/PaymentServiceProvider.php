<?php

namespace App\Providers;

use App\Payments\Gateways\StripeGateway;
use App\Payments\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;
use Stripe\ApiRequestor;
use Stripe\HttpClient\CurlClient;
use Stripe\Stripe;
use Stripe\StripeClient;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class);
        $this->app->bind(StripeGateway::class, fn() => new StripeGateway(
            new StripeClient( (string) config('services.stripe.secret')),
            (string) config('services.stripe.webhook-secret'),
        ));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (str_contains((string) config('services.stripe.secret'), '_live_')
            && ! config('payments.allow_live_keys')) {
            throw new \RuntimeException('Live Stripe keys are not allowed in this release.');
        }
        Stripe::setMaxNetworkRetries(2);
        $curl = new CurlClient();
        $curl->setTimeout(15);
        $curl->setConnectTimeout(5);
        ApiRequestor::setHttpClient($curl);
    }
}
