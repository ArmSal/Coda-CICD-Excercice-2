<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Fabrique la connexion PDO à partir des variables d'environnement.
 * Les mêmes variables servent en local, dans Docker et dans GitLab CI.
 */
final class Database
{
    public static function connect(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'taches';
        $user = getenv('DB_USER') ?: 'app';
        $password = getenv('DB_PASSWORD') ?: 'app';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
