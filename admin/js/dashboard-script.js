/**
 * Dashboard specific JavaScript functionality
 *
 * @package Asosiasi
 * @version 2.1.0
 * Path: admin/js/dashboard-script.js
 * 
 * Changelog:
 * 2.1.0 - 2024-03-14 
 * - Added real-time search filtering
 * - Enhanced service tag tooltips
 * - Added flash message handling
 * 2.0.0 - Initial dashboard functionality
 */

jQuery(document).ready(function($) {
    'use strict';

    // Fungsi untuk menginisialisasi fitur-fitur dashboard
    function initDashboard() {
        initSearch();
        initTooltips();
        initDeleteConfirmation();
        initFlashMessages();
    }

    // Fungsi pencarian real-time
    function initSearch() {
        $('#member-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#member-list tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    // Fungsi untuk tooltip layanan
    function initTooltips() {
        $('.service-tag').hover(
            function() {
                if (!$(this).find('.tooltip').length) {
                    var tooltip = $('<div class="tooltip">' + $(this).attr('title') + '</div>');
                    $(this).append(tooltip);
                }
            },
            function() {
                $(this).find('.tooltip').remove();
            }
        );
    }

    // Fungsi konfirmasi penghapusan
    function initDeleteConfirmation() {
        $('.button-link-delete').on('click', function(e) {
            if (typeof asosiasiAdmin !== 'undefined' && asosiasiAdmin.strings.confirmDelete) {
                if (!confirm(asosiasiAdmin.strings.confirmDelete)) {
                    e.preventDefault();
                    return false;
                }
            } else {
                if (!confirm('Yakin ingin menghapus?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Fungsi untuk menangani flash messages
    function initFlashMessages() {
        var $notices = $('.notice.is-dismissible');
        if ($notices.length) {
            $notices.each(function() {
                var $notice = $(this);
                
                // Tambahkan tombol dismiss jika belum ada
                if (!$notice.find('.notice-dismiss').length) {
                    var dismissButton = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
                    $notice.append(dismissButton);
                }
                
                // Handle tombol dismiss
                $notice.on('click', '.notice-dismiss', function() {
                    $notice.fadeOut(300, function() { 
                        $(this).remove(); 
                    });
                });
            });
            
            // Auto hide setelah 5 detik
            setTimeout(function() {
                $notices.each(function() {
                    $(this).fadeOut(300, function() { 
                        $(this).remove(); 
                    });
                });
            }, 5000);
        }
    }

    // Inisialisasi saat dokumen siap
    try {
        initDashboard();
    } catch (error) {
        console.error('Error initializing dashboard:', error);
    }

    // Handle window resize untuk tooltip
    $(window).on('resize', function() {
        $('.tooltip').remove();
    });

});