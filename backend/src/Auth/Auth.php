<?php

declare(strict_types = 1);

namespace App\Auth;

use App\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class Auth
{
    public function __construct(protected Settings $settings)
    {
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
