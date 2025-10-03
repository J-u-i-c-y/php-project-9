<?php

namespace Hexlet\Code;

class UrlValidator
{
    public function validate(array $urlData): array
    {
        $errors = [];
        $wrongEmail = "Некорректный URL";

        $url = parse_url($urlData['name']);
        $scheme = $url['scheme'] ?? '';
        $host = $url['host'] ?? '';

        if (empty($urlData['name'])) {
            $errors[] = 'URL не должен быть пустым';
        }

        if (empty($scheme) || empty($host)) {
            $errors[] = $wrongEmail;
        }

        if ($scheme !== 'http' && $scheme !== 'https') {
            $errors[] = $wrongEmail;
        }

        if (!str_starts_with($urlData['name'], 'http://') && !str_starts_with($urlData['name'], 'https://')) {
            $errors[] = $wrongEmail;
        }

        return $errors;
    }
}
