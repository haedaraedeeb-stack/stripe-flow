<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use Illuminate\Contracts\Container\Container;

final class PaymentGatewayManager {
    public function __construct(
        private Container $app,
    ){}

    public function driver(string $name): PaymentGateway {
        $class = config("payments.gateways.$name") ??
            throw new \InvalidArgumentException("Unsupported gateway [$name]");
        return $this->app->make($class);

    }
}
