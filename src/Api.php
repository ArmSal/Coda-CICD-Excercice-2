<?php

declare(strict_types=1);

namespace App;

/**
 * Routeur minimal : associe une méthode HTTP et un chemin à une action.
 * Retourne [code HTTP, données] : facile à tester sans serveur web.
 */
final class Api
{
    public function __construct(private TaskRepository $tasks, private TaskValidator $validator)
    {
    }

    /**
     * @param array<string, mixed> $body
     * @return array{0: int, 1: array<mixed>}
     */
    public function handle(string $method, string $path, array $body = []): array
    {
        if ($method === 'GET' && $path === '/tasks') {
            return [200, $this->tasks->all()];
        }

        if ($method === 'POST' && $path === '/tasks') {
            $errors = $this->validator->validate($body);
            if ($errors !== []) {
                return [422, ['errors' => $errors]];
            }
            $id = $this->tasks->create((string) $body['title'], $body['priority'] ?? 'normale');

            return [201, $this->tasks->find($id) ?? []];
        }

        if (preg_match('#^/tasks/(\d+)$#', $path, $m) && $method === 'GET') {
            $task = $this->tasks->find((int) $m[1]);

            return $task === null ? [404, ['error' => 'Tâche introuvable']] : [200, $task];
        }

        if (preg_match('#^/tasks/(\d+)/done$#', $path, $m) && $method === 'PATCH') {
            return $this->tasks->markDone((int) $m[1])
                ? [200, $this->tasks->find((int) $m[1]) ?? []]
                : [404, ['error' => 'Tâche introuvable']];
        }

        return [404, ['error' => 'Route inconnue']];
    }
}
