<?php
/**
 * Created by Denis Zhadan on 14.03.2024 17:12.
 */

class Puzzle
{
    protected string $url = 'https://cv.microservices.credy.com/v1';

    protected static function getSignature(int $timestamp): string
    {
        return sha1(strval($timestamp) . 'credy');
    }

    public function send(string $first_name, string $last_name, string $email, string $bio,
                         array  $technologies, int $timestamp, string $vcs_uri): string|false
    {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'bio' => $bio,
            'technologies' => $technologies,
            'timestamp' => $timestamp,
            'signature' => self::getSignature($timestamp),
            'vcs_uri' => $vcs_uri
        ];

        $options = [
            'http' => [
                'protocol_version' => '1.1',
                'method' => 'POST',
                'header' =>
                    'Accept: application/json, text/javascript, */*; q=0.01' . PHP_EOL .
                    'Content-type: application/json',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        return file_get_contents($this->url, false, $context);
    }
}
