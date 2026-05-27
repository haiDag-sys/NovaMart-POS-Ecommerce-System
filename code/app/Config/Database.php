<?php
namespace App\Config;

use mysqli;

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        $servername = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'taphoa_db';

        self::$connection = new mysqli($servername, $username, $password, $dbname);

        if (self::$connection->connect_error) {
            die('Kết nối thất bại: ' . self::$connection->connect_error);
        }

        self::$connection->set_charset('utf8mb4');
        return self::$connection;
    }
}
?>