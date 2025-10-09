<?php

namespace Hexlet\Code;

use Carbon\Carbon;

class Url
{
    private ?int $id = null;
    private ?string $name = null;
    private ?Carbon $createdAt = null;
    private ?int $lastCheckCode = null;
    private ?string $lastCheckDate = null;

    public function __construct(string $name, ?int $id = null, ?Carbon $createdAt = null)
    {
        $this->name = self::normalizeName($name);
        $this->id = $id;
        $this->createdAt = $createdAt ?? Carbon::now();
    }

    public static function normalizeName(string $name): string
    {
        $parsed = parse_url(trim($name));
        if (!isset($parsed['scheme']) || !isset($parsed['host'])) {
            throw new \InvalidArgumentException("Invalid URL");
        }
        return "{$parsed['scheme']}://{$parsed['host']}";
    }

    public static function fromArray(array $urlData): Url
    {
        [$name] = $urlData;
        return new Url($name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLastCheckCode(): ?int
    {
        return $this->lastCheckCode;
    }

    public function getLastCheckDate(): ?string
    {
        return $this->lastCheckDate;
    }

    public function setLastCheckCode(int $lastCheckCode): void
    {
        $this->lastCheckCode = $lastCheckCode;
    }

    public function setLastCheckDate(string $lastCheckDate): void
    {
        $this->lastCheckDate = $lastCheckDate;
    }

    public function exists(): bool
    {
        return !is_null($this->getId());
    }
}
