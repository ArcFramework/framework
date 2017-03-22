<?php

use Arc\Mail\Email;
use Arc\Mail\Mailer;

class MailerTest extends FrameworkTestCase
{
    /** @test */
    public function send_method_calls_wp_mail_with_expected_arguments()
    {
        $email = Mockery::mock(Email::class);

        $email->shouldReceive('getTo')
            ->once()
            ->andReturn('test@domain.com');

        $email->shouldReceive('getSubject')
            ->once()
            ->andReturn('Test Subject');

        $email->shouldReceive('getMessage')
            ->once()
            ->andReturn('Test message.');

        $email->shouldReceive('getHeaders')
            ->once()
            ->andReturn(null);

        $email->shouldReceive('getAttachments')
            ->once()
            ->andReturn(null);

        WP_Mock::wpFunction('wp_mail', [
            'times' => 1,
            'args' => [
                'test@domain.com',
                'Test Subject',
                'Test message.',
                null,
                null
            ]
        ]);

        $mailer = $this->plugin->make(Mailer::class);
        $mailer->send($email);
    }
}

