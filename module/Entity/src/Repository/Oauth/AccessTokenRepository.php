<?php

namespace Document\Repository\Oauth;

use Entity\Repository\BaseRepository;

/**
 * Access tokens
 */
class AccessTokenRepository extends BaseRepository
{
    /**
     * Find a live token based on the key. Only returns live tokens
     * @param $key
     */
    public function findLiveToken($key)
    {

    }

}
