<?php

namespace Arc\Mail;

use Arc\Contracts\Mail\Mailer as MailerContract;
use Arc\Hooks\Actions;
use Arc\Hooks\Filters;
use Arc\Config\WPOptions;
use Arc\View\Builder;
use Html2Text\Html2Text;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mailer implements MailerContract
{
    protected $filters;
    protected $blankEmail;
    protected $viewBuilder;
    protected $cssInliner;
    protected $wpOptions;

    public function __construct(Actions $actions, Email $email, Builder $viewBuilder, CssToInlineStyles $cssInliner, Filters $filters, WPOptions $wpOptions)
    {
        $this->actions = $actions;
        $this->filters = $filters;
        $this->blankEmail = $email;
        $this->cssInliner = $cssInliner;
        $this->viewBuilder = $viewBuilder;
        $this->wpOptions = $wpOptions;
    }

    public function compose()
    {
        return $this->blankEmail;
    }

    public function send(Email $email)
    {
        // If no from address is set in wordpress we'll use the default for the site
        if (!$this->fromAddressIsSet()) {
            $this->setFromAddress($this->wpOptions->get('admin_email'), $this->wpOptions->get('blogname'));
        }

        // Render the message
        $message = $this->renderMessage($email);

        // Automatically render a plain text version of the email
        $this->actions
            ->forHook('phpmailer_init')
            ->doThis(function($phpMailer) use ($message) {
                $phpMailer->AltBody = Html2Text::convert($message);
            });

        // Send the email
        return wp_mail(
            $email->getTo(),
            $email->getSubject(),
            $message,
            $email->getHeaders(),
            $email->getAttachments()
        );
    }

    /**
     * Renders the message for the given email and returns it
     * @param Arc\Mail\Email $email
     * @return string
     **/
    protected function renderMessage(Email $email)
    {
        // If a template is set, we'll default to that
        if ($email->hasTemplate()) {
            $message = $this->viewBuilder->render($email->getTemplate());
        }

        // If a plain text message is set, that overrides any templates
        if ($email->hasMessage()) {
            $message = $email->getMessage();
        }

        // If we still have no text the email message can't be rendered
        if (!isset($message)) {
            throw new \Exception('Email does not have any content.');
        }

        // If CSS has been applied, fetch that
        if ($email->hasCSS()) {
            $message = $this->cssInliner->convert($message, $email->getCSS());
        }

        return $message;
    }

    /**
     * Returns true if a from address has been set for outgoing mail
     * @return bool
     **/
    protected function fromAddressIsSet()
    {
        return !empty($this->filters->apply('wp_mail_from', ''));
    }

    /**
     * Set the from address for outgoing mail
     * @param string $address The email address
     * @param string $name (optional) The from name
     **/
    protected function setFromAddress($address, $name = null)
    {
        $this->filters->forHook('wp_mail_from')->doThis(function() use ($address) {
            return $address;
        });
        $this->filters->forHook('wp_mail_from_name')->doThis(function() use ($name) {
            return $name;
        });
    }
}
