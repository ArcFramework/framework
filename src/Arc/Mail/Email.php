<?php

namespace Arc\Mail;

use Arc\BasePlugin;
use Illuminate\Support\Str;

class Email
{
    public $bcc;
    public $cc;
    public $from;
    public $subject;
    public $to;

    protected $template;
    protected $templateParameters;
    protected $css;

    /**
     * Gets the email attachments for the email
     * TODO
     **/
    public function getAttachments()
    {

    }

    /**
     * Returns the CSS styles if they have been set
     * @return string
     **/
    public function getCSS()
    {
        return $this->css;
    }

    public function withTemplate($template, $parameters = [])
    {
        $this->template = $template;
        $this->templateParameters = $parameters;
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
     * Sets the subject for the message
     * @param string $subject
     * @return $this
     **/
    public function withSubject($subject)
    {
        $this->subject = $subject;
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
     * Returns the from address set for the email if it exists
     * @erturn string|null
     **/
    public function getFromAddress()
    {
        return $this->from;
    }

    /**
     * Gets the email headers for the email
     * @return array
     **/
    public function getHeaders()
    {
        return ['Content-Type: text/html; charset=UTF-8'];
    }

    /**
     * Renders and returns the content of the email message
     **/
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the name of the blade template
     * @return string
     **/
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Returns the parameters being passed into the blade template
     * @return array
     **/
    public function getTemplateParameters()
    {
        return $this->templateParameters;
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
     * Returns true if the email has CSS styles
     * @return bool
     **/
    public function hasCSS()
    {
        return !empty($this->css);
    }

    /**
     * Returns true if a from address has been set for the email
     * @erturn bool
     **/
    public function hasFromAddress()
    {
        return !empty($this->from);
    }

    /**
     * Returns true if the email has a plain text message
     * @return bool
     **/
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * Returns true if the email has a blade template
     * @return bool
     **/
    public function hasTemplate()
    {
        return !empty($this->template);
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

    /**
     * Set the sender address of the email
     * @param string $email An email address
     * @return $this
     **/
    public function from($email)
    {
        $this->from = $email;
        return $this;
    }
}
