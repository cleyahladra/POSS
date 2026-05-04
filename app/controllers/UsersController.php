<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class UsersController extends Controller {
    private UserModel $userModel;

    public function __construct() {
        $this->requireAdmin();
        $this->userModel = new UserModel();
    }

    public function index(): void {
        $users = $this->userModel->findAll('', 'name ASC');
        $this->view('users/index', [
            'pageTitle' => 'Manage Users',
            'users' => $users,
            'flash' => $this->getFlash(),
        ]);
    }

    public function create(): void {
        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $this->sanitize($_POST['role'] ?? 'cashier'),
            ];
            $id = $this->userModel->create($data);
            $this->setFlash($id ? 'success' : 'error', $id ? 'User created!' : 'Failed to create user.');
            $this->redirect('users');
        }
        $this->view('users/form', ['pageTitle' => 'Add User', 'user' => null]);
    }

    public function edit($id): void {
        $user = $this->userModel->findById((int)$id);
        if (!$user) { $this->setFlash('error', 'User not found.'); $this->redirect('users'); }

        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $this->sanitize($_POST['role'] ?? 'cashier'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            $this->userModel->update((int)$id, $data);
            $this->setFlash('success', 'User updated!');
            $this->redirect('users');
        }
        $this->view('users/form', ['pageTitle' => 'Edit User', 'user' => $user]);
    }
}
