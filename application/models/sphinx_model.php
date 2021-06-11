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
 * Shpinx_Model Class
 *
 * @category   Class
 * @package    CI
 * @subpackage Model
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @link       https://ams.americanarchive.org
 */
class Sphinx_Model extends CI_Model
{

	private $unique_separator = '___';

	/**
	 *
	 * constructor. Load Sphinx Search Library
	 * 
	 */
	function __construct()
	{

		parent::__construct();
                $this->db->save_queries = FALSE;
		$this->load->library('sphinxsearch');
		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();
	}

	/**
	 * Get list of all the stations based on search params
	 * 
	 * @Perm Get Array of Perm possible value of array are certified,agreed,start_date,end_date,search_kewords
	 * @return Object 
	 */
	public function search_stations($params, $offset = 0, $limit = 1000)
	{
		$stations_list = array();
		$total_record = 0;
		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();
		//$where = $this->get_sphinx_search_condtion($params);
		$mode = SPH_MATCH_EXTENDED;
		$this->sphinxsearch->set_array_result(true);
		$this->sphinxsearch->set_match_mode($mode);
		$this->sphinxsearch->set_sort_mode(SPH_SORT_ATTR_ASC, 'station_name');
		$this->sphinxsearch->set_connect_timeout(120);
		if ($limit)
			$this->sphinxsearch->set_limits((int) $offset, (int) $limit, ( $limit > 1000 ) ? $limit : 1000 );
		if (isset($params['certified']) && $params['certified'] != '')
			$this->sphinxsearch->set_filter("is_certified", array($params['certified']));
		if (isset($params['agreed']) && $params['agreed'] != '')
			$this->sphinxsearch->set_filter("is_agreed", array($params['agreed']));
		if (isset($params['start_date']) && $params['start_date'] != '' && isset($params['end_date']) && $params['end_date'] != '')
			$this->sphinxsearch->set_filter_range("start_date", strtotime($params['start_date']), strtotime($params['end_date']));


		$res = $this->sphinxsearch->query($params['search_keywords'], $this->config->item('station_index'));


		$execution_time = $res['time'];
		if ($res)
		{
			$total_record = $res['total_found'];
			if ($total_record > 0)
			{
				if (isset($res['matches']))
				{
					foreach ($res['matches'] as $record)
					{
						if ($this->is_station_user)
						{
							if ($this->station_id == $record['id'])
								$stations_list[] = (object) array_merge(array('id' => $record['id']), $record['attrs']);
						}
						else
						{
							$stations_list[] = (object) array_merge(array('id' => $record['id']), $record['attrs']);
						}
					}
				}
			}
		}

		return array("total_count" => $total_record, "records" => $stations_list, "query_time" => $execution_time);
	}

	/**
	 * Get a list of facet.
	 * 
	 * @param string  $column_name
	 * @param string  $index_name
	 * @param string  $type
	 * @param integer $offset
	 * @param integer $limit
	 * @return array
	 */
	public function facet_index($column_name, $index_name, $type = NULL, $offset = 0, $limit = 1000)
	{
		$query = '';
		$list = array();
		$total_record = 0;
		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();
		$mode = SPH_MATCH_EXTENDED;
		$this->sphinxsearch->set_array_result(true);
		$this->sphinxsearch->set_match_mode($mode);

		$this->sphinxsearch->set_group_by($column_name, SPH_GROUPBY_ATTR);

		$this->sphinxsearch->set_connect_timeout(120);
		if ($limit)
			$this->sphinxsearch->set_limits((int) $offset, (int) $limit, ( $limit > 1000 ) ? $limit : 1000 );

		$query = $this->make_where_clause($type, $index_name);
		$result = $this->sphinxsearch->query($query, $index_name);
		$execution_time = $result['time'];
		if ($result)
		{
			$total_record = $result['total_found'];
			if ($total_record > 0)
			{
				if (isset($result['matches']))
				{
					foreach ($result['matches'] as $record)
					{
						$list[] = array_merge(array('id' => $record['id']), $record['attrs']);
					}
				}
			}
		}

		return array("total_count" => $total_record, "records" => $list, "query_time" => $execution_time);
	}

	/*
	 * Update Index Attribute Value
	 * @Perm Name of index
	 * @Perm Name of attribute
	 * @Perm Value of attribute
	 */

	public function update_indexes($index, $attr, $values)
	{
		$this->sphinxsearch->update_attributes($index, $attr, $values);
	}

	/**
	 * Get All stations
	 * @return array
	 */
	public function get_all_stations()
	{
		$res = $this->search_stations('', 0, 400);
		if ($res['total_count'] > 0)
		{
			return $res['records'];
		}
	}

	/**
	 * Get list of instantiations for standalone report
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $select
	 * @return arrat
	 */
	function standalone_report($offset = 0, $limit = 100, $select = FALSE)
	{
		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();
		$mode = SPH_MATCH_EXTENDED;
		$this->sphinxsearch->set_array_result(true);
		$this->sphinxsearch->set_match_mode($mode);
		$this->sphinxsearch->set_connect_timeout(120);
		if ($select)
			$this->sphinxsearch->set_select('id');
		if ($limit)
			$this->sphinxsearch->set_limits((int) $offset, (int) $limit, ( $limit > 1000 ) ? $limit : 1000 );
		if (isset($this->session->userdata['standalone_column_order']))
		{
			if ($this->session->userdata['standalone_column_order'] == 'asc')
				$sort_mode = SPH_SORT_ATTR_ASC;
			else
				$sort_mode = SPH_SORT_ATTR_DESC;
			$this->sphinxsearch->set_sort_mode($sort_mode, $this->session->userdata['index_column']);
		}



		$query = $this->where_filter();

		$result = $this->sphinxsearch->query($query, $this->config->item('instantiatiion_index'));

		return $this->make_response($result);
	}

	/**
	 * Make where for standalone report.
	 * 
	 * @return string
	 */
	function where_filter()
	{
		$this->sphinxsearch->set_filter("digitized", array(1));
		$where = '';

		if (isset($this->session->userdata['stand_date_filter']) && $this->session->userdata['stand_date_filter'] != '')
		{
			$date_range = explode("to", str_replace('-', '/', $this->session->userdata['stand_date_filter']));

			if (isset($date_range[0]) && trim($date_range[0]) != '')
			{
				$start_date = strtotime(($date_range[0]));
			}
			if (isset($date_range[1]) && trim($date_range[1]) != '')
			{
				$end_date = strtotime(($date_range[1]));
			}
			else
			{
				$end_date = strtotime(($date_range[0]));
			}

			if ($start_date != '' && is_numeric($start_date) && isset($end_date) && is_numeric($end_date) && $end_date >= $start_date)
			{
				$where .=' @event_type "migration"';
				$this->sphinxsearch->set_filter_range("event_date", $start_date, $end_date);
			}
		}
		return $where;
	}

	/**
	 * List down instantations.
	 * 
	 * @param array $params
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $select
	 * @return array
	 */
	function instantiations_list($params, $offset = 0, $limit = 100, $select = FALSE)
	{

		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();
		//$where = $this->get_sphinx_search_condtion($params);
		if (isset($params['asset_id']))
		{
			$this->sphinxsearch->set_filter("assets_id", array($params['asset_id']));
		}

		$mode = SPH_MATCH_EXTENDED;
		$this->sphinxsearch->set_array_result(true);
		$this->sphinxsearch->set_match_mode($mode);
		$this->sphinxsearch->set_connect_timeout(120);
		if ($select)
			$this->sphinxsearch->set_select('id');
		if ($limit)
			$this->sphinxsearch->set_limits((int) $offset, (int) $limit, ( $limit > 1000 ) ? $limit : 1000 );
		if ($select)
		{
			if (isset($this->session->userdata['column']) && $this->session->userdata['column'] != '' && $this->session->userdata['column'] != 'flag')
			{

				if ($this->session->userdata['column_order'] == 'asc')
					$sort_mode = SPH_SORT_ATTR_ASC;
				else
					$sort_mode = SPH_SORT_ATTR_DESC;
				$this->sphinxsearch->set_sort_mode($sort_mode, trim($this->session->userdata['column']));
			}
		}
		$query = $this->make_where_clause();



		$result = $this->sphinxsearch->query($query, $this->config->item('instantiatiion_index'));


		return $this->make_response($result);
	}

	/**
	 * Make where for sphnix result
	 * @param string $type
	 * @param string $sphnix_index
	 * @return string
	 */
	function make_where_clause($type = NULL, $sphnix_index = NULL)
	{
		$where = '';
		if (isset($this->session->userdata['custom_search']) && $this->session->userdata['custom_search'] != '')
		{
			$keyword_json = $this->session->userdata['custom_search'];
			foreach ($keyword_json as $index => $key_columns)
			{
				$count = 0;
				foreach ($key_columns as $keys => $keywords)
				{
					$keyword = trim($keywords->value);
					if ($index == 'all')
					{
						if ($count == 0)
						{
							$where .=" \"$keyword\"";
						}
						else
						{
							$where .=" | \"$keyword\"";
						}
					}
					else
					{
						if ($sphnix_index == 'assets_list')
						{
							$col_name = "s_{$index}";
							if ($index == 'asset_description')
								$col_name = 's_description';
						}
						else
						{
							$col_name = $index;
							if ($index == 'asset_title')
								$col_name = "s_{$index}";
						}

						if ($count == 0)
							$where .=" @{$col_name} \"$keyword\"";
						else
							$where .=" | \"$keyword\"";
					}
					$count ++;
				}
			}
		}

		if (isset($this->session->userdata['date_range']) && $this->session->userdata['date_range'] != '')
		{
			$keyword_json = $this->session->userdata['date_range'];
			foreach ($keyword_json as $index => $key_columns)
			{

				foreach ($key_columns as $keys => $keywords)
				{

					$date_range = explode("to", $keywords->value);
					if (isset($date_range[0]) && trim($date_range[0]) != '')
					{
						$start_date = strtotime(trim($date_range[0]));
					}
					if (isset($date_range[1]) && trim($date_range[1]) != '')
					{
						$end_date = strtotime(trim($date_range[1]));
					}
					else
					{
						$end_date = strtotime(trim($date_range[0]));
					}
					if ($start_date != '' && is_numeric($start_date) && isset($end_date) && is_numeric($end_date) && $end_date >= $start_date)
					{
						$column_sphinx = 'dates';
						if ($sphnix_index == 'assets_list')
							$column_sphinx = 'instantiation_date';
						$this->sphinxsearch->set_filter_range($column_sphinx, $start_date, $end_date);
						if ($index != 'All')
						{
							$where .=" @date_type \"$index\"";
						}
					}
				}
			}
		}
		if ((isset($this->session->userdata['digitized']) && $this->session->userdata['digitized'] === '1') || $type == 'digitized')
		{
			$this->sphinxsearch->set_filter("digitized", array(1, 0));
		}
//		if (isset($this->session->userdata['digitized']) && $this->session->userdata['digitized'] != '')
//			{
//				if ($this->session->userdata['digitized'] == 1)
//				{
//					$this->sphinxsearch->set_filter("digitized", array(1));
//				}
//				if ($this->session->userdata['digitized'] == 2)
//				{
//					$this->sphinxsearch->set_filter("digitized", array(0));
//				}
//			}

		if (isset($this->session->userdata['organization']) && $this->session->userdata['organization'] != '')
		{
			$station_name = trim($this->session->userdata['organization']);
			$where .=" @s_organization \"^$station_name$\"";
		}
		if (isset($this->session->userdata['states']) && $this->session->userdata['states'] != '')
		{
			$station_state = trim($this->session->userdata['states']);
			$where .=" @s_state \"^$station_state$\"";
		}
		if (isset($this->session->userdata['nomination']) && $this->session->userdata['nomination'] != '')
		{
			$nomination = trim($this->session->userdata['nomination']);
			$where .=" @s_status \"^$nomination$\"";
		}
		if (isset($this->session->userdata['media_type']) && $this->session->userdata['media_type'] != '')
		{
			$media_type = $this->unique_separator . trim($this->session->userdata['media_type']) . $this->unique_separator;
			$where .=" @s_media_type \"$media_type\"";
		}
		if (isset($this->session->userdata['physical_format']) && $this->session->userdata['physical_format'] != '')
		{

			$physical_format = $this->unique_separator . trim($this->session->userdata['physical_format']) . $this->unique_separator;
			$where .=" @s_physical_format_name \"$physical_format\"";
		}

		if (isset($this->session->userdata['digital_format']) && $this->session->userdata['digital_format'] != '')
		{
			$digital_format = $this->unique_separator . trim($this->session->userdata['digital_format']) . $this->unique_separator;
			$where .=" @s_digital_format_name \"$digital_format\"";
		}

		if (isset($this->session->userdata['generation']) && $this->session->userdata['generation'] != '')
		{
			$generation = $this->unique_separator . trim($this->session->userdata['generation']) . $this->unique_separator;
			$where .=" @s_facet_generation \"$generation\"";
		}

		if ((isset($this->session->userdata['migration_failed']) && $this->session->userdata['migration_failed'] === '1' ) || $type == 'migration')
		{
			$where .=' @event_type "migration" @event_outcome "FAIL"';
		}
		if (@$this->is_station_user)
		{
			$where .=" @s_organization \"^$this->station_name$\"";
		}

		return $where;
	}

	/**
	 * Get all assets info.
	 * 
	 * @param array $params
	 * @param integer $offset
	 * @param integer $limit
	 * @param boolean $select
	 * @return array 
	 * 
	 */
	function assets_listing($offset = 0, $limit = 100, $select = FALSE)
	{
		$mode = SPH_MATCH_EXTENDED;
		$this->sphinxsearch->reset_filters();
		$this->sphinxsearch->reset_group_by();
		$this->sphinxsearch->reset_overrides();

		$this->sphinxsearch->set_array_result(true);
		$this->sphinxsearch->set_match_mode($mode);
		$this->sphinxsearch->set_connect_timeout(120);
		if ($select)
			$this->sphinxsearch->set_select('id');
		if ($limit)
			$this->sphinxsearch->set_limits((int) $offset, (int) $limit, ( $limit > 1000 ) ? $limit : 1000 );
		if ($select)
		{
			if (isset($this->session->userdata['column']) && $this->session->userdata['column'] != '' && $this->session->userdata['column'] != 'flag')
			{
				if ($this->session->userdata['column_order'] == 'asc')
					$sort_mode = SPH_SORT_ATTR_ASC;
				else
					$sort_mode = SPH_SORT_ATTR_DESC;
				$this->sphinxsearch->set_sort_mode($sort_mode, $this->session->userdata['column']);
			}
		}
		$query = $this->make_where_clause(NULL, $this->config->item('asset_index'));

		$result = $this->sphinxsearch->query($query, $this->config->item('asset_index'));
		return $this->make_response($result);
	}

	function make_response($result)
	{
		$records = array();
		$total_record = 0;
		$execution_time = $result['time'];
		if ($result)
		{
			$total_record = $result['total_found'];
			if ($total_record > 0)
			{
				if (isset($result['matches']))
				{
					foreach ($result['matches'] as $record)
					{
						$records[] = (object) array_merge(array('id' => $record['id']), $record['attrs']);
					}
				}
			}
		}

		return array("total_count" => $total_record, "records" => $records, "query_time" => $execution_time);
	}

}
