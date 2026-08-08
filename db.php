<?php
    $host = "TEST_HOST";
    $db   = "DATABASE_NAME";
    $user = "USERNAME";
    $pass = "PASSWORD";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user,
            $pass
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch(PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

?>