<?php

declare(strict_types = 1);

namespace App\Auth;

use App\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ServerRequestInterface;

class Auth
{
    public function __construct(protected Settings $settings)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     */
    public static function getAccessTokenFromRequest(ServerRequestInterface $request): ?string
    {
        // Access token in header is stronger.
        $tokenFromHeader = $request->getHeader('Authorization')[0] ?? null;
        if ($tokenFromHeader !== null) {
            return $tokenFromHeader;
        }

        $tokenFromQuery = $request->getQueryParams()['accessToken'] ?? null;
        if ($tokenFromQuery !== null) {
            return $tokenFromQuery;
        }

        return null;
    }

    /**
     * This function throw exception if the access token is not valid.
     *
     * @param string $accessToken
     * @throws GuzzleException
     */
    public function validateAccessToken(string $accessToken): void
    {
        $client = new Client();

        $client->get($this->settings->getAuthUrl() . '/user', [
            RequestOptions::HEADERS => [
                'Authorization' => $accessToken,
            ],
            RequestOptions::TIMEOUT => 5,
        ]);
    }
}
