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
            max-width: 297mm; /* A4 width */
            height: 210mm; /* A4 height in landscape */
            margin: 0 auto;
            padding: 20mm;
            box-sizing: border-box;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 100px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24pt;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #000;
        }
        .subtitle {
            font-size: 16pt;
            margin: 10px 0;
            text-align: center;
        }
        .content {
            margin: 40px 0;
        }
        .company-info {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .company-info td, 
        .company-info th {
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
        }
        .qr-code {
            position: absolute;
            bottom: 20mm;
            right: 20mm;
            width: 100px;
            height: 100px;
        }
        .issue-date {
            position: absolute;
            bottom: 20mm;
            left: 20mm;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Header Section -->
        <div class="header">
            <img src="<?php echo esc_url($data['image:logo']); ?>" alt="Logo" class="logo">
            <div class="title">SERTIFIKAT ANGGOTA</div>
            <div class="subtitle"><?php echo esc_html($data['nomor_sertifikat']); ?></div>
        </div>

        <!-- Content will continue... -->
</body>
</html>
