<?php
namespace App\Models;

class EmployeeModel extends BaseModel
{
    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare('SELECT * FROM nhan_vien WHERE nv_taikhoan = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}
?>