<?php


namespace src\Logger;


interface TelegramInterface
{
    public function getApiToken(): string;
    public function getChannel(): string;
    public function send(string $message): void;
}