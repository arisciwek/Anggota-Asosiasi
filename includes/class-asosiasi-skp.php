<?php
// Add this at the beginning of the file after the initial member check
class Asosiasi_SKP {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'asosiasi_skp';
    }

    public function add_skp($data) {
        global $wpdb;
        
        // Handle file upload
        $upload_result = $this->handle_pdf_upload($data['pdf_file']);
        if (is_wp_error($upload_result)) {
            return $upload_result;
        }

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'member_id' => $data['member_id'],
                'skp_type' => $data['skp_type'],
                'skp_number' => $data['skp_number'],
                'name' => $data['name'],
                'issue_date' => $data['issue_date'],
                'expiry_date' => $data['expiry_date'],
                'pdf_url' => $upload_result['url'],
                'pdf_path' => $upload_result['path'],
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    public function update_skp($id, $data) {
        global $wpdb;
        
        $update_data = array(
            'skp_number' => $data['skp_number'],
            'name' => $data['name'],
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'],
            'updated_at' => current_time('mysql')
        );
        
        // Handle new PDF upload if provided
        if (!empty($data['pdf_file'])) {
            // Get old PDF to delete
            $old_skp = $this->get_skp($id);
            
            // Upload new PDF
            $upload_result = $this->handle_pdf_upload($data['pdf_file']);
            if (is_wp_error($upload_result)) {
                return $upload_result;
            }
            
            // Add new PDF paths to update data
            $update_data['pdf_url'] = $upload_result['url'];
            $update_data['pdf_path'] = $upload_result['path'];
            
            // Delete old PDF
            if ($old_skp) {
                $this->delete_pdf_file($old_skp['pdf_path']);
            }
        }

        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    }

    public function delete_skp($id) {
        global $wpdb;
        
        // Get SKP data to delete file
        $skp = $this->get_skp($id);
        if ($skp) {
            // Delete PDF file
            $this->delete_pdf_file($skp['pdf_path']);
            
            // Delete database record
            return $wpdb->delete(
                $this->table_name,
                array('id' => $id),
                array('%d')
            );
        }
        return false;
    }

    public function get_skp($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );
    }

    public function get_member_skps($member_id, $type = null) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name} WHERE member_id = %d";
        $params = array($member_id);
        
        if ($type) {
            $sql .= " AND skp_type = %s";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $wpdb->get_results(
            $wpdb->prepare($sql, $params),
            ARRAY_A
        );
    }

    private function handle_pdf_upload($file) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array('pdf' => 'application/pdf')
        );

        $moved_file = wp_handle_upload($file, $upload_overrides);

        if ($moved_file && !isset($moved_file['error'])) {
            return array(
                'url' => $moved_file['url'],
                'path' => $moved_file['file']
            );
        } else {
            return new WP_Error('upload_error', $moved_file['error']);
        }
    }

    private function delete_pdf_file($file_path) {
        if (file_exists($file_path)) {
            return wp_delete_file($file_path);
        }
        return false;
    }
}

// Add AJAX handlers for SKP operations
function handle_skp_operations() {
    add_action('wp_ajax_add_skp', 'ajax_add_skp');
    add_action('wp_ajax_update_skp', 'ajax_update_skp');
    add_action('wp_ajax_delete_skp', 'ajax_delete_skp');
    add_action('wp_ajax_get_skp_data', 'ajax_get_skp_data');
}
add_action('admin_init', 'handle_skp_operations');

function ajax_add_skp() {
    check_ajax_referer('skp_operations', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $skp = new Asosiasi_SKP();
    $result = $skp->add_skp($_POST);

    if ($result && !is_wp_error($result)) {
        wp_send_json_success(array(
            'message' => __('SKP berhasil ditambahkan', 'asosiasi'),
            'skp_id' => $result
        ));
    } else {
        wp_send_json_error($result->get_error_message());
    }
}

function ajax_update_skp() {
    check_ajax_referer('skp_operations', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $skp = new Asosiasi_SKP();
    $result = $skp->update_skp($_POST['id'], $_POST);

    if ($result && !is_wp_error($result)) {
        wp_send_json_success(__('SKP berhasil diperbarui', 'asosiasi'));
    } else {
        wp_send_json_error($result->get_error_message());
    }
}

function ajax_delete_skp() {
    check_ajax_referer('skp_operations', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $skp = new Asosiasi_SKP();
    $result = $skp->delete_skp($_POST['id']);

    if ($result) {
        wp_send_json_success(__('SKP berhasil dihapus', 'asosiasi'));
    } else {
        wp_send_json_error(__('Gagal menghapus SKP', 'asosiasi'));
    }
}

function ajax_get_skp_data() {
    check_ajax_referer('skp_operations', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $skp = new Asosiasi_SKP();
    $data = $skp->get_member_skps($_POST['member_id'], $_POST['type']);

    wp_send_json_success($data);
}

// Update modal form HTML and add necessary JavaScript
?>

<!-- Modal SKP Form -->
<div id="skp-modal" class="modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 50%; min-width: 300px; max-width: 600px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div class="modal-header" style="margin-bottom: 20px;">
            <h2 style="margin: 0; display: inline-block;" id="modal-title">Add SKP</h2>
            <span class="close" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
        </div>

        <form id="skp-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('skp_operations', 'skp_nonce'); ?>
            <input type="hidden" name="action" id="form_action" value="add_skp">
            <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
            <input type="hidden" name="skp_type" id="skp_type" value="">
            <input type="hidden" name="skp_id" id="skp_id" value="">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="skp_number">Nomor SKP</label></th>
                    <td><input type="text" id="skp_number" name="skp_number" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="name">Nama</label></th>
                    <td><input type="text" id="name" name="name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="issue_date">Tanggal Terbit</label></th>
                    <td><input type="date" id="issue_date" name="issue_date" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="expiry_date">Masa Berlaku</label></th>
                    <td><input type="date" id="expiry_date" name="expiry_date" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_file">File PDF</label></th>
                    <td>
                        <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                        <p class="description" id="pdf_current"></p>
                    </td>
                </tr>
            </table>

            <div class="submit-wrapper" style="text-align: right; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px;">
                <button type="button" class="button" onclick="closeSkpModal()">Cancel</button>
                <button type="submit" class="button button-primary">Save SKP</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize datepicker if available
    if ($.fn.datepicker) {
        $('#issue_date, #expiry_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
    }

    // Modal functions
    window.openSkpModal = function(type, data = null) {
        $('#skp_type').val(type);
        $('#modal-title').text((data ? 'Edit' : 'Add') + ' SKP ' + 
            (type === 'company' ? 'Perusahaan' : 'Tenaga Ahli'));
        
        if (data) {
            $('#form_action').val('update_skp');
            $('#skp_id').val(data.id);
            $('#skp_number').val(data.skp_number);
            $('#name').val(data.name);
            $('#issue_date').val(data.issue_date);
            $('#expiry_date').val(data.expiry_date);
            $('#pdf_file').prop('required', false);
            $('#pdf_current').html('Current file: ' + data.pdf_url.split('/').pop());
        } else {
            $('#skp-form')[0].reset();
            $('#form_action').val('add_skp');
            $('#skp_id').val('');
            $('#pdf_file').prop('required', true);
            $('#pdf_current').html('');
        }
        
        $('#skp-modal').show();
    };

    window.closeSkpModal = function() {
        $('#skp-modal').hide();
        $('#skp-form')[0].reset();
    };

    // Form submission
    $('#skp-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    closeSkpModal();
                    loadSkpData($('#skp_type').val());
                    alert(response.data.message || 'Operation successful');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Server error occurred');
            }
        });
    });

    // Delete SKP
    window.deleteSkp = function(id, type) {
        if (confirm('Are you sure you want to delete this SKP?')) {
            $.post(ajaxurl, {
                action: 'delete_skp',
                id: id,
                nonce: $('#skp_nonce').val()
            }, function(response) {
                if (response.success) {
                    loadSkpData(type);
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
    };

    // Load SKP data function
    window.loadSkpData = function(type) {
        $.post(ajaxurl, {
            action: 'get_skp_data',
            member_id: <?php echo $member_id; ?>,
            type: type,
            nonce: $('#skp_nonce').val()
        }, function(response) {
            if (response.success) {
                var tableBody = $('#' + type + '-skp-list');
                tableBody.empty();
                
                response.data.forEach(function(item, index) {
                    var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.skp_number}</td>
                            <td>${item.name}</td>
                            <td>${formatDate(item.issue_date)}</td>
                            <td>${formatDate(item.expiry_date)}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="${item.pdf_url}" target="_blank" class="button button-small" title="View PDF">
                                        <span class="dashicons dashicons-pdf"></span>
                                    </a>
                                    <button type="button" class="button button-small" 
                                            onclick="openSkpModal('${type}', ${JSON.stringify(item)})" 
                                            title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete" 
                                            onclick="deleteSkp(${item.id}, '${type}')" 
                                            title="Delete">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                if (response.data.length === 0) {
                    tableBody.append(`
                        <tr>
                            <td colspan="6" style="text-align: center;">
                                <em><?php _e('Belum ada data SKP', 'asosiasi'); ?></em>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    };

    // Helper function to format dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Initialize tooltips for action buttons
    function initTooltips() {
        $('.row-actions button, .row-actions a').each(function() {
            $(this).tooltip({
                position: {
                    my: "center bottom-10",
                    at: "center top"
                },
                show: {
                    effect: "fade",
                    duration: 200
                }
            });
        });
    }

    // Add validation for date inputs
    $('#issue_date, #expiry_date').on('change', function() {
        const issueDate = new Date($('#issue_date').val());
        const expiryDate = new Date($('#expiry_date').val());

        if (expiryDate < issueDate) {
            alert('Masa berlaku tidak boleh lebih awal dari tanggal terbit');
            $(this).val('');
        }
    });

    // Add file size validation
    $('#pdf_file').on('change', function() {
        const file = this.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (file && file.size > maxSize) {
            alert('Ukuran file tidak boleh lebih dari 5MB');
            this.value = '';
        }
    });

    // Style enhancements
    const style = `
        <style>
            .row-actions {
                display: flex;
                gap: 5px;
                justify-content: flex-start;
                align-items: center;
            }
            
            .row-actions button,
            .row-actions a {
                padding: 4px !important;
                min-width: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .row-actions .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }

            .row-actions .button-link-delete {
                color: #dc3232;
            }

            .row-actions .button-link-delete:hover {
                color: #a00;
            }

            .modal-content {
                animation: slideIn 0.3s ease-out;
            }

            @keyframes slideIn {
                from {
                    transform: translateY(-10%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            #skp-form .form-table th {
                width: 150px;
            }

            .pdf-preview {
                margin-top: 10px;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .loading {
                position: relative;
                opacity: 0.5;
                pointer-events: none;
            }

            .loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    $('head').append(style);

    // Initialize tooltips
    initTooltips();

    // Initial data load
    loadSkpData('company');
    loadSkpData('expert');

    // Close modal when clicking outside
    $(window).click(function(event) {
        if ($(event.target).is('#skp-modal')) {
            closeSkpModal();
        }
    });

    // Add loading state to form during submission
    $('#skp-form').on('submit', function() {
        const $form = $(this);
        $form.addClass('loading');
        
        // Remove loading state after completion
        setTimeout(function() {
            $form.removeClass('loading');
        }, 3000);
    });

    // Expose functions to window object for global access
    window.initTooltips = initTooltips;
});
</script>

<?php
// Add activation hook to create SKP table
register_activation_hook(__FILE__, 'create_skp_table');

function create_skp_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'asosiasi_skp';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        member_id mediumint(9) NOT NULL,
        skp_type VARCHAR(50) NOT NULL,
        skp_number VARCHAR(100) NOT NULL,
        name VARCHAR(255) NOT NULL,
        issue_date DATE NOT NULL,
        expiry_date DATE NOT NULL,
        pdf_url VARCHAR(255) NOT NULL,
        pdf_path VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY member_id (member_id),
        KEY skp_type (skp_type)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}