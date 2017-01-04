<?php

namespace Arc\Testing;

class TestCase
{
    /**
     * Performs an HTTP request
     *
     * @param string $method (GET', 'POST', 'HEAD', or 'PUT') The HTTP method verb
     * @param string $url The url the request is being sent to
     * @param array $data The data to send with the request
     **/
    public function call($method, $url, $data)
    {
        WP_HTTP::request($url, [
            'method' => $method,
            'headers' => $data
        ]);
    }
}
