<?php

declare(strict_types=1);

namespace Yatmo\Laravel\Tests;

use Yatmo\Transport\TransportInterface;

/** Canned JSON answers keyed by a substring of the URL; records every call. 404 otherwise. */
final class FakeTransport implements TransportInterface
{
    /** @var array<int, array{url: string, headers: array<string, string>}> */
    public array $calls = [];

    /** @param array<string, mixed> $answers */
    public function __construct(private readonly array $answers)
    {
    }

    public function get(string $url, array $headers, int $timeoutSeconds): array
    {
        $this->calls[] = ['url' => $url, 'headers' => $headers];
        foreach ($this->answers as $needle => $body) {
            if (str_contains($url, $needle)) {
                return ['status' => 200, 'body' => json_encode($body, JSON_THROW_ON_ERROR)];
            }
        }

        return ['status' => 404, 'body' => '{"Error":"no canned answer"}'];
    }
}
