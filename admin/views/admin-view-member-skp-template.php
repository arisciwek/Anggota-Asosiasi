<?php
if (!defined('ABSPATH')) {
    exit;
}




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
    </div>
    <?php
}
?>