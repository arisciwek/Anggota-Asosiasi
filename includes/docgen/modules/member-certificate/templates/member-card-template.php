<?php
/**
 * Certificate PDF Template
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: modules/member-certificate/templates/member-card-template.php
 * 
 * Description:
 * Template for generating PDF member card directly using mPDF.
 * This template provides the HTML/CSS structure that will be 
 * converted to PDF by mPDF library. Includes proper styling
 * and layout for member member card.
 * 
 * Variables available:
 * - $data['nomor_sertifikat']   : Member number
 * - $data['company_name']       : Company name
 * - $data['company_leader']     : Company leader name
 * - $data['leader_position']    : Leader position
 * - $data['business_field']     : Business field
 * - $data['city']              : City
 * - $data['company_address']    : Complete address
 * - $data['npwp']              : Tax ID number
 * - $data['issue_date']        : Certificate issue date
 * - $data['qr_data']           : QR code verification URL
 * - $data['ketua_umum']        : Ketua Umum's name
 * - $data['sekretaris_umum']   : Sekretaris Umum's name
 * - $data['contact_email']     : Email contact
 * - $data['website']           : Website URL
 * 
 * Dependencies:
 * - mPDF library
 * - WP DocGen Plugin
 * - Asosiasi Member Certificate Provider
 * 
 * Usage:
 * This template is loaded by handle_direct_pdf_generation()
 * in the Asosiasi_DocGen_Member_Certificate_Module class
 * 
 * Changelog:
 * 1.0.0 - 2024-12-29
 * - Initial release
 * - Added basic certificate structure
 * - Added styling for landscape A4
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}
?>
<?php
/**
 * Certificate PDF Template
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: modules/member-certificate/templates/member-card-template.php
 * 
 * Description:
 * Template for generating PDF member card directly using mPDF.
 * This template provides the HTML/CSS structure that will be 
 * converted to PDF by mPDF library. Includes proper styling
 * and layout for member member card.
 * 
 * Variables available:
 * - $data['nomor_sertifikat']   : Member number
 * - $data['company_name']       : Company name
 * - $data['company_leader']     : Company leader name
 * - $data['leader_position']    : Leader position
 * - $data['business_field']     : Business field
 * - $data['city']              : City
 * - $data['company_address']    : Complete address
 * - $data['npwp']              : Tax ID number
 * - $data['issue_date']        : Certificate issue date
 * - $data['qr_data']           : QR code verification URL
 * - $data['ketua_umum']        : Ketua Umum's name
 * - $data['sekretaris_umum']   : Sekretaris Umum's name
 * - $data['contact_email']     : Email contact
 * - $data['website']           : Website URL
 * 
 * Dependencies:
 * - mPDF library
 * - WP DocGen Plugin
 * - Asosiasi Member Certificate Provider
 * 
 * Usage:
 * This template is loaded by handle_direct_pdf_generation()
 * in the Asosiasi_DocGen_Member_Certificate_Module class
 * 
 * Changelog:
 * 1.0.0 - 2024-12-29
 * - Initial release
 * - Added basic certificate structure
 * - Added styling for landscape A4
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>

        body {
            font-family: dejavusans;
            margin: 0;
            padding: 0;
            background-color: white;
        }

        .page {
            width: 100%;
            max-width: 297mm;
            height: 210mm;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            width: 100%;
            height: 70px;
            position: relative;
            margin-bottom: 5px;
        }

        .logo {
            width: 50px;
        }

        .title-container {
            position: absolute;
            left: 80px;
            right: 80px;
            top: 50%;
            transform: translateY(-50%);
            text-align: center;
        }

        .organization-name {
            font-size: 8pt;
            margin-bottom: 5px;
        }

        .title {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            margin: 0;
            line-height: 1;
        }

        .card-container {
            margin: auto;
        }

        .member-card {
            width: 85mm; /* Lebar kartu anggota */
            height: 54mm; /* Tinggi kartu anggota */
            margin: auto;
            border: 1px solid #000;
            border-radius: 12px;
        }

        .card-container .member-card {
            margin-bottom: 120px;
        }

        .card-body {
            margin:2px;
        }

        .terms h4{
            font-size: 8pt;
            text-align: center;
            margin-bottom: 1px;
        }

        .content {
            font-size: 8pt;
            margin-left: 5px;
            table-layout: fixed; /* Penting untuk mengontrol lebar kolom */
        }

        .content td {
            vertical-align: top;
            padding: 0.3mm 1mm;
            word-wrap: break-word; /* Memungkinkan text wrapping */
            overflow: hidden;
        }

        /* Khusus untuk cell alamat */
        .address-cell {
            max-height: 12mm; /* Batasi tinggi maksimum */
            overflow: hidden;
            text-overflow: ellipsis; /* Tambahkan ellipsis jika text terpotong */
            display: block; /* Penting untuk text-overflow bekerja */
            word-break: break-word; /* Memungkinkan pemecahan kata jika perlu */
            line-height: 1.2; /* Mengontrol jarak antar baris */
        }

        .terms{
            font-size: 6pt;
        }

        .footer {
            position: absolute;
            left: 10mm;
            right: 10mm;
            height: 100px;
            text-align: center;
        }

        .footer-col {
            float: left;
            width: 50%;
            text-align: center;
            position: relative;
        }
        .footer-bottom{
            margin-top: 2px;
            font-size: 5pt;
            text-align: center;
        }

        .signature-block {
            position: relative;
            text-align: center;
            margin-top: -5px; /* Menggeser seluruh block ke atas */
        }

        .signature-position {
            font-size: 7pt;
            font-weight: bold;
            position: relative;
            z-index: 1;
            margin-top: -4px; /* Menggeser 'Ketua Umum' lebih ke atas */
        }

        .signature-image {
            position: relative;
            margin-top: -12px; /* Menggeser image lebih dekat ke text 'Ketua Umum' */
            z-index: 2;
        }

        .signature-image img {
            width: auto;
            display: block;
            margin: 0 auto;
        }

        .signature-name {
            font-size: 7pt;
            font-weight: bold;
            margin-top: -2px; /* Menggeser nama lebih dekat ke tanda tangan */
            position: relative;
            z-index: 3;
        }

        /* Menyesuaikan posisi footer */
        .footer {
            margin-top: 8px;
            text-align: center;
        }

        .footer-col {
            display: inline-block;
            vertical-align: top;
            width: 48%;
        }

        /* Menyesuaikan QR Code container */
        .qr-code-container {
            margin-top: -15px; /* Menggeser QR code ke atas untuk menyesuaikan */
        }

        .qr-code-title {
            font-size: 6pt;
            color: #666;
            margin-top: 1px;
            text-align: center;
        }

    </style>
</head>
<body>

    <div class="page">

        <div class="card-container clearfix">

            <div class="member-card">
                <!-- Tampak Depan -->
                <div class="card-body">
                    <!-- Header -->
                    <table class="header" style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td style="width: 55px; vertical-align: middle; padding: 0;">
                                <img src="<?php echo esc_url($data['image:logo']); ?>" alt="Logo" class="logo">
                            </td>
                            <td style="vertical-align: middle; text-align: center; padding-left: 10px;">
                                <div class="organization-name"><?php echo esc_html($data['organization_name']); ?></div>
                                <h1 class="title">KARTU ANGGOTA</h1>
                            </td>
                            <td style="width: 55px;">
                                <img src="<?php echo esc_url($data['image:logo_k3']); ?>" alt="Logo K3" class="logo">
                            </td> <!-- Balancing column -->
                        </tr>
                    </table>

                    <!-- Content -->
                    <table class="content">
                        <tr>
                            <td style="width: 40%;">Nama Perusahaan</td>
                            <td style="width: 5%; text-align: center;">:</td>
                            <td style="width: 55%;"><?php echo htmlspecialchars($data['company_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Nama Direktur</td>
                            <td style="text-align: center;">:</td>
                            <td><?php echo htmlspecialchars($data['company_leader']); ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td style="text-align: center;">:</td>
                            <td class="address-cell"><?php echo htmlspecialchars($data['company_address']); ?></td>
                        </tr>
                        <tr>
                            <td>Nomor Anggota</td>
                            <td style="text-align: center;">:</td>
                            <td><?php echo htmlspecialchars($data['nomor_sertifikat']); ?></td>
                        </tr>
                        <tr>
                            <td>Berlaku Sampai</td>
                            <td style="text-align: center;">:</td>
                            <td><?php echo htmlspecialchars($data['valid_until']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-container clearfix">

            <div class="member-card">
                <!-- Tampak Belakang -->
                <div class="card-body">
                    <div class="terms">
                    <h4>KETENTUAN:</h4>
                    <ol>
                        <li>Kartu ini adalah milik <?php echo htmlspecialchars($data['organization_name']); ?> dan tidak dapat dipindahtangankan</li>
                        <li>Masa berlaku kartu ini 1 Tahun</li>
                        <li>Harap kembalikan kartu ini jika ditemukan</li>
                    </ol>
                    </div>
                    <!-- Footer -->
                    <div class="footer clearfix">
                        <div class="footer-row clearfix">

                            <!-- Signature Section -->
                            <div class="footer-col">
                                <div class="signature-block">
                                    <div class="signature-position">Ketua Umum</div>
                                    <div class="signature-image">
                                        <img class="image-signature" src="<?php echo esc_url($data['image:ttd_ketua']); ?>" 
                                             alt="Tanda tangan Ketua" 
                                             style="height: 65px; width: auto; display: block; margin: 0 auto;">
                                    </div>
                                    <div class="signature-name">
                                        <?php echo esc_html($data['ketua_umum']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="footer-col">
                                <div class="qr-code-container">
                                    <?php if(isset($data['base64QRCode'])): ?>
                                        <img src="data:image/png;base64,<?php echo $data['base64QRCode']; ?>" 
                                             alt="QR Code" 
                                             style="width: 70px; height: 70px; 
                                                    background: white;
                                                    image-rendering: -webkit-optimize-contrast;
                                                    image-rendering: crisp-edges;
                                                    display: block;
                                                    margin: 0 auto;">
                                        <div class="qr-code-title">Scan untuk verifikasi</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="footer-bottom clearfix">
                            <div class="info-website">Website:
                                <span class="info-value"><?php echo esc_html($data['website']); ?></span> 
                            </div>

                            <div class="info-website">Tanggal Cetak:
                                <span class="info-value"><?php echo esc_html($data['issue_date']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</body>
</html>
