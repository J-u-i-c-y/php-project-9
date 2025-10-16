<?php

namespace Hexlet\Code\Entities;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use DiDom\Document;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use DiDom\Element;

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

    public static function fromArray(array $checkData): self
    {
        [$urlId] = $checkData;
        return new self($urlId);
    }

    public function checkStatus(string $urlName): ?self
    {
        $client = new Client(['timeout' => 6]);

        try {
            $response = $client->request('GET', $urlName);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (RequestException $e) {
            $resp = $e->getResponse();
            if ($resp instanceof ResponseInterface) {
                $status = $resp->getStatusCode();
                $body = (string) $resp->getBody();
            } else {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $this->setStatusCode($status);

        if (empty($body)) {
            return $this;
        }

        $document = new Document($body);
        $this->setH1($this->getTextSafe($document->first('h1')));
        $this->setTitle($this->getTextSafe($document->first('title')));
        $this->setDescription($this->getMetaSafe($document->first('meta[name=description]')));

        return $this;
    }

    private function getTextSafe(Element|\DOMElement|null $element): ?string
    {
        if ($element instanceof Element) {
            return trim($element->text() ?? '');
        }
        if ($element instanceof \DOMElement) {
            return trim($element->nodeValue ?? '');
        }
        return null;
    }

    private function getMetaSafe(Element|\DOMElement|null $element): ?string
    {
        if ($element instanceof Element || $element instanceof \DOMElement) {
            return trim($element->getAttribute('content') ?? '');
        }
        return null;
    }
}
