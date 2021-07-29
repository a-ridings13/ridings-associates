<?php

namespace Siteworx\Library\Queue;

/**
 * Class Message
 * @package App\Library\Queue
 */
final class Message
{

    private $id;

    private $body;

    /**
     * Message constructor.
     * @param array $message
     */
    public function __construct(array $message)
    {
        $bodyMd5 = md5($message['Body']);

        if ($bodyMd5 !== $message['MD5OfBody']) {
            throw new \LogicException('Message HMAC check failed.');
        }

        $this->id = $message['MessageId'];
        $this->body = $message['Body'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->body, JSON_THROW_ON_ERROR, 512);
    }
}
