<?php

namespace Tests\Fakes;

use Stripe\HttpClient\ClientInterface;

/**
 * Stands in for stripe-php's cURL client so the real StripePaymentGateway can
 * be exercised without network access. Records every request and answers
 * with a queued (or default) JSON body.
 */
class RecordingStripeHttpClient implements ClientInterface
{
    /** @var list<array{method: string, url: string, params: array<string, mixed>, headers: array<int, string>}> */
    public array $requests = [];

    /** @var list<array{0: int, 1: array<string, mixed>}> */
    private array $responses = [];

    /**
     * @param  array<string, mixed>  $body
     */
    public function queue(array $body, int $status = 200): self
    {
        $this->responses[] = [$status, $body];

        return $this;
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        $this->requests[] = ['method' => $method, 'url' => $absUrl, 'params' => (array) $params, 'headers' => (array) $headers];

        [$status, $body] = array_shift($this->responses) ?? [200, ['id' => 'obj_test', 'object' => 'unknown']];

        return [json_encode($body, JSON_THROW_ON_ERROR), $status, ['request-id' => 'req_test']];
    }

    /**
     * @return array{method: string, url: string, params: array<string, mixed>, headers: array<int, string>}
     */
    public function last(): array
    {
        return $this->requests[array_key_last($this->requests)];
    }
}
