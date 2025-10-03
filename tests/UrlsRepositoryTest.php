<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Url;
use Hexlet\Code\UrlsRepository;
use PDO;

class UrlsRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UrlsRepository $repository;
    public string $exampleUrl = 'https://example.com';

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('
            CREATE TABLE urls (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                created_at TEXT
            )
        ');

        $this->repository = new UrlsRepository($this->pdo);
    }

    private function createUrl(?string $name = null): Url
    {
        if ($name === null) {
            $name = $this->exampleUrl;
        }
        $url = new Url($name);
        $this->repository->save($url);
        return $url;
    }

    public function testSaveReturnsUrlWithId(): void
    {
        $savedUrl = $this->createUrl();

        $this->assertNotNull($savedUrl->getId());
        $this->assertSame($exampleUrl, $savedUrl->getName());
    }

    public function testFindExistingUrl(): void
    {
        $savedUrl = $this->createUrl();
        $foundUrl = $this->repository->find($savedUrl->getId());

        $this->assertNotNull($foundUrl);
        $this->assertSame($savedUrl->getId(), $foundUrl->getId());
        $this->assertSame($savedUrl->getName(), $foundUrl->getName());
    }

    public function testFindNonExistingUrlReturnsNull(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function testFindByName(): void
    {
        $this->createUrl();

        $foundUrl = $this->repository->findByName($exampleUrl);
        $this->assertNotNull($foundUrl);
        $this->assertSame($exampleUrl, $foundUrl->getName());

        $this->assertNull($this->repository->findByName('https://nonexistent.com'));
    }

    public function testAllReturnsArrayOfUrls(): void
    {
        $this->createUrl('https://example1.com');
        $this->createUrl('https://example2.com');

        $allUrls = $this->repository->all();
        $this->assertIsArray($allUrls);
        $this->assertCount(2, $allUrls);
        $this->assertSame('https://example1.com', $allUrls[0]->getName());
        $this->assertSame('https://example2.com', $allUrls[1]->getName());
    }
}
