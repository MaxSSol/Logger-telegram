<?php
declare(strict_types=1);

namespace src\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use RuntimeException;

class TelegramHandler extends AbstractProcessingHandler implements TelegramInterface
{
    /**
     * @var string  Telegram bot access token provided by BotFather
     */
    private string $apiToken;
    /**
     * @var string Telegram channel name.
     * Since to start with '@' symbol as prefix.
     */
    private string $channel;
    private const BOT_API = 'https://api.telegram.org/bot';
    public function __construct(string $apiToken, string $channel, $level = Logger::DEBUG)
    {
        $this->apiToken = $apiToken;
        $this->channel = $channel;
    }
    public function getApiToken(): string
    {
        return $this->apiToken;
    }
    public function getChannel(): string
    {
        return $this->channel;
    }
    public function write(array $record): void
    {
        $message = 'Date: ' .
            date(DATE_RFC822)  .
            '| Level: ' . $record['level_name'] .
            '| channel: ' . $record['channel'] .
            '| message: ' .
            $record['message'];
        $this->send($message);
    }
    public function send(string $message): void
    {
        $ch = curl_init();
        $url = self::BOT_API . $this->apiToken . '/SendMessage';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'text' => $message,
            'chat_id' => $this->getChannel()
        ]));
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if ($result['ok'] === false) {
            throw new RuntimeException('Telegram API error. Description: ' . $result['description']);
        }
    }
}
