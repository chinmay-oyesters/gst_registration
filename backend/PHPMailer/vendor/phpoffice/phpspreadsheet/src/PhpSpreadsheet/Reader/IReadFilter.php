<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

interface IReadFilter
{
   
    public function readCell($column, $row, $worksheetName = '');
}
