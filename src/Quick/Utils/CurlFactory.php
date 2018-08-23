<?php

namespace Quick\Utils;

use \Curl\Curl;

class CurlFactory {
    /**
     * @return Curl
     */
    public function create() {
        $curl = new Curl();
        $curl->setTimeout(20);
        $curl->setConnectTimeout(5);

        return $curl;
    }
}
