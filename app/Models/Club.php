<?php

class Club {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM clubs WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $club = $stmt->fetch();
        return $club ? : null;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, name FROM clubs ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}