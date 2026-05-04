<?php
class Controller {
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            die('View not found: ' . $view);
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $url): void {
        header('Location: ' . BASE_URL . '/index.php?url=' . $url);
        exit;
    }

    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function sanitize(string $value): string {
        return htmlspecialchars(strip_tags(trim($value)));
    }

    protected function currentUser(): array {
        return $_SESSION['user'] ?? [];
    }

    protected function isAdmin(): bool {
        return ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    protected function requireAdmin(): void {
        if (!$this->isAdmin()) {
            $this->redirect('dashboard');
        }
    }

    protected function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): array {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}
