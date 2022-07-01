<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Reader\Security\XmlScanner;
use PhpOffice\PhpSpreadsheet\Shared\File;

abstract class BaseReader implements IReader
{
    protected $readDataOnly = false;
    protected $readEmptyCells = true;
    protected $includeCharts = false;
    protected $loadSheetsOnly;
    protected $readFilter;
    protected $fileHandle;
    protected $securityScanner;
    public function __construct()
    {
        $this->readFilter = new DefaultReadFilter();
    }

    public function getReadDataOnly()
    {
        return $this->readDataOnly;
    }

    public function setReadDataOnly($pValue)
    {
        $this->readDataOnly = (bool) $pValue;

        return $this;
    }

    public function getReadEmptyCells()
    {
        return $this->readEmptyCells;
    }

    public function setReadEmptyCells($pValue)
    {
        $this->readEmptyCells = (bool) $pValue;

        return $this;
    }

    public function getIncludeCharts()
    {
        return $this->includeCharts;
    }

    public function setIncludeCharts($pValue)
    {
        $this->includeCharts = (bool) $pValue;

        return $this;
    }

    public function getLoadSheetsOnly()
    {
        return $this->loadSheetsOnly;
    }

    public function setLoadSheetsOnly($value)
    {
        if ($value === null) {
            return $this->setLoadAllSheets();
        }

        $this->loadSheetsOnly = is_array($value) ? $value : [$value];

        return $this;
    }

    public function setLoadAllSheets()
    {
        $this->loadSheetsOnly = null;

        return $this;
    }

    public function getReadFilter()
    {
        return $this->readFilter;
    }

    public function setReadFilter(IReadFilter $pValue)
    {
        $this->readFilter = $pValue;

        return $this;
    }

    public function getSecurityScanner()
    {
        return $this->securityScanner;
    }
    protected function openFile($pFilename): void
    {
        if ($pFilename) {
            File::assertFile($pFilename);

            // Open file
            $fileHandle = fopen($pFilename, 'rb');
        } else {
            $fileHandle = false;
        }
        if ($fileHandle !== false) {
            $this->fileHandle = $fileHandle;
        } else {
            throw new ReaderException('Could not open file ' . $pFilename . ' for reading.');
        }
    }
}
