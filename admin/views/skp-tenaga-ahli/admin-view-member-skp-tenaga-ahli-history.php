<?php
/**
* Template for SKP History tab content
*
* @package Asosiasi
* @version 1.0.0
* Path: admin/views/skp-tenaga-ahli/admin-view-member-skp-history.php
* 
* Changelog:
* 1.0.0 - 2024-11-19
* - Initial release
* - Added history tab content structure
* - Moved from SKP Perusahaan main file
*/

if (!defined('ABSPATH')) {
   exit;
}
?>

<!-- History Tab Content -->
<div id="skp-tenaga-ahli-history" class="tab-pane-tenaga-ahli">
   <div class="skp-table-container">
       <table class="wp-list-table widefat fixed striped skp-table">
           <thead>
               <tr>
                   <th class="column-number"><?php _e('No', 'asosiasi'); ?></th>
                   <th class="column-skp"><?php _e('Nomor SKP', 'asosiasi'); ?></th>
                   <th class="column-status"><?php _e('Status Lama', 'asosiasi'); ?></th>
                   <th class="column-status"><?php _e('Status Baru', 'asosiasi'); ?></th>
                   <th class="column-reason"><?php _e('Alasan', 'asosiasi'); ?></th>
                   <th class="column-user"><?php _e('Diubah Oleh', 'asosiasi'); ?></th>
                   <th class="column-date"><?php _e('Waktu', 'asosiasi'); ?></th>
               </tr>
           </thead>
           <tbody id="status-history-list">
               <tr class="skp-loading">
                   <td colspan="7" class="text-center">
                       <span class="spinner is-active"></span>
                       <span class="loading-text">
                           <?php _e('Memuat riwayat status...', 'asosiasi'); ?>
                       </span>
                   </td>
               </tr>
           </tbody>
       </table>
   </div>
</div>