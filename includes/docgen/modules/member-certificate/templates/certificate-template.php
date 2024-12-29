<?php
/**
 * Certificate PDF Template
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: modules/member-certificate/templates/certificate-template.php
 * 
 * Description:
 * Template for generating PDF certificates directly using mPDF.
 * This template provides the HTML/CSS structure that will be 
 * converted to PDF by mPDF library. Includes proper styling
 * and layout for member certificates.
 * 
 * Variables available:
 * - $data['nomor_sertifikat']   : Certificate number
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
        
        .certificate {
            width: 100%;
            max-width: 297mm;
            height: 210mm;
            margin: 0 auto;
            padding: 5mm; /* Mengurangi padding */
            box-sizing: border-box;
            position: relative;
        }

        /* Header Styling - Perbaikan alignment */
        .header {
            width: 100%;
            height: 70px;
            position: relative;
            margin-bottom: 10px;
        }

        .logo-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
            height: 70px;
            text-align: center;
        }

        .certificate-section,
        .footer-bottom{
            text-align: center;

        }

        .logo {
            max-width: 100%;
            max-height: 100%;
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
            font-size: 14pt;
            margin-bottom: 5px;
        }

        .title {
            position: relative;
            transform: none;
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            margin: 0;
            line-height: 1.2;
        }

        /* Certificate Info Styling */
        .info-section {
            margin: 10px 0;
        }

        .info-row {
            width: 100%;
            margin-bottom: 8px;
            clear: both;
        }

        .info-col {
            float: left;
            width: 48%;
            margin-right: 2%;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
        }

        /* Services Section */
        .services-section {
            margin: 15px 0;
            clear: both;
        }

        .services-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Footer with 4 columns */
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 10mm;
            right: 10mm;
            height: 120px;
        }

        .footer-col {
            float: left;
            width: 25%;
            text-align: center;
            position: relative;
        }

        .photo-placeholder {
            width: 100px;
            height: 120px;
            border: 1px dashed #999;
            margin: 0 auto;
        }

        .signature-block {
            margin-top: 10px;
        }

        .signature-line {
            width: 80%;
            margin: 40px auto 5px;
            border-bottom: 1px solid black;
        }

        /* Utility */
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        .info-container {
            width: 100%;
            display: table;
            margin: 10px 0;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }

        .info-label {
            font-weight: bold;
            min-width: 150px;
            display: inline-block;
        }

        .info-value {
            display: inline-block;
        }        

        .main-content {
            margin: 10px 0;
        }

        /* Service with 3 columns */
        .service {
            width: 100%;
        }

        .service-col {
            float: left;
            width: 33.33%;
            position: relative;
        }

.info-item {
    clear: left;
    margin-bottom: 5px;
}

.info-label {
    float: left;
    width: 50%;
    display: inline-block; /* Agar elemen label tidak mengganggu elemen setelahnya */
    vertical-align: top; /* Agar teks label (Nama Perusahaan dan Company Name) sejajar di atas */
}

.statement-section{
    width: 100%;
    display: block;
    text-align: center;
}

.statement-col-25{
    width: 25%;
    display: inline-block;
    float: left;
}

.statement-col-50{
    width: 50%;
    display: inline-block;
    float: left;
}

.info-label-100 {
    float: left;
    width: 100%;
    display: block; 
    vertical-align: top;
}

.info-bold {
    font-weight: bold;
    font-size: 14px; /* Ukuran teks bahasa Indonesia */
}

.info-small {
    font-size: 10px; /* Ukuran teks bahasa Inggris yang lebih kecil */
    color: #888888; /* Warna abu-abu untuk teks bahasa Inggris */
}

.info-value {
    display: inline-block; /* Membuat nama perusahaan berada di sebelah kanan */
    margin-left: 10px; /* Memberikan jarak antara label dan nama perusahaan */
}

.qr-code-container {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    text-align: center;
}

.qr-code-image {
    width: 100%;
    height: 100%;
}

.qr-code-title {
    font-size: 8pt;
    color: #666;
    margin-top: 5px;
    text-align: center;
}

</style>
</head>
<body>
    <div class="certificate">
        <!-- Header -->
        <table class="header" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="width: 70px; vertical-align: middle; padding: 0;">
                    <img src="<?php echo esc_url($data['image:logo']); ?>" alt="Logo" class="logo">
                </td>
                <td style="vertical-align: middle; text-align: center; padding-left: 20px;">
                    <div class="organization-name"><?php echo esc_html($data['organization_name']); ?></div>
                    <h1 class="title">SERTIFIKAT ANGGOTA</h1>
                </td>
                <td style="width: 70px;">
                    <img src="<?php echo esc_url($data['image:logo_k3']); ?>" alt="Logo K3" class="logo">
                </td> <!-- Balancing column -->
            </tr>
        </table>


        <!-- Main Content -->
        <div class="main-content clearfix">



            <!-- Certificate Info -->
            <div class="certificate-section clearfix">
                <div class="info-row clearfix">
                    <div class="info-col">
                        <div class="info-item">
                            <div class="info-label-100">
                                <div class="info-bold">Nomor Anggota PPJK3 RUI Banten</div>
                                <div class="info-small">Registration Number</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['nomor_sertifikat']); ?></div>
                        </div>
                    </div>
                    <div class="info-col">
                        <div class="info-item">
                            <div class="info-label-100">
                                <div class="info-bold">Berlaku Sampai</div>
                                <div class="info-small">Valid Untill</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['issue_date']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-section clearfix">
                <div class="info-row">
                    <!-- Company Information -->
                    <div class="info-col">
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Nama Perusahaan:</div>
                                <div class="info-small">Company Name:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['company_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Pimpinan Perusahaan:</div>
                                <div class="info-small">Person in Charge:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['company_leader']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Jabatan:</div>
                                <div class="info-small">Position:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['leader_position']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">NPWP:</div>
                                <div class="info-small">Tax Number:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['npwp']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">No. AHU:</div>
                                <div class="info-small">Legal Number:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['ahu_number']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Bidang Usaha:</div>
                                <div class="info-small">Line of Business:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['business_field']); ?></div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="info-col">
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Alamat Perusahaan:</div>
                                <div class="info-small">Company Address:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['company_address']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <div class="info-bold">Kabupaten/Kota:</div>
                                <div class="info-small">District/City:</div>
                            </div>
                            <div class="info-value"><?php echo esc_html($data['city']); ?></div>
                        </div>


                        <div class="info-item">
                            <div class="services-col">
                                <!-- Services Section -->
                                <div class="services-title">Layanan yang Dimiliki:</div>
                                <div class="services-content">
                                    <ul style="list-style-type: disc; margin: 5px 0 5px 20px; padding: 0;">
                                        <?php 
                                        // Memisahkan string berdasarkan koma
                                        $services = explode(',', $data['services']);
                                        foreach ($services as $service) {
                                            $service = trim($service); // Menghilangkan spasi di awal dan akhir
                                            if (!empty($service)) {
                                                echo '<li style="margin-bottom: 3px;">' . esc_html($service) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statement -->
            <div class="statement-section clearfix">
                <!-- Empty space -->
                <div class="statement-col-25"><p>&nbsp;</p></div>
                <div class="statement-col-50">
                    <!-- Statements Section -->
                        <div class="statements-title">Adalah Anggota PPJK3 RUI Banten</div>
                        <div class="info-small">Is an Ordinary Member of PPJK3 RUI Banten</div>
                        <div class="statements-content">
                            <span>&nbsp;</span>
                        </div>
                </div>
                <!-- Empty space -->
                <div class="statement-col-25"><p>&nbsp;</p></div>
            </div>



        </div>

        <!-- Footer -->
        <div class="footer clearfix">
            <div class="footer-row clearfix">
                <!-- Photo Section -->
                <div class="footer-col">
                    <div class="photo-placeholder">
                        <?php if(isset($data['image:member_photo'])): ?>
                            <img src="<?php echo esc_url($data['image:member_photo']); ?>" alt="Member Photo" style="max-width: 100%; max-height: 100%;">
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Ketua Umum -->
                <div class="footer-col">
                    <div class="signature-block">
                        <strong>Ketua Umum</strong>
                        <div class="signature-line"></div>
                        <?php echo esc_html($data['ketua_umum']); ?>
                    </div>
                </div>
                
                <!-- Sekretaris Umum -->
                <div class="footer-col">
                    <div class="signature-block">
                        <strong>Sekretaris Umum</strong>
                        <div class="signature-line"></div>
                        <?php echo esc_html($data['sekretaris_umum']); ?>
                    </div>
                </div>
                    <div class="footer-col">
                        <div class="qr-code-container">
                            <?php if(isset($data['base64QRCode'])): ?>
                                <img src="data:image/png;base64,<?php echo $data['base64QRCode']; ?>" 
                                     alt="QR Code" 
                                     style="width: 400px; height: 400px; 
                                            padding: 5px;
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
                <span class="info-website">Website:</span>
                <span><?php echo esc_html($data['website']); ?></span>
            </div>
        </div>

    </div>
</body>
</html>