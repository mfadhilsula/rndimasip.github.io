<?php

/**
 * AMS Archive Management System
 * 
 * PHP version 5
 * 
 * @category   AMS
 * @package    CI
 * @subpackage Controller
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @version    GIT: <$Id>
 * @link       https://github.com/avpreserve/AMS
 */

/**
 * Instantiations Class
 *
 * @category   Class
 * @package    CI
 * @subpackage Controller
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @link       https://ams.americanarchive.org
 */
class Instantiations extends MY_Records_Controller
{

	/**
	 * Constructor
	 * 
	 * Load the layout, Models and Libraries
	 * 
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('instantiations_model', 'instantiation');
		$this->load->model('manage_asset_model', 'manage_asset');
		$this->load->model('export_csv_job_model', 'csv_job');
		$this->load->model('assets_model');
		$this->load->model('sphinx_model', 'sphinx');
		$this->load->library('pagination');
		$this->load->library('Ajax_pagination');
		$this->load->library('memcached_library');
		$this->load->helper('datatable');
		$this->load->model('refine_modal');
		$this->load->model('cron_model');
		$this->load->model('pbcore_model');
	}

	/**
	 * List all the instantiation records with pagination and filters. 
	 * 
	 * @return instantiations/index view
	 */
	public function index()
	{
		$offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$this->session->set_userdata('offset', $offset);
		$params = array('search' => '');
		$data['station_records'] = $this->station_model->get_all();

		if (isAjax())
		{
			$this->unset_facet_search();

			$search['custom_search'] = json_decode($this->input->post('keyword_field_main_search'));
			$search['date_range'] = json_decode($this->input->post('date_field_main_search'));

			$search['organization'] = $this->input->post('organization_main_search');
			$search['states'] = $this->input->post('states_main_search');
			$search['nomination'] = $this->input->post('nomination_status_main_search');
			$search['media_type'] = $this->input->post('media_type_main_search');
			$search['physical_format'] = $this->input->post('physical_format_main_search');
			$search['digital_format'] = $this->input->post('digital_format_main_search');
			$search['generation'] = $this->input->post('generation_main_search');

			if ($this->input->post('digitized') && $this->input->post('digitized') === '1')
			{
				$search['digitized'] = $this->input->post('digitized');
			}
			if ($this->input->post('migration_failed') && $this->input->post('migration_failed') === '1')
			{
				$search['migration_failed'] = $this->input->post('migration_failed');
			}

			$this->set_facet_search($search);
		}

		$this->session->set_userdata('page_link', 'instantiations/index/' . $offset);
		$data['get_column_name'] = $this->make_array();


		$data['date_types'] = $this->instantiation->get_date_types();
		$data['is_refine'] = $this->refine_modal->get_active_refine();


		$data['current_tab'] = '';
		$is_hidden = array();
		$data['table_type'] = 'instantiation';
		foreach ($this->column_order as $index => $value)
		{
			if ($value['hidden'] === '1')
				$is_hidden[] = $index;
		}
		$data['hidden_fields'] = $is_hidden;
		$data['isAjax'] = FALSE;

		$records = $this->sphinx->instantiations_list($params, $offset);
//		debug($records );
		$data['total'] = $records['total_count'];
		$config['total_rows'] = $data['total'];
		$config['per_page'] = 100;
		$data['records'] = $records['records'];
		$data['count'] = count($data['records']);
		if ($data['count'] > 0 && $offset === 0)
		{
			$data['start'] = 1;
			$data['end'] = $data['count'];
		}
		else
		{
			$data['start'] = $offset;
			$data['end'] = intval($offset) + intval($data['count']);
		}
		$data['facet_search_url'] = site_url('instantiations/index');
		$config['prev_link'] = '<i class="icon-chevron-left"></i>';
		$config['next_link'] = '<i class="icon-chevron-right"></i>';
		$config['use_page_numbers'] = FALSE;
		$config['first_link'] = FALSE;
		$config['last_link'] = FALSE;
		$config['display_pages'] = FALSE;
		$config['js_method'] = 'facet_search';
		$config['postVar'] = 'page';
		$this->ajax_pagination->initialize($config);

		if (isAjax())
		{
			$data['isAjax'] = TRUE;
			echo $this->load->view('instantiations/index', $data, TRUE);
			exit(0);
		}
		$this->load->view('instantiations/index', $data);
	}

	/**
	 * Show the detail of an instantiation
	 *  
	 * @return instantiations/detail view
	 */
	public function detail()
	{
		$instantiation_id = (is_numeric($this->uri->segment(3))) ? $this->uri->segment(3) : FALSE;
		if ($instantiation_id)
		{
			$detail = $data['detail_instantiation'] = $this->instantiation->get_by_id($instantiation_id);

			if (count($detail) > 0)
			{
				$data['asset_id'] = $detail->assets_id;
				$data['inst_id'] = $instantiation_id;
				$data['list_assets'] = $this->instantiation->get_instantiations_by_asset_id($detail->assets_id);
				$data['asset_guid'] = $this->assets_model->get_guid_by_asset_id($data['asset_id']);
				$data['ins_nomination'] = $this->instantiation->get_nomination_by_instantiation_id($instantiation_id);
				$data['inst_identifier'] = $this->instantiation->get_identifier_by_instantiation_id($instantiation_id);

				$data['inst_dates'] = $this->instantiation->get_dates_by_instantiation_id($instantiation_id);
				$data['inst_media_type'] = $this->instantiation->get_media_type_by_instantiation_media_id($detail->instantiation_media_type_id);
				$data['inst_format'] = $this->instantiation->get_format_by_instantiation_id($instantiation_id);
				$data['inst_generation'] = $this->instantiation->get_generation_by_instantiation_id($instantiation_id);
				$data['inst_demension'] = $this->instantiation->get_demension_by_instantiation_id($instantiation_id);
				$data['inst_data_rate_unit'] = $this->instantiation->get_data_rate_unit_by_data_id($detail->data_rate_units_id);
				$data['inst_color'] = $this->instantiation->get_color_by_instantiation_colors_id($detail->instantiation_colors_id);
				$data['inst_annotation'] = $this->instantiation->get_annotation_by_instantiation_id($instantiation_id);
				$data['inst_relation'] = $this->manage_asset->get_relation_by_instantiation_id($instantiation_id);

				$data['essence_track'] = $this->pbcore_model->get_essence_tracks_by_instantiations_id($instantiation_id);
//				debug($data['essence_track']);

				$data['instantiation_events'] = $this->instantiation->get_events_by_instantiation_id($instantiation_id);

				$data['asset_details'] = $this->assets_model->get_asset_by_asset_id($detail->assets_id);
				$search_results_data = $this->sphinx->instantiations_list(array(), 0, 1000, TRUE);
				$data['nominations'] = $this->instantiation->get_nomination_status();

				$data['media'] = $this->proxy_files($data['asset_guid']->guid_identifier);
				$data['next_result_id'] = FALSE;
				$data['prev_result_id'] = FALSE;
				$cur_key = NULL;
				if (isset($search_results_data['records']) && ! is_empty($search_results_data['records']))
				{

					$search_results = $search_results_data['records'];
					foreach ($search_results as $key => $value)
					{
						if ($value->id == $instantiation_id)
							$cur_key = $key;
					}
					if (isset($search_results[$cur_key - 1]))
						$data['prev_result_id'] = $search_results[$cur_key - 1]->id;
					if (isset($search_results[$cur_key + 1]))
						$data['next_result_id'] = $search_results[$cur_key + 1]->id;
				}
				$data['last_page'] = '';
				if (isset($this->session->userdata['page_link']) && ! is_empty($this->session->userdata['page_link']))
				{
					$data['last_page'] = $this->session->userdata['page_link'];
				}

				$this->load->view('instantiations/detail', $data);
			}
			else
			{
				show_404();
			}
		}
		else
		{
			show_404();
		}
	}

	/**
	 * Set last state of table view
	 *  
	 * @return json
	 */
	public function update_user_settings()
	{
		if (isAjax())
		{
			$user_id = $this->user_id;
			$settings = $this->input->post('settings');
			$freeze_columns = $this->input->post('frozen_column');
			$table_type = $this->input->post('table_type');
			$settings = json_encode($settings);
			$data = array('view_settings' => $settings, 'frozen_column' => $freeze_columns);
			$this->user_settings->update_setting($user_id, $table_type, $data);
			echo json_encode(array('success' => TRUE));
			exit(0);
		}
		show_404();
	}

	/**
	 * Edit instantations record.
	 * 
	 * @return view
	 */
	public function edit()
	{
		$instantiation_id = $this->uri->segment(3);

		if ( ! empty($instantiation_id))
		{


			$detail = $data['instantiation_detail'] = $this->instantiation->get_by_id($instantiation_id);

			if (count($data['instantiation_detail']) > 0)
			{

				if ($this->input->post())
				{
					/* Instantiation Identifier Start */
					if ($this->input->post('instantiation_id_identifier'))
					{
						foreach ($this->input->post('instantiation_id_identifier') as $index => $ins_identifier)
						{
							$identifier['instantiation_identifier'] = $ins_identifier;
							$ins_identifer_id = $this->input->post('instantiation_id_identifier_id');
							$ins_source = $this->input->post('instantiation_id_source');
							if (isset($ins_source[$index]) && ! empty($ins_source[$index]))
								$identifier['instantiation_source'] = $ins_source[$index];

							if (isset($ins_identifer_id[$index]) && ! empty($ins_identifer_id[$index]))
							{

								$this->instantiation->update_instantiation_identifier_by_id($ins_identifer_id[$index], $identifier);
							}
							else
							{

								$identifier['instantiations_id'] = $instantiation_id;

								$this->instantiation->insert_instantiation_identifier($identifier);
							}
						}
					}
					else if ($this->input->post('instantiation_id_source'))
					{
						foreach ($this->input->post('instantiation_id_source') as $index => $identifier_src)
						{
							$ins_identifer_id = $this->input->post('instantiation_id_identifier_id');
							if ( ! empty($identifier_src))
							{
								$identifier['instantiation_source'] = $identifier_src;
								$this->instantiation->update_instantiation_identifier_by_id($ins_identifer_id[$index], $identifier);
							}
						}
					}

					/* Instantiation Identifier End */
					/* Nomination Start */

					$nomination = $this->input->post('nomination');
					$reason = $this->input->post('nomination_reason');
					$nomination_exist = $this->assets_model->get_nominations($instantiation_id);
					if ( ! empty($nomination))
					{
						$nomination_id = $this->assets_model->get_nomination_status_by_status($nomination)->id;

						$nomination_record = array('nomination_status_id' => $nomination_id, 'nomination_reason' => $reason, 'nominated_by' => $this->user_id, 'nominated_at' => date('Y-m-d H:i:s'));
						if ($nomination_exist)
						{
							$nomination_record['updated'] = date('Y-m-d H:i:s');
							$this->assets_model->update_nominations($instantiation_id, $nomination_record);
						}
						else
						{
							$nomination_record['instantiations_id'] = $instantiation_id;
							$nomination_record['created'] = date('Y-m-d H:i:s');
							$this->assets_model->insert_nominations($nomination_record);
						}
						$this->sphinx->update_indexes('instantiations_list', array('nomination_status_id'), array($instantiation_id => array((int) $nomination_id)));
						$this->sphinx->update_indexes('assets_list', array('nomination_status_id'), array($detail->assets_id => array((int) $nomination_id)));
					}
					else
					{
						if ($nomination_exist)
						{
							$this->manage_asset->delete_row($instantiation_id, 'nominations', 'instantiations_id');
							$this->sphinx->update_indexes('instantiations_list', array('nomination_status_id'), array($instantiation_id => array((int) 0)));
							$this->sphinx->update_indexes('assets_list', array('nomination_status_id'), array($detail->assets_id => array((int) 0)));
						}
					}
					/* Nomination End */
					/* Media Type Start */
					$media_type = $this->input->post('media_type');
					$db_media_type = $this->instantiation->get_instantiation_media_types_by_media_type($media_type);
					if ($db_media_type)
					{
						$update_instantiation['instantiation_media_type_id'] = $db_media_type->id;
					}
					else
					{
						$update_instantiation['instantiation_media_type_id'] = $this->instantiation->insert_instantiation_media_types(array('media_type' => $media_type));
					}
					/* Media Type End */
					/* Generation Start */
					if ($this->input->post('generation'))
					{

						$this->manage_asset->delete_row($instantiation_id, 'instantiation_generations', 'instantiations_id');
						foreach ($this->input->post('generation') as $row)
						{
							$db_generation = $this->instantiation->get_generations_by_generation($row);
							if ($db_generation)
							{
								$db_gen_id = $db_generation->id;
							}
							else
							{
								$db_gen_id = $this->instantiation->insert_generations(array('generation' => $row));
							}
							$this->instantiation->insert_instantiation_generations(array('instantiations_id' => $instantiation_id, 'generations_id' => $db_gen_id));
						}
					}
					/* Generation End */
					if ($this->input->post('instantiation_id_identifier'))
					{
						/* Date Start */
						if ($this->input->post('inst_date'))
						{
							$this->manage_asset->delete_row($instantiation_id, 'instantiation_dates', 'instantiations_id');
							foreach ($this->input->post('inst_date') as $index => $value)
							{
								$inst_date_types = $this->input->post('inst_date_type');
								if ( ! empty($value))
								{
									$date_type = $this->instantiation->get_date_types_by_type($inst_date_types[$index]);
									if (isset($date_type) && isset($date_type->id))
										$instantiation_dates_d['date_types_id'] = $date_type->id;
									else
										$instantiation_dates_d['date_types_id'] = $this->instantiation->insert_date_types(array('date_type' => $inst_date_types[$index]));
									$instantiation_dates_d['instantiation_date'] = $value;
									$instantiation_dates_d['instantiations_id'] = $instantiation_id;
									$this->instantiation->insert_instantiation_dates($instantiation_dates_d);
								}
							}
						}
						/* Date End */
						/* Demension Start */
						if ($this->input->post('asset_dimension'))
						{

							$this->manage_asset->delete_row($instantiation_id, 'instantiation_dimensions', 'instantiations_id');
							foreach ($this->input->post('asset_dimension') as $index => $value)
							{
								$unit_measure = $this->input->post('dimension_unit');
								$instantiation_dimension_d['instantiations_id'] = $instantiation_id;
								$instantiation_dimension_d['instantiation_dimension'] = $value;
								$instantiation_dimension_d['unit_of_measure'] = $unit_measure[$index];
								$this->instantiation->insert_instantiation_dimensions($instantiation_dimension_d);
							}
						}
						/* Demension End */
						/* Physical Format Start */

						$physical_format = $this->instantiation->get_format_by_instantiation_id($instantiation_id);

						$instantiation_format_physical_d['format_name'] = $this->input->post('physical_format');
						$instantiation_format_physical_d['format_type'] = 'physical';

						if (count($physical_format) > 0)
						{
							$instantiation_format_physical_id = $this->instantiation->update_instantiation_formats($physical_format->id, $instantiation_format_physical_d);
						}
						else
						{
							$instantiation_format_physical_d['instantiations_id'] = $instantiation_id;
							$instantiation_format_physical_id = $this->instantiation->insert_instantiation_formats($instantiation_format_physical_d);
						}

						/* Physical Format End */
						/* Standard Start */
						if ($this->input->post('standard'))
						{
							$update_instantiation['standard'] = $this->input->post('standard');
						}
						/* Standard End */
						/* Location Start */
						if ($this->input->post('location'))
						{
							$update_instantiation['location'] = $this->input->post('location');
						}
						/* Location End */
						/* Time Start Start */
						if ($this->input->post('time_start'))
						{
							$update_instantiation['time_start'] = $this->input->post('time_start');
						}
						/* Time Start End */
						/* Porjected Duration Start */
						if ($this->input->post('projected_duration'))
						{
							$update_instantiation['projected_duration'] = $this->input->post('projected_duration');
						}
						/* Porjected Duration End */
						/* Porjected Alernative Modes Start */
						if ($this->input->post('alternative_modes'))
						{
							$update_instantiation['alternative_modes'] = $this->input->post('alternative_modes');
						}
						/* Porjected Alernative Modes End */
						/* Color Start */
						if ($this->input->post('color'))
						{

							$inst_color_d = $this->instantiation->get_instantiation_colors_by_color($this->input->post('color'));
							if (isset($inst_color_d) && ! is_empty($inst_color_d))
							{
								$update_instantiation['instantiation_colors_id'] = $inst_color_d->id;
							}
							else
							{
								$update_instantiation['instantiation_colors_id'] = $this->instantiation->insert_instantiation_colors(array('color' => $this->input->post('color')));
							}
						}
						/* Color End */
						/* Tracks Start */
						if ($this->input->post('tracks'))
						{
							$update_instantiation['tracks'] = $this->input->post('tracks');
						}
						/* Tracks End */
						/* Channel Configuration Start */
						if ($this->input->post('channel_configuration'))
						{
							$update_instantiation['channel_configuration'] = $this->input->post('channel_configuration');
						}
						/* Channel Configuration End */
					}
					/* Language Configuration Start */
					if ($this->input->post('language'))
					{
						$update_instantiation['language'] = $this->input->post('language');
					}
					/* Language Configuration End */
					/* Update Instantiation */
					$this->instantiation->update_instantiations($instantiation_id, $update_instantiation);
					if ($this->input->post('instantiation_id_identifier'))
					{
						/* Annotation Start */
						if ($this->input->post('annotation'))
						{

							$this->manage_asset->delete_row($instantiation_id, 'instantiation_annotations', 'instantiations_id');
							foreach ($this->input->post('annotation') as $index => $value)
							{
								if ( ! empty($value))
								{
									$annotation_type = $this->input->post('annotation_type');
									$instantiation_annotation_d['instantiations_id'] = $instantiation_id;
									$instantiation_annotation_d['annotation'] = $value;
									$instantiation_annotation_d['annotation_type'] = $annotation_type[$index];
									$this->instantiation->insert_instantiation_annotations($instantiation_annotation_d);
								}
							}
						}
						/* Annotation End */
						/* Relation Start */
						if ($this->input->post('relation'))
						{
							$this->manage_asset->delete_row($instantiation_id, 'instantiation_relations', 'instantiations_id');
							$relation_src = $this->input->post('relation_source');
							$relation_ref = $this->input->post('relation_ref');
							$relation_type = $this->input->post('relation_type');
							foreach ($this->input->post('relation') as $index => $value)
							{
								if ( ! empty($value))
								{
									$relation['instantiations_id'] = $instantiation_id;
									$relation['relation_identifier'] = $value;
									$relation_types['relation_type'] = $relation_type[$index];
									if ( ! empty($relation_src[$index]))
										$relation_types['relation_type_source'] = $relation_src[$index];
									if ( ! empty($relation_ref[$index]))
										$relation_types['relation_type_ref'] = $relation_ref[$index];
									$db_relations = $this->assets_model->get_relation_types_all($relation_types);
									if (isset($db_relations) && isset($db_relations->id))
									{
										$relation['relation_types_id'] = $db_relations->id;
									}
									else
									{
										$relation['relation_types_id'] = $this->assets_model->insert_relation_types($relation_types);
									}
									$this->instantiation->insert_instantiation_relation($relation);
								}
							}
						}
						/* Relation End */

						/* Essence Track Frame Size Start */
						$db_essence_track = FALSE;
						if ($this->input->post('width') && $this->input->post('height'))
						{
							$width = $this->input->post('width');
							$height = $this->input->post('height');
							if ( ! empty($width) && ! empty($height))
							{
								$db_essence_track = TRUE;

								$track_frame_size_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_frame_sizes, array('width' => trim($this->input->post('width')), 'height' => trim($this->input->post('height'))));
								if ($track_frame_size_d)
								{
									$essence_tracks_d['essence_track_frame_sizes_id'] = $track_frame_size_d->id;
								}
								else
								{
									$essence_tracks_d['essence_track_frame_sizes_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_frame_sizes, array("width" => $this->input->post('width'), "height" => $this->input->post('height')));
								}
							}
						}
						/* Essence Track Frame Size End */
						/* Essence Track Frame Rate Start */
						if ($frame_rate = $this->input->post('frame_rate'))
						{
							if ( ! empty($frame_rate))
							{
								$db_essence_track = TRUE;
								$essence_tracks_d['frame_rate'] = $this->input->post('frame_rate');
							}
						}
						/* Essence Track Frame Rate End */
						/* Essence Track Playback Speed Start */
						if ($playback_speed = $this->input->post('playback_speed'))
						{
							if ( ! empty($playback_speed))
							{
								$db_essence_track = TRUE;
								$essence_tracks_d['playback_speed'] = $this->input->post('playback_speed');
							}
						}
						/* Essence Track Playback Speed End */
						/* Essence Track Sampling Rate Start */
						if ($sampling_rate = $this->input->post('sampling_rate'))
						{
							if ( ! empty($sampling_rate))
							{
								$db_essence_track = TRUE;
								$essence_tracks_d['sampling_rate'] = $this->input->post('sampling_rate');
							}
						}
						/* Essence Track Sampling Rate End */
						/* Essence Track Aspect Ratio Start */
						if ($aspect_ratio = $this->input->post('aspect_ratio'))
						{
							if ( ! empty($aspect_ratio))
							{
								$db_essence_track = TRUE;
								$essence_tracks_d['aspect_ratio'] = $this->input->post('aspect_ratio');
							}
						}
						/* Essence Track Aspect Ratio End */
						/* Essence Track Type Start */

						$essence_track_type_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_types, array('essence_track_type' => 'General'), TRUE);
						if (isset($essence_track_type_d) && isset($essence_track_type_d->id))
						{
							$essence_tracks_d['essence_track_types_id'] = $essence_track_type_d->id;
						}
						else
						{
							$essence_tracks_d['essence_track_types_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_types, array('essence_track_type' => 'General'));
						}
						/* Essence Track Type End */


						/* Essence Track Start */
						if ($db_essence_track)
						{
							$essence_track = $this->manage_asset->get_single_essence_tracks_by_instantiations_id($instantiation_id);
							if ($essence_track)
							{
								$this->pbcore_model->update_essence_track($essence_track->id, $essence_tracks_d);
							}
							else
							{
								$essence_tracks_d['instantiations_id'] = $instantiation_id;
								$this->pbcore_model->insert_record($this->pbcore_model->table_essence_tracks, $essence_tracks_d);
							}
						}
						/* Essence Track End */
					}
					// Update Sphnix Indexes
					$this->load->library('sphnixrt');
					$this->load->model('searchd_model');
					$this->load->helper('sphnixdata');
					$instantiation_list = $this->searchd_model->get_ins_index(array($instantiation_id));
					$new_list_info = make_instantiation_sphnix_array($instantiation_list[0], FALSE);
					$this->sphnixrt->update('instantiations_list', $new_list_info);
					$asset_list = $this->searchd_model->get_asset_index(array($instantiation_list[0]->assets_id));
					$new_asset_info = make_assets_sphnix_array($asset_list[0], FALSE);
					$this->sphnixrt->update('assets_list', $new_asset_info);
					// End Update Sphnix Indexes

					$log = array('user_id' => $this->user_id, 'record_id' => $instantiation_id, 'record' => 'instantiation', 'type' => 'edit', 'comments' => 'update from instantiations detail view.');
					$this->audit_trail($log);
					redirect('instantiations/detail/' . $instantiation_id);
				}
				$data['asset_id'] = $detail->assets_id;
				$data['inst_id'] = $instantiation_id;
				$data['list_assets'] = $this->instantiation->get_instantiations_by_asset_id($detail->assets_id);
				$data['ins_nomination'] = $this->instantiation->get_nomination_by_instantiation_id($instantiation_id);
				$data['inst_identifier'] = $this->manage_asset->get_identifier_by_instantiation_id($instantiation_id);
				$data['date'] = $this->manage_asset->get_dates_by_instantiation_id($instantiation_id);
				$data['inst_demension'] = $this->manage_asset->get_demension_by_instantiation_id($instantiation_id);
				$data['inst_format'] = $this->instantiation->get_format_by_instantiation_id($instantiation_id);
				$data['inst_media_type'] = $this->instantiation->get_media_type_by_instantiation_media_id($detail->instantiation_media_type_id);
				$data['inst_generation'] = $this->instantiation->get_generation_by_instantiation_id($instantiation_id);
				$data['inst_data_rate_unit'] = $this->instantiation->get_data_rate_unit_by_data_id($detail->data_rate_units_id);
				$data['inst_color'] = $this->instantiation->get_color_by_instantiation_colors_id($detail->instantiation_colors_id);
				$data['inst_annotation'] = $this->manage_asset->get_annotation_by_instantiation_id($instantiation_id);
				$data['inst_relation'] = $this->manage_asset->get_relation_by_instantiation_id($instantiation_id);
				$data['asset_details'] = $this->assets_model->get_asset_by_asset_id($detail->assets_id);
				$data['essence_track'] = $this->manage_asset->get_single_essence_tracks_by_instantiations_id($instantiation_id);

				$data['pbcore_asset_date_types'] = $this->manage_asset->get_picklist_values(2);
				$data['pbcore_media_types'] = $this->manage_asset->get_picklist_values(11);
				$data['pbcore_generations'] = $this->manage_asset->get_picklist_values(12);
				$data['pbcore_relation_types'] = $this->manage_asset->get_picklist_values(7);
				$data['pbcore_standards'] = $this->manage_asset->get_picklist_values(14);
				$data['pbcore_colors'] = $this->manage_asset->get_picklist_values(15);
				$data['pbcore_physical_formats'] = $this->manage_asset->get_picklist_values(13);
				$data['nominations'] = $this->instantiation->get_nomination_status();
				$this->load->view('instantiations/edit', $data);
			}
			else
			{
				show_error('Not a valid instantiation id');
			}
		}
		else
		{
			show_error('Instantiation ID is required for editing.');
		}
	}

	/**
	 * Add new instantiation
	 * 
	 * @return view
	 */
	public function add()
	{
		$asset_id = $data['asset_id'] = $this->uri->segment(3);
		if ($this->input->post())
		{
			/* Media Type Start */
			$media_type = $this->input->post('media_type');
			$db_media_type = $this->instantiation->get_instantiation_media_types_by_media_type($media_type);
			if ($db_media_type)
			{
				$update_instantiation['instantiation_media_type_id'] = $db_media_type->id;
			}
			else
			{
				$update_instantiation['instantiation_media_type_id'] = $this->instantiation->insert_instantiation_media_types(array('media_type' => $media_type));
			}
			/* Media Type End */
			/* Standard Start */
			if ($this->input->post('standard'))
			{
				$update_instantiation['standard'] = $this->input->post('standard');
			}
			/* Standard End */
			/* Location Start */
			if ($this->input->post('location'))
			{
				$update_instantiation['location'] = $this->input->post('location');
			}
			/* Location End */
			/* Time Start Start */
			if ($this->input->post('time_start'))
			{
				$update_instantiation['time_start'] = $this->input->post('time_start');
			}
			/* Time Start End */
			/* Porjected Duration Start */
			if ($this->input->post('projected_duration'))
			{
				$update_instantiation['projected_duration'] = $this->input->post('projected_duration');
			}
			/* Porjected Duration End */
			/* Porjected Alernative Modes Start */
			if ($this->input->post('alternative_modes'))
			{
				$update_instantiation['alternative_modes'] = $this->input->post('alternative_modes');
			}
			/* Porjected Alernative Modes End */
			/* Color Start */
			if ($this->input->post('color'))
			{

				$inst_color_d = $this->instantiation->get_instantiation_colors_by_color($this->input->post('color'));
				if (isset($inst_color_d) && ! is_empty($inst_color_d))
				{
					$update_instantiation['instantiation_colors_id'] = $inst_color_d->id;
				}
				else
				{
					$update_instantiation['instantiation_colors_id'] = $this->instantiation->insert_instantiation_colors(array('color' => $this->input->post('color')));
				}
			}
			/* Color End */
			/* Tracks Start */
			if ($this->input->post('tracks'))
			{
				$update_instantiation['tracks'] = $this->input->post('tracks');
			}
			/* Tracks End */
			/* Channel Configuration Start */
			if ($this->input->post('channel_configuration'))
			{
				$update_instantiation['channel_configuration'] = $this->input->post('channel_configuration');
			}
			/* Channel Configuration End */

			/* Language Configuration Start */
			if ($this->input->post('language'))
			{
				$update_instantiation['language'] = $this->input->post('language');
			}
			/* Language Configuration End */
			/* Insert Instantiation Start */
			$update_instantiation['assets_id'] = $asset_id;
			$instantiation_id = $this->instantiation->insert_instantiations($update_instantiation);
			/* Insert Instantiation End */
			/* Instantiation Identifier Start */
			if ($this->input->post('instantiation_id_identifier'))
			{
				foreach ($this->input->post('instantiation_id_identifier') as $index => $ins_identifier)
				{
					$identifier['instantiation_identifier'] = $ins_identifier;

					$ins_source = $this->input->post('instantiation_id_source');
					if (isset($ins_source[$index]) && ! empty($ins_source[$index]))
						$identifier['instantiation_source'] = $ins_source[$index];
					$identifier['instantiations_id'] = $instantiation_id;

					$this->instantiation->insert_instantiation_identifier($identifier);
				}
			}
			/* Instantiation Identifier End */
			/* Nomination Start */

			$nomination = $this->input->post('nomination');
			$reason = $this->input->post('nomination_reason');

			if ( ! empty($nomination))
			{
				$nomination_id = $this->assets_model->get_nomination_status_by_status($nomination)->id;

				$nomination_record = array('nomination_status_id' => $nomination_id, 'nomination_reason' => $reason, 'nominated_by' => $this->user_id, 'nominated_at' => date('Y-m-d H:i:s'));

				$nomination_record['instantiations_id'] = $instantiation_id;
				$nomination_record['created'] = date('Y-m-d H:i:s');
				$this->assets_model->insert_nominations($nomination_record);
			}

			/* Nomination End */
			/* Generation Start */
			if ($this->input->post('generation'))
			{
				foreach ($this->input->post('generation') as $row)
				{
					$db_generation = $this->instantiation->get_generations_by_generation($row);
					if ($db_generation)
					{
						$db_gen_id = $db_generation->id;
					}
					else
					{
						$db_gen_id = $this->instantiation->insert_generations(array('generation' => $row));
					}
					$this->instantiation->insert_instantiation_generations(array('instantiations_id' => $instantiation_id, 'generations_id' => $db_gen_id));
				}
			}
			/* Generation End */

			/* Date Start */
			if ($this->input->post('inst_date'))
			{
				foreach ($this->input->post('inst_date') as $index => $value)
				{
					$inst_date_types = $this->input->post('inst_date_type');
					if ( ! empty($value))
					{
						$date_type = $this->instantiation->get_date_types_by_type($inst_date_types[$index]);
						if (isset($date_type) && isset($date_type->id))
							$instantiation_dates_d['date_types_id'] = $date_type->id;
						else
							$instantiation_dates_d['date_types_id'] = $this->instantiation->insert_date_types(array('date_type' => $inst_date_types[$index]));
						$instantiation_dates_d['instantiation_date'] = $value;
						$instantiation_dates_d['instantiations_id'] = $instantiation_id;
						$this->instantiation->insert_instantiation_dates($instantiation_dates_d);
					}
				}
			}
			/* Date End */
			/* Demension Start */
			if ($this->input->post('asset_dimension'))
			{
				foreach ($this->input->post('asset_dimension') as $index => $value)
				{
					$unit_measure = $this->input->post('dimension_unit');
					$instantiation_dimension_d['instantiations_id'] = $instantiation_id;
					$instantiation_dimension_d['instantiation_dimension'] = $value;
					$instantiation_dimension_d['unit_of_measure'] = $unit_measure[$index];
					$this->instantiation->insert_instantiation_dimensions($instantiation_dimension_d);
				}
			}
			/* Demension End */
			/* Physical Format Start */
			if ($this->input->post('physical_format'))
			{
				$instantiation_format_physical_d['format_name'] = $this->input->post('physical_format');
				$instantiation_format_physical_d['format_type'] = 'physical';
				$instantiation_format_physical_d['instantiations_id'] = $instantiation_id;
				$instantiation_format_physical_id = $this->instantiation->insert_instantiation_formats($instantiation_format_physical_d);
			}

			/* Physical Format End */
			/* Annotation Start */
			if ($this->input->post('annotation'))
			{
				foreach ($this->input->post('annotation') as $index => $value)
				{
					if ( ! empty($value))
					{
						$annotation_type = $this->input->post('annotation_type');
						$instantiation_annotation_d['instantiations_id'] = $instantiation_id;
						$instantiation_annotation_d['annotation'] = $value;
						$instantiation_annotation_d['annotation_type'] = $annotation_type[$index];
						$this->instantiation->insert_instantiation_annotations($instantiation_annotation_d);
					}
				}
			}
			/* Annotation End */
			/* Relation Start */
			if ($this->input->post('relation'))
			{
				$relation_src = $this->input->post('relation_source');
				$relation_ref = $this->input->post('relation_ref');
				$relation_type = $this->input->post('relation_type');
				foreach ($this->input->post('relation') as $index => $value)
				{
					if ( ! empty($value))
					{
						$relation['instantiations_id'] = $instantiation_id;
						$relation['relation_identifier'] = $value;
						$relation_types['relation_type'] = $relation_type[$index];
						if ( ! empty($relation_src[$index]))
							$relation_types['relation_type_source'] = $relation_src[$index];
						if ( ! empty($relation_ref[$index]))
							$relation_types['relation_type_ref'] = $relation_ref[$index];
						$db_relations = $this->assets_model->get_relation_types_all($relation_types);
						if (isset($db_relations) && isset($db_relations->id))
						{
							$relation['relation_types_id'] = $db_relations->id;
						}
						else
						{
							$relation['relation_types_id'] = $this->assets_model->insert_relation_types($relation_types);
						}
						$this->instantiation->insert_instantiation_relation($relation);
					}
				}
			}
			/* Relation End */
			/* Essence Track Frame Size Start */
			$db_essence_track = FALSE;
			if ($this->input->post('width') && $this->input->post('height'))
			{
				$width = $this->input->post('width');
				$height = $this->input->post('height');
				if ( ! empty($width) && ! empty($height))
				{
					$db_essence_track = TRUE;

					$track_frame_size_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_frame_sizes, array('width' => trim($this->input->post('width')), 'height' => trim($this->input->post('height'))));
					if ($track_frame_size_d)
					{
						$essence_tracks_d['essence_track_frame_sizes_id'] = $track_frame_size_d->id;
					}
					else
					{
						$essence_tracks_d['essence_track_frame_sizes_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_frame_sizes, array("width" => $this->input->post('width'), "height" => $this->input->post('height')));
					}
				}
			}
			/* Essence Track Frame Size End */
			/* Essence Track Frame Rate Start */
			if ($frame_rate = $this->input->post('frame_rate'))
			{
				if ( ! empty($frame_rate))
				{
					$db_essence_track = TRUE;
					$essence_tracks_d['frame_rate'] = $this->input->post('frame_rate');
				}
			}
			/* Essence Track Frame Rate End */
			/* Essence Track Playback Speed Start */
			if ($playback_speed = $this->input->post('playback_speed'))
			{
				if ( ! empty($playback_speed))
				{
					$db_essence_track = TRUE;
					$essence_tracks_d['playback_speed'] = $this->input->post('playback_speed');
				}
			}
			/* Essence Track Playback Speed End */
			/* Essence Track Sampling Rate Start */
			if ($sampling_rate = $this->input->post('sampling_rate'))
			{
				if ( ! empty($sampling_rate))
				{
					$db_essence_track = TRUE;
					$essence_tracks_d['sampling_rate'] = $this->input->post('sampling_rate');
				}
			}
			/* Essence Track Sampling Rate End */
			/* Essence Track Aspect Ratio Start */
			if ($aspect_ratio = $this->input->post('aspect_ratio'))
			{
				if ( ! empty($aspect_ratio))
				{
					$db_essence_track = TRUE;
					$essence_tracks_d['aspect_ratio'] = $this->input->post('aspect_ratio');
				}
			}
			/* Essence Track Aspect Ratio End */
			/* Essence Track Type Start */
			$essence_track_type_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_types, array('essence_track_type' => 'General'), TRUE);
			if (isset($essence_track_type_d) && isset($essence_track_type_d->id))
			{
				$essence_tracks_d['essence_track_types_id'] = $essence_track_type_d->id;
			}
			else
			{
				$essence_tracks_d['essence_track_types_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_types, array('essence_track_type' => 'General'));
			}
			/* Essence Track Type End */


			/* Essence Track Start */
			if ($db_essence_track)
			{
				$essence_tracks_d['instantiations_id'] = $instantiation_id;
				$this->pbcore_model->insert_record($this->pbcore_model->table_essence_tracks, $essence_tracks_d);
			}
			/* Essence Track End */

			// Update Sphnix Indexes
			$this->load->library('sphnixrt');
			$this->load->model('searchd_model');
			$this->load->helper('sphnixdata');
			$instantiation_list = $this->searchd_model->get_ins_index(array($instantiation_id));
			$new_list_info = make_instantiation_sphnix_array($instantiation_list[0]);
			$this->sphnixrt->insert('instantiations_list', $new_list_info, $instantiation_id);
			$asset_list = $this->searchd_model->get_asset_index(array($instantiation_list[0]->assets_id));
			$new_asset_info = make_assets_sphnix_array($asset_list[0], FALSE);
			$this->sphnixrt->update('assets_list', $new_asset_info);
			// End Update Sphnix Indexes

			$log = array('user_id' => $this->user_id, 'record_id' => $instantiation_id, 'record' => 'instantiation', 'type' => 'add', 'comments' => 'new instantiation created.');
			$this->audit_trail($log);
			if ($this->input->post('add_another'))
			{
				redirect('instantiations/add/' . $asset_id);
			}
			else
			{
				redirect('records/details/' . $asset_id);
			}
		}
		$data['asset_id'] = $asset_id;
		$data['pbcore_asset_date_types'] = $this->manage_asset->get_picklist_values(2);
		$data['pbcore_media_types'] = $this->manage_asset->get_picklist_values(11);
		$data['pbcore_generations'] = $this->manage_asset->get_picklist_values(12);
		$data['pbcore_relation_types'] = $this->manage_asset->get_picklist_values(7);
		$data['pbcore_standards'] = $this->manage_asset->get_picklist_values(14);
		$data['pbcore_colors'] = $this->manage_asset->get_picklist_values(15);
		$data['pbcore_physical_formats'] = $this->manage_asset->get_picklist_values(13);
		$data['nominations'] = $this->instantiation->get_nomination_status();
		$this->load->view('instantiations/add', $data);
	}

	/**
	 * Export CSV file 
	 */
	public function export_csv()
	{
		@ini_set("memory_limit", "3000M"); # 1GB
		@ini_set("max_execution_time", 999999999999); # 1GB
		$params = array('search' => '');
		$records = $this->sphinx->instantiations_list($params);
		if ($records['total_count'] <= 10000)
		{
			$records = $this->instantiation->export_limited_csv();

			if (count($records) > 0)
			{
				$this->load->library('excel');
				$this->excel->getActiveSheetIndex();
				$this->excel->getActiveSheet()->setTitle('Limited CSV');
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(45);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
				$this->excel->getActiveSheet()->getStyle("A1:F1")->getFont()->setBold(true);
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(0, 1, 'GUID');
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, 1, 'Unique ID');
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, 1, 'Title');
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, 1, 'Format');
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, 1, 'Duration');
				$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(5, 1, 'Priority');
				$row = 2;
				foreach ($records as $value)
				{
					$col = 0;
					foreach ($value as $field)
					{

						$this->excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($col, $row, $field);

						$col ++;
					}

					$row ++;
				}
				$filename = 'CSV_Export_' . time() . '.csv';
				$folder_path = 'uploads/csv_exports/' . date('Y') . '/' . date('M') . '/';
				$file_path = $folder_path . $filename;
				if ( ! is_dir($folder_path))
					mkdir($folder_path, 0777, TRUE);
				$file_path = $folder_path . $filename;
				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				$objWriter->save($file_path);
				$this->excel->disconnectWorksheets();
				unset($this->excel);
				echo json_encode(array('link' => 'true', 'msg' => site_url() . $file_path));
				exit_function();
			}
			else
			{
				echo json_encode(array('link' => 'false', 'msg' => 'No Record available for limited csv export'));
				exit_function();
			}
		}
		else
		{
			$query = $this->instantiation->export_limited_csv(TRUE);
			$record = array('user_id' => $this->user_id, 'status' => 0, 'export_query' => $query, 'query_loop' => ceil($records['total_count'] / 100000));
			$this->csv_job->insert_job($record);
			echo json_encode(array('link' => 'false', 'msg' => 'Email will be sent to you with the link of limited csv export.'));
			exit_function();
		}
	}

}

// END Instantiations Controller

// End of file instantiations.php 
/* Location: ./application/controllers/instantiations.php */
