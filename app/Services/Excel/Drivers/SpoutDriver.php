<?php

namespace App\Services\Excel\Drivers;

use App\Interfaces\ExcelInterface;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;

class SpoutDriver implements ExcelInterface
{

    public function import(string $filePath, array $options = []): array
    {
        $reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $reader->open($filePath);

        $data = [];
        $startRow = $options['start_row'] ?? 1;
        $currentRow = 1;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($currentRow < $startRow) {
                    $currentRow++;
                    continue;
                }

                $data[] = $row->toArray();
                $currentRow++;
            }
            break; // 只处理第一个sheet
        }

        $reader->close();
        return $data;
    }

    public function export(array $data, string $fileName, array $headers = []): string
    {
        $filePath = storage_path('app/' . $fileName);
        $writer = WriterEntityFactory::createWriterFromFile($filePath);
        $writer->openToFile($filePath);

        // 添加表头
        if (!empty($headers)) {
            $headerRow = WriterEntityFactory::createRowFromArray($headers);
            $writer->addRow($headerRow);
        }

        // 添加数据
        foreach ($data as $row) {
            $dataRow = WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($dataRow);
        }

        $writer->close();
        return $filePath;
    }
}