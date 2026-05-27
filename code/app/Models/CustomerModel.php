<?php
namespace App\Models;

class CustomerModel extends BaseModel
{
    public function getById($khId)
    {
        $stmt = $this->conn->prepare('SELECT * FROM khach_hang WHERE kh_id = ? LIMIT 1');
        $stmt->bind_param('i', $khId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }


    public function updateProfile($khId, $fullName, $address)
    {
        $stmt = $this->conn->prepare('UPDATE khach_hang SET kh_hoten = ?, kh_diachi = ? WHERE kh_id = ?');
        $stmt->bind_param('ssi', $fullName, $address, $khId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateAvatar($khId, $avatarPath)
    {
        $stmt = $this->conn->prepare('UPDATE khach_hang SET kh_avatar = ? WHERE kh_id = ?');
        $stmt->bind_param('si', $avatarPath, $khId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function verifyPassword($khId, $plainPassword)
    {
        $stmt = $this->conn->prepare('SELECT kh_matkhau FROM khach_hang WHERE kh_id = ? LIMIT 1');
        $stmt->bind_param('i', $khId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }

        return password_verify($plainPassword, $row['kh_matkhau']);
    }

    public function updatePassword($khId, $plainPassword)
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('UPDATE khach_hang SET kh_matkhau = ? WHERE kh_id = ?');
        $stmt->bind_param('si', $hash, $khId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
?>