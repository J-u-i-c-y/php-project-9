<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Entities\Url;
use Hexlet\Code\Repositories\UrlRepo;
use Carbon\Carbon;
use PDO;

class UrlRepoTest extends TestCase
{
    private PDO $pdo;
    private UrlRepo $repo;
    public string $exampleUrl = 'https://example.com';

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__ . '/../database.sql');
        $this->pdo->exec($schema);

        $this->repo = new UrlRepo($this->pdo);
    }

    private function createUrl(?string $name = null): Url
    {
        if ($name === null) {
            $name = $this->exampleUrl;
        }
        $url = new Url($name);
        $this->repo->save($url);
        return $url;
    }

    public function testSaveReturnsUrlWithId(): void
    {
        $savedUrl = $this->createUrl();

        $this->assertNotNull($savedUrl->getId());
        $this->assertSame($this->exampleUrl, $savedUrl->getName());
    }

    public function testFindExistingUrl(): void
    {
        $savedUrl = $this->createUrl();
        $foundUrl = $this->repo->find($savedUrl->getId());

        $this->assertNotNull($foundUrl);
        $this->assertSame($savedUrl->getId(), $foundUrl->getId());
        $this->assertSame($savedUrl->getName(), $foundUrl->getName());
    }

    public function testFindNonExistingUrlReturnsNull(): void
    {
        $this->assertNull($this->repo->find(999));
    }

    public function testFindByName(): void
    {
        $this->createUrl();

        $foundUrl = $this->repo->findByName($this->exampleUrl);
        $this->assertNotNull($foundUrl);
        $this->assertSame($this->exampleUrl, $foundUrl->getName());

        $this->assertNull($this->repo->findByName('https://nonexistent.com'));
    }

    public function testAllReturnsArrayOfUrls(): void
    {
        $this->createUrl('https://example1.com');
        $this->createUrl('https://example2.com');

        $allUrls = $this->repo->all();
        $this->assertIsArray($allUrls);
        $this->assertCount(2, $allUrls);
        $this->assertSame('https://example1.com', $allUrls[0]->getName());
        $this->assertSame('https://example2.com', $allUrls[1]->getName());
    }
}
