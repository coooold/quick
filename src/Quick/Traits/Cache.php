<?php

namespace Quick\Traits;

trait Cache {
    /**
     * @Inject
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;
}
