<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Api;
use App\Database;
use App\TaskRepository;
use App\TaskValidator;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests sur une VRAIE base MySQL : en local via docker compose, en CI via un service GitLab.
 */
final class TaskRepositoryTest extends TestCase
{
    private PDO $pdo;
    private TaskRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = Database::connect();
        // le test crée lui-même la table : il ne dépend pas de l'état de la base
        $this->pdo->exec((string) file_get_contents(__DIR__ . '/../../database/schema.sql'));
        $this->pdo->exec('TRUNCATE TABLE tasks');      // chaque test part d'une table vide
        $this->repo = new TaskRepository($this->pdo);
    }

    public function testCreerPuisRetrouverUneTache(): void
    {
        $id = $this->repo->create('Écrire le pipeline', 'haute');
        $task = $this->repo->find($id);

        $this->assertNotNull($task);
        $this->assertSame('Écrire le pipeline', $task['title']);
        $this->assertSame('haute', $task['priority']);
        $this->assertSame(0, (int) $task['done']);
    }

    public function testListerLesTaches(): void
    {
        $this->repo->create('Première');
        $this->repo->create('Deuxième');

        $this->assertCount(2, $this->repo->all());
    }

    public function testMarquerUneTacheCommeFaite(): void
    {
        $id = $this->repo->create('À terminer');

        $this->assertTrue($this->repo->markDone($id));
        $this->assertSame(1, (int) $this->repo->find($id)['done']);
        $this->assertFalse($this->repo->markDone(9999));
    }

    public function testApiCreationEtValidation(): void
    {
        $api = new Api($this->repo, new TaskValidator());

        [$status, $data] = $api->handle('POST', '/tasks', ['title' => 'Via l\'API']);
        $this->assertSame(201, $status);
        $this->assertSame('Via l\'API', $data['title']);

        [$status, $data] = $api->handle('POST', '/tasks', ['title' => '']);
        $this->assertSame(422, $status);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testSupprimerUneTache(): void
    {
        $api = new Api($this->repo, new TaskValidator());
        $id = $this->repo->create('À supprimer');

        [$status] = $api->handle('DELETE', '/tasks/' . $id);
        $this->assertSame(204, $status);
        $this->assertNull($this->repo->find($id));

        [$status] = $api->handle('DELETE', '/tasks/' . $id);
        $this->assertSame(404, $status);
    }
}
