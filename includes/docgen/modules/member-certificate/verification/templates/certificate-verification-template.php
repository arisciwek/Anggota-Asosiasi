<?php
/**
 * Certificate Verification Template
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/MemberCertificate/Verificatiton/Templates
 * @version     1.0.0
 * @author      arisciwek
 * @copyright  2024
 * @license    GPL-2.0+
 * 
 * Description:
 * Template untuk menampilkan halaman verifikasi sertifikat yang valid.
 * Menampilkan detail sertifikat anggota dalam format yang terstruktur.
 * 
 * Path: includes/docgen/modules/member-certificate/verification/templates/certificate-verification-template.php
 * Timestamp: 2024-12-29 11:00:00
 * 
 * Variables:
 * - $GLOBALS['verification_data'] : Data sertifikat dan member
 * - $GLOBALS['member']           : Data organisasi
 * 
 * Dependencies:
 * - WordPress Core
 * - Asosiasi Certificate Verification Handler
 * - Asosiasi Member Certificate Provider
 */

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Sertifikat</title>
    <?php wp_head(); ?>
</head>
<body>


<div class="verification-container">
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
        <div class="verification-status success">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3>Sertifikat Valid</h3>
        </div>

        <div class="member-details">
            <table class="verification-table">
                <?php 
                global $verification_data;
                ?>
                <tr>
                    <th>Nomor Sertifikat</th>
                    <td><?php echo esc_html($verification_data['nomor_sertifikat']); ?></td>
                </tr>
                <tr>
                    <th>Nama Perusahaan</th>
                    <td><?php echo esc_html($verification_data['company_name']); ?></td>
                </tr>
                <tr>
                    <th>Pimpinan</th>
                    <td><?php echo esc_html($verification_data['company_leader']); ?></td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td><?php echo esc_html($verification_data['leader_position']); ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?php echo esc_html($verification_data['company_address']); ?></td>
                </tr>
                <tr>
                    <th>Kota</th>
                    <td><?php echo esc_html($verification_data['city']); ?></td>
                </tr>
                <tr>
                    <th>Layanan</th>
                    <td>

						<ol>
						    <?php 
						    // Memisahkan string layanan berdasarkan koma
						    $services = explode(',', $verification_data['services']);
						    foreach ($services as $service) {
						        $service = trim($service); // Menghilangkan spasi di awal dan akhir
						        if (!empty($service)) {
						            echo '<li>' . esc_html($service) . '</li>';
						        }
						    }
						    ?>
						</ol>


                    	<?php //echo esc_html($verification_data['services']); ?>
                    </td>
                </tr>
                <tr>
                    <th>Masa Berlaku</th>
                    <td><?php echo esc_html($verification_data['valid_until']); ?></td>
                </tr>
            </table>
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



</body>
</html>



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
    background: #f0f9f0;
    border-radius: 4px;
}

.verification-status.success {
    color: #28a745;
}

.verification-status .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.verification-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.verification-table th,
.verification-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.verification-table th {
    width: 30%;
    background: #f8f9fa;
}

.verification-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>
