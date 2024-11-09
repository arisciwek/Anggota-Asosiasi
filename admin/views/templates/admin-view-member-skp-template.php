<?php
if (!defined('ABSPATH')) {
    exit;
}

$member_id = isset($_GET['amp;id']) ? intval($_GET['amp;id']) : 0;
$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$member = $crud->get_member($member_id);

if ($member) {
    $member_services = $services->get_member_services($member_id);
    ?>
    <div class="wrap">
            <!-- Right Column -->
            <div style="flex: 1;">
                <!-- SKP Perusahaan -->
                <fieldset class="card" style="margin-top: 20px; border: 1px solid #ccd0d4; padding: 0;">
                    <legend style="padding: 0 10px; margin-left: 10px;">
                        <h3 style="margin: 0;"><?php _e('SKP Perusahaan', 'asosiasi'); ?></h3>
                    </legend>
                    <div class="inside" style="padding: 10px;">
                        <button type="button" class="button add-skp-btn" data-type="company" style="margin-bottom: 10px;">
                            <?php _e('Add SKP', 'asosiasi'); ?>
                        </button>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nomor SKP</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Tanggal Terbit</th>
                                    <th>Masa Berlaku</th>
                                    <th style="width: 100px;">PDF</th>
                                </tr>
                            </thead>
                            <tbody id="company-skp-list">
                                <!-- Data SKP Perusahaan akan di-load di sini -->
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                <!-- SKP Tenaga Ahli -->
                <fieldset class="card" style="margin-top: 20px; border: 1px solid #ccd0d4; padding: 0;">
                    <legend style="padding: 0 10px; margin-left: 10px;">
                        <h3 style="margin: 0;"><?php _e('SKP Tenaga Ahli', 'asosiasi'); ?></h3>
                    </legend>
                    <div class="inside" style="padding: 10px;">
                        <button type="button" class="button add-skp-btn" data-type="expert" style="margin-bottom: 10px;">
                            <?php _e('Add SKP', 'asosiasi'); ?>
                        </button>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nomor SKP</th>
                                    <th>Nama Tenaga Ahli</th>
                                    <th>Tanggal Terbit</th>
                                    <th>Masa Berlaku</th>
                                    <th style="width: 100px;">PDF</th>
                                </tr>
                            </thead>
                            <tbody id="expert-skp-list">
                                <!-- Data SKP Tenaga Ahli akan di-load di sini -->
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
        </div>

        <!-- Modal Form SKP -->
        <div id="skp-modal" class="modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 50%; min-width: 300px; max-width: 600px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div class="modal-header" style="margin-bottom: 20px;">
                    <h2 style="margin: 0; display: inline-block;" id="modal-title">Add SKP</h2>
                    <span class="close" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
                </div>

                <form id="skp-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                    <input type="hidden" name="skp_type" id="skp_type" value="">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="skp_number">Nomor SKP</label></th>
                            <td><input type="text" id="skp_number" name="skp_number" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="skp_name">Nama</label></th>
                            <td><input type="text" id="skp_name" name="skp_name" class="regular-text" required></td>
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
                            <td><input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required></td>
                        </tr>
                    </table>

                    <div class="submit-wrapper" style="text-align: right; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px;">
                        <button type="button" class="button" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="button button-primary">Save SKP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>