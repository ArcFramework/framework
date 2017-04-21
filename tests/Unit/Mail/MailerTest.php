<?php

use Arc\Config\WPOptions;
use Arc\Hooks\Actions;
use Arc\Hooks\Filters;
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
            ->from('from@domain.com')
            ->withSubject('Test Subject');

        $viewBuilder = Mockery::mock('Arc\View\Builder');
        $viewBuilder->shouldReceive('build')
            ->once()
            ->andReturn('Rendered view');

        $this->app->instance(Builder::class, $viewBuilder);

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

        $mailer = $this->app->make(Mailer::class);
        $mailer->send($email);
    }

    /** @test */
    public function send_method_uses_default_wp_address_for_email_without_from_address()
    {
        $wpOptions = $this->app->make(WPOptions::class);
        $wpOptions->setTest('admin_email', 'admin@domain.com');
        $wpOptions->setTest('wp_mail_from', 'from@domain.com');

        $email = (new Email)
            ->withMessage('Test message.')
            ->to('test@domain.com');

        $this->app->instance(WPOptions::class, $wpOptions);

        $mailer = $this->app->make(Mailer::class);
        $mailer->send($email);

        // TODO Verify that the default address is actually being used
    }

    /** @test */
    public function get_from_method_returns_default_wp_address_for_email_without_from_address()
    {
        $wpOptions = $this->app->make(WPOptions::class);
        $wpOptions->setTest('admin_email', 'admin@domain.com');

        \WP_Mock::onFilter('wp_mail_from')
            ->with('')
            ->reply('from@domain.com');

        $this->app->instance(WPOptions::class, $wpOptions);

        $email = (new Email)
            ->withMessage('Test message.')
            ->to('test@domain.com');

        $mailer = $this->app->make(Mailer::class);
        $this->assertEquals('from@domain.com', $mailer->getFromAddress($email));
    }
}
