<?php

namespace PhpOffice\PhpSpreadsheet;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Iterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Spreadsheet
{
    // Allowable values for workbook window visilbity
    const VISIBILITY_VISIBLE = 'visible';
    const VISIBILITY_HIDDEN = 'hidden';
    const VISIBILITY_VERY_HIDDEN = 'veryHidden';

    private static $workbookViewVisibilityValues = [
        self::VISIBILITY_VISIBLE,
        self::VISIBILITY_HIDDEN,
        self::VISIBILITY_VERY_HIDDEN,
    ];
    private $properties;
    private $security;
    private $workSheetCollection = [];
    private $calculationEngine;
    private $activeSheetIndex = 0;
    private $namedRanges = [];
    private $cellXfSupervisor;
    private $cellXfCollection = [];
    private $cellStyleXfCollection = [];
    private $hasMacros = false;
    private $macrosCode;
    private $macrosCertificate;
    private $ribbonXMLData;
    private $ribbonBinObjects;
    private $unparsedLoadedData = [];
    private $showHorizontalScroll = true;
    private $showVerticalScroll = true;
    private $showSheetTabs = true;
    private $minimized = false;
    private $autoFilterDateGrouping = true;
    private $firstSheetIndex = 0;
    private $visibility = self::VISIBILITY_VISIBLE;
    private $tabRatio = 600;
    public function hasMacros()
    {
        return $this->hasMacros;
    }
    public function setHasMacros($hasMacros): void
    {
        $this->hasMacros = (bool) $hasMacros;
    }
    public function setMacrosCode($macroCode): void
    {
        $this->macrosCode = $macroCode;
        $this->setHasMacros($macroCode !== null);
    }
    public function getMacrosCode()
    {
        return $this->macrosCode;
    }
    public function setMacrosCertificate($certificate): void
    {
        $this->macrosCertificate = $certificate;
    }
    public function hasMacrosCertificate()
    {
        return $this->macrosCertificate !== null;
    }
    public function getMacrosCertificate()
    {
        return $this->macrosCertificate;
    }
    public function discardMacros(): void
    {
        $this->hasMacros = false;
        $this->macrosCode = null;
        $this->macrosCertificate = null;
    }
    public function setRibbonXMLData($target, $xmlData): void
    {
        if ($target !== null && $xmlData !== null) {
            $this->ribbonXMLData = ['target' => $target, 'data' => $xmlData];
        } else {
            $this->ribbonXMLData = null;
        }
    }
    public function getRibbonXMLData($what = 'all') //we need some constants here...
    {
        $returnData = null;
        $what = strtolower($what);
        switch ($what) {
            case 'all':
                $returnData = $this->ribbonXMLData;

                break;
            case 'target':
            case 'data':
                if (is_array($this->ribbonXMLData) && isset($this->ribbonXMLData[$what])) {
                    $returnData = $this->ribbonXMLData[$what];
                }

                break;
        }

        return $returnData;
    }
    public function setRibbonBinObjects($BinObjectsNames, $BinObjectsData): void
    {
        if ($BinObjectsNames !== null && $BinObjectsData !== null) {
            $this->ribbonBinObjects = ['names' => $BinObjectsNames, 'data' => $BinObjectsData];
        } else {
            $this->ribbonBinObjects = null;
        }
    }
    public function getUnparsedLoadedData()
    {
        return $this->unparsedLoadedData;
    }
    public function setUnparsedLoadedData(array $unparsedLoadedData): void
    {
        $this->unparsedLoadedData = $unparsedLoadedData;
    }
    private function getExtensionOnly($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }
    public function getRibbonBinObjects($what = 'all')
    {
        $ReturnData = null;
        $what = strtolower($what);
        switch ($what) {
            case 'all':
                return $this->ribbonBinObjects;

                break;
            case 'names':
            case 'data':
                if (is_array($this->ribbonBinObjects) && isset($this->ribbonBinObjects[$what])) {
                    $ReturnData = $this->ribbonBinObjects[$what];
                }

                break;
            case 'types':
                if (is_array($this->ribbonBinObjects) &&
                    isset($this->ribbonBinObjects['data']) && is_array($this->ribbonBinObjects['data'])) {
                    $tmpTypes = array_keys($this->ribbonBinObjects['data']);
                    $ReturnData = array_unique(array_map([$this, 'getExtensionOnly'], $tmpTypes));
                } else {
                    $ReturnData = []; // the caller want an array... not null if empty
                }

                break;
        }

        return $ReturnData;
    }
    public function hasRibbon()
    {
        return $this->ribbonXMLData !== null;
    }
    public function hasRibbonBinObjects()
    {
        return $this->ribbonBinObjects !== null;
    }
    public function sheetCodeNameExists($pSheetCodeName)
    {
        return $this->getSheetByCodeName($pSheetCodeName) !== null;
    }
    public function getSheetByCodeName($pName)
    {
        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            if ($this->workSheetCollection[$i]->getCodeName() == $pName) {
                return $this->workSheetCollection[$i];
            }
        }

        return null;
    }
    public function __construct()
    {
        $this->uniqueID = uniqid('', true);
        $this->calculationEngine = new Calculation($this);

        // Initialise worksheet collection and add one worksheet
        $this->workSheetCollection = [];
        $this->workSheetCollection[] = new Worksheet($this);
        $this->activeSheetIndex = 0;

        // Create document properties
        $this->properties = new Document\Properties();

        // Create document security
        $this->security = new Document\Security();

        // Set named ranges
        $this->namedRanges = [];

        // Create the cellXf supervisor
        $this->cellXfSupervisor = new Style(true);
        $this->cellXfSupervisor->bindParent($this);

        // Create the default style
        $this->addCellXf(new Style());
        $this->addCellStyleXf(new Style());
    }
    public function __destruct()
    {
        $this->calculationEngine = null;
        $this->disconnectWorksheets();
    }
    public function disconnectWorksheets(): void
    {
        $worksheet = null;
        foreach ($this->workSheetCollection as $k => &$worksheet) {
            $worksheet->disconnectCells();
            $this->workSheetCollection[$k] = null;
        }
        unset($worksheet);
        $this->workSheetCollection = [];
    }
    public function getCalculationEngine()
    {
        return $this->calculationEngine;
    }
    public function getProperties()
    {
        return $this->properties;
    }
    public function setProperties(Document\Properties $pValue): void
    {
        $this->properties = $pValue;
    }
    public function getSecurity()
    {
        return $this->security;
    }
    public function setSecurity(Document\Security $pValue): void
    {
        $this->security = $pValue;
    }
    public function getActiveSheet()
    {
        return $this->getSheet($this->activeSheetIndex);
    }
    public function createSheet($sheetIndex = null)
    {
        $newSheet = new Worksheet($this);
        $this->addSheet($newSheet, $sheetIndex);

        return $newSheet;
    }
    public function sheetNameExists($pSheetName)
    {
        return $this->getSheetByName($pSheetName) !== null;
    }
    public function addSheet(Worksheet $pSheet, $iSheetIndex = null)
    {
        if ($this->sheetNameExists($pSheet->getTitle())) {
            throw new Exception(
                "Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename this worksheet first."
            );
        }

        if ($iSheetIndex === null) {
            if ($this->activeSheetIndex < 0) {
                $this->activeSheetIndex = 0;
            }
            $this->workSheetCollection[] = $pSheet;
        } else {
            // Insert the sheet at the requested index
            array_splice(
                $this->workSheetCollection,
                $iSheetIndex,
                0,
                [$pSheet]
            );

            // Adjust active sheet index if necessary
            if ($this->activeSheetIndex >= $iSheetIndex) {
                ++$this->activeSheetIndex;
            }
        }

        if ($pSheet->getParent() === null) {
            $pSheet->rebindParent($this);
        }

        return $pSheet;
    }
    public function removeSheetByIndex($pIndex): void
    {
        $numSheets = count($this->workSheetCollection);
        if ($pIndex > $numSheets - 1) {
            throw new Exception(
                "You tried to remove a sheet by the out of bounds index: {$pIndex}. The actual number of sheets is {$numSheets}."
            );
        }
        array_splice($this->workSheetCollection, $pIndex, 1);

        // Adjust active sheet index if necessary
        if (($this->activeSheetIndex >= $pIndex) &&
            ($pIndex > count($this->workSheetCollection) - 1)) {
            --$this->activeSheetIndex;
        }
    }
    public function getSheet($pIndex)
    {
        if (!isset($this->workSheetCollection[$pIndex])) {
            $numSheets = $this->getSheetCount();

            throw new Exception(
                "Your requested sheet index: {$pIndex} is out of bounds. The actual number of sheets is {$numSheets}."
            );
        }

        return $this->workSheetCollection[$pIndex];
    }
    public function getAllSheets()
    {
        return $this->workSheetCollection;
    }
    public function getSheetByName($pName)
    {
        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            if ($this->workSheetCollection[$i]->getTitle() === trim($pName, "'")) {
                return $this->workSheetCollection[$i];
            }
        }

        return null;
    }
    public function getIndex(Worksheet $pSheet)
    {
        foreach ($this->workSheetCollection as $key => $value) {
            if ($value->getHashCode() == $pSheet->getHashCode()) {
                return $key;
            }
        }

        throw new Exception('Sheet does not exist.');
    }
    public function setIndexByName($sheetName, $newIndex)
    {
        $oldIndex = $this->getIndex($this->getSheetByName($sheetName));
        $pSheet = array_splice(
            $this->workSheetCollection,
            $oldIndex,
            1
        );
        array_splice(
            $this->workSheetCollection,
            $newIndex,
            0,
            $pSheet
        );

        return $newIndex;
    }
    public function getSheetCount()
    {
        return count($this->workSheetCollection);
    }
    public function getActiveSheetIndex()
    {
        return $this->activeSheetIndex;
    }
    public function setActiveSheetIndex($pIndex)
    {
        $numSheets = count($this->workSheetCollection);

        if ($pIndex > $numSheets - 1) {
            throw new Exception(
                "You tried to set a sheet active by the out of bounds index: {$pIndex}. The actual number of sheets is {$numSheets}."
            );
        }
        $this->activeSheetIndex = $pIndex;

        return $this->getActiveSheet();
    }
    public function setActiveSheetIndexByName($pValue)
    {
        if (($worksheet = $this->getSheetByName($pValue)) instanceof Worksheet) {
            $this->setActiveSheetIndex($this->getIndex($worksheet));

            return $worksheet;
        }

        throw new Exception('Workbook does not contain sheet:' . $pValue);
    }
    public function getSheetNames()
    {
        $returnValue = [];
        $worksheetCount = $this->getSheetCount();
        for ($i = 0; $i < $worksheetCount; ++$i) {
            $returnValue[] = $this->getSheet($i)->getTitle();
        }

        return $returnValue;
    }
    public function addExternalSheet(Worksheet $pSheet, $iSheetIndex = null)
    {
        if ($this->sheetNameExists($pSheet->getTitle())) {
            throw new Exception("Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename the external sheet first.");
        }

        // count how many cellXfs there are in this workbook currently, we will need this below
        $countCellXfs = count($this->cellXfCollection);

        // copy all the shared cellXfs from the external workbook and append them to the current
        foreach ($pSheet->getParent()->getCellXfCollection() as $cellXf) {
            $this->addCellXf(clone $cellXf);
        }

        // move sheet to this workbook
        $pSheet->rebindParent($this);

        // update the cellXfs
        foreach ($pSheet->getCoordinates(false) as $coordinate) {
            $cell = $pSheet->getCell($coordinate);
            $cell->setXfIndex($cell->getXfIndex() + $countCellXfs);
        }

        return $this->addSheet($pSheet, $iSheetIndex);
    }
    public function getNamedRanges()
    {
        return $this->namedRanges;
    }
    public function addNamedRange(NamedRange $namedRange)
    {
        if ($namedRange->getScope() == null) {
            // global scope
            $this->namedRanges[$namedRange->getName()] = $namedRange;
        } else {
            // local scope
            $this->namedRanges[$namedRange->getScope()->getTitle() . '!' . $namedRange->getName()] = $namedRange;
        }

        return true;
    }
    public function getNamedRange($namedRange, ?Worksheet $pSheet = null)
    {
        $returnValue = null;

        if ($namedRange != '' && ($namedRange !== null)) {
            // first look for global defined name
            if (isset($this->namedRanges[$namedRange])) {
                $returnValue = $this->namedRanges[$namedRange];
            }

            // then look for local defined name (has priority over global defined name if both names exist)
            if (($pSheet !== null) && isset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
                $returnValue = $this->namedRanges[$pSheet->getTitle() . '!' . $namedRange];
            }
        }

        return $returnValue;
    }
    public function removeNamedRange($namedRange, ?Worksheet $pSheet = null)
    {
        if ($pSheet === null) {
            if (isset($this->namedRanges[$namedRange])) {
                unset($this->namedRanges[$namedRange]);
            }
        } else {
            if (isset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
                unset($this->namedRanges[$pSheet->getTitle() . '!' . $namedRange]);
            }
        }

        return $this;
    }
    public function getWorksheetIterator()
    {
        return new Iterator($this);
    }
    public function copy()
    {
        $copied = clone $this;

        $worksheetCount = count($this->workSheetCollection);
        for ($i = 0; $i < $worksheetCount; ++$i) {
            $this->workSheetCollection[$i] = $this->workSheetCollection[$i]->copy();
            $this->workSheetCollection[$i]->rebindParent($this);
        }

        return $copied;
    }
    public function __clone()
    {
        foreach ($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }
    public function getCellXfCollection()
    {
        return $this->cellXfCollection;
    }
    public function getCellXfByIndex($pIndex)
    {
        return $this->cellXfCollection[$pIndex];
    }
    public function getCellXfByHashCode($pValue)
    {
        foreach ($this->cellXfCollection as $cellXf) {
            if ($cellXf->getHashCode() == $pValue) {
                return $cellXf;
            }
        }

        return false;
    }
    public function cellXfExists($pCellStyle)
    {
        return in_array($pCellStyle, $this->cellXfCollection, true);
    }
    public function getDefaultStyle()
    {
        if (isset($this->cellXfCollection[0])) {
            return $this->cellXfCollection[0];
        }

        throw new Exception('No default style found for this workbook');
    }
    public function addCellXf(Style $style): void
    {
        $this->cellXfCollection[] = $style;
        $style->setIndex(count($this->cellXfCollection) - 1);
    }
    public function removeCellXfByIndex($pIndex): void
    {
        if ($pIndex > count($this->cellXfCollection) - 1) {
            throw new Exception('CellXf index is out of bounds.');
        }

        // first remove the cellXf
        array_splice($this->cellXfCollection, $pIndex, 1);

        // then update cellXf indexes for cells
        foreach ($this->workSheetCollection as $worksheet) {
            foreach ($worksheet->getCoordinates(false) as $coordinate) {
                $cell = $worksheet->getCell($coordinate);
                $xfIndex = $cell->getXfIndex();
                if ($xfIndex > $pIndex) {
                    // decrease xf index by 1
                    $cell->setXfIndex($xfIndex - 1);
                } elseif ($xfIndex == $pIndex) {
                    // set to default xf index 0
                    $cell->setXfIndex(0);
                }
            }
        }
    }
    public function getCellXfSupervisor()
    {
        return $this->cellXfSupervisor;
    }
    public function getCellStyleXfCollection()
    {
        return $this->cellStyleXfCollection;
    }
    public function getCellStyleXfByIndex($pIndex)
    {
        return $this->cellStyleXfCollection[$pIndex];
    }
    public function getCellStyleXfByHashCode($pValue)
    {
        foreach ($this->cellStyleXfCollection as $cellStyleXf) {
            if ($cellStyleXf->getHashCode() == $pValue) {
                return $cellStyleXf;
            }
        }

        return false;
    }
    public function addCellStyleXf(Style $pStyle): void
    {
        $this->cellStyleXfCollection[] = $pStyle;
        $pStyle->setIndex(count($this->cellStyleXfCollection) - 1);
    }
    public function removeCellStyleXfByIndex($pIndex): void
    {
        if ($pIndex > count($this->cellStyleXfCollection) - 1) {
            throw new Exception('CellStyleXf index is out of bounds.');
        }
        array_splice($this->cellStyleXfCollection, $pIndex, 1);
    }
    public function garbageCollect(): void
    {
        // how many references are there to each cellXf ?
        $countReferencesCellXf = [];
        foreach ($this->cellXfCollection as $index => $cellXf) {
            $countReferencesCellXf[$index] = 0;
        }

        foreach ($this->getWorksheetIterator() as $sheet) {
            // from cells
            foreach ($sheet->getCoordinates(false) as $coordinate) {
                $cell = $sheet->getCell($coordinate);
                ++$countReferencesCellXf[$cell->getXfIndex()];
            }

            // from row dimensions
            foreach ($sheet->getRowDimensions() as $rowDimension) {
                if ($rowDimension->getXfIndex() !== null) {
                    ++$countReferencesCellXf[$rowDimension->getXfIndex()];
                }
            }

            // from column dimensions
            foreach ($sheet->getColumnDimensions() as $columnDimension) {
                ++$countReferencesCellXf[$columnDimension->getXfIndex()];
            }
        }

        // remove cellXfs without references and create mapping so we can update xfIndex
        // for all cells and columns
        $countNeededCellXfs = 0;
        foreach ($this->cellXfCollection as $index => $cellXf) {
            if ($countReferencesCellXf[$index] > 0 || $index == 0) { // we must never remove the first cellXf
                ++$countNeededCellXfs;
            } else {
                unset($this->cellXfCollection[$index]);
            }
            $map[$index] = $countNeededCellXfs - 1;
        }
        $this->cellXfCollection = array_values($this->cellXfCollection);

        // update the index for all cellXfs
        foreach ($this->cellXfCollection as $i => $cellXf) {
            $cellXf->setIndex($i);
        }

        // make sure there is always at least one cellXf (there should be)
        if (empty($this->cellXfCollection)) {
            $this->cellXfCollection[] = new Style();
        }

        // update the xfIndex for all cells, row dimensions, column dimensions
        foreach ($this->getWorksheetIterator() as $sheet) {
            // for all cells
            foreach ($sheet->getCoordinates(false) as $coordinate) {
                $cell = $sheet->getCell($coordinate);
                $cell->setXfIndex($map[$cell->getXfIndex()]);
            }

            // for all row dimensions
            foreach ($sheet->getRowDimensions() as $rowDimension) {
                if ($rowDimension->getXfIndex() !== null) {
                    $rowDimension->setXfIndex($map[$rowDimension->getXfIndex()]);
                }
            }

            // for all column dimensions
            foreach ($sheet->getColumnDimensions() as $columnDimension) {
                $columnDimension->setXfIndex($map[$columnDimension->getXfIndex()]);
            }

            // also do garbage collection for all the sheets
            $sheet->garbageCollect();
        }
    }
    public function getID()
    {
        return $this->uniqueID;
    }
    public function getShowHorizontalScroll()
    {
        return $this->showHorizontalScroll;
    }
    public function setShowHorizontalScroll($showHorizontalScroll): void
    {
        $this->showHorizontalScroll = (bool) $showHorizontalScroll;
    }
    public function getShowVerticalScroll()
    {
        return $this->showVerticalScroll;
    }
    public function setShowVerticalScroll($showVerticalScroll): void
    {
        $this->showVerticalScroll = (bool) $showVerticalScroll;
    }
    public function getShowSheetTabs()
    {
        return $this->showSheetTabs;
    }
    public function setShowSheetTabs($showSheetTabs): void
    {
        $this->showSheetTabs = (bool) $showSheetTabs;
    }
    public function getMinimized()
    {
        return $this->minimized;
    }
    public function setMinimized($minimized): void
    {
        $this->minimized = (bool) $minimized;
    }
    public function getAutoFilterDateGrouping()
    {
        return $this->autoFilterDateGrouping;
    }
    public function setAutoFilterDateGrouping($autoFilterDateGrouping): void
    {
        $this->autoFilterDateGrouping = (bool) $autoFilterDateGrouping;
    }
    public function getFirstSheetIndex()
    {
        return $this->firstSheetIndex;
    }
    public function setFirstSheetIndex($firstSheetIndex): void
    {
        if ($firstSheetIndex >= 0) {
            $this->firstSheetIndex = (int) $firstSheetIndex;
        } else {
            throw new Exception('First sheet index must be a positive integer.');
        }
    }
    public function getVisibility()
    {
        return $this->visibility;
    }
    public function setVisibility($visibility): void
    {
        if ($visibility === null) {
            $visibility = self::VISIBILITY_VISIBLE;
        }

        if (in_array($visibility, self::$workbookViewVisibilityValues)) {
            $this->visibility = $visibility;
        } else {
            throw new Exception('Invalid visibility value.');
        }
    }
    public function getTabRatio()
    {
        return $this->tabRatio;
    }
    public function setTabRatio($tabRatio): void
    {
        if ($tabRatio >= 0 || $tabRatio <= 1000) {
            $this->tabRatio = (int) $tabRatio;
        } else {
            throw new Exception('Tab ratio must be between 0 and 1000.');
        }
    }
}
