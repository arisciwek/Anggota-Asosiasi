<?php
/**
 * Handler untuk cron job SKP
 * 
 * @package Asosiasi
 * @version 1.1.0
 * Path: includes/class-asosiasi-skp-cron.php
 * 
 * Changelog:
 * 1.1.0 - 2024-11-22
 * - Added SKP Tenaga Ahli status check
 * - Modified notification to handle both SKP types
 * - Improved error logging
 * 1.0.0 - Initial version with company SKP only
 */

class Asosiasi_SKP_Cron {
    /**
     * Schedule cron events
     */
    public static function schedule_events() {
        if (!wp_next_scheduled('asosiasi_daily_skp_check')) {
            wp_schedule_event(strtotime('today midnight'), 'daily', 'asosiasi_daily_skp_check');
        }
    }

    /**
     * Unschedule cron events
     */
    public static function unschedule_events() {
        $timestamp = wp_next_scheduled('asosiasi_daily_skp_check');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'asosiasi_daily_skp_check');
        }
    }

    /**
     * Check and update SKP status
     */
    public static function check_skp_status() {
        global $wpdb;
        $current_date = current_time('Y-m-d');
        
        // Check SKP Perusahaan
        $table_perusahaan = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_perusahaan 
             SET status = 'expired',
                 status_changed_at = %s
             WHERE masa_berlaku < %s 
             AND status = 'active'",
            current_time('mysql'),
            $current_date
        ));

        // Check SKP Tenaga Ahli
        $table_tenaga_ahli = $wpdb->prefix . 'asosiasi_skp_tenaga_ahli';
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_tenaga_ahli 
             SET status = 'expired',
                 status_changed_at = %s
             WHERE masa_berlaku < %s 
             AND status = 'active'",
            current_time('mysql'),
            $current_date
        ));

        // Send notifications for both types
        self::send_expiry_notifications();
    }

    /**
     * Kirim notifikasi untuk SKP yang akan expired
     */
    private static function send_expiry_notifications() {
        global $wpdb;
        $members_table = $wpdb->prefix . 'asosiasi_members';
        $current_date = current_time('Y-m-d');

        // Get expiring SKP Perusahaan
        $expiring_perusahaan = self::get_expiring_skp('asosiasi_skp_perusahaan', 30);
        
        // Get expiring SKP Tenaga Ahli
        $expiring_tenaga_ahli = self::get_expiring_skp('asosiasi_skp_tenaga_ahli', 30);

        // Send notifications
        foreach ($expiring_perusahaan as $skp) {
            self::send_notification($skp, 'perusahaan');
        }

        foreach ($expiring_tenaga_ahli as $skp) {
            self::send_notification($skp, 'tenaga_ahli');
        }
    }

    /**
     * Get expiring SKP from specific table
     */
    private static function get_expiring_skp($table, $days) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $members_table = $wpdb->prefix . 'asosiasi_members';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, m.email, m.company_name
             FROM $table_name s
             JOIN $members_table m ON s.member_id = m.id
             WHERE s.masa_berlaku BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)
             AND s.status = 'active'",
            $days
        ));
    }

    /**
     * Send notification email for expiring SKP
     */
    private static function send_notification($skp, $type) {
        // Calculate remaining days
        $expiry_date = new DateTime($skp->masa_berlaku);
        $today = new DateTime('today');
        $days_remaining = $today->diff($expiry_date)->days;

        // Prepare subject and message based on SKP type
        if ($type === 'perusahaan') {
            $subject = sprintf(
                __('[%s] SKP Perusahaan akan berakhir dalam %d hari', 'asosiasi'),
                get_bloginfo('name'),
                $days_remaining
            );

            $message = sprintf(
                __('SKP Perusahaan untuk %s dengan nomor %s akan berakhir pada %s.', 'asosiasi'),
                $skp->company_name,
                $skp->nomor_skp,
                date_i18n(get_option('date_format'), strtotime($skp->masa_berlaku))
            );
        } else {
            $subject = sprintf(
                __('[%s] SKP Tenaga Ahli akan berakhir dalam %d hari', 'asosiasi'),
                get_bloginfo('name'),
                $days_remaining
            );

            $message = sprintf(
                __('SKP Tenaga Ahli %s (%s) untuk %s dengan nomor %s akan berakhir pada %s.', 'asosiasi'),
                $skp->nama_tenaga_ahli,
                $skp->penanggung_jawab,
                $skp->company_name,
                $skp->nomor_skp,
                date_i18n(get_option('date_format'), strtotime($skp->masa_berlaku))
            );
        }

        // Send email
        wp_mail($skp->email, $subject, $message);
        
        // Log notification if debug enabled
        if (WP_DEBUG) {
            error_log(sprintf(
                'Sent %s SKP expiry notification for member %s (SKP: %s, Expires: %s)',
                $type,
                $skp->company_name,
                $skp->nomor_skp,
                $skp->masa_berlaku
            ));
        }
    }
}
