<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use DiDom\Document;
use Carbon\Carbon;

class Check
{
    private ?int $id;
    private int $urlId;
    private ?int $statusCode;
    private ?string $h1;
    private ?string $title;
    private ?string $description;
    private Carbon $createdAt;

    public function __construct(
        int $urlId,
        ?int $id = null,
        ?int $statusCode = null,
        ?string $h1 = null,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $createdAt = null
    ) {
        $this->urlId = $urlId;
        $this->id = $id;
        $this->statusCode = $statusCode;
        $this->h1 = $h1;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt ?? Carbon::now();
    }

    public static function fromArray(array $checkData): Check
    {
        [$urlId] = $checkData;
        return new self($urlId);
    }

    public function checkStatus(string $urlName): ?self
    {
        $client = new Client(['timeout' => 6]);

        try {
            $response = $client->request('GET', $urlName);

            $this->setStatusCode($response->getStatusCode());

            $body = (string) $response->getBody();
            $document = new Document($body);

            $this->setH1($document->first('h1')?->text() ?? null);
            $this->setTitle($document->first('title')?->text() ?? null);
            $this->setDescription($document->first('meta[name=description]')?->getAttribute('content'));

        } catch (ConnectException $e) {
            return null;
        } catch (RequestException $e) {
            $this->setStatusCode($e->hasResponse() ? $e->getResponse()->getStatusCode() : null);

            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : null;
            if ($body) {
                $document = new Document($body);
                $this->setH1($document->first('h1')?->text() ?? null);
                $this->setTitle($document->first('title')?->text() ?? null);
                $this->setDescription($document->first('meta[name=description]')?->getAttribute('content'));
            }
        } catch (\Exception $e) {
            return null;
        }

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlId(): int
    {
        return $this->urlId;
    }
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
    public function getH1(): ?string
    {
        return $this->h1;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setUrlId(int $urlId): void
    {
        $this->urlId = $urlId;
    }
    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
    public function setH1(?string $h1): void
    {
        $this->h1 = $h1;
    }
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function exists(): bool
    {
        return $this->id !== null;
    }
}
