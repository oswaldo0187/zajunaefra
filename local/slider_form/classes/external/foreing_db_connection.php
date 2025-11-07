<?php

class foreing_db_connection {
    private static $pdo = null;

    public static function get_connection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = 'localhost';
        $port = '5432';
        $dbname = 'post_office_center';
        $user = 'postgres';
        $password = '12345';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        try {
            self::$pdo = new PDO($dsn, $user, $password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return self::$pdo;
            echo"<script>alert('Conexión a la base de datos externa exitosa');</script>";
        } catch (PDOException $e) {
          echo"<script>alert('Error de conexión a la base de datos externa: " . $e->getMessage() . "');</script>";
            return null;
        }
    }
}
