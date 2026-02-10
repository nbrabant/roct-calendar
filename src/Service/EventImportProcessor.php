<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Season;
use App\Enum\EventType;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventImportProcessor
{
    public const FIELD_MAP = [
        'name' => ['label' => 'Nom', 'required' => true],
        'description' => ['label' => 'Description', 'required' => false],
        'type' => ['label' => 'Type', 'required' => true],
        'season' => ['label' => 'Saison', 'required' => true],
        'eventDate' => ['label' => 'Date', 'required' => true],
        'categories' => ['label' => 'Catégories', 'required' => false],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private SeasonRepository $seasonRepository,
        private PlayerCategoryRepository $playerCategoryRepository,
    ) {
    }

    /**
     * @param iterable<int, array<string, string>> $rows
     * @param array<string, string> $mapping column header => field name
     */
    public function process(iterable $rows, array $mapping): ImportResult
    {
        $result = new ImportResult();
        $reversedMapping = array_flip($mapping);

        foreach ($rows as $rowNumber => $row) {
            try {
                $event = $this->buildEvent($row, $reversedMapping);
                $this->em->persist($event);
                $result->successCount++;
            } catch (\Throwable $e) {
                $result->addError($rowNumber, $e->getMessage());
            }
        }

        if ($result->successCount > 0) {
            $this->em->flush();
        }

        return $result;
    }

    /**
     * @param array<string, string> $row
     * @param array<string, string> $reversedMapping field name => column header
     */
    private function buildEvent(array $row, array $reversedMapping): Event
    {
        $event = new Event();

        $name = $this->getFieldValue('name', $row, $reversedMapping);
        if ($name === null || trim($name) === '') {
            throw new \RuntimeException('Le nom est requis.');
        }
        $event->setName(trim($name));

        $description = $this->getFieldValue('description', $row, $reversedMapping);
        if ($description !== null && trim($description) !== '') {
            $event->setDescription(trim($description));
        }

        $typeValue = $this->getFieldValue('type', $row, $reversedMapping);
        if ($typeValue === null || trim($typeValue) === '') {
            throw new \RuntimeException('Le type est requis.');
        }
        $event->setType($this->resolveType(trim($typeValue)));

        $seasonValue = $this->getFieldValue('season', $row, $reversedMapping);
        if ($seasonValue === null || trim($seasonValue) === '') {
            throw new \RuntimeException('La saison est requise.');
        }
        $event->setSeason($this->resolveSeason(trim($seasonValue)));

        $dateValue = $this->getFieldValue('eventDate', $row, $reversedMapping);
        if ($dateValue === null || trim($dateValue) === '') {
            throw new \RuntimeException('La date est requise.');
        }
        $event->setEventDate($this->resolveDate(trim($dateValue)));

        $categoriesValue = $this->getFieldValue('categories', $row, $reversedMapping);
        if ($categoriesValue !== null && trim($categoriesValue) !== '') {
            $this->resolveCategories($event, trim($categoriesValue));
        }

        return $event;
    }

    /**
     * @param array<string, string> $row
     * @param array<string, string> $reversedMapping
     */
    private function getFieldValue(string $field, array $row, array $reversedMapping): ?string
    {
        if (!isset($reversedMapping[$field])) {
            return null;
        }

        $column = $reversedMapping[$field];

        return $row[$column] ?? null;
    }

    private function resolveType(string $value): EventType
    {
        $type = EventType::tryFrom($value);
        if ($type !== null) {
            return $type;
        }

        $valueLower = mb_strtolower($value);
        foreach (EventType::cases() as $case) {
            if (mb_strtolower($case->label()) === $valueLower) {
                return $case;
            }
        }

        throw new \RuntimeException(sprintf('Type inconnu : "%s".', $value));
    }

    private function resolveSeason(string $value): Season
    {
        $seasons = $this->seasonRepository->findAll();
        foreach ($seasons as $season) {
            if ((string) $season === $value) {
                return $season;
            }
        }

        $date = $this->tryParseDate($value);
        if ($date !== null) {
            foreach ($seasons as $season) {
                if ($date >= $season->getStartDate() && $date <= $season->getEndDate()) {
                    return $season;
                }
            }
        }

        throw new \RuntimeException(sprintf('Saison introuvable : "%s".', $value));
    }

    private function resolveDate(string $value): \DateTimeInterface
    {
        if (is_numeric($value)) {
            $unix = ($value - 25569) * 86400;
            $date = new \DateTime();
            $date->setTimestamp((int) $unix);
            $date->setTime(0, 0);

            return $date;
        }

        $date = $this->tryParseDate($value);
        if ($date !== null) {
            return $date;
        }

        throw new \RuntimeException(sprintf('Format de date invalide : "%s".', $value));
    }

    private function tryParseDate(string $value): ?\DateTime
    {
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date !== false && $date->format($format) === $value) {
                $date->setTime(0, 0);

                return $date;
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            $date->setTime(0, 0);

            return $date;
        }

        return null;
    }

    private function resolveCategories(Event $event, string $value): void
    {
        $codes = array_map('trim', explode(',', $value));

        foreach ($codes as $code) {
            if ($code === '') {
                continue;
            }

            $category = $this->playerCategoryRepository->findOneBy(['code' => $code]);
            if ($category === null) {
                throw new \RuntimeException(sprintf('Catégorie introuvable : "%s".', $code));
            }

            $event->addCategory($category);
        }
    }
}
