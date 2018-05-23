<?php

namespace JwtOAuth2Bundle\Annotation;

/**
 * Annotation class for @Authenticated.
 *
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 *
 */
class Authenticated
{
    private $scopes;

    public function __construct(array $data)
    {
        $this->scopes = $data['scopes'];
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
