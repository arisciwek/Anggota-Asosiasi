<?php
/**
 * Tampilan halaman pengaturan utama
 * 
 * @package Asosiasi
 * @version 2.1.2
 * 
 * Changelog:
 * 2.1.2 - 2024-03-13 12:01:10
 * - Added permissions management tab
 * - Updated tab labels for better context
 * - Reorganized tab order for better UX
 * 
 * 2.1.1 - 2024-03-13
 * - Added roles management tab
 * - Updated tab paths configuration
 * 
 * 2.1.0 - 2024-03-13
 * - Restructured settings into modular tab system
 * - Separated services management into its own file
 * - Added tab interface for settings
 * - Improved code organization for future tab additions
 */

if (!defined('ABSPATH')) {
    die;
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Define tabs - tambahkan tab baru di sini
$tabs = array(
    'general' => __('Pengaturan Umum', 'asosiasi'),
    'services' => __('Kelola Layanan', 'asosiasi'),
    'permissions' => __('Hak Akses Role', 'asosiasi')
);

// Define file paths for tab content
$tab_paths = array(
    'general' => '', // General settings rendered below
    'services' => ASOSIASI_DIR . 'admin/views/tabs/tab-services.php',
    'permissions' => ASOSIASI_DIR . 'admin/views/tabs/tab-permissions.php'
);

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('asosiasi_messages'); ?>

    <nav class="nav-tab-wrapper wp-clearfix">
        <?php
        foreach ($tabs as $tab_key => $tab_caption) {
            $active = $current_tab === $tab_key ? 'nav-tab-active' : '';
            $url = add_query_arg('tab', $tab_key);
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . $active . '">' . esc_html($tab_caption) . '</a>';
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php 
        if ($current_tab === 'general'): 
            // General Settings Tab Content 
        ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('asosiasi_settings_group');
                do_settings_sections('asosiasi_settings_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_organization_name"><?php _e('Nama Organisasi', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="asosiasi_organization_name" name="asosiasi_organization_name" 
                                value="<?php echo esc_attr(get_option('asosiasi_organization_name')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_contact_email"><?php _e('Email Kontak', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="asosiasi_contact_email" name="asosiasi_contact_email" 
                                value="<?php echo esc_attr(get_option('asosiasi_contact_email')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php 
        else:
            // Load other tab content from separate files
            if (!empty($tab_paths[$current_tab]) && file_exists($tab_paths[$current_tab])) {
                require_once $tab_paths[$current_tab];
            }
        endif; 
        ?>
    </div>
</div>