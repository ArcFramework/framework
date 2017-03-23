<?php

use Arc\Mail\Email;
use Arc\Mail\Mailer;
use Arc\View\Builder;

use Illuminate\Support\Str;

class MailerTest extends FrameworkTestCase
{
    /** @test */
    public function send_method_calls_wp_mail_with_expected_arguments()
    {
        $email = (new Email)
            ->withTemplate('email.template')
            ->withMessage('<span class="red"> Test message. </span>')
            ->withCSS('.red { color: red; }')
            ->to('test@domain.com')
            ->withSubject('Test Subject');

        $viewBuilder = Mockery::mock('Arc\View\Builder');
        $viewBuilder->shouldReceive('render')
            ->once()
            ->andReturn('Rendered view');

        $this->plugin->instance(Builder::class, $viewBuilder);

        WP_Mock::wpFunction('wp_mail', [
            'times' => 1,
            'args' => [
                'test@domain.com',
                'Test Subject',
                \Mockery::on(function ($message) {
                    return Str::contains($message, '<span class="red" style="color: red;"> Test message. </span>');
                }),
                \Mockery::any(),
                null
            ]
        ]);

        $mailer = $this->plugin->make(Mailer::class);
        $mailer->send($email);
    }
}

