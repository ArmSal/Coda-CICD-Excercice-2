<?php

declare(strict_types=1);

namespace App;

/**
 * Règles métier d'une tâche. Aucune dépendance à la base : testable en test unitaire.
 */
final class TaskValidator
{
    public const PRIORITIES = ['basse', 'normale', 'haute'];

    /**
     * @param array<string, mixed> $data
     * @return list<string> la liste des erreurs (vide si la tâche est valide)
     */
    public function validate(array $data): array
    {
        $errors = [];
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            $errors[] = 'Le titre est obligatoire.';
        } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 100) {
            $errors[] = 'Le titre doit contenir entre 3 et 100 caractères.';
        } elseif (ctype_digit($title)) {
            $errors[] = 'Le titre doit contenir des lettres.';
        }

        $priority = $data['priority'] ?? 'normale';
        if (!in_array($priority, self::PRIORITIES, true)) {
            $errors[] = 'La priorité doit être : ' . implode(', ', self::PRIORITIES) . '.';
        }

        return $errors;
    }
}
