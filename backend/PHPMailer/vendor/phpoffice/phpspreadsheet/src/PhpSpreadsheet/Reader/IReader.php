<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

interface IReader
{
    public function __construct();
    public function canRead($pFilename);
    public function getReadDataOnly();
    public function setReadDataOnly($pValue);
    public function getReadEmptyCells();
    public function setReadEmptyCells($pValue);
    public function getIncludeCharts();
    public function setIncludeCharts($pValue);
    public function getLoadSheetsOnly();
    public function setLoadSheetsOnly($value);
    public function setLoadAllSheets();
    public function getReadFilter();
    public function setReadFilter(IReadFilter $pValue);
    public function load($pFilename);
}
