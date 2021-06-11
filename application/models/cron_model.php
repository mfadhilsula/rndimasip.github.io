<?php

/**
 * AMS Archive Management System
 * 
 * 
 * PHP version 5
 * 
 * @category   AMS
 * @package    CI
 * @subpackage Model
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @version    GIT: <$Id>
 * @link       https://github.com/avpreserve/AMS
 */

/**
 * Cron_Model Class
 *
 * @category   Class
 * @package    CI
 * @subpackage Model
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @link       https://ams.americanarchive.org
 */
class Cron_Model extends CI_Model {

    /**
     * constructor. set table name amd prefix
     * 
     */
    function __construct() {
        parent::__construct();
        $this->db->save_queries = FALSE;
        $this->_prefix = '';
        $this->_table = 'process_pbcore_data';
        $this->_table_data_folders = 'data_folders';
        $this->_table_rotate_indexes = 'rotate_indexes';
    }

    /**
     * Get process_pbcore_data through file_path
     * 
     * @param string $file_path
     * 
     * @return boolean
     */
    function get_pbcore_file_by_path($file_path) {
        $this->db->select("*");
        $this->db->from($this->_table);
        $this->db->where("file_path", $file_path);
        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            return $res->row();
        }
        return false;
    }

    /**
     * Get process_pbcore_data through file_path.
     * 
     * @param string $file_path
     * @param integer $data_folder_id
     * 
     * @return boolean
     */
    function is_pbcore_file_by_path($file_path, $data_folder_id) {
        $this->db->select("COUNT(id) AS total");
        $this->db->from($this->_table);
        $this->db->where("file_path", $file_path);
        $this->db->where("data_folder_id", $data_folder_id);
        $this->db->limit(1);
        $res = $this->db->get()->row();
        if (isset($res) && $res->total > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get process_pbcore_data through data_folder_id
     * 
     * @param integer $data_folder_id
     * @param integer $offset
     * @param integer $limit
     * 
     * @return boolean
     */
    function get_pbcore_file_by_folder_id($data_folder_id, $offset = 0, $limit = 20) {
        $this->db->select("*");
        $this->db->from($this->_table);
        $this->db->where("data_folder_id ", $data_folder_id);
        $this->db->where("is_processed ", 0);
        $this->db->like("status_reason", 'Not processed');
        if ($offset)
            $this->db->limit($limit, $offset);
        $res = $this->db->get();
        if (isset($res)) {
            return $res->result();
        }
        return false;
    }

    /**
     * Get process_pbcore_data un-processed files count through data_folder_id
     * 
     * @param integer $data_folder_id
     * 
     * @return boolean
     */
    function get_pbcore_file_count_by_folder_id($data_folder_id) {
        $ids = explode(',', $data_folder_id);
        $this->db->select("COUNT(id) AS total");
        $this->db->from($this->_table);
        $this->db->where_in("data_folder_id ", $ids);
        $this->db->where("is_processed ", 0);
        $this->db->where("processed_start_at", '0000-00-00 00:00:00');
        $this->db->like("status_reason", 'Not processed');

        $res = $this->db->get();
        if (isset($res)) {
            $row = $res->row();
            return $row->total;
        }
        return false;
    }

    /**
     * Get data folder through folder_path.
     * 
     * @param string $folder_path
     * @return boolean
     */
    function get_data_folder_id_by_path($folder_path) {
        $this->db->select("id");
        $this->db->from($this->_table_data_folders);
        $this->db->where("folder_path LIKE ", $folder_path);
        $this->db->limit(1);
        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            $folder = $res->row();
            if (isset($folder) && !empty($folder)) {
                return $folder->id;
            }
        }
        return false;
    }

    /**
     * Get all data folder.
     * 
     * @return boolean
     */
    function get_all_data_folder() {
        $this->db->select("*");
        $this->db->from($this->_table_data_folders);
        $this->db->where('folder_status', 'complete');
        $this->db->where('data_type', 'assets');
        $this->db->order_by('id', 'ASC');
        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            $folders = $res->result();
            if (isset($folders) && !empty($folders)) {
                return $folders;
            }
        }
        return false;
    }

    /**
     * Get all the PBCore 2 folders whose status is complete.
     * 
     * @return boolean
     */
    function get_all_pbcoretwo_folder() {
        $this->db->select("*");
        $this->db->from($this->_table_data_folders);
        $this->db->where('folder_status', 'complete');
        $this->db->where('data_type', 'pbcore2');
        $this->db->order_by('id', 'ASC');
        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            $folders = $res->result();
            if (isset($folders) && !empty($folders)) {
                return $folders;
            }
        }
        return false;
    }

    /**
     * Get all the Mediainfo folders whose status is complete.
     * 
     * @return boolean
     */
    function get_all_mediainfo_folder() {
        $this->db->select("*");
        $this->db->from($this->_table_data_folders);
//        $this->db->where('id', 160761); 
        $this->db->where('folder_status', 'complete');
        $this->db->where('data_type', 'mediainfo');
        $this->db->order_by('id', 'ASC');

        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            $folders = $res->result();
            if (isset($folders) && !empty($folders)) {
                return $folders;
            }
        }
        return false;
    }

    function get_all_incomp_mediainfo_folder($status) {
        $this->db->select("*");
        $this->db->from($this->_table_data_folders);
        $this->db->where('folder_status', $status);
        $this->db->where('data_type', 'mediainfo');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }

    function update_mediainfo_folder($folder_id) {
        $this->db->where('id', $folder_id);
        $this->db->update($this->_table_data_folders, array('is_processed' => 1));
    }

    /**
     * Get all data folder
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    function get_data_folder_by_id($id) {
        $this->db->select('*');
        $this->db->from($this->_table_data_folders);
        $this->db->where('id', $id);
        $res = $this->db->get();
        if (isset($res) && !empty($res)) {
            $folders = $res->row();
            if (isset($folders) && !empty($folders)) {
                return $folders;
            }
        }
        return false;
    }

    /**
     * Insert data into process_pbcore_data
     * 
     * @param array $data
     * @return integer
     */
    function insert_prcoess_data($data) {
        $this->db->insert($this->_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update data into process_pbcore_data
     * 
     * @param array $data
     * @param integer $file_id
     */
    function update_prcoess_data($data, $file_id) {
        $this->db->where('id', $file_id);
        $this->db->update($this->_table, $data);
    }

    /**
     * Insert data folder
     * 
     * @param array $data
     * @return integer
     */
    function insert_data_folder($data) {
        $this->db->insert($this->_table_data_folders, $data);
        return $this->db->insert_id();
    }

    /**
     * Update data folder.
     * 
     * @param array $data
     * @param integer $id
     */
    function update_data_folder($data, $id) {
        $this->db->where('id', $id);
        $this->db->update($this->_table_data_folders, $data);
    }

    /**
     * Scan Directory and Store Path in process_pbcore_data.
     * 
     * @param string $dir
     * @param array $my_data_array
     */
    function scan_directory($dir, &$my_data_array) {
        $dir = rtrim(trim($dir, '\\'), '/') . '/';
        $d = @opendir($dir);

        if (!$d) {
            die('The directory ' . $dir . ' does not exists or PHP have no access to it<br>');
        }
        while (false !== ($file = @readdir($d))) {
            if ($file != '.' && $file != '..') {

                if (is_file($dir . $file) && $file === 'manifest-md5.txt') {
                    $my_data_array[] = $dir;
                } else {

                    if (is_dir($dir . $file) && strpos($dir, 'data') === false) {
                        $this->scan_directory($dir . $file, $my_data_array);
                    } else {
                        continue;
                    }
                }
            }
        }

        @closedir($d);
    }

    /**
     * Get process_pbcore_data by type and is_processed=0
     * 
     * @param string $type
     * @param integer $is_processed
     * 
     * @return boolean
     */
    function get_pbcore_data_type_is_processed($type, $is_processed = 0) {
        $this->db->select("*");
        $this->db->from($this->_table);
        $this->db->where("type LIKE ", $file_type);
        $this->db->where("is_processed ", $is_processed);
        $res = $this->db->get();
        if (isset($res)) {
            return $res->result();
        }
        return false;
    }

    /**
     * Scan directory of mint.
     * 
     * @param string $dir
     * @param array $my_data_array
     */
    function scan_mint_directory($dir, &$my_data_array) {
        $dir = rtrim(trim($dir, '\\'), '/') . '/';
        $d = @opendir($dir);

        if (!$d) {
            die('The directory ' . $dir . ' does not exists or PHP have no access to it<br>');
        }
        while (false !== ($file = @readdir($d))) {
            if ($file != '.' && $file != '..') {

                if (is_file($dir . $file)) {
                    $my_data_array[] = $dir;
                } else {

                    $this->scan_directory($dir . $file, $my_data_array);
                }
            }
        }

        @closedir($d);
    }

}
