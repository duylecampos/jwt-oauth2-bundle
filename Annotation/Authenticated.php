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
        $this->scopes = array_key_exists('scopes', $data) ? $data['scopes'] : null;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
