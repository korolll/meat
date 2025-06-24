<?php

namespace App\Services\Documents\Spreadsheets;

use App\Services\Framework\HasStaticMakeMethod;
use PhpOffice\PhpSpreadsheet\Spreadsheet as BaseSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Spreadsheet extends BaseSpreadsheet
{
    use HasStaticMakeMethod;

    /**
     * @param string $filename
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function xlsx(string $filename)
    {
        (new Xlsx($this))->save($filename);

        return $filename;
    }

    /**
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function asBinary()
    {
        $filename = tempnam(sys_get_temp_dir(), 'xlsx');

        try {
            return file_get_contents($this->xlsx($filename));
        } finally {
            @unlink($filename);
        }
    }
}
