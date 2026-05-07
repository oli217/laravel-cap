<?php

namespace LaravelCap;

use Illuminate\Http\Client\Factory as HttpFactory;
use LaravelCap\Exceptions\CapVerificationException;

class Cap
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly array $config,
    ) {}

    /**
     * Vérifie un token Cap auprès de l'instance distante.
     */
    public function verify(string $token): bool
    {
        try {
            $response = $this->http
                ->timeout($this->config['timeout'])
                ->post($this->siteVerifyUrl(), [
                    'secret'   => $this->config['secret'],
                    'response' => $token,
                ]);
        } catch (\Throwable) {
            return $this->failOpen();
        }

        if ($response->failed()) {
            return $this->failOpen();
        }

        return (bool) ($response->json('success') ?? false);
    }

    private function failOpen(): bool
    {
        return (bool) ($this->config['fail_open'] ?? false);
    }

    /**
     * Vérifie un token Cap et lève une exception en cas d'échec.
     *
     * @throws CapVerificationException
     */
    public function verifyOrFail(string $token): void
    {
        if (! $this->verify($token)) {
            throw new CapVerificationException();
        }
    }

    private function siteVerifyUrl(): string
    {
        return rtrim($this->config['endpoint'], '/') . '/siteverify';
    }
}
