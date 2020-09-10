<?php

namespace Auth\Service;

interface OauthAware
{
    public function setOauthService(Oauth $service): self;
}
