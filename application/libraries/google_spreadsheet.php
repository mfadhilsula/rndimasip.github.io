<?php

/*

  Copyright (c) 2009 Dimas Begunoff, http://www.farinspace.com/

  Permission is hereby granted, free of charge, to any person obtaining
  a copy of this software and associated documentation files (the
  "Software"), to deal in the Software without restriction, including
  without limitation the rights to use, copy, modify, merge, publish,
  distribute, sublicense, and/or sell copies of the Software, and to
  permit persons to whom the Software is furnished to do so, subject to
  the following conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
  OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
  WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

 */

class Google_Spreadsheet {

    private $client;
    private $spreadsheet;
    private $spreadsheet_id;
    private $worksheet = "";
    private $worksheet_id;
    var $user = '';
    var $pass = '';
    var $ss = false;
    var $ws = false;

    function __construct($config = array()) {
        $this->ci = & get_instance();
        $this->ci->load->library('zend');
        $this->user = $this->ci->config->item('google_spreadsheet_email');
        $this->pass = $this->ci->config->item('google_spreadsheet_password');
        if (count($config) > 0) {
            $this->initialize($config);
        }

        $this->ci->zend->load('Zend/Http/Client');
        $this->ci->zend->load('Zend/Gdata');
        $this->ci->zend->load('Zend/Gdata/AuthSub');
        $this->ci->zend->load('Zend/Gdata/Gbase');
        $this->ci->zend->load('Zend/Gdata/AuthSub');
        $this->ci->zend->load('Zend/Gdata/Spreadsheets');
        $this->ci->zend->load('Zend/Gdata/ClientLogin');
        $this->ci->zend->load('Zend/Gdata/OAuthClient');
        if (!isset($this->client)) {
            $this->login($this->user, base64_decode($this->pass));
        }
        if ($this->ss)
            $this->useSpreadsheet($this->ss);
        if ($this->ws)
            $this->useWorksheet($this->ws);
    }

    /**
     * Initialize preferences
     *
     * @access	public
     * @param	array
     * @return	void
     */
    public function initialize($config = array()) {
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $method = 'set_' . $key;

                if (method_exists($this, $method)) {
                    $this->$method($val);
                } else {
                    $this->$key = $val;
                }
            }
        }
        return $this;
    }

    function useSpreadsheet($ss, $ws = FALSE) {
        $this->spreadsheet = $ss;
        $this->spreadsheet_id = NULL;
        if ($ws)
            $this->useWorksheet($ws);
    }

    function useWorksheet($ws) {
        $this->worksheet = $ws;
        $this->worksheet_id = NULL;
    }

    function addRow($row) {
        if ($this->client instanceof Zend_Gdata_Spreadsheets) {
            $ss_id = $this->getSpreadsheetId($this->spreadsheet);

            if (!$ss_id)
                throw new Exception('Unable to find spreadsheet by name: "' . $this->spreadsheet . '", confirm the name of the spreadsheet');

            $ws_id = $this->getWorksheetId($ss_id, $this->worksheet);

            if (!$ws_id)
                throw new Exception('Unable to find worksheet by name: "' . $this->worksheet . '", confirm the name of the worksheet');

            $insert_row = array();

            foreach ($row as $k => $v)
                $insert_row[$this->cleanKey($k)] = $v;

            $entry = $this->client->insertRow($insert_row, $ss_id, $ws_id);

            if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)
                return TRUE;
        }

        throw new Exception('Unable to add row to the spreadsheet');
    }

    // http://code.google.com/apis/spreadsheets/docs/2.0/reference.html#ListParameters
    function updateRow($row, $search) {
        if ($this->client instanceof Zend_Gdata_Spreadsheets AND $search) {
            $feed = $this->findRows($search);

            if ($feed->entries) {
                foreach ($feed->entries as $entry) {
                    if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                        $update_row = array();

                        $customRow = $entry->getCustom();
                        foreach ($customRow as $customCol) {
                            $update_row[$customCol->getColumnName()] = $customCol->getText();
                        }

                        // overwrite with new values
                        foreach ($row as $k => $v) {
                            $update_row[$this->cleanKey($k)] = $v;
                        }

                        // update row data, then save
                        $entry = $this->client->updateRow($entry, $update_row);
                        if (!($entry instanceof Zend_Gdata_Spreadsheets_ListEntry))
                            return FALSE;
                    }
                }

                return TRUE;
            }
        }

        return FALSE;
    }

    // http://code.google.com/apis/spreadsheets/docs/2.0/reference.html#ListParameters
    function getRows($search = FALSE) {
        $rows = array();

        if ($this->client instanceof Zend_Gdata_Spreadsheets) {
            $feed = $this->findRows($search);

            if ($feed->entries) {
                foreach ($feed->entries as $entry) {
                    if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                        $row = array();

                        $customRow = $entry->getCustom();
                        foreach ($customRow as $customCol) {
                            $row[$customCol->getColumnName()] = $customCol->getText();
                        }

                        $rows[] = $row;
                    }
                }
            }
        }

        return $rows;
    }

    // user contribution by dmon (6/10/2009)
    function deleteRow($search) {
        if ($this->client instanceof Zend_Gdata_Spreadsheets && $search) {
            $feed = $this->findRows($search);

            if ($feed->entries) {
                foreach ($feed->entries as $entry) {
                    if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                        $this->client->deleteRow($entry);

                        if (!($entry instanceof Zend_Gdata_Spreadsheets_ListEntry))
                            return FALSE;
                    }
                }

                return TRUE;
            }
        }

        return FALSE;
    }

    function getColumnNames() {
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($this->getSpreadsheetId());
        $query->setWorksheetId($this->getWorksheetId());
        $query->setMaxResults(1);
        $query->setStartIndex(1);

        $feed = $this->client->getListFeed($query);

        $data = array();

        if ($feed->entries) {
            foreach ($feed->entries as $entry) {
                if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                    $customRow = $entry->getCustom();

                    foreach ($customRow as $customCol) {
                        array_push($data, $customCol->getColumnName());
                    }
                }
            }
        }

        return $data;
    }

    function getAllSpreedSheetsDetails($spreedSheet = '') {
        $feed = $this->client->getSpreadsheetFeed();
//                echo 'herere'.count($feed);
//                echo '<pre>';
//                print_r($feed);
//                exit;
        foreach ($feed->entries as $key => $entry) {
            if (!empty($spreedSheet)) {

                if ($spreedSheet == $entry->title->text) {
                    $spreadSheets[$key]['name'] = $entry->title->text;
                    $spreadSheets[$key]['URL'] = $entry->link[1]->href;
                    $spreadSheets[$key]['spreedSheetId'] = basename($entry->id);
                }
            } else {
                $spreadSheets[$key]['name'] = $entry->title->text;
                $spreadSheets[$key]['URL'] = $entry->link[1]->href;
                $spreadSheets[$key]['spreedSheetId'] = basename($entry->id);
            }
        }
        return $spreadSheets;
    }

    function getAllWorksSheetsDetails($spreadSheetKey) {
        $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
        $query->setSpreadsheetKey($spreadSheetKey);
        $feed = $this->client->getWorksheetFeed($query);
        foreach ($feed->entries as $key => $entry) {
            $workSheet[$key]['spreedSheetId'] = $spreadSheetKey;
            $workSheet[$key]['name'] = $entry->title->text;
            $workSheet[$key]['workSheetId'] = basename($entry->id);
        }
        return $workSheet;
    }

    function displayWorksheetData($spreedSheetId, $worksheetId) {
        $query = new Zend_Gdata_Spreadsheets_CellQuery();
        $query->setSpreadsheetKey($spreedSheetId);
        $query->setWorksheetId($worksheetId);
        $query->setMinRow(4);
        $cellFeed = $this->client->getCellFeed($query);
        $i = 1;
        $row_previous_value = 0;
        foreach ($cellFeed as $cellEntry) {
            $column = intval($cellEntry->cell->getColumn());
            $row = intval($cellEntry->cell->getRow());
            if ($row_previous_value != $row) {
                $i = 1;
                $row_previous_value = $row;
            }
            while ($column != $i) {
                $data[$row][$i] = '';
                $i ++;
            }
            $data[$row][$column] = $cellEntry->cell->getText();
            $i ++;
        }
        return $data;
    }

    public function autologin($user, $pass) {
        $this->login($user, base64_decode($pass));
    }

    private function login($user, $pass) {
        // Zend Gdata package required
        // http://framework.zend.com/download/gdata
//		$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
        $client_id = $this->ci->config->item('google_client_id');
        $client_secret = $this->ci->config->item('google_client_secret');
        $refresh_token = $this->ci->config->item('refresh_token');
        $http = Zend_Gdata_OAuthClient::getHttpClient($client_id, $client_secret, $refresh_token);
//		$http = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
        $this->client = new Zend_Gdata_Spreadsheets($http);

        if ($this->client instanceof Zend_Gdata_Spreadsheets) {
            return TRUE;
        }
        return FALSE;
    }

    private function findRows($search = FALSE) {
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($this->getSpreadsheetId());
        $query->setWorksheetId($this->getWorksheetId());

        if ($search)
            $query->setSpreadsheetQuery($search);

        $feed = $this->client->getListFeed($query);

        return $feed;
    }

    private function getSpreadsheetId($ss = FALSE) {
        if ($this->spreadsheet_id)
            return $this->spreadsheet_id;

        $ss = $ss ? $ss : $this->spreadsheet;

        $ss_id = FALSE;

        $feed = $this->client->getSpreadsheetFeed();

        foreach ($feed->entries as $entry) {
            if ($entry->title->text == $ss) {
                $ss_id = array_pop(explode("/", $entry->id->text));
                $this->spreadsheet_id = $ss_id;
                break;
            }
        }

        return $ss_id;
    }

    private function getWorksheetId($ss_id = FALSE, $ws = FALSE) {
        if ($this->worksheet_id)
            return $this->worksheet_id;

        $ss_id = $ss_id ? $ss_id : $this->spreadsheet_id;


        $ws = $ws ? $ws : $this->worksheet;


        $wk_id = FALSE;

        if ($ss_id AND $ws) {
            $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
            $query->setSpreadsheetKey($ss_id);
            $feed = $this->client->getWorksheetFeed($query);

            foreach ($feed->entries as $entry) {
                if ($entry->title->text == $ws) {
                    $wk_id = array_pop(explode("/", $entry->id->text));

                    $this->worksheet_id = $wk_id;

                    break;
                }
            }
        }

        return $wk_id;
    }

    function cleanKey($k) {
        return strtolower(preg_replace('/[^A-Za-z0-9\-\.]+/', '', $k));
    }

}
