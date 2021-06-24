<?php

namespace Drugovich\Logging;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class RocketChatHandler extends AbstractProcessingHandler
{
    /**
     * @var Client;
     */
    private $client;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * Instance of the SlackRecord util class preparing data for Slack API.
     * @var RocketChatRecord
     */
    private $rocketChatRecord;

    /**
     * RocketChatHandler constructor.
     *
     * @param array $webhooks
     * @param string $username
     * @param string $emoji
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        array $webhooks,
        string $username = null,
        string $emoji = null,
        int $level = Logger::ERROR,
        bool $bubble = true
    )
    {
        parent::__construct($level, $bubble);

        $this->webhooks = $webhooks;

        $this->client = new Client();

        $this->rocketChatRecord = new RocketChatRecord(
            $username,
            $emoji,
            $this->formatter
        );
    }

    /**
     * @param array $record
     * @throws GuzzleException
     */
    protected function write(array $record): void
    {
        $content = $this->rocketChatRecord->getRocketChatData($record);

        foreach ($this->webhooks as $webhook) {
            $this->client->request('POST', $webhook, [
                'json' => $content,
            ]);
        }
    }
}
