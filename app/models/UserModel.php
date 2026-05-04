<?php
require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    protected string $table = 'users';

    public function findByEmail(string $email): array|null {
        $email = $this->escape($email);
        $result = $this->db->query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
        if (!$result) return null;
        return $result->fetch_assoc() ?: null;
    }

    public function authenticate(string $email, string $password): array|false {
        $user = $this->findByEmail($email);
        if (!$user || !$user['is_active']) return false;
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function create(array $data): int|false {
        $name = $this->escape($data['name']);
        $email = $this->escape($data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $this->escape($data['role']);

        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        $this->db->query($sql);
        return $this->db->lastInsertId() ?: false;
    }

    public function update(int $id, array $data): bool {
        $id = (int)$id;
        $name = $this->escape($data['name']);
        $email = $this->escape($data['email']);
        $role = $this->escape($data['role']);
        $is_active = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE users SET name='$name', email='$email', role='$role', is_active=$is_active WHERE id=$id";
        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$name', email='$email', password='$password', role='$role', is_active=$is_active WHERE id=$id";
        }
        $this->db->query($sql);
        return $this->db->affectedRows() > 0;
    }
}
