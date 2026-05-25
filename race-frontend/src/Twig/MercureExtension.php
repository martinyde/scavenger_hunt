<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MercureExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $mercureJwtSecret,
        private readonly string $mercurePublicUrl,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mercure_subscribe_url', $this->getMercureSubscribeUrl(...)),
        ];
    }

    /**
     * Generate a Mercure subscription URL with JWT for the given topics.
     *
     * @param array<string> $topics
     */
    public function getMercureSubscribeUrl(array $topics): string
    {
        // Build JWT for subscriber
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'mercure' => ['subscribe' => $topics],
        ]));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, $this->mercureJwtSecret, true)
        );
        $jwt = $header . '.' . $payload . '.' . $signature;

        // Mercure expects repeated `topic=<value>` query parameters (no `topic[0]=`,
        // `topic[1]=` indexing). http_build_query() would produce indexed keys for
        // an array, so build the query string manually.
        $topicParams = array_map(
            static fn (string $topic): string => 'topic=' . rawurlencode($topic),
            $topics,
        );
        $query = implode('&', $topicParams);

        return $this->mercurePublicUrl . '?' . $query . '&authorization=' . $jwt;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
