<?php
/**
 * Tampilan halaman pengaturan utama
 * 
 * @package Asosiasi
 * @version 2.1.4
 * 
 * Changelog:
 * 2.1.4 - 2024-12-01 10:30:00
 * - Added hook host_settings_tabs for tab registration
 * - Added hook host_settings_tab_paths for tab content paths
 * - Added hook host_render_settings_tab_{$current_tab}
 * - Maintained existing structure and functionality
 * 
 */

if (!defined('ABSPATH')) {
    die;
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Define tabs - tambahkan tab baru di sini
$tabs = array(
    'general' => __('Pengaturan Umum', 'host'),
    'services' => __('Kelola Layanan', 'host'),
    'permissions' => __('Hak Akses Role', 'host'),
);

$tabs = apply_filters('host_settings_tabs', $tabs);

// Define file paths for tab content
$tab_paths = array(
    'general' => '', // General settings rendered below
    'services' => ASOSIASI_DIR . 'admin/views/tabs/tab-services.php',
    'permissions' => ASOSIASI_DIR . 'admin/views/tabs/tab-permissions.php'
);

// Di atas apply_filters

$tabs = apply_filters('host_settings_tabs', $tabs);

// Filter untuk extensibility
$tabs = apply_filters('host_settings_tabs', $tabs);
$tab_paths = apply_filters('host_settings_tab_paths', $tab_paths);

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
                            <label for="asosiasi_organization_name"><?php _e('Nama Organisasi', 'host'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="asosiasi_organization_name" name="asosiasi_organization_name" 
                                value="<?php echo esc_attr(get_option('asosiasi_organization_name')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_ketua_umum"><?php _e('Ketua Umum', 'host'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="asosiasi_ketua_umum" name="asosiasi_ketua_umum" 
                                value="<?php echo esc_attr(get_option('asosiasi_ketua_umum')); ?>" class="regular-text">
                            <p class="description"><?php _e('Nama lengkap ketua umum organisasi', 'host'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_sekretaris_umum"><?php _e('Sekretaris Umum', 'host'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="asosiasi_sekretaris_umum" name="asosiasi_sekretaris_umum" 
                                value="<?php echo esc_attr(get_option('asosiasi_sekretaris_umum')); ?>" class="regular-text">
                            <p class="description"><?php _e('Nama lengkap sekretaris umum organisasi', 'host'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_contact_email"><?php _e('Email Kontak', 'host'); ?></label>
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
            // Check for custom tab content via action
            ob_start();
            do_action('host_render_settings_tab_' . $current_tab);
            //do_action('host_render_tab_' . $current_tab);

            $custom_content = ob_get_clean();
            
            if (!empty($custom_content)) {
                // Display custom tab content from action
                echo $custom_content;
            } 
            else{
                error_log('Attempting to render tab: ' . $current_tab);

                // Check for custom tab content via action hook
                ob_start();
                do_action('host_render_settings_tab_' . $current_tab);
                $custom_content = ob_get_clean();
                
                if (!empty($custom_content)) {
                    // Display custom tab content from action
                    echo $custom_content;
                    error_log('Custom content rendered for tab: ' . $current_tab);
                } else {
                    error_log('No custom content, checking tab path for: ' . $current_tab);
                    // Load default tab content if exists
                    if (!empty($tab_paths[$current_tab]) && file_exists($tab_paths[$current_tab])) {
                        require_once $tab_paths[$current_tab];
                    }
                }
            }
            endif;
        ?>
    </div>
</div>
