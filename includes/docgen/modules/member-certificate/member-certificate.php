<?php
/**
 * Test Certificate View
 * 
 * @package Asosiasi
 * Path: admin/views/test-certificate.php
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

// Get organization settings
$data = array(
    'nomor_sertifikat' => $member['nomor_sertifikat'] ?? '',
    'company_name' => $member['company_name'] ?? '',
    'company_leader' => $member['company_leader'] ?? '',
    'leader_position' => $member['leader_position'] ?? '',
    'business_field' => $member['business_field'] ?? '',
    'city' => $member['city'] ?? '',
    'company_address' => $member['company_address'] ?? '',
    'npwp' => $member['npwp'] ?? '',
    'issue_date' => $member['tanggal_cetak'] ?? current_time('mysql'),
    'ahu_number' => $member['ahu_number'] ?? '',
    
    // Organization settings
    'organization_name' => get_option('asosiasi_organization_name'),
    'ketua_umum' => get_option('asosiasi_ketua_umum'),
    'sekretaris_umum' => get_option('asosiasi_sekretaris_umum'),
    'contact_email' => get_option('asosiasi_contact_email'),
    'website' => get_option('asosiasi_website'),
    
    // Image paths - adjust path as needed
    'image:logo' => plugins_url('assets/images/logo-rui-02.png', dirname(__FILE__))
);

// Get member services
$services = $wpdb->get_results($wpdb->prepare(
    "SELECT s.short_name, s.full_name 
     FROM {$wpdb->prefix}asosiasi_member_services ms
     JOIN {$wpdb->prefix}asosiasi_services s ON ms.service_id = s.id
     WHERE ms.member_id = %d",
    $member_id
), ARRAY_A);

$services_list = array();
foreach ($services as $service) {
    $services_list[] = $service['short_name'] . ' - ' . $service['full_name'];
}
$data['services'] = implode(', ', $services_list);

// Include the certificate template
include_once dirname(__FILE__) . '/templates/certificate-template.php';