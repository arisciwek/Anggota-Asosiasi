<?php
/**
 * Member Card View
 * 
 * @package Asosiasi
 * Path: includes/modules/member-certificate/member-card.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Get member ID from URL
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$member_id) {
    wp_die(__('Invalid member ID', 'asosiasi'));
}

// Initialize required objects
global $wpdb;
$member = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}asosiasi_members WHERE id = %d",
    $member_id
), ARRAY_A);

if (!$member) {
    wp_die(__('Member not found', 'asosiasi'));
}
// Get upload directory path
$upload_dir = wp_upload_dir();

// Get organization settings and prepare data
$data = array(
    'nomor_sertifikat' => $member['nomor_sertifikat'] ?? '',
    'company_name' => $member['company_name'] ?? '',
    'company_leader' => $member['company_leader'] ?? '',
    'leader_position' => $member['leader_position'] ?? '',
    'city' => $member['city'] ?? '',
    'company_address' => $member['company_address'] ?? '',
    'issue_date' => $member['tanggal_cetak'] ?? current_time('mysql'),
    'valid_until' => $member['valid_until'] ?? '',
    
    // Organization settings
    'organization_name' => get_option('asosiasi_organization_name'),
    'ketua_umum' => get_option('asosiasi_ketua_umum'),
    'contact_email' => get_option('asosiasi_contact_email'),
    'website' => get_option('asosiasi_website'),
    
    // Image paths
    'image:logo' => $upload_dir['basedir'] . '/asosiasi/logo-rui-02.png',
    'image:ttd_ketua' => $upload_dir['basedir'] . '/asosiasi/ttd-ketua-02.png',
    'image:member_card_pattern' => $upload_dir['basedir'] . '/asosiasi/watermark-card-pattern.svg'

);

// Generate QR Code data
$verification_code = base64_encode($member_id . '_' . time());
$verification_url = add_query_arg([
    'certificate_verify' => 1,
    'member_id' => $member_id,
    'verify_code' => $verification_code
], home_url());

$data['qr_data'] = $verification_url;

// Include the card template
include_once dirname(__FILE__) . '/templates/member-card-template.php';
