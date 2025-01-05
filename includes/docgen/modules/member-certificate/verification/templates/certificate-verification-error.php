<?php

/**
 * Certificate Verification Error Template
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/MemberCertificate/Verificatiton/Templates
 * @version     1.0.0
 * @author      arisciwek
 * @copyright  2024
 * @license    GPL-2.0+
 * 
 * Description:
 * Template untuk menampilkan halaman error saat verifikasi sertifikat gagal.
 * Menampilkan pesan error dalam format yang user-friendly.
 * 
 * Path: includes/docgen/modules/member-certificate/verification/templates/certificate-verification-error.php
 * Timestamp: 2024-12-29 11:00:00
 * 
 * Variables:
 * - $message                    : Pesan error yang akan ditampilkan
 * - $GLOBALS['member']         : Data organisasi
 * 
 * Dependencies:
 * - WordPress Core
 * - Asosiasi Certificate Verification Handler
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Sertifikat</title>
    <?php wp_head(); ?>
</head>
<body>

<div class="verification-container error">
    <div class="verification-header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70px; text-align: left; vertical-align: middle;">
                    <img src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/asosiasi/logo-rui-02.png'); ?>" 
                         alt="Logo" class="logo" style="max-width: 70px;">
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <h1><?php echo esc_html($GLOBALS['member']['organization_name']); ?></h1>
                    <h2>Verifikasi Sertifikat Anggota</h2>
                </td>
                <td style="width: 70px; text-align: right; vertical-align: middle;">
                    <img src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/asosiasi/logo-k3.png'); ?>" 
                         alt="Logo K3" class="logo" style="max-width: 70px;">
                </td>
            </tr>
        </table>
    </div>

    <div class="verification-content">
        <div class="verification-status error">
            <span class="dashicons dashicons-warning"></span>
            <h3>Verifikasi Gagal</h3>
            <p><?php echo esc_html($message); ?></p>
        </div>
    </div>

    <div class="verification-footer">
        <p>Untuk informasi lebih lanjut, silakan kunjungi website kami di 
           <a href="<?php echo esc_url($GLOBALS['member']['website']); ?>" target="_blank">
               <?php echo esc_html($GLOBALS['member']['website']); ?>
           </a>
        </p>
    </div>
</div>


<style>
.verification-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.verification-header {
    text-align: center;
    margin-bottom: 30px;
}

.verification-header img.logo {
    max-width: 150px;
    height: auto;
}

.verification-status {
    text-align: center;
    margin: 20px 0;
    padding: 20px;
    border-radius: 4px;
}

.verification-status.error {
    background: #fff2f0;
    color: #dc3545;
}

.verification-status .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.verification-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>

</body>
</html>
