<?php

namespace Arc\Mail;

use Illuminate\Support\Str;
use Illuminate\View\ViewBuilder;

abstract class Email
{
    public $to;
    public $from;
    public $cc;
    public $bcc;

    protected $template;

    public function withTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Renders and returns the content of the email message
     **/
    public function getMessage()
    {
        return app('blade')->view()->make($this->template)->render();
    }

    /**
     * Returns the recipient address(es)
     * @return string
     **/
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Returns true if the given email address is one of the recipients of the
     * email
     **/
     public function isBeingSentTo($emailAddress)
     {
         return Str::contains($this->to, $emailAddress)
            || Str::contains($this->cc, $emailAddress)
            || Str::contains($this->bcc, $emailAddress);
     }
}
