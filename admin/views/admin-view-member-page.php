<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check untuk kedua kemungkinan parameter id
$member_id = 0;
if (isset($_GET['amp;id'])) {
    $member_id = intval($_GET['amp;id']); // Untuk kasus redirect setelah update
} else if (isset($_GET['id'])) {
    $member_id = intval($_GET['id']); // Untuk kasus klik dari tabel
}

// Inisialisasi database handler
$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();

// Get member data
$member = $crud->get_member($member_id);


if ($member) {
    $member_services = $services->get_member_services($member_id);
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php echo esc_html($member['company_name']); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-add-member&action=edit&id=' . $member_id)); ?>" 
               class="page-title-action">
                <?php _e('Edit', 'asosiasi'); ?>
            </a>
        </h1>
        
        <hr class="wp-header-end">

        <!-- Company Information Card -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
                <?php _e('Company Information', 'asosiasi'); ?>
            </h2>
            <div class="inside" style="padding: 20px;">
                <table class="form-table" style="margin: 0;">
                    <tr>
                        <th scope="row" style="padding: 10px 0;"><?php _e('Company Name', 'asosiasi'); ?></th>
                        <td style="padding: 10px 0;"><?php echo esc_html($member['company_name']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="padding: 10px 0;"><?php _e('Contact Person', 'asosiasi'); ?></th>
                        <td style="padding: 10px 0;"><?php echo esc_html($member['contact_person']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="padding: 10px 0;"><?php _e('Email', 'asosiasi'); ?></th>
                        <td style="padding: 10px 0;">
                            <a href="mailto:<?php echo esc_attr($member['email']); ?>" style="color: #0073aa; text-decoration: none;">
                                <?php echo esc_html($member['email']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="padding: 10px 0;"><?php _e('Phone', 'asosiasi'); ?></th>
                        <td style="padding: 10px 0;">
                            <?php if (!empty($member['phone'])): ?>
                                <a href="tel:<?php echo esc_attr($member['phone']); ?>" style="color: #0073aa; text-decoration: none;">
                                    <?php echo esc_html($member['phone']); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Services Card -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2 style="margin-top: 0;"><?php _e('Layanan', 'asosiasi'); ?></h2>
            <?php if ($member_services): ?>
                <div class="service-tags" style="margin-top: 10px;">
                    <?php 
                    foreach ($member_services as $service_id):
                        $service = $services->get_service($service_id);
                        if ($service):
                    ?>
                        <span class="service-tag" style="margin-right: 10px; margin-bottom: 10px;">
                            <span class="service-name"><?php echo esc_html($service['short_name']); ?></span>
                            <span class="service-description" style="display: block; font-size: 0.8em; color: #666;">
                                <?php echo esc_html($service['full_name']); ?>
                            </span>
                        </span>
                    <?php 
                        endif;
                    endforeach;
                    ?>
                </div>
            <?php else: ?>
                <p><em><?php _e('Tidak ada layanan yang terdaftar', 'asosiasi'); ?></em></p>
            <?php endif; ?>
        </div>

        <!-- Actions Card -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
                <?php _e('Actions', 'asosiasi'); ?>
            </h2>
            <div class="inside" style="padding: 20px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-add-member&action=edit&id=' . $member_id)); ?>" 
                   class="button button-primary" style="margin-right: 10px;">
                    <?php _e('Edit Member', 'asosiasi'); ?>
                </a>

                <button type="button" class="button" style="color: #d63638; margin-right: 10px;"
                        onclick="if(confirm('<?php esc_attr_e('Are you sure you want to delete this member?', 'asosiasi'); ?>')) { 
                            document.getElementById('delete-member-form').submit(); 
                        }">
                    <?php _e('Delete Member', 'asosiasi'); ?>
                </button>

                <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi')); ?>" 
                   class="button">
                    <?php _e('Back to List', 'asosiasi'); ?>
                </a>

                <form id="delete-member-form" method="post" action="<?php echo admin_url('admin.php?page=asosiasi'); ?>" style="display:none;">
                    <?php wp_nonce_field('delete_member_' . $member_id); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                </form>
            </div>
        </div>
    </div>
    <?php

// Include only the HTML structure
include_once ASOSIASI_DIR . 'admin/views/templates/skp-skp-template.php';
include_once ASOSIASI_DIR . 'admin/views/templates/skp-modal-template.php';

}

if (!$member) {?>

    <div class="wrap">
        <h1><?php _e('Anggota Tidak Ditemukan', 'asosiasi'); ?></h1>
        <p><?php _e('Maaf, anggota yang Anda cari tidak ditemukan.', 'asosiasi'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=asosiasi'); ?>" class="button">
            <?php _e('Kembali ke Daftar Anggota', 'asosiasi'); ?>
        </a>
    </div>
    <?php
}
