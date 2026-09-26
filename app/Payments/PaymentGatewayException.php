<?php

namespace App\Payments;

use RuntimeException;

/**
 * Thrown when the payment provider cannot be reached or rejects a request.
 */
class PaymentGatewayException extends RuntimeException {}
