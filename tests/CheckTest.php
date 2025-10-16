<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Entities\Check;
use Carbon\Carbon;

class CheckTest extends TestCase
{
    public function testConstructorSetsAllFields(): void
    {
        $createdAt = new \Carbon\Carbon('2024-01-01 12:00:00');
        $check = new \Hexlet\Code\Entities\Check(
            urlId: 1,
            id: 123,
            statusCode: 200,
            h1: 'Test H1',
            title: 'Test Title',
            description: 'Test Description',
            createdAt: $createdAt
        );

        $check->setId(123);
        $check->setUrlId(1);
        $check->setStatusCode(200);
        $check->setH1('Test H1');
        $check->setTitle('Test Title');
        $check->setDescription('Test Description');
        $check->setCreatedAt($createdAt);

        $this->assertEquals(123, $check->getId());
        $this->assertEquals(1, $check->getUrlId());
        $this->assertEquals(200, $check->getStatusCode());
        $this->assertEquals('Test H1', $check->getH1());
        $this->assertEquals('Test Title', $check->getTitle());
        $this->assertEquals('Test Description', $check->getDescription());
        $this->assertEquals($createdAt, $check->getCreatedAt());
    }

    public function testCreatedAtDefaultsToNow(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $check = new Check(urlId: 1);

        $this->assertEquals($now, $check->getCreatedAt());
        Carbon::setTestNow();
    }
}
