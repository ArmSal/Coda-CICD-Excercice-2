<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Accès à la table tasks. Toutes les requêtes sont préparées (pas d'injection SQL).
 */
final class TaskRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM tasks ORDER BY id')->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();

        return $task === false ? null : $task;
    }

    public function create(string $title, string $priority = 'normale'): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (title, priority) VALUES (:title, :priority)');
        $stmt->execute(['title' => trim($title), 'priority' => $priority]);

        return (int) $this->pdo->lastInsertId();
    }

    public function markDone(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tasks SET done = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
