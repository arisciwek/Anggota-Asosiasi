<?php
/**
 * Member Certificate Provider
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/providers/class-member-certificate-provider.php
 * 
 * Description: Provider untuk generate sertifikat anggota asosiasi.
 *              Mengambil data dari database dan memformat untuk template.
 * 
 * Dependencies:
 * - class-asosiasi-docgen-provider.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Member_Certificate_Provider extends Asosiasi_DocGen_Provider {
    /**
     * ID anggota
     * @var int
     */
    private $member_id;

    /**
     * Constructor
     * @param int $member_id ID anggota
     */
    public function __construct($member_id) {
        parent::__construct();
        
        $this->member_id = $member_id;
        $this->load_member_data();
    }

    /**
     * Load data anggota dari database
     */
    private function load_member_data() {
        global $wpdb;

        $member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}asosiasi_members WHERE id = %d",
            $this->member_id
        ), ARRAY_A);

        if (!$member) {
            throw new Exception('Member not found');
        }

        $this->data = $member;
    }

    /**
     * Get template name
     * @return string
     */
    protected function get_template_name() {
        return 'member-certificate.docx';
    }

    /**
     * Get document identifier
     * @return string
     */
    protected function get_document_identifier() {
        return 'sertifikat-anggota-' . $this->member_id;
    }

    /**
     * Get source identifier
     * @return string
     */
    protected function get_source_identifier() {
        return 'member';
    }

    /**
     * Get data untuk template
     * @return array
     */
    public function get_data() {
        // Format tanggal Indonesia
        $join_date = new DateTime($this->data['join_date']);
        setlocale(LC_TIME, 'id_ID');
        
        return array(
            'nomor_anggota' => $this->data['member_number'],
            'nama_perusahaan' => $this->data['company_name'],
            'alamat' => $this->data['company_address'],
            'kota' => $this->data['city'],
            'tanggal_bergabung' => strftime('%d %B %Y', $join_date->getTimestamp()),
            'pimpinan' => $this->data['company_leader'],
            'jabatan' => $this->data['leader_position'],
            'npwp' => $this->data['npwp'],
            'bidang_usaha' => $this->data['business_field'],
            
            // Metadata
            'tanggal_cetak' => '${date:' . date('Y-m-d H:i:s') . ':j F Y}',
            'dicetak_oleh' => '${user:display_name}',
            'nomor_sertifikat' => $this->generate_certificate_number()
        );
    }

    /**
     * Generate nomor sertifikat
     * @return string
     */
    private function generate_certificate_number() {
        $year = date('Y');
        $month = date('m');
        
        return sprintf(
            'CERT/%s/%s/%04d',
            $this->data['member_number'],
            $year,
            $this->member_id
        );
    }
}