<?php

namespace Auth\Service;

use Auth\Model\OAuthResponse;
use Psr\Http\Client\ClientInterface;
use Laminas\Diactoros\Request;
use InvalidArgumentException;
use json_decode;

class FacebookOauth implements OAuthInterface, HttpClientAware
{
    private ClientInterface $client;

    public function query(string $token, string $id, string $domain): OAuthResponse
    {
        $request = new Request(
            implode('', [
                "https://graph.facebook.com/v8.0/me?access_token=",
                $token,
                "&fields=last_name%2Cemail%2Cfirst_name%2Cid&method=get",
                "&pretty=0&sdk=joey&suppress_http_code=1"
            ]),
            'GET'
        );

        $response = $this->client->sendRequest($request);
        $data = json_decode($response->getBody(), true);

        if (key_exists('error', $data) || $id !== $data['id']) {
            throw new InvalidArgumentException('Invalid OAuth token for Facebook', 401);
        }

        return $data;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = $client;
        return $this;
    }
}
