<?php
/**
 * Status Handler Class untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Includes/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/includes/skp-tenaga-ahli/class-asosiasi-status-skp-tenaga-ahli.php 
 *
 * Description: Menangani perubahan dan riwayat status SKP Tenaga Ahli
 *              termasuk validasi dan logging perubahan status
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added status update handling
 * - Added status history tracking
 * - Added status validation
 */

class Asosiasi_Status_Skp_Tenaga_Ahli {
    /**
     * Nama tabel database
     */
    private $table_skp;
    private $table_history;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        global $wpdb;
        $this->table_skp = $wpdb->prefix . 'asosiasi_skp_tenaga_ahli';
        $this->table_history = $wpdb->prefix . 'asosiasi_skp_status_history';
    }

    /**
     * Update status SKP
     * 
     * @param int    $id       ID SKP
     * @param string $status   Status baru
     * @param string $reason   Alasan perubahan
     * @param int    $user_id  ID user yang melakukan perubahan (optional)
     * @return bool|WP_Error
     */
    public function update_status($id, $status, $reason, $user_id = null) {
        global $wpdb;

        // Validate status
        $valid_statuses = array('active', 'activated', 'expired', 'inactive');
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error(
                'invalid_status', 
                __('Status tidak valid', 'asosiasi')
            );
        }

        // Get current SKP data
        $current_skp = $this->get_skp($id);
        if (!$current_skp) {
            return new WP_Error(
                'skp_not_found', 
                __('SKP tidak ditemukan', 'asosiasi')
            );
        }

        $old_status = $current_skp['status'];
        
        // Don't update if status hasn't changed
        if ($old_status === $status) {
            return true;
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Update SKP status
            $updated = $wpdb->update(
                $this->table_skp,
                array(
                    'status' => $status,
                    'status_changed_at' => current_time('mysql')
                ),
                array('id' => $id),
                array('%s', '%s'),
                array('%d')
            );

            if ($updated === false) {
                throw new Exception(
                    __('Gagal mengupdate status SKP', 'asosiasi')
                );
            }

            // Log status change
            $logged = $wpdb->insert(
                $this->table_history,
                array(
                    'skp_id' => $id,
                    'skp_type' => 'tenaga_ahli',
                    'old_status' => $old_status,
                    'new_status' => $status,
                    'reason' => $reason,
                    'changed_by' => $user_id ?: get_current_user_id(),
                    'changed_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s', '%s', '%d', '%s')
            );

            if ($logged === false) {
                throw new Exception(
                    __('Gagal mencatat riwayat perubahan status', 'asosiasi')
                );
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('status_update_failed', $e->getMessage());
        }
    }

    /**
     * Get SKP data
     *
     * @param int $id SKP ID
     * @return array|null
     */
    private function get_skp($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_skp} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
    }

    /**
     * Get status history for SKP
     *
     * @param int $id SKP ID
     * @return array
     */
    public function get_status_history($id) {
        global $wpdb;
        $users_table = $wpdb->base_prefix . 'users';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT h.*, 
                    u.display_name as changed_by_name
             FROM {$this->table_history} h
             LEFT JOIN {$users_table} u ON h.changed_by = u.ID
             WHERE h.skp_id = %d 
             AND h.skp_type = 'tenaga_ahli'
             ORDER BY h.changed_at DESC",
            $id
        ), ARRAY_A);
    }

    /**
     * Get available status list
     *
     * @param string $current_status Current status
     * @return array Array of available status transitions
     */
    public function get_available_statuses($current_status) {
        $transitions = array(
            'active' => array(
                array(
                    'value' => 'inactive',
                    'label' => __('Tidak Aktif', 'asosiasi')
                ),
                array(
                    'value' => 'expired',
                    'label' => __('Kadaluarsa', 'asosiasi')
                )
            ),
            'inactive' => array(
                array(
                    'value' => 'activated',
                    'label' => __('Diaktifkan', 'asosiasi')
                )
            ),
            'expired' => array(
                array(
                    'value' => 'activated',
                    'label' => __('Diaktifkan', 'asosiasi')
                )
            ),
            'activated' => array(
                array(
                    'value' => 'inactive',
                    'label' => __('Tidak Aktif', 'asosiasi')
                )
            )
        );

        return isset($transitions[$current_status]) 
            ? $transitions[$current_status] 
            : array();
    }

    /**
     * Get status label
     *
     * @param string $status Status key
     * @return string Translated label
     */
    public function get_status_label($status) {
        $labels = array(
            'active' => __('Aktif', 'asosiasi'),
            'activated' => __('Diaktifkan', 'asosiasi'),
            'expired' => __('Kadaluarsa', 'asosiasi'),
            'inactive' => __('Tidak Aktif', 'asosiasi')
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
