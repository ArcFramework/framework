<?php

namespace Arc\Http;

use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse
{
    /**
     * Returns true if this response is for a route which should be handled by wordpress.
     *
     * @return bool
     **/
    public function shouldBeHandledByWordpress()
    {
        return $this instanceof DeferToWordpress;
    }
}
