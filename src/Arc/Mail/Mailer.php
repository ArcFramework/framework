<?php

namespace Arc\Mail;

use Arc\Contracts\Mail\Mailer as MailerContract;
use Arc\Hooks\Actions;
use Arc\Hooks\Filters;
use Arc\Config\WPOptions;
use Illuminate\View\Factory as ViewFactory;
use Html2Text\Html2Text;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mailer implements MailerContract
{
    protected $filters;
    protected $blankEmail;
    protected $viewFactory;
    protected $cssInliner;
    protected $wpOptions;

    public function __construct(
        Actions $actions,
        ViewFactory $viewFactory,
        CssToInlineStyles $cssInliner,
        Email $email,
        Filters $filters,
        WPOptions $wpOptions
    )
    {
        $this->actions = $actions;
        $this->filters = $filters;
        $this->blankEmail = $email;
        $this->cssInliner = $cssInliner;
        $this->viewFactory = $viewFactory;
        $this->wpOptions = $wpOptions;
    }

    public function compose()
    {
        return $this->blankEmail;
    }

    public function send(Email $email)
    {
        $this->setFromAddress($this->getFromAddress($email));

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
            $message = $this->viewFactory->make($email->getTemplate(), $email->getTemplateParameters());
        }

        // If a plain text message is set, that overrides any templates
        if ($email->hasMessage()) {
            $message = $email->getMessage();
        }

        // If we still have no text the email message can't be rendered
        if (!isset($message)) {
            throw new \Exception('Email does not have any content.');
        }

        // Inline the email with any CSS
        $message = $this->cssInliner->convert($message, $email->getCSS());

        return $message;
    }

    /**
     * Set the from address for outgoing mail
     * @param string $address The email address
     * @param string $name (optional) The from name
     **/
    public function setFromAddress($address, $name = null)
    {
        $this->wpOptions->setDefaultFromAddress($address);
    }

    /**
     * Returns the appropriate from address that we should use to send this email
     * @param Arc\Mail\Email
     * @return string
     **/
    public function getFromAddress(Email $email)
    {
        // If the email has a from address we should use that above all else
        if ($email->hasFromAddress()) {
            return $email->getFromAddress();
        }

        // Return the default wordpress from address if it is set
        if ($this->wpOptions->defaultFromAddressIsSet()) {
            return $this->wpOptions->getDefaultFromAddress();
        }

        // Since no from address is set in wordpress we'll use the default for the site
        return $this->wpOptions->get('admin_email');
    }
}
