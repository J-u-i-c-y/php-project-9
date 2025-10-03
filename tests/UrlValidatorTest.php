<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\UrlValidator;

class UrlValidatorTest extends TestCase
{
    public function testValidUrlReturnsNoErrors()
    {
        $data = ['name' => 'https://example.com'];
        $errors = UrlValidator::validate($data);
        $this->assertEmpty($errors);
    }

    public function testEmptyUrlReturnsError()
    {
        $data = ['name' => ''];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('URL не должен быть пустым', $errors);
    }

    public function testInvalidUrlReturnsError()
    {
        $data = ['name' => 'invalid-url'];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('Некорректный URL', $errors);
    }

    public function testUrlExceedsMaxLengthReturnsError()
    {
        $longUrl = 'http://' . str_repeat('a', 250) . '.com';
        $data = ['name' => $longUrl];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('URL не должен превышать 255 символов', $errors);
    }
}
