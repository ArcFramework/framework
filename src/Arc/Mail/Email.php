<?php

namespace Arc\Mail;

use Arc\BasePlugin;
use Illuminate\Support\Str;
use Illuminate\View\ViewBuilder;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Email
{
    public $bcc;
    public $cc;
    public $from;
    public $subject;
    public $to;

    protected $plugin;
    protected $template;
    protected $css;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function withTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Sets the HTML text to be sent in the message
     * @param string $message
     * @return $this
     **/
    public function withMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets the CSS styling rules to be applied to the HTML text in the message
     * @param string $css
     * @return $this
     **/
    public function withCSS($css)
    {
        $this->css = $css;
        return $this;
    }

    /**
     * Gets the email attachments for the email
     **/
    public function getAttachments()
    {

    }

    /**
     * Gets the email headers for the email
     **/
    public function getHeaders()
    {

    }

    /**
     * Renders and returns the content of the email message
     **/
    public function getMessage()
    {
        // Render message as HTML
        if (!empty($this->message)) {
            $message = $this->message;
        }
        else {
            $message = $this->plugin->make('blade')->view()->make($this->template)->render();
        }

        // If CSS has been applied, fetch that
        if (isset($this->css)) {
            $message = $this->plugin->make(CssToInlineStyles::class)->convert($message, $this->css);
        }

        return $message;
    }

    /**
     * Returns the subject text for the email
     * @return string
     **/
    public function getSubject()
    {
        return $this->subject;
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

    /**
     * Set the recipient of the email
     * @param string $email An email address, or comma separated list of email addresses
     * @return $this
     **/
    public function to($email)
    {
        $this->to = $email;
        return $this;
    }
}
