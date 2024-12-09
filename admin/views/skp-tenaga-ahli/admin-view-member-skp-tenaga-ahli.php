<?php
/**
 * Tampilan untuk SKP Tenaga Ahli di halaman member
 *
 * @package     Asosiasi
 * @subpackage  Admin/Views/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: admin/views/skp-tenaga-ahli/admin-view-member-skp-tenaga-ahli.php
 * 
 * Description: Menampilkan daftar SKP Tenaga Ahli, tab status aktif/nonaktif,
 *              dan riwayat perubahan status
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial release
 * - Added SKP listing tabs
 * - Added status history
 */

defined('ABSPATH') || exit;

if ($member) {
   $member_services = $services->get_member_services($member_id);
   $can_change_status = current_user_can('manage_options') || current_user_can('manage_skp_status');
   ?>
   <div class="wrap">
       <div class="skp-container">
           <!-- SKP Tenaga Ahli Section -->
           <fieldset class="skp-card skp-section" id="skp-tenaga-ahli-section">
               <input type="hidden" id="skp_tenaga_ahli_nonce" name="skp_nonce" 
                       value="<?php echo wp_create_nonce('asosiasi_skp_tenaga_ahli_nonce'); ?>">

               <input type="hidden" id="member_id" value="<?php echo esc_attr($member_id); ?>">
               <legend>
                   <h3><?php _e('SKP Tenaga Ahli', 'asosiasi'); ?></h3>
               </legend>
               
               <div class="skp-content">
                   <?php if (!empty($member_services)): ?>
                       <div class="skp-actions">
                           <button type="button" 
                                   class="button add-skp-btn" 
                                   data-type="tenaga-ahli" 
                                   data-member-id="<?php echo esc_attr($member_id); ?>">
                               <span class="dashicons dashicons-plus-alt"></span>
                               <?php _e('Tambah SKP', 'asosiasi'); ?>
                           </button>
                       </div>

                       <!-- Tab Navigation -->
                       <div class="skp-tabs">
                           <nav class="nav-tab-wrapper">
                               <a href="#skp-active" class="nav-tab nav-tab-active" data-tab="active">
                                   <?php _e('SKP Aktif', 'asosiasi'); ?>
                               </a>
                               <a href="#skp-inactive" class="nav-tab" data-tab="inactive">
                                   <?php _e('SKP Tidak Aktif', 'asosiasi'); ?>
                               </a>
                               <a href="#skp-history" class="nav-tab" data-tab="history">
                                   <?php _e('Riwayat Status', 'asosiasi'); ?>
                               </a>
                           </nav>

                           <!-- Tab Content -->
                           <div class="tab-content">
                               <!-- Active SKP Tab -->
                               <div id="skp-tenaga-ahli-active" class="tab-pane active">
                                   <div class="skp-table-container">
                                       <table class="wp-list-table widefat fixed striped skp-table">
                                           <thead>
                                               <tr>
                                                   <th class="column-number"><?php _e('No', 'asosiasi'); ?></th>
                                                   <th class="column-nomor"><?php _e('Nomor SKP', 'asosiasi'); ?></th>
                                                   <th class="column-service"><?php _e('Layanan', 'asosiasi'); ?></th>
                                                   <th class="column-name"><?php _e('Nama Tenaga Ahli', 'asosiasi'); ?></th>
                                                   <th class="column-position"><?php _e('Jabatan', 'asosiasi'); ?></th>
                                                   <th class="column-date"><?php _e('Tanggal Terbit', 'asosiasi'); ?></th>
                                                   <th class="column-date"><?php _e('Masa Berlaku', 'asosiasi'); ?></th>
                                                   <th class="column-status"><?php _e('Status', 'asosiasi'); ?></th>
                                                   <th class="column-pdf"><?php _e('File', 'asosiasi'); ?></th>
                                                   <th class="column-actions"><?php _e('Aksi', 'asosiasi'); ?></th>
                                               </tr>
                                           </thead>
                                           <tbody id="active-skp-tenaga-ahli-list">
                                               <tr class="skp-loading">
                                                   <td colspan="10" class="text-center">
                                                       <span class="spinner is-active"></span>
                                                       <span class="loading-text">
                                                           <?php _e('Memuat data SKP aktif...', 'asosiasi'); ?>
                                                       </span>
                                                   </td>
                                               </tr>
                                           </tbody>
                                       </table>
                                   </div>
                               </div>

                               <!-- Inactive SKP Tab -->
                               <div id="skp-tenaga-ahli-inactive" class="tab-pane">
                                   <div class="skp-table-container">
                                       <table class="wp-list-table widefat fixed striped skp-table">
                                           <thead>
                                               <tr>
                                                   <th class="column-number"><?php _e('No', 'asosiasi'); ?></th>
                                                   <th class="column-nomor"><?php _e('Nomor SKP', 'asosiasi'); ?></th>
                                                   <th class="column-service"><?php _e('Layanan', 'asosiasi'); ?></th>
                                                   <th class="column-name"><?php _e('Nama Tenaga Ahli', 'asosiasi'); ?></th>
                                                   <th class="column-position"><?php _e('Jabatan', 'asosiasi'); ?></th>
                                                   <th class="column-date"><?php _e('Tanggal Terbit', 'asosiasi'); ?></th>
                                                   <th class="column-date"><?php _e('Masa Berlaku', 'asosiasi'); ?></th>
                                                   <th class="column-status"><?php _e('Status', 'asosiasi'); ?></th>
                                                   <th class="column-pdf"><?php _e('File', 'asosiasi'); ?></th>
                                                   <th class="column-actions"><?php _e('Aksi', 'asosiasi'); ?></th>
                                               </tr>
                                           </thead>
                                           <tbody id="inactive-skp-list">
                                               <tr class="skp-loading">
                                                   <td colspan="10" class="text-center">
                                                       <span class="spinner is-active"></span>
                                                       <span class="loading-text">
                                                           <?php _e('Memuat data SKP tidak aktif...', 'asosiasi'); ?>
                                                       </span>
                                                   </td>
                                               </tr>
                                           </tbody>
                                       </table>
                                   </div>
                               </div>

                               <!-- History Tab Content -->
                               <?php 
                               $history_template = ASOSIASI_DIR . 'admin/views/admin-view-member-skp-history.php';
                               if (file_exists($history_template)) {
                                   include $history_template;
                               }
                               ?>
                           </div>
                       </div>
                   <?php else: ?>
                       <div class="notice notice-warning inline">
                           <p>
                               <?php _e('Anggota belum memiliki layanan yang terdaftar. Tambahkan layanan terlebih dahulu sebelum menambah SKP.', 'asosiasi'); ?>
                           </p>
                       </div>
                   <?php endif; ?>
               </div>
           </fieldset>
       </div>

       <?php 
       // Include modal templates if member has services
       if (!empty($member_services)) {
           // Include status change modal if user has permissions
           if ($can_change_status) {
               require_once ASOSIASI_DIR . 'admin/views/skp-tenaga-ahli/admin-view-member-modal-status-skp-tenaga-ahli.php';
           }

           // Include SKP form modal
           require_once ASOSIASI_DIR . 'admin/views/skp-tenaga-ahli/admin-view-member-modal-skp-tenaga-ahli.php';
       }
       ?>
   </div>
   <?php
}
