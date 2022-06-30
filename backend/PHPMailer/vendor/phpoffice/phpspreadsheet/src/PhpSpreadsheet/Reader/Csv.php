<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Csv extends BaseReader
{
    private $inputEncoding = 'UTF-8';
    private $delimiter;
    private $enclosure = '"';
    private $sheetIndex = 0;
    private $contiguous = false;
    private $escapeCharacter = '\\';
    public function __construct()
    {
        parent::__construct();
    }
    public function setInputEncoding($pValue)
    {
        $this->inputEncoding = $pValue;

        return $this;
    }
    public function getInputEncoding()
    {
        return $this->inputEncoding;
    }
    protected function skipBOM(): void
    {
        rewind($this->fileHandle);

        switch ($this->inputEncoding) {
            case 'UTF-8':
                fgets($this->fileHandle, 4) == "\xEF\xBB\xBF" ?
                    fseek($this->fileHandle, 3) : fseek($this->fileHandle, 0);

                break;
        }
    }
    protected function checkSeparator(): void
    {
        $line = fgets($this->fileHandle);
        if ($line === false) {
            return;
        }

        if ((strlen(trim($line, "\r\n")) == 5) && (stripos($line, 'sep=') === 0)) {
            $this->delimiter = substr($line, 4, 1);

            return;
        }

        $this->skipBOM();
    }
    protected function inferSeparator(): void
    {
        if ($this->delimiter !== null) {
            return;
        }

        $potentialDelimiters = [',', ';', "\t", '|', ':', ' ', '~'];
        $counts = [];
        foreach ($potentialDelimiters as $delimiter) {
            $counts[$delimiter] = [];
        }

        // Count how many times each of the potential delimiters appears in each line
        $numberLines = 0;
        while (($line = $this->getNextLine()) !== false && (++$numberLines < 1000)) {
            $countLine = [];
            for ($i = strlen($line) - 1; $i >= 0; --$i) {
                $char = $line[$i];
                if (isset($counts[$char])) {
                    if (!isset($countLine[$char])) {
                        $countLine[$char] = 0;
                    }
                    ++$countLine[$char];
                }
            }
            foreach ($potentialDelimiters as $delimiter) {
                $counts[$delimiter][] = $countLine[$delimiter]
                    ?? 0;
            }
        }

        // If number of lines is 0, nothing to infer : fall back to the default
        if ($numberLines === 0) {
            $this->delimiter = reset($potentialDelimiters);
            $this->skipBOM();

            return;
        }

        // Calculate the mean square deviations for each delimiter (ignoring delimiters that haven't been found consistently)
        $meanSquareDeviations = [];
        $middleIdx = floor(($numberLines - 1) / 2);

        foreach ($potentialDelimiters as $delimiter) {
            $series = $counts[$delimiter];
            sort($series);

            $median = ($numberLines % 2)
                ? $series[$middleIdx]
                : ($series[$middleIdx] + $series[$middleIdx + 1]) / 2;

            if ($median === 0) {
                continue;
            }

            $meanSquareDeviations[$delimiter] = array_reduce(
                $series,
                function ($sum, $value) use ($median) {
                    return $sum + ($value - $median) ** 2;
                }
            ) / count($series);
        }

        // ... and pick the delimiter with the smallest mean square deviation (in case of ties, the order in potentialDelimiters is respected)
        $min = INF;
        foreach ($potentialDelimiters as $delimiter) {
            if (!isset($meanSquareDeviations[$delimiter])) {
                continue;
            }

            if ($meanSquareDeviations[$delimiter] < $min) {
                $min = $meanSquareDeviations[$delimiter];
                $this->delimiter = $delimiter;
            }
        }

        // If no delimiter could be detected, fall back to the default
        if ($this->delimiter === null) {
            $this->delimiter = reset($potentialDelimiters);
        }

        $this->skipBOM();
    }
    private function getNextLine()
    {
        $line = '';
        $enclosure = '(?<!' . preg_quote($this->escapeCharacter, '/') . ')' . preg_quote($this->enclosure, '/');

        do {
            // Get the next line in the file
            $newLine = fgets($this->fileHandle);

            // Return false if there is no next line
            if ($newLine === false) {
                return false;
            }

            // Add the new line to the line passed in
            $line = $line . $newLine;

            // Drop everything that is enclosed to avoid counting false positives in enclosures
            $line = preg_replace('/(' . $enclosure . '.*' . $enclosure . ')/Us', '', $line);

            // See if we have any enclosures left in the line
            // if we still have an enclosure then we need to read the next line as well
        } while (preg_match('/(' . $enclosure . ')/', $line) > 0);

        return $line;
    }
    public function listWorksheetInfo($pFilename)
    {
        // Open file
        $this->openFileOrMemory($pFilename);
        $fileHandle = $this->fileHandle;

        // Skip BOM, if any
        $this->skipBOM();
        $this->checkSeparator();
        $this->inferSeparator();

        $worksheetInfo = [];
        $worksheetInfo[0]['worksheetName'] = 'Worksheet';
        $worksheetInfo[0]['lastColumnLetter'] = 'A';
        $worksheetInfo[0]['lastColumnIndex'] = 0;
        $worksheetInfo[0]['totalRows'] = 0;
        $worksheetInfo[0]['totalColumns'] = 0;

        // Loop through each line of the file in turn
        while (($rowData = fgetcsv($fileHandle, 0, $this->delimiter, $this->enclosure, $this->escapeCharacter)) !== false) {
            ++$worksheetInfo[0]['totalRows'];
            $worksheetInfo[0]['lastColumnIndex'] = max($worksheetInfo[0]['lastColumnIndex'], count($rowData) - 1);
        }

        $worksheetInfo[0]['lastColumnLetter'] = Coordinate::stringFromColumnIndex($worksheetInfo[0]['lastColumnIndex'] + 1);
        $worksheetInfo[0]['totalColumns'] = $worksheetInfo[0]['lastColumnIndex'] + 1;

        // Close file
        fclose($fileHandle);

        return $worksheetInfo;
    }
    public function load($pFilename)
    {
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Load into this instance
        return $this->loadIntoExisting($pFilename, $spreadsheet);
    }

    private function openFileOrMemory($pFilename): void
    {
        // Open file
        $fhandle = $this->canRead($pFilename);
        if (!$fhandle) {
            throw new Exception($pFilename . ' is an Invalid Spreadsheet file.');
        }
        $this->openFile($pFilename);
        if ($this->inputEncoding !== 'UTF-8') {
            fclose($this->fileHandle);
            $entireFile = file_get_contents($pFilename);
            $this->fileHandle = fopen('php://memory', 'r+b');
            $data = StringHelper::convertEncoding($entireFile, 'UTF-8', $this->inputEncoding);
            fwrite($this->fileHandle, $data);
            rewind($this->fileHandle);
        }
    }
    public function loadIntoExisting($pFilename, Spreadsheet $spreadsheet)
    {
        $lineEnding = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', true);

        // Open file
        $this->openFileOrMemory($pFilename);
        $fileHandle = $this->fileHandle;

        // Skip BOM, if any
        $this->skipBOM();
        $this->checkSeparator();
        $this->inferSeparator();

        // Create new PhpSpreadsheet object
        while ($spreadsheet->getSheetCount() <= $this->sheetIndex) {
            $spreadsheet->createSheet();
        }
        $sheet = $spreadsheet->setActiveSheetIndex($this->sheetIndex);

        // Set our starting row based on whether we're in contiguous mode or not
        $currentRow = 1;
        $outRow = 0;

        // Loop through each line of the file in turn
        while (($rowData = fgetcsv($fileHandle, 0, $this->delimiter, $this->enclosure, $this->escapeCharacter)) !== false) {
            $noOutputYet = true;
            $columnLetter = 'A';
            foreach ($rowData as $rowDatum) {
                if ($rowDatum != '' && $this->readFilter->readCell($columnLetter, $currentRow)) {
                    if ($this->contiguous) {
                        if ($noOutputYet) {
                            $noOutputYet = false;
                            ++$outRow;
                        }
                    } else {
                        $outRow = $currentRow;
                    }
                    // Set cell value
                    $sheet->getCell($columnLetter . $outRow)->setValue($rowDatum);
                }
                ++$columnLetter;
            }
            ++$currentRow;
        }

        // Close file
        fclose($fileHandle);

        ini_set('auto_detect_line_endings', $lineEnding);

        // Return
        return $spreadsheet;
    }
    public function getDelimiter()
    {
        return $this->delimiter;
    }
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }
    public function getEnclosure()
    {
        return $this->enclosure;
    }
    public function setEnclosure($enclosure)
    {
        if ($enclosure == '') {
            $enclosure = '"';
        }
        $this->enclosure = $enclosure;

        return $this;
    }
    public function getSheetIndex()
    {
        return $this->sheetIndex;
    }
    public function setSheetIndex($pValue)
    {
        $this->sheetIndex = $pValue;

        return $this;
    }
    public function setContiguous($contiguous)
    {
        $this->contiguous = (bool) $contiguous;

        return $this;
    }
    public function getContiguous()
    {
        return $this->contiguous;
    }
    public function setEscapeCharacter($escapeCharacter)
    {
        $this->escapeCharacter = $escapeCharacter;

        return $this;
    }
    public function getEscapeCharacter()
    {
        return $this->escapeCharacter;
    }
    public function canRead($pFilename)
    {
        // Check if file exists
        try {
            $this->openFile($pFilename);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        fclose($this->fileHandle);

        // Trust file extension if any
        $extension = strtolower(pathinfo($pFilename, PATHINFO_EXTENSION));
        if (in_array($extension, ['csv', 'tsv'])) {
            return true;
        }

        // Attempt to guess mimetype
        $type = mime_content_type($pFilename);
        $supportedTypes = [
            'text/csv',
            'text/plain',
            'inode/x-empty',
        ];

        return in_array($type, $supportedTypes, true);
    }
}
