<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login(): void {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        $error = '';
        if ($this->isPost()) {
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                $this->redirect('dashboard');
            } else {
                $error = 'Invalid email or password.';
            }
        }

        $this->view('auth/login', ['error' => $error, 'pageTitle' => 'Login'], 'auth');
    }

    public function logout(): void {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?url=auth/login');
        exit;
    }
}
