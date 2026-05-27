<?php
namespace App\Models;

class BaseModel
{
    protected \mysqli $conn;

    public function __construct()
    {
        $this->conn = \db();
    }

    protected function getColumnType(string $table, string $column): ?string
    {
        $allowedTables = [
            'hoa_don',
            'khach_hang',
            'nhan_vien',
            'admin',
            'san_pham',
            'ct_hoa_don',
            'phieu_nhap_kho',
            'ct_phieu_nhap',
            'danh_gia',
            'loai_san_pham',
            'nha_cung_cap',
            'thong_bao'
        ];

        if (!in_array($table, $allowedTables, true)) {
            return null;
        }

        $table = $this->conn->real_escape_string($table);
        $column = $this->conn->real_escape_string($column);

        $sql = "
            SELECT COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '{$table}'
              AND COLUMN_NAME = '{$column}'
            LIMIT 1
        ";

        $result = $this->conn->query($sql);
        if (!$result) {
            return null;
        }

        $row = $result->fetch_assoc();
        return isset($row['COLUMN_TYPE']) ? (string) $row['COLUMN_TYPE'] : null;
    }
}
