<?php

namespace Auth\Service;

use Psr\Http\Client\ClientInterface;

interface HttpClientAware
{
    public function setHttpClient(ClientInterface $client): self;
}
