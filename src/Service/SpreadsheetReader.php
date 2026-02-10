<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetReader
{
    /** @return string[] */
    public function readHeaders(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [];

        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $value = $cell->getValue();
                if ($value !== null && $value !== '') {
                    $headers[] = (string) $value;
                }
            }
        }

        return $headers;
    }

    /** @return array<int, array<string, string>> */
    public function readPreviewRows(string $filePath, int $maxRows = 5): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headers = $this->readHeaders($filePath);
        $rows = [];
        $count = 0;

        foreach ($sheet->getRowIterator(2) as $row) {
            if ($count >= $maxRows) {
                break;
            }

            $rowData = [];
            $colIndex = 0;
            foreach ($row->getCellIterator() as $cell) {
                if ($colIndex < count($headers)) {
                    $rowData[$headers[$colIndex]] = (string) $cell->getValue();
                }
                $colIndex++;
            }

            $rows[] = $rowData;
            $count++;
        }

        return $rows;
    }

    /** @return \Generator<int, array<string, string>> */
    public function readAllRows(string $filePath): \Generator
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headers = $this->readHeaders($filePath);

        foreach ($sheet->getRowIterator(2) as $row) {
            $rowData = [];
            $colIndex = 0;
            foreach ($row->getCellIterator() as $cell) {
                if ($colIndex < count($headers)) {
                    $rowData[$headers[$colIndex]] = (string) $cell->getValue();
                }
                $colIndex++;
            }

            yield $row->getRowIndex() => $rowData;
        }
    }
}
