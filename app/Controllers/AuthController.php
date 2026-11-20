<?php

class AuthController {
    private Club $clubModel;

    public function __construct(Club $clubModel) {
        $this->clubModel = $clubModel;
    }

    public function showLogin(?string $error = null): void {
        if (isset($_SESSION['club_id'])) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/../Views/login.php';
    }

    public function login(): void {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $club = $this->clubModel->findByUsername($username);

        if (!$club || !password_verify($password, $club['password_hash'])) {
            $this->showLogin('Invalid club username or password.');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['club_id'] = (int) $club['id'];
        $_SESSION['club_name'] = $club['name'];

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: /');
        exit;
    }
}