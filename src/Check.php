<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use DiDom\Document;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @param string $urlName
     * @return ?self
     */
    public function checkStatus(string $urlName): ?self
    {
        $client = new Client(['timeout' => 6]);

        $status = null;
        $body = null;

        try {
            /** @var ResponseInterface $response */
            $response = $client->request('GET', $urlName);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (ConnectException $e) {
            return null;
        } catch (RequestException $e) {
            $resp = $e->getResponse();
            if ($resp instanceof ResponseInterface) {
                $status = $resp->getStatusCode();
                $body = (string) $resp->getBody();
            } else {
                $status = null;
                $body = null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        $this->setStatusCode($status);

        if ($body !== null && $body !== '') {
            $document = new Document($body);

            $h1Text = null;
            $h1Element = $document->first('h1');
            if ($h1Element instanceof \DiDom\Element) {
                $h1Text = $h1Element->text();
            } elseif ($h1Element instanceof \DOMElement) {
                $h1Text = $h1Element->nodeValue;
            }
            $this->setH1($h1Text !== null ? trim($h1Text) : null);

            $titleText = null;
            $titleElement = $document->first('title');
            if ($titleElement instanceof \DiDom\Element) {
                $titleText = $titleElement->text();
            } elseif ($titleElement instanceof \DOMElement) {
                $titleText = $titleElement->nodeValue;
            }
            $this->setTitle($titleText !== null ? trim($titleText) : null);

            $meta = $document->first('meta[name=description]');
            $description = null;
            if ($meta instanceof \DiDom\Element) {
                $description = $meta->getAttribute('content') ?: null;
            } elseif ($meta instanceof \DOMElement) {
                $description = $meta->getAttribute('content') ?: null;
            }
            $this->setDescription($description !== null ? trim($description) : null);
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
