<?php

class Event {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllWithClub(): array {
        $sql = "SELECT e.*, c.name AS club_name FROM club_events e JOIN clubs c ON e.club_id = c.id ORDER BY e.event_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getByClubId(int $clubId): array {
        $stmt = $this->db->prepare("SELECT * FROM club_events WHERE club_id = :club_id ORDER BY event_date ASC");
        $stmt->execute(['club_id' => $clubId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM club_events WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch();
        return $event ? : null;
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO club_events (club_id, title, category, description, event_date, venue, poster, registration_link, deadline) VALUES (:club_id, :title, :category, :description, :event_date, :venue, :poster, :registration_link, :deadline)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, int $clubId, array $data): bool {
        $sql = "UPDATE club_events SET title = :title, category = :category, description = :description, event_date = :event_date, venue = :venue, poster = :poster, registration_link = :registration_link, deadline = :deadline WHERE id = :id AND club_id = :club_id";
        $data['id'] = $id;
        $data['club_id'] = $clubId;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id, int $clubId): bool {
        $stmt = $this->db->prepare("DELETE FROM club_events WHERE id = :id AND club_id = :club_id");
        return $stmt->execute(['id' => $id, 'club_id' => $clubId]);
    }
}