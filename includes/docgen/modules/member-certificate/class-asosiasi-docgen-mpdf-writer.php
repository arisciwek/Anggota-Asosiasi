<?php
/**
 * Custom MPDF Writer untuk PHPWord
 */
class Asosiasi_DocGen_MPDF_Writer extends \PhpOffice\PhpWord\Writer\PDF\MPDF
{
    protected function createExternalWriterInstance()
    {
        // Get paths dari WP_MPDF_Activator
        $paths = WP_MPDF_Activator::get_mpdf_paths();

        return new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'tempDir' => $paths['temp_path'],
            'fontDir' => [
                WP_MPDF_DIR . 'libs/mpdf/ttfonts',
                $paths['font_path']
            ],
            'fontCache' => $paths['cache_path']
        ]);
    }
}