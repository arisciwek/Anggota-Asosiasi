<?php
/**
 * Custom MPDF Writer untuk PHPWord
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Writers
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/class-asosiasi-docgen-mpdf-writer.php
 * 
 * Description: Writer untuk konversi dokumen DOCX ke PDF.
 *              Menggunakan mPDF library untuk proses konversi.
 *              Menyediakan konfigurasi dasar untuk format A4 landscape.
 *              Menangani path untuk font dan temporary files.
 *              Memastikan konversi dengan encoding UTF-8.
 * 
 * Dependencies:
 * - PHPWord library
 * - mPDF library
 * - WP_MPDF Plugin
 *
 * Changelog:
 * 1.0.0 - 2024-12-25
 * - Initial release with basic PDF conversion
 * - Added font and path configuration
 * - Added landscape orientation support
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
            'fontCache' => $paths['cache_path'],
            'default_font' => 'dejavusans',

            'charset_in' => 'UTF-8',  // Mengatur encoding input menjadi UTF-8            
            'table_error_report' => false,
            'table_layout' => 'fixed',  // Penting untuk layout tabel
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,  // Keep with table
            'keepColumns' => true,
            'keep_table_proportions' => true,
            'tabSpaces' => 6
        ]);
    }
}
