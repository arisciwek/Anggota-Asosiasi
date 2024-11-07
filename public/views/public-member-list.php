<?php
/**
 * Template untuk menampilkan daftar anggota di halaman publik
 *
 * @package Asosiasi
 * @version 1.3.0
 */

if (!defined('ABSPATH')) {
    die;
}

$organization_name = get_option('asosiasi_organization_name');
$layout = isset($atts['layout']) ? $atts['layout'] : 'list';
$services_obj = new Asosiasi_Services();
?>

<div class="asosiasi-member-list" data-layout="<?php echo esc_attr($layout); ?>">
    <?php if ($organization_name): ?>
        <h2><?php printf(__('Anggota %s', 'asosiasi'), esc_html($organization_name)); ?></h2>
    <?php else: ?>
        <h2><?php _e('Daftar Anggota Asosiasi', 'asosiasi'); ?></h2>
    <?php endif; ?>

    <?php 
    if (!isset($members)) {
        $crud = new Asosiasi_CRUD();
        $members = $crud->get_members();
    }
    
    if ($members && !empty($members)): ?>
        <div class="asosiasi-member-filter">
            <input type="text" 
                   class="asosiasi-search-input" 
                   placeholder="<?php esc_attr_e('Cari anggota atau layanan...', 'asosiasi'); ?>"
                   aria-label="<?php esc_attr_e('Cari anggota atau layanan', 'asosiasi'); ?>">
        </div>

        <?php if ($layout === 'grid'): ?>
            <div class="asosiasi-member-grid">
                <?php foreach ($members as $member): ?>
                    <div class="asosiasi-member-card">
                        <div class="asosiasi-member-header">
                            <h3><?php echo esc_html($member['company_name']); ?></h3>
                        </div>
                        <div class="asosiasi-member-content">
                            <p class="asosiasi-member-info">
                                <span class="info-label"><?php _e('Kontak:', 'asosiasi'); ?></span>
                                <span class="info-value"><?php echo esc_html($member['contact_person']); ?></span>
                            </p>
                            <p class="asosiasi-member-info">
                                <span class="info-label"><?php _e('Email:', 'asosiasi'); ?></span>
                                <span class="info-value">
                                    <a href="mailto:<?php echo esc_attr($member['email']); ?>">
                                        <?php echo esc_html($member['email']); ?>
                                    </a>
                                </span>
                            </p>
                            <?php if (!empty($member['phone'])): ?>
                                <p class="asosiasi-member-info">
                                    <span class="info-label"><?php _e('Telepon:', 'asosiasi'); ?></span>
                                    <span class="info-value">
                                        <a href="tel:<?php echo esc_attr($member['phone']); ?>">
                                            <?php echo esc_html($member['phone']); ?>
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                            <?php 
                            $member_services = $services_obj->get_member_services($member['id']);
                            if ($member_services): ?>
                                <div class="asosiasi-member-services">
                                    <span class="info-label"><?php _e('Layanan:', 'asosiasi'); ?></span>
                                    <div class="service-tags">
                                        <?php foreach ($member_services as $service_id):
                                            $service = $services_obj->get_service($service_id);
                                            if ($service): ?>
                                                <span class="service-tag" title="<?php echo esc_attr($service['full_name']); ?>">
                                                    <?php echo esc_html($service['short_name']); ?>
                                                </span>
                                            <?php endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <ul class="asosiasi-member-list-items">
                <?php foreach ($members as $member): ?>
                    <li class="asosiasi-member-item">
                        <strong class="member-company"><?php echo esc_html($member['company_name']); ?></strong>
                        <div class="asosiasi-member-details">
                            <div class="member-info">
                                <span class="info-label"><?php _e('Kontak:', 'asosiasi'); ?></span>
                                <span class="info-value"><?php echo esc_html($member['contact_person']); ?></span>
                            </div>
                            <div class="member-info">
                                <span class="info-label"><?php _e('Email:', 'asosiasi'); ?></span>
                                <span class="info-value">
                                    <a href="mailto:<?php echo esc_attr($member['email']); ?>">
                                        <?php echo esc_html($member['email']); ?>
                                    </a>
                                </span>
                            </div>
                            <?php if (!empty($member['phone'])): ?>
                                <div class="member-info">
                                    <span class="info-label"><?php _e('Telepon:', 'asosiasi'); ?></span>
                                    <span class="info-value">
                                        <a href="tel:<?php echo esc_attr($member['phone']); ?>">
                                            <?php echo esc_html($member['phone']); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php 
                            $member_services = $services_obj->get_member_services($member['id']);
                            if ($member_services): ?>
                                <div class="member-services">
                                    <span class="info-label"><?php _e('Layanan:', 'asosiasi'); ?></span>
                                    <div class="service-tags">
                                        <?php foreach ($member_services as $service_id):
                                            $service = $services_obj->get_service($service_id);
                                            if ($service): ?>
                                                <span class="service-tag" title="<?php echo esc_attr($service['full_name']); ?>">
                                                    <?php echo esc_html($service['short_name']); ?>
                                                </span>
                                            <?php endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <p class="asosiasi-no-members">
            <?php _e('Belum ada anggota yang terdaftar.', 'asosiasi'); ?>
        </p>
    <?php endif; ?>
</div>