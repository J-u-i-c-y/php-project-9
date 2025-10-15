<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Entities\Check;
use Hexlet\Code\Repositories\CheckRepo;
use PDO;
use Carbon\Carbon;

class CheckRepoTest extends TestCase
{
    private PDO $pdo;
    private CheckRepo $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('
            CREATE TABLE urls (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                created_at TEXT
            );
        ');

        $this->pdo->exec('
            CREATE TABLE url_checks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url_id INTEGER,
                status_code INTEGER,
                h1 TEXT,
                title TEXT,
                description TEXT,
                created_at TEXT
            );
        ');

        $this->repo = new CheckRepo($this->pdo);
    }

    private function createCheck(
        int $urlId = 1,
        int $statusCode = 200,
        ?string $h1 = null,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $createdAt = null
    ): Check {
        return new Check(
            urlId: $urlId,
            statusCode: $statusCode,
            h1: $h1,
            title: $title,
            description: $description,
            createdAt: $createdAt ?? Carbon::now()
        );
    }

    public function testSaveReturnsCheckWithId(): void
    {
        $check = $this->createCheck();
        $savedCheck = $this->repo->save($check);

        $this->assertNotNull($savedCheck->getId());
        $this->assertSame(1, $savedCheck->getUrlId());
        $this->assertSame(200, $savedCheck->getStatusCode());
    }

    public function testAllReturnsArrayOfChecks(): void
    {
        $this->repo->save($this->createCheck(urlId: 1));
        $this->repo->save($this->createCheck(urlId: 2));

        $allChecks = $this->repo->all();
        $this->assertIsArray($allChecks);
        $this->assertCount(2, $allChecks);
        $this->assertSame(1, $allChecks[0]->getUrlId());
        $this->assertSame(2, $allChecks[1]->getUrlId());
    }

    public function testFindAllByUrlIdReturnsChecksForUrl(): void
    {
        $this->repo->save($this->createCheck(urlId: 1));
        $this->repo->save($this->createCheck(urlId: 1));
        $this->repo->save($this->createCheck(urlId: 2));

        $checks = $this->repo->findAllByUrlId(1);
        $this->assertCount(2, $checks);
        $this->assertSame(1, $checks[0]->getUrlId());
        $this->assertSame(1, $checks[1]->getUrlId());
    }

    public function testFindLastCreatedAtByUrlIdReturnsDate(): void
    {
        $check1 = $this->createCheck(urlId: 1, createdAt: Carbon::now());
        $check2 = $this->createCheck(urlId: 1, createdAt: Carbon::now()->addSecond());
        $this->repo->save($check1);
        $this->repo->save($check2);

        $lastDate = $this->repo->findLastCreatedAtByUrlId(1);
        $this->assertEquals($check2->getCreatedAt(), $lastDate);
    }

    public function testFindLastStatusCodeByUrlIdReturnsCode(): void
    {
        $this->repo->save($this->createCheck(urlId: 1, statusCode: 200, createdAt: Carbon::now()));
        $this->repo->save($this->createCheck(
            urlId: 1,
            statusCode: 404,
            createdAt:
            Carbon::now()->addSecond()
        ));

        $lastCode = $this->repo->findLastStatusCodeByUrlId(1);
        $this->assertSame(404, $lastCode);
    }
}
