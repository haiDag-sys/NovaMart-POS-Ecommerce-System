<?php
namespace App\Models;

class AdminModel extends BaseModel
{
    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare('SELECT * FROM admin WHERE ad_taikhoan = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}
?>