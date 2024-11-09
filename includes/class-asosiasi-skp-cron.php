<?php
/**
 * Handler untuk cron job SKP Perusahaan
 * 
 * @package Asosiasi
 * @version 1.0.0
 * Path: includes/class-asosiasi-skp-cron.php
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
        $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        $current_date = current_time('Y-m-d');

        // Update status SKP yang sudah expired
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
             SET status = 'expired',
                 status_changed_at = %s
             WHERE masa_berlaku < %s 
             AND status = 'active'",
            current_time('mysql'),
            $current_date
        ));

        // Optional: Kirim notifikasi untuk SKP yang akan expired dalam X hari
        self::send_expiry_notifications();
    }

    /**
     * Kirim notifikasi untuk SKP yang akan expired
     */
    private static function send_expiry_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        $members_table = $wpdb->prefix . 'asosiasi_members';
        
        // Check SKP yang akan expired dalam 30 hari
        $expiring_soon = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, m.email, m.company_name
             FROM $table_name s
             JOIN $members_table m ON s.member_id = m.id
             WHERE s.masa_berlaku BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND s.status = 'active'",
            current_time('Y-m-d')
        ));

        if ($expiring_soon) {
            foreach ($expiring_soon as $skp) {
                // Hitung sisa hari
                $expiry_date = new DateTime($skp->masa_berlaku);
                $today = new DateTime('today');
                $days_remaining = $today->diff($expiry_date)->days;

                // Kirim email notifikasi
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

                wp_mail($skp->email, $subject, $message);
                
                // Log notification
                if (WP_DEBUG) {
                    error_log(sprintf(
                        'Sent SKP expiry notification for member %s (SKP: %s, Expires: %s)',
                        $skp->company_name,
                        $skp->nomor_skp,
                        $skp->masa_berlaku
                    ));
                }
            }
        }
    }
}