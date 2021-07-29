<?php

declare(strict_types=1);

namespace Siteworx\Library\Email;

use Carbon\Carbon;
use Siteworx\Library\Application\Core;
use Siteworx\Mail\{Client, Exceptions\ValidationException, Transports\ApiTransport, Transports\TransportInterface};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

/**
 * Class MailServer
 * @package Siteworx\Library\Email
 */
final class MailServer
{

    /**
     * @var string
     */
    private string $from;

    /**
     * @var Client
     */
    private Client $mailClient;

    /**
     * @var string
     */
    private string $subject = '<No Subject>';

    /**
     * @var Carbon
     */
    private Carbon $scheduleTime;

    /**
     * @param Carbon $date
     * @return $this
     */
    public function scheduleEmail(Carbon $date): self
    {
        $this->scheduleTime = $date;

        return $this;
    }

    /**
     * MailServer constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $transportClass = Core::di()->config->get(
            'mail.driver',
            ApiTransport::class
        );

        /** @var TransportInterface $transport */
        $transport = new $transportClass(
            Core::di()->config->get('mail', [])
        );

        $transport->setLogger(Core::di()->log);
        $this->mailClient = new Client($transport);
        $this->from = (string) Core::di()->config->get('mail.from');
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $email
     * @param string $template
     * @param array $params
     * @return bool
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws ValidationException
     */
    public function send(string $email, string $template, array $params = []): bool
    {
        $content = Core::di()->view->render('Email/' . $template, $params);

        $this->mailClient->setFrom($this->from);
        $this->mailClient->setAllTo([$email]);
        $this->mailClient->setSubject($this->subject);
        $this->mailClient->setBody($content, true);

        if ($this->scheduleTime instanceof Carbon) {
            $this->mailClient->sendTime($this->scheduleTime);
        }

        $results = $this->mailClient->send((bool) Core::di()->config->get('mail.catch', false));

        return $results->status === 'ok';
    }

    /**
     * @param string $localPath
     * @throws ValidationException
     */
    public function addAttachment(string $localPath): void
    {
        $this->mailClient->addAttachment($localPath);
    }
}
