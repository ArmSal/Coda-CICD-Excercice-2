<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\TaskValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskValidatorTest extends TestCase
{
    private TaskValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TaskValidator();   // exécuté avant CHAQUE test
    }

    public function testUneTacheValideNaAucuneErreur(): void
    {
        $this->assertSame([], $this->validator->validate(['title' => 'Réviser GitLab CI', 'priority' => 'haute']));
    }

    public function testLaPrioriteParDefautEstAcceptee(): void
    {
        $this->assertSame([], $this->validator->validate(['title' => 'Sans priorité']));
    }

    public function testLeTitreEstObligatoire(): void
    {
        $this->assertContains('Le titre est requis.', $this->validator->validate(['title' => '   ']));
    }

    #[DataProvider('titresInvalides')]
    public function testLaLongueurDuTitreEstControlee(string $title): void
    {
        $this->assertCount(1, $this->validator->validate(['title' => $title]));
    }

    /** @return array<string, array{string}> */
    public static function titresInvalides(): array
    {
        return [
            'trop court' => ['ab'],
            'trop long' => [str_repeat('x', 101)],
        ];
    }

    public function testUnePrioriteInconnueEstRefusee(): void
    {
        $errors = $this->validator->validate(['title' => 'Tâche', 'priority' => 'urgente']);
        $this->assertSame(['La priorité doit être : basse, normale, haute.'], $errors);
    }

    public function testUnTitreFaitUniquementDeChiffresEstRefuse(): void
    {
        $errors = $this->validator->validate(['title' => '1234']);
        $this->assertSame(['Le titre doit contenir des lettres.'], $errors);
    }
}
