<?php

namespace Arc\Mail;

use Arc\Contracts\Mail\Mailer as MailerContract;

class Mailer implements MailerContract
{
    protected $blankEmail;

    public function __construct(Email $email)
    {
        $this->blankEmail = $email;
    }

    public function compose()
    {
        return $this->blankEmail;
    }

    public function send(Email $email)
    {
        wp_mail(
            $email->getTo(),
            $email->getSubject(),
            $email->getMessage(),
            $email->getHeaders(),
            $email->getAttachments()
        );
    }
}
