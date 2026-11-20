<?php

class EventController {
    private Event $eventModel;
    private Club $clubModel;

    public function __construct(Event $eventModel, Club $clubModel) {
        $this->eventModel = $eventModel;
        $this->clubModel = $clubModel;
    }

    private function requireAuth(): int {
        if (!isset($_SESSION['club_id'])) {
            header('Location: /login');
            exit;
        }
        return (int) $_SESSION['club_id'];
    }

    private function handlePosterUpload(?array $file, ?string $existingPoster = null): ?string {
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $existingPoster;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed. error code: ' . $file['error']);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Poster image must be smaller than 5MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png'  => 'png', 'image/webp' => 'webp'];

        if (!isset($allowedTypes[$mimeType])) {
            throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
        }

        $uploadDir = __DIR__ . '/../../public/uploads/posters/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('poster_', true) . '.' . $allowedTypes[$mimeType];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            throw new RuntimeException('Failed to save uploaded poster');
        }

        if ($existingPoster) {
            $oldPath = __DIR__ . '/../../public' . $existingPoster;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        return '/uploads/posters/' . $filename;
    }

    public function publicFeed(): void {
        $clubs = $this->clubModel->getAll();
        require __DIR__ . '/../Views/feed.php';
    }

    public function apiEvents(): void {
        header('Content-Type: application/json');
        echo json_encode($this->eventModel->getAllWithClub());
    }

    public function dashboard(): void {
        $clubId = $this->requireAuth();
        $events = $this->eventModel->getByClubId($clubId);
        $editingEvent = null;

        if (isset($_GET['edit'])) {
            $candidate = $this->eventModel->findById((int) $_GET['edit']);
            if ($candidate && (int) $candidate['club_id'] === $clubId) {
                $editingEvent = $candidate;
            }
        }
        require __DIR__ . '/../Views/dashboard.php';
    }

    public function store(): void {
        $clubId = $this->requireAuth();
        try {
            $posterPath = $this->handlePosterUpload($_FILES['poster'] ?? null);
            $this->eventModel->create([
                'club_id' => $clubId,
                'title' => trim($_POST['title']),
                'category' => trim($_POST['category']),
                'description' => trim($_POST['description']),
                'event_date' => $_POST['event_date'],
                'venue' => trim($_POST['venue']),
                'poster' => $posterPath,
                'registration_link' => trim($_POST['registration_link']),
                'deadline' => $_POST['deadline'],
            ]);

            header('Location: /dashboard?status=created');
        } catch (Throwable $e) {
            header('Location: /dashboard?error=' . urlencode($e->getMessage()));
        }
        exit;
    }

    public function update(): void {
        $clubId = $this->requireAuth();
        $eventId = (int) ($_POST['id'] ?? 0);
        $existing = $this->eventModel->findById($eventId);

        if (!$existing || (int) $existing['club_id'] !== $clubId) {
            header('Location: /dashboard?error=' . urlencode('Unauthorised access.'));
            exit;
        }

        try {
            $posterPath = $this->handlePosterUpload($_FILES['poster'] ?? null, $existing['poster']);

            $this->eventModel->update($eventId, $clubId, [
                'title' => trim($_POST['title']),
                'category' => trim($_POST['category']),
                'description' => trim($_POST['description']),
                'event_date' => $_POST['event_date'],
                'venue' => trim($_POST['venue']),
                'poster' => $posterPath,
                'registration_link' => trim($_POST['registration_link']),
                'deadline' => $_POST['deadline'],
            ]);
            header('Location: /dashboard?status=updated');
        } catch (Throwable $e) {
            header('Location: /dashboard?error=' . urlencode($e->getMessage()));
        }
        exit;
    }

    public function delete(): void {
        $clubId = $this->requireAuth();
        $eventId = (int) ($_POST['id'] ?? 0);
        $existing = $this->eventModel->findById($eventId);

        if ($existing && (int) $existing['club_id'] === $clubId) {
            if (!empty($existing['poster'])) {
                $filePath = __DIR__ . '/../../public' . $existing['poster'];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
            $this->eventModel->delete($eventId, $clubId);
        }

        header('Location: /dashboard?status=deleted');
        exit;
    }
}