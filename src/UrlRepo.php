<?php

namespace Hexlet\Code;

use Carbon\Carbon;

class UrlRepo
{
    private \PDO $conn;
    private string $nameParam = ':name';

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getEntities(): array
    {
        $sqlUrls = "SELECT id, name, created_at FROM urls ORDER BY id";
        $stmt = $this->conn->query($sqlUrls);
        $urls = [];

        while ($row = $stmt->fetch()) {
            $url = Url::fromArray([$row['name']]);
            $url->setId($row['id']);
            $url->setCreatedAt(new Carbon($row['created_at']));
            $urls[$row['id']] = $url;
        }

        if (empty($urls)) {
            return [];
        }

        $sqlChecks = "
            SELECT DISTINCT ON (url_id)
                url_id, status_code, created_at
            FROM url_checks
            ORDER BY url_id, created_at DESC
        ";
        $stmtChecks = $this->conn->query($sqlChecks);

        while ($row = $stmtChecks->fetch()) {
            $urlId = $row['url_id'];
            if (isset($urls[$urlId])) {
                $urls[$urlId]->setLastCheckCode($row['status_code']);
                $urls[$urlId]->setLastCheckDate($row['created_at']);
            }
        }

        return array_values($urls);
    }

    public function find(int $id): ?Url
    {
        $sql = "SELECT * FROM urls WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            $url = Url::fromArray([$row['name']]);
            $url->setId($row['id']);
            $url->setCreatedAt(new Carbon($row['created_at']));
            return $url;
        }

        return null;
    }

    public function save(Url $url): void
    {
        if ($url->exists()) {
            $this->update($url);
        } else {
            $this->create($url);
        }
    }

    private function update(Url $url): void
    {
        $sql = "UPDATE urls SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $id = $url->getId();
        $name = $url->getName();
        $stmt->bindParam($this->nameParam, $name);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    private function create(Url $url): void
    {
        $date = Carbon::now();
        $dateFormated = $date->format('Y-m-d H:i:s');

        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
        $stmt = $this->conn->prepare($sql);
        $name = $url->getName();
        $stmt->bindParam($this->nameParam, $name);
        $stmt->bindParam(':created_at', $dateFormated);
        $stmt->execute();
        $id = (int) $this->conn->lastInsertId();
        $url->setId($id);
    }

    public function findByName(string $name): ?Url
    {
        $stmt = $this->conn->prepare("SELECT * FROM urls WHERE name = :name");
        $stmt->execute(['name' => Url::normalizeName($name)]);
        $row = $stmt->fetch();
        if ($row) {
            return new Url(
                $row['name'],
                $row['id'],
                new Carbon($row['created_at'])
            );
        }
        return null;
    }

    public function all(): array
    {
        $stmt = $this->conn->query("SELECT * FROM urls");
        $urls = [];
        while ($row = $stmt->fetch()) {
            $urls[] = new Url(
                $row['name'],
                $row['id'],
                new Carbon($row['created_at'])
            );
        }
        return $urls;
    }

    public function isNameExists(Url $url): bool
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $this->conn->prepare($sql);
        $name = $url->getName();
        $stmt->bindParam($this->nameParam, $name);
        $stmt->execute();
        $urls = $stmt->fetch();

        if ($urls) {
            $url->setId($urls['id']);
            return true;
        }

        return false;
    }
}
