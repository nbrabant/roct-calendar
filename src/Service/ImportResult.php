<?php

namespace App\Service;

class ImportResult
{
    public int $successCount = 0;
    public int $deletedCount = 0;

    /** @var array<int, string> */
    private array $errors = [];

    public function addError(int $rowNumber, string $message): void
    {
        $this->errors[$rowNumber] = $message;
    }

    /** @return array<int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }
}
