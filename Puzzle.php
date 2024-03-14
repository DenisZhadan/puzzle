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

    function arrayToJSONx($data, $xml = null, bool $with_name = true)
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement('<json:object xsi:schemaLocation="http://www.datapower.com/schemas/json jsonx.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:json="http://www.ibm.com/xmlns/prod/2009/jsonx"/>');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_values($value) === $value) {  // Check if array is associative or not
                    $subnode = $xml->addChild("json:array");
                    $subnode->addAttribute('name', $key);
                    $this->arrayToJSONx($value, $subnode, false);
                } else {
                    $subnode = $xml->addChild("json:object");
                    $subnode->addAttribute('name', $key);
                    $this->arrayToJSONx($value, $subnode);
                }
            } else {
                if (is_null($value)) {
                    $child = $xml->addChild("json:null");
                } elseif (is_bool($value)) {
                    $child = $xml->addChild("json:boolean", htmlspecialchars(var_export($value, true)));
                } elseif (is_numeric($value)) {
                    $child = $xml->addChild("json:number", htmlspecialchars("$value"));
                } else {
                    $child = $xml->addChild("json:string", htmlspecialchars("$value"));
                }
                if ($with_name) {
                    $child->addAttribute('name', $key);
                }
            }
        }

        return $xml;
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

        $jsonx = $this->arrayToJSONx($data)->asXML();

        $options = [
            'http' => [
                'protocol_version' => '1.1',
                'method' => 'POST',
                'header' =>
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' . PHP_EOL .
                    'Content-type: application/xml',
                'content' => $jsonx
            ]
        ];

        $context = stream_context_create($options);
        return file_get_contents($this->url, false, $context);
    }
}
