<?php

namespace Arc\Mail;

use Arc\Contracts\Mail\Mailer as MailerContract;

class Mailer implements MailerContract
{
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
