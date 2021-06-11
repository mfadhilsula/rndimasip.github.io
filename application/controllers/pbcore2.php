<?php

// @codingStandardsIgnoreFile
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
 * Pbcore2 Class
 *
 * @category   Class
 * @package    CI
 * @subpackage Controller
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @link       https://ams.americanarchive.org
 */
class Pbcore2 extends CI_Controller
{

	/**
	 *
	 * constructor. Load layout,Model,Library and helpers
	 * 
	 */
	public $pbcore_path;

	function __construct()
	{
		parent::__construct();
		$this->load->model('cron_model');
		$this->load->model('assets_model');
		$this->load->model('instantiations_model', 'instant');
		$this->load->model('station_model');
		$this->pbcore_path = 'assets/export_pbcore2/';
		$this->load->model('pbcore_model');
	}

	/**
	 * Store all PBCore 2.x directories and data files in the database.
	 *  
	 */
	function process_dir()
	{
		set_time_limit(0);
//								$this->myLog("Calculating Number of Directories...");
		$this->cron_model->scan_directory($this->pbcore_path, $dir_files);
		$count = count($dir_files);
//								$this->myLog("Total Directories: $count");
		if (isset($count) && $count > 0)
		{
//												$this->myLog("Total Number of process: "	.	$count);
			$loop_counter = 0;
			$maxProcess = 5;
			foreach ($dir_files as $dir)
			{
				$cmd = escapeshellcmd('/usr/bin/php ' . $this->config->item('path') . 'index.php pbcore2 pbcore2_dir_child ' . base64_encode($dir));
				$this->config->item('path') . "cronlog/pbcore2_dir_child.log";
				$pidFile = $this->config->item('path') . "PIDs/pbcore2_dir_child/" . $loop_counter . ".txt";
				@exec('touch ' . $pidFile);
				$this->runProcess($cmd, $pidFile, $this->config->item('path') . "cronlog/pbcore2_dir_child.log");
				$file_text = file_get_contents($pidFile);
				$this->arrPIDs[$file_text] = $loop_counter;
				$proc_cnt = $this->procCounter();
				$loop_counter ++;
				while ($proc_cnt == $maxProcess)
				{
//																				$this->myLog('Number of Processes running: '	.	$loop_counter	.	'/.'	.	$count	.	' Sleeping ...');
					sleep(30);
					$proc_cnt = $this->procCounter();
				}
			}
//												$this->myLog("Waiting for all process to complete.");
			$proc_cnt = $this->procCounter();
			while ($proc_cnt > 0)
			{
//																echo	"Sleeping for 10 second...\n";
				sleep(10);
				echo "\010\010\010\010\010\010\010\010\010\010\010\010";
				echo "\n";
				$proc_cnt = $this->procCounter();
//																echo	"Number of Processes running: $proc_cnt/$maxProcess\n";
			}
		}
		echo "All Data Path Under {$this->pbcore_path} Directory Stored ";
		exit_function();
	}

	/**
	 * Store all PBCore 2.x sub files in the database
	 * @param type $path 
	 */
	function pbcore2_dir_child($path)
	{
		set_time_limit(0);
		@ini_set("memory_limit", "1000M"); # 1GB
		@ini_set("max_execution_time", 999999999999); # 1GB
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		$type = 'pbcore2';
		$file = 'manifest-md5.txt';
		$directory = base64_decode($path);
		$folder_status = 'complete';
		if ( ! $data_folder_id = $this->cron_model->get_data_folder_id_by_path($directory))
		{
			$data_folder_id = $this->cron_model->insert_data_folder(array("folder_path" => $directory, "created_at" => date('Y-m-d H:i:s'), "data_type" => $type));
		}
		if (isset($data_folder_id) && $data_folder_id > 0)
		{
			$data_result = file($directory . $file);
			if (isset($data_result) && ! is_empty($data_result))
			{
				$db_error_counter = 0;
				foreach ($data_result as $value)
				{
					$data_file = (explode(" ", $value));
					$data_file_path = trim(str_replace(array('\r\n', '\n', '<br>'), '', trim($data_file[1])));
//																				$this->myLog('Checking File '	.	$data_file_path);
					if (isset($data_file_path) && ! is_empty($data_file_path))
					{
						$file_path = trim($directory . $data_file_path);
						if (strpos($data_file_path, 'organization.xml') === false)
						{
							if (file_exists($file_path))
							{
								if ( ! $this->cron_model->is_pbcore_file_by_path($data_file_path, $data_folder_id))
								{
									$this->cron_model->insert_prcoess_data(array('file_type' => $type, 'file_path' => ($data_file_path), 'is_processed' => 0, 'created_at' => date('Y-m-d H:i:s'), "data_folder_id" => $data_folder_id));
								}
							}
							else
							{
								if ( ! $this->cron_model->is_pbcore_file_by_path($data_file_path, $data_folder_id))
								{
									$this->cron_model->insert_prcoess_data(array('file_type' => $type, 'file_path' => ($data_file_path), 'is_processed' => 0, 'created_at' => date('Y-m-d H:i:s'), "data_folder_id" => $data_folder_id, 'status_reason' => 'file_not_found'));
								}
								$folder_status = 'incomplete';
							}
						}
					}
					if ($db_error_counter == 20000)
					{
						$db_error_counter = 0;
						sleep(3);
					}
					$db_error_counter ++;
				}
			}
//												$this->myLog('folder Id '	.	$data_folder_id	.	' => folder_status '	.	$folder_status);
			$this->cron_model->update_data_folder(array('updated_at' => date('Y-m-d H:i:s'), 'folder_status' => $folder_status), $data_folder_id);
		}
	}

	/**
	 * 
	 * Process all pending PBCore 2.x files.
	 *
	 */
	function process_xml_file()
	{
		$folders = $this->cron_model->get_all_pbcoretwo_folder();
		if (isset($folders) && ! empty($folders))
		{
			foreach ($folders as $folder)
			{
				$data1 = file_get_contents($folder->folder_path . 'data/organization.xml');
				$x = @simplexml_load_string($data1);
				unset($data1);
				$data = xmlObjToArr($x);
				$station_cpb_id = $data['children']['cpb-id'][0]['text'];
				if (isset($station_cpb_id))
				{
					$count = $this->cron_model->get_pbcore_file_count_by_folder_id($folder->id);
					if (isset($count) && $count > 0)
					{
						$maxProcess = 50;
						$limit = 500;
						$loop_end = ceil($count / $limit);
						$this->myLog("Run $loop_end times  $maxProcess at a time");
						for ($loop_counter = 0; $loop_end > $loop_counter; $loop_counter ++ )
						{
							$offset = $loop_counter * $limit;
							$this->myLog("Started $offset~$limit of $count");
							$cmd = escapeshellcmd('/usr/bin/php ' . $this->config->item('path') . 'index.php pbcore2 process_xml_file_child ' . $folder->id . ' ' . $station_cpb_id . ' ' . $offset . ' ' . $limit);
							$pidFile = $this->config->item('path') . "PIDs/processxmlfile2/" . $loop_counter . ".txt";
							@exec('touch ' . $pidFile);
							$this->runProcess($cmd, $pidFile, $this->config->item('path') . "cronlog/processxmlfile2.log");
							$file_text = file_get_contents($pidFile);
							$this->arrPIDs[$file_text] = $loop_counter;
							$proc_cnt = $this->procCounter();
							while ($proc_cnt == $maxProcess)
							{
								$this->myLog("Sleeping ...");
								sleep(30);
								$proc_cnt = $this->procCounter();
								echo "Number of Processes running : $proc_cnt/$maxProcess\n";
							}
						}
						$this->myLog("Waiting for all process to complete");
						$proc_cnt = $this->procCounter();
						while ($proc_cnt > 0)
						{
							echo "Sleeping....\n";
							sleep(10);
							echo "\010\010\010\010\010\010\010\010\010\010\010\010";
							echo "\n";
							$proc_cnt = $this->procCounter();
							echo "Number of Processes running : $proc_cnt/$maxProcess\n";
						}
					}
				}
				unset($x);
				unset($data);
			}
		}
	}

	/**
	 * Process all pending PBCore 1.x files.
	 * @param type $folder_id
	 * @param type $station_cpb_id
	 * @param type $offset
	 * @param type $limit 
	 */
	function process_xml_file_child($folder_id, $station_cpb_id, $offset = 0, $limit = 100)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		$station_data = $this->station_model->get_station_by_cpb_id($station_cpb_id);
		if (isset($station_data) && ! empty($station_data) && isset($station_data->id))
		{
			$folder_data = $this->cron_model->get_data_folder_by_id($folder_id);
			if ($folder_data)
			{
				$data_files = $this->cron_model->get_pbcore_file_by_folder_id($folder_data->id, $offset, $limit);
				if (isset($data_files) && ! is_empty($data_files))
				{
					foreach ($data_files as $d_file)
					{
						if ($d_file->is_processed == 0)
						{
							$this->cron_model->update_prcoess_data(array("processed_start_at" => date('Y-m-d H:i:s')), $d_file->id);
							$file_path = '';
							$file_path = trim($folder_data->folder_path . $d_file->file_path);
							if (file_exists($file_path))
							{
//																																$this->myLog("Currently Parsing Files "	.	$file_path);
								$asset_data = file_get_contents($file_path);
								if (isset($asset_data) && ! empty($asset_data))
								{
									$asset_xml_data = @simplexml_load_string($asset_data);
									$asset_d = xmlObjToArr($asset_xml_data);

									//$this->db->trans_start	();
									$asset_id = $this->assets_model->insert_assets(array("stations_id" => $station_data->id, "created" => date("Y-m-d H:i:s")));
//																																				echo	"\n in Process \n";
									$asset_children = $asset_d['children'];
									if (isset($asset_children))
									{
										//echo "<pre>";
										//print_r($asset_children);
										// Assets Start
//																																								$this->myLog(" Assets Start ");
										$this->import_assets($asset_children, $asset_id);
//																																								$this->myLog(" Assets Ends ");
										// Assets End
										// Instantiation Start
//																																								$this->myLog(" Instantiation Start ");
										$this->import_instantiations($asset_children, $asset_id);
										// Instantiation End
//																																								$this->myLog(" Instantiation End ");
										$this->cron_model->update_prcoess_data(array('is_processed' => 1, "processed_at" => date('Y-m-d H:i:s'), 'status_reason' => 'Complete'), $d_file->id);
									}
									else
									{
										$this->myLog(" Attribut children not found " . $file_path);
										$this->cron_model->update_prcoess_data(array('status_reason' => 'attribut_children_not_found'), $d_file->id);
									}

									//$this->db->trans_complete	();
									unset($asset_d);
									unset($asset_xml_data);
									unset($asset_data);
								}
								else
								{
									$this->myLog(" Data is empty in file " . $file_path);
									$this->cron_model->update_prcoess_data(array('status_reason' => 'data_empty'), $d_file->id);
								}
							}
							else
							{
								$this->myLog(" Is File Check Issues " . $file_path);
								$this->cron_model->update_prcoess_data(array('status_reason' => 'file_not_found'), $d_file->id);
							}
						}
						else
						{
							$this->myLog(" Already Processed " . $file_path);
							$this->cron_model->update_prcoess_data(array('status_reason' => 'already_processed'), $d_file->id);
						}
					}
					unset($data_files);
				}
				else
				{
					$this->myLog(" Data files not found " . $file_path);
				}
			}
			else
			{
				$this->myLog(" folders Data not found " . $file_path);
			}
		}
		else
		{
			$this->myLog(" Station data not Found against " . $station_cpb_id);
		}
	}

	/**
	 * Process Asset Elements and store into the database.
	 * @param type $asset_children
	 * @param type $asset_id 
	 */
	function import_assets($asset_children, $asset_id)
	{
//								debug($asset_children,	FALSE);
		// Asset Type Start //
		if (isset($asset_children['pbcoreassettype']))
		{
			foreach ($asset_children['pbcoreassettype'] as $pbcoreassettype)
			{

				if (isset($pbcoreassettype['text']) && ! is_empty($pbcoreassettype['text']))
				{
					$asset_type_detail = array();
					$asset_type_detail['assets_id'] = $asset_id;
					if ($asset_type = $this->assets_model->get_assets_type_by_type($pbcoreassettype['text']))
					{
						$asset_type_detail['asset_types_id'] = $asset_type->id;
					}
					else
					{
						$asset_type_detail['asset_types_id'] = $this->assets_model->insert_asset_types(array("asset_type" => $pbcoreassettype['text']));
					}
					$this->assets_model->insert_assets_asset_types($asset_type_detail);
					unset($asset_type_detail);
				}
			}
		}
		// Asset Type End //
		// Asset Date and Type Start //
		if (isset($asset_children['pbcoreassetdate']))
		{
			foreach ($asset_children['pbcoreassetdate'] as $pbcoreassetdate)
			{
				$asset_date_info = array();
				$asset_date_info['assets_id'] = $asset_id;
				if (isset($pbcoreassetdate['text']) && ! is_empty($pbcoreassetdate['text']))
				{
					$asset_date_info['asset_date'] = $pbcoreassetdate['text'];
					if (isset($pbcoreassetdate['attributes']['datetype']) && ! is_empty($pbcoreassetdate['attributes']['datetype']))
					{
						if ($asset_date_type = $this->instant->get_date_types_by_type($pbcoreassetdate['attributes']['datetype']))
						{
							$asset_date_info['date_types_id'] = $asset_date_type->id;
						}
						else
						{
							$asset_date_info['date_types_id'] = $this->instant->insert_date_types(array("date_type" => $pbcoreassetdate['attributes']['datetype']));
						}
					}
					$this->assets_model->insert_asset_date($asset_date_info);
				}
			}
		}
		// Asset Date and Type End //
		// Asset Identifier Start //
		if (isset($asset_children['pbcoreidentifier']))
		{
			foreach ($asset_children['pbcoreidentifier'] as $pbcoreidentifier)
			{

				$identifier_detail = array();
				if (isset($pbcoreidentifier['text']) && ! is_empty($pbcoreidentifier['text']))
				{
					$identifier_detail['assets_id'] = $asset_id;
					$identifier_detail['identifier'] = trim($pbcoreidentifier['text']);
					$identifier_detail['identifier_source'] = '';
					$identifier_detail['identifier_ref'] = '';
					if (isset($pbcoreidentifier['attributes']['source']) && ! is_empty($pbcoreidentifier['attributes']['source']))
					{
						$identifier_detail['identifier_source'] = trim($pbcoreidentifier['attributes']['source']);
					}
					if (isset($pbcoreidentifier['attributes']['ref']) && ! is_empty($pbcoreidentifier['attributes']['ref']))
					{
						$identifier_detail['identifier_ref'] = trim($pbcoreidentifier['attributes']['ref']);
					}
					$this->assets_model->insert_identifiers($identifier_detail);
					unset($identifier_detail);
				}
			}
		}


		// Asset Identifier End //
		// Asset Title Start //

		if (isset($asset_children['pbcoretitle']))
		{
			foreach ($asset_children['pbcoretitle'] as $pbcoretitle)
			{
				$title_detail = array();
				if (isset($pbcoretitle['text']) && ! is_empty($pbcoretitle['text']))
				{
					$title_detail['assets_id'] = $asset_id;
					$title_detail['title'] = $pbcoretitle['text'];
					if (isset($pbcoretitle['attributes']['titletype']) && ! is_empty($pbcoretitle['attributes']['titletype']))
					{
						$asset_title_types = $this->assets_model->get_asset_title_types_by_title_type($pbcoretitle['attributes']['titletype']);
						if (isset($asset_title_types) && isset($asset_title_types->id))
						{
							$asset_title_types_id = $asset_title_types->id;
						}
						else
						{
							$asset_title_types_id = $this->assets_model->insert_asset_title_types(array("title_type" => $pbcoretitle['attributes']['titletype']));
						}
						$title_detail['asset_title_types_id'] = $asset_title_types_id;
					}
					if (isset($pbcoretitle['attributes']['ref']) && ! is_empty($pbcoretitle['attributes']['ref']))
					{
						$title_detail['title_ref'] = $pbcoretitle['attributes']['ref'];
					}
					if (isset($pbcoretitle['attributes']['source']) && ! is_empty($pbcoretitle['attributes']['source']))
					{
						$title_detail['title_source'] = $pbcoretitle['attributes']['source'];
					}
					$title_detail['created'] = date('Y-m-d H:i:s');
					$this->assets_model->insert_asset_titles($title_detail);
					unset($title_detail);
				}
			}
		}
		// Asset Title End  //
		// Asset Subject Start //

		if (isset($asset_children['pbcoresubject']))
		{
			foreach ($asset_children['pbcoresubject'] as $pbcoresubject)
			{
				$subject_detail = array();
				if (isset($pbcoresubject['text']) && ! is_empty($pbcoresubject['text']))
				{
					$subject_detail['assets_id'] = $asset_id;
					if (isset($pbcoresubject['attributes']['subjecttype']) && ! is_empty($pbcoresubject['attributes']['subjecttype']))
					{
						$subject_d = array();
						$subject_d['subject'] = $pbcoresubject['attributes']['subjecttype'];
						if (isset($pbcoresubject['attributes']['ref']) && ! is_empty($pbcoresubject['attributes']['ref']))
						{
							$subject_d['subject_ref'] = $pbcoresubject['attributes']['ref'];
						}
						if (isset($pbcoresubject['attributes']['source']) && ! is_empty($pbcoresubject['attributes']['source']))
						{
							$subject_d['subject_source'] = $pbcoresubject['attributes']['source'];
						}

						$subjects = $this->assets_model->get_subjects_id_by_subject($pbcoresubject['attributes']['subjecttype']);
						if (isset($subjects) && isset($subjects->id))
						{
							$subject_id = $subjects->id;
						}
						else
						{
							$subject_id = $this->assets_model->insert_subjects($subject_d);
						}
						$subject_detail['subjects_id'] = $subject_id;
						$assets_subject_id = $this->assets_model->insert_assets_subjects($subject_detail);
					}
				}
			}
		}
		// Asset Subject End  //
		// Asset Description Start //

		if (isset($asset_children['pbcoredescription']))
		{
			foreach ($asset_children['pbcoredescription'] as $pbcoredescription)
			{
				$asset_descriptions_d = array();
				if (isset($pbcoredescription['text']) && ! is_empty($pbcoredescription['text']))
				{
					$asset_descriptions_d['assets_id'] = $asset_id;
					$asset_descriptions_d['description'] = $pbcoredescription['text'];
					if (isset($pbcoredescription['attributes']['descriptiontype']) && ! is_empty($pbcoredescription['attributes']['descriptiontype']))
					{
						$asset_description_type = $this->assets_model->get_description_by_type($pbcoredescription['attributes']['descriptiontype']);
						if (isset($asset_description_type) && isset($asset_description_type->id))
						{
							$asset_description_types_id = $asset_description_type->id;
						}
						else
						{
							$asset_description_types_id = $this->assets_model->insert_description_types(array("description_type" => $pbcoredescription['attributes']['descriptiontype']));
						}
						$asset_descriptions_d['description_types_id'] = $asset_description_types_id;
					}
					$this->assets_model->insert_asset_descriptions($asset_descriptions_d);
				}
			}
		}
		// Asset Description End  //
		// Asset Genre Start //

		if (isset($asset_children['pbcoregenre']))
		{
			foreach ($asset_children['pbcoregenre'] as $pbcoregenre)
			{
				$asset_genre_d = array();
				$asset_genre = array();
				$asset_genre['assets_id'] = $asset_id;
				if (isset($pbcoregenre['text']) && ! is_empty($pbcoregenre['text']))
				{
					$asset_genre_d['genre'] = $pbcoregenre['text'];
					$asset_genre_type = $this->assets_model->get_genre_type($pbcoregenre['text']);
					if (isset($asset_genre_type) && isset($asset_genre_type->id))
					{
						$asset_genre['genres_id'] = $asset_genre_type->id;
					}
					else
					{
						if (isset($pbcoregenre['attributes']['source']) && ! is_empty($pbcoregenre['attributes']['source']))
						{
							$asset_genre_d['genre_source'] = $pbcoregenre['attributes']['source'];
						}
						if (isset($pbcoregenre['attributes']['ref']) && ! is_empty($pbcoregenre['attributes']['ref']))
						{
							$asset_genre_d['genre_ref'] = $pbcoregenre['attributes']['ref'];
						}
						$asset_genre_id = $this->assets_model->insert_genre($asset_genre_d);
						$asset_genre['genres_id'] = $asset_genre_id;
					}
					$this->assets_model->insert_asset_genre($asset_genre);
				}
			}
		}
		// Asset Genre End  //
		// Asset Coverage Start  //
		if (isset($asset_children['pbcorecoverage']))
		{
			foreach ($asset_children['pbcorecoverage'] as $pbcore_coverage)
			{
				$coverage = array();
				$coverage['assets_id'] = $asset_id;
				if (isset($pbcore_coverage['children']['coverage'][0]['text']) && ! is_empty($pbcore_coverage['children']['coverage'][0]['text']))
				{
					$coverage['coverage'] = $pbcore_coverage['children']['coverage'][0]['text'];
					if (isset($pbcore_coverage['children']['coveragetype'][0]['text']) && ! is_empty($pbcore_coverage['children']['coveragetype'][0]['text']))
					{
						$coverage['coverage_type'] = $pbcore_coverage['children']['coveragetype'][0]['text'];
					}
					$asset_coverage = $this->assets_model->insert_coverage($coverage);
				}
			}
		}
		// Asset Coverage End  //
		// Asset Audience Level Start //

		if (isset($asset_children['pbcoreaudiencelevel']))
		{
			foreach ($asset_children['pbcoreaudiencelevel'] as $pbcoreaudiencelevel)
			{
				$audience_level = array();
				$asset_audience_level = array();
				$asset_audience_level['assets_id'] = $asset_id;
				if (isset($pbcoreaudiencelevel['text']) && ! is_empty($pbcoreaudiencelevel['text']))
				{
					$audience_level['audience_level'] = trim($pbcoreaudiencelevel['text']);
					if (isset($pbcoreaudiencelevel['attributes']['source']) && ! is_empty($pbcoreaudiencelevel['attributes']['source']))
					{
						$audience_level['audience_level_source'] = $pbcoreaudiencelevel['attributes']['source'];
					}
					if (isset($pbcoreaudiencelevel['attributes']['ref']) && ! is_empty($pbcoreaudiencelevel['attributes']['ref']))
					{
						$audience_level['audience_level_ref'] = $pbcoreaudiencelevel['attributes']['ref'];
					}
					$db_audience_level = $this->assets_model->get_audience_level($pbcoreaudiencelevel['text']);
					if (isset($db_audience_level) && isset($db_audience_level->id))
					{
						$asset_audience_level['audience_levels_id'] = $db_audience_level->id;
					}
					else
					{
						$asset_audience_level['audience_levels_id'] = $this->assets_model->insert_audience_level($audience_level);
					}
					$asset_audience = $this->assets_model->insert_asset_audience($asset_audience_level);
				}
			}
		}
		// Asset Audience Level End  //
		// Asset Audience Rating Start //

		if (isset($asset_children['pbcoreaudiencerating']))
		{
			foreach ($asset_children['pbcoreaudiencerating'] as $pbcoreaudiencerating)
			{
				$audience_rating = array();
				$asset_audience_rating = array();
				$asset_audience_rating['assets_id'] = $asset_id;
				if (isset($pbcoreaudiencerating['text']) && ! is_empty($pbcoreaudiencerating['text']))
				{
					$db_audience_rating = $this->assets_model->get_audience_rating($pbcoreaudiencerating['text']);
					if (isset($db_audience_rating) && isset($db_audience_rating->id))
					{
						$asset_audience_rating['audience_ratings_id'] = $db_audience_rating->id;
					}
					else
					{
						$audience_rating['audience_rating'] = $pbcoreaudiencerating['text'];
						if (isset($pbcoreaudiencerating['attributes']['source']) && ! is_empty($pbcoreaudiencerating['attributes']['source']))
						{
							$audience_rating['audience_rating_source'] = $pbcoreaudiencerating['attributes']['source'];
						}
						if (isset($pbcoreaudiencerating['attributes']['ref']) && ! is_empty($pbcoreaudiencerating['attributes']['ref']))
						{
							$audience_rating['audience_rating_ref'] = $pbcoreaudiencerating['attributes']['ref'];
						}
						$asset_audience_rating['audience_ratings_id'] = $this->assets_model->insert_audience_rating($audience_rating);
					}
					$asset_audience_rate = $this->assets_model->insert_asset_audience_rating($asset_audience_rating);
				}
			}
		}
		// Asset Audience Rating End  //
		// Asset Annotation Start //

		if (isset($asset_children['pbcoreannotation']))
		{
			foreach ($asset_children['pbcoreannotation'] as $pbcoreannotation)
			{
				$annotation = array();
				$annotation['assets_id'] = $asset_id;
				if (isset($pbcoreannotation['text']) && ! is_empty($pbcoreannotation['text']))
				{
					$annotation['annotation'] = $pbcoreannotation['text'];
					if (isset($pbcoreannotation['attributes']['annotationtype']) && ! is_empty($pbcoreannotation['attributes']['annotationtype']))
					{
						$annotation['annotation_type'] = $pbcoreannotation['attributes']['annotationtype'];
					}
					if (isset($pbcoreannotation['attributes']['ref']) && ! is_empty($pbcoreannotation['attributes']['ref']))
					{
						$annotation['annotation_ref'] = $pbcoreannotation['attributes']['ref'];
					}

					$asset_annotation = $this->assets_model->insert_annotation($annotation);
				}
			}
		}
		// Asset Annotation End  //
		// Asset Relation Start  //
		if (isset($asset_children['pbcorerelation']))
		{
			foreach ($asset_children['pbcorerelation'] as $pbcorerelation)
			{
				$assets_relation = array();
				$assets_relation['assets_id'] = $asset_id;
				$relation_types = array();
				if (isset($pbcorerelation['children']['pbcorerelationidentifier'][0]['text']) && ! is_empty($pbcorerelation['children']['pbcorerelationidentifier'][0]['text']))
				{
					$assets_relation['relation_identifier'] = $pbcorerelation['children']['pbcorerelationidentifier'][0]['text'];
					if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['text']) && ! is_empty($pbcorerelation['children']['pbcorerelationtype'][0]['text']))
					{

						$relation_types['relation_type'] = $pbcorerelation['children']['pbcorerelationtype'][0]['text'];
						if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['source']) && ! is_empty($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['source']))
						{
							$relation_types['relation_type_source'] = $pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['source'];
						}
						if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['ref']) && ! is_empty($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['ref']))
						{
							$relation_types['relation_type_ref'] = $pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['ref'];
						}
						$db_relations = $this->assets_model->get_relation_types($relation_types['relation_type']);
						if (isset($db_relations) && isset($db_relations->id))
						{
							$assets_relation['relation_types_id'] = $db_relations->id;
						}
						else
						{
							$assets_relation['relation_types_id'] = $this->assets_model->insert_relation_types($relation_types);
						}
					}
					$this->assets_model->insert_asset_relation($assets_relation);
				}
			}
		}
		// Asset Relation End  //
		// Asset Creator Start  //
		if (isset($asset_children['pbcorecreator']))
		{
			foreach ($asset_children['pbcorecreator'] as $pbcore_creator)
			{
				$assets_creators_roles_d = array();
				$assets_creators_roles_d['assets_id'] = $asset_id;

				if (isset($pbcore_creator['children']['creator'][0]['text']) && ! is_empty($pbcore_creator['children']['creator'][0]['text']))
				{

					$creater['creator_name'] = $pbcore_creator['children']['creator'][0]['text'];
					if (isset($pbcore_creator['children']['creator'][0]['attributes']['affiliation']) && ! is_empty($pbcore_creator['children']['creator'][0]['attributes']['affiliation']))
					{
						$creater['creator_affiliation'] = $pbcore_creator['children']['creator'][0]['attributes']['affiliation'];
					}
					if (isset($pbcore_creator['children']['creator'][0]['attributes']['ref']) && ! is_empty($pbcore_creator['children']['creator'][0]['attributes']['ref']))
					{
						$creater['creator_ref'] = $pbcore_creator['children']['creator'][0]['attributes']['ref'];
					}
					$creator_d = $this->assets_model->get_creator_by_creator_name($creater['creator_name']);
					if (isset($creator_d) && isset($creator_d->id))
					{
						$assets_creators_roles_d['creators_id'] = $creator_d->id;
					}
					else
					{
						$assets_creators_roles_d['creators_id'] = $this->assets_model->insert_creators($creater);
					}
				}
				if (isset($pbcore_creator['children']['creatorrole'][0]['text']) && ! is_empty($pbcore_creator['children']['creatorrole'][0]['text']))
				{
					$role['creator_role'] = $pbcore_creator['children']['creatorrole'][0]['text'];
					if (isset($pbcore_creator['children']['creatorrole'][0]['attributes']['source']) && ! is_empty($pbcore_creator['children']['creatorrole'][0]['attributes']['source']))
					{

						$role['creator_role_source'] = $pbcore_creator['children']['creatorrole'][0]['attributes']['source'];
					}
					if (isset($pbcore_creator['children']['creatorrole'][0]['attributes']['ref']) && ! is_empty($pbcore_creator['children']['creatorrole'][0]['attributes']['ref']))
					{

						$role['creator_role_ref'] = $pbcore_creator['children']['creatorrole'][0]['attributes']['ref'];
					}
					$creator_role = $this->assets_model->get_creator_role_by_role($pbcore_creator['children']['creatorrole'][0]['text']);
					if (isset($creator_role) && isset($creator_role->id))
					{
						$assets_creators_roles_d['creator_roles_id'] = $creator_role->id;
					}
					else
					{
						$assets_creators_roles_d['creator_roles_id'] = $this->assets_model->insert_creator_roles($role);
					}
				}
				if ((isset($assets_creators_roles_d['creators_id']) && ! is_empty($assets_creators_roles_d['creators_id'])) || (isset($assets_creators_roles_d['creator_roles_id']) && ! is_empty($assets_creators_roles_d['creator_roles_id'])))
				{
					$assets_creators_roles_id = $this->assets_model->insert_assets_creators_roles($assets_creators_roles_d);
				}
			}
		}
		// Asset Creator End  //
		// Asset Contributor Start  //
		if (isset($asset_children['pbcorecontributor']))
		{
			foreach ($asset_children['pbcorecontributor'] as $pbcore_contributor)
			{
				$assets_contributors_d = array();
				$assets_contributors_d['assets_id'] = $asset_id;
				if (isset($pbcore_contributor['children']['contributor'][0]['text']) && ! is_empty($pbcore_contributor['children']['contributor'][0]['text']))
				{
					$contributor_info['contributor_name'] = $pbcore_contributor['children']['contributor'][0]['text'];

					if (isset($pbcore_contributor['children']['contributor'][0]['attributes']['affiliation']) && ! is_empty($pbcore_contributor['children']['contributor'][0]['attributes']['affiliation']))
					{
						$contributor_info['contributor_affiliation'] = $pbcore_contributor['children']['contributor'][0]['attributes']['affiliation'];
					}
					if (isset($pbcore_contributor['children']['contributor'][0]['attributes']['ref']) && ! is_empty($pbcore_contributor['children']['contributor'][0]['attributes']['ref']))
					{
						$contributor_info['contributor_ref'] = $pbcore_contributor['children']['contributor'][0]['attributes']['ref'];
					}
					$contributor_d = $this->assets_model->get_contributor_by_contributor_name($contributor_info['contributor_name']);
					if (isset($contributor_d) && isset($contributor_d->id))
					{
						$assets_contributors_d['contributors_id'] = $contributor_d->id;
					}
					else
					{
						$last_insert_id = $this->assets_model->insert_contributors($contributor_info);
						if (isset($last_insert_id) && $last_insert_id > 0)
						{
							$assets_contributors_d['contributors_id'] = $last_insert_id;
						}
					}
				}
				if (isset($pbcore_contributor['children']['contributorrole'][0]['text']) && ! is_empty($pbcore_contributor['children']['contributorrole'][0]['text']))
				{
					$contributorrole_info['contributor_role'] = $pbcore_contributor['children']['contributorrole'][0]['text'];

					if (isset($pbcore_contributor['children']['contributorrole'][0]['attributes']['source']) && ! is_empty($pbcore_contributor['children']['contributorrole'][0]['attributes']['source']))
					{

						$contributorrole_info['contributor_role_source'] = $pbcore_contributor['children']['contributorrole'][0]['attributes']['source'];
					}
					if (isset($pbcore_contributor['children']['contributorrole'][0]['attributes']['ref']) && ! is_empty($pbcore_contributor['children']['contributorrole'][0]['attributes']['ref']))
					{
						$contributorrole_info['contributor_role_ref'] = $pbcore_contributor['children']['contributorrole'][0]['attributes']['ref'];
					}
					$contributor_role = $this->assets_model->get_contributor_role_by_role($contributorrole_info['contributor_role']);
					if (isset($contributor_role) && isset($contributor_role->id))
					{
						$assets_contributors_d['contributor_roles_id'] = $contributor_role->id;
					}
					else
					{
						$last_insert_id = $this->assets_model->insert_contributor_roles($contributorrole_info);
						if (isset($last_insert_id) && $last_insert_id > 0)
						{
							$assets_contributors_d['contributor_roles_id'] = $last_insert_id;
						}
					}
				}
				if ((isset($assets_contributors_d['contributors_id']) && ! is_empty($assets_contributors_d['contributors_id'])) ||
				(isset($assets_contributors_d['contributor_roles_id']) && ! is_empty($assets_contributors_d['contributor_roles_id'])))
				{
					$assets_contributors_roles_id = $this->assets_model->insert_assets_contributors_roles($assets_contributors_d);
				}
			}
		}
		// Asset Contributor End  //
		// Asset Publisher Start  //
		if (isset($asset_children['pbcorepublisher']))
		{
			foreach ($asset_children['pbcorepublisher'] as $pbcorepublisher)
			{
				$assets_publisher_d = array();
				$assets_publisher_d['assets_id'] = $asset_id;
				if (isset($pbcorepublisher['children']['publisher'][0]['text']) && ! is_empty($pbcorepublisher['children']['publisher'][0]['text']))
				{
					$publisher_info['publisher'] = $pbcorepublisher['children']['publisher'][0]['text'];
					if (isset($pbcorepublisher['children']['publisher'][0]['attributes']['affiliation']) && ! is_empty($pbcorepublisher['children']['publisher'][0]['attributes']['affiliation']))
					{

						$publisher_info['publisher_affiliation'] = $pbcorepublisher['children']['publisher'][0]['attributes']['affiliation'];
					}
					if (isset($pbcorepublisher['children']['publisher'][0]['attributes']['ref']) && ! is_empty($pbcorepublisher['children']['publisher'][0]['attributes']['ref']))
					{

						$publisher_info['publisher_ref'] = $pbcorepublisher['children']['publisher'][0]['attributes']['ref'];
					}
					$publisher_d = $this->assets_model->get_publishers_by_publisher($publisher_info['publisher']);
					if (isset($publisher_d) && isset($publisher_d->id))
					{
						$assets_publisher_d['publishers_id'] = $publisher_d->id;
					}
					else
					{
						$assets_publisher_d['publishers_id'] = $this->assets_model->insert_publishers($publisher_info);
					}
				}
				if (isset($pbcorepublisher['children']['publisherrole'][0]['text']) && ! is_empty($pbcorepublisher['children']['publisherrole'][0]['text']))
				{

					$publisher_role_info['publisher_role'] = $pbcorepublisher['children']['publisherrole'][0]['text'];
					if (isset($pbcorepublisher['children']['publisherrole'][0]['attributes']['source']) && ! is_empty($pbcorepublisher['children']['publisherrole'][0]['attributes']['source']))
					{

						$publisher_role_info['publisher_role_source'] = $pbcorepublisher['children']['publisherrole'][0]['attributes']['source'];
					}
					if (isset($pbcorepublisher['children']['publisherrole'][0]['attributes']['ref']) && ! is_empty($pbcorepublisher['children']['publisherrole'][0]['attributes']['ref']))
					{

						$publisher_role_info['publisher_role_ref'] = $pbcorepublisher['children']['publisherrole'][0]['attributes']['ref'];
					}
					$publisher_role = $this->assets_model->get_publisher_role_by_role($publisher_role_info['publisher_role']);
					if (isset($publisher_role) && isset($publisher_role->id))
					{
						$assets_publisher_d['publisher_roles_id'] = $publisher_role->id;
					}
					else
					{
						$assets_publisher_d['publisher_roles_id'] = $this->assets_model->insert_publisher_roles($publisher_role_info);
					}
				}
				if ((isset($assets_publisher_d['publishers_id']) && ! is_empty($assets_publisher_d['publishers_id'])) || (isset($assets_publisher_d['publisher_roles_id']) && ! is_empty($assets_publisher_d['publisher_roles_id'])))
				{
					$assets_publishers_roles_id = $this->assets_model->insert_assets_publishers_role($assets_publisher_d);
				}
			}
		}
		// Asset Publisher End  //
		// Asset Right Summary Start  //
		if (isset($asset_children['pbcorerightssummary']))
		{
			foreach ($asset_children['pbcorerightssummary'] as $pbcore_rights)
			{
				$rights_summary_d = array();
				$rights_summary_d['assets_id'] = $asset_id;
				if (isset($pbcore_rights['children']['rightssummary'][0]['text']) && ! is_empty($pbcore_rights['children']['rightssummary'][0]['text']))
				{
					$rights_summary_d['rights'] = $pbcore_rights['children']['rightssummary'][0]['text'];
					if (isset($pbcore_rights['children']['rightslink'][0]['text']) && ! is_empty($pbcore_rights['children']['rightslink'][0]['text']))
					{
						$rights_summary_d['rights_link'] = $pbcore_rights['children']['rightslink'][0]['text'];
					}
					$this->assets_model->insert_rights_summaries($rights_summary_d);
				}
			}
		}
		// Asset Right Summary End  //
		// Asset Extension Start //

		if (isset($asset_children['pbcoreextension']) && ! is_empty($asset_children['pbcoreextension']))
		{
			foreach ($asset_children['pbcoreextension'] as $pbcore_extension)
			{
				$map_extension = $pbcore_extension['children']['extensionwrap'][0]['children'];
				if (isset($map_extension['extensionauthorityused'][0]['text']) && ! is_empty($map_extension['extensionauthorityused'][0]['text']))
				{
					$extension_d = array();
					$extension_d['assets_id'] = $asset_id;
					if (strtolower($map_extension['extensionauthorityused'][0]['text']) == strtolower('AACIP Record Tags'))
					{

						if (isset($map_extension['extensionvalue'][0]['text']) && ! is_empty($map_extension['extensionvalue'][0]['text']))
						{
							if ( ! preg_match('/historical value|risk of loss|local cultural value|potential to repurpose/', strtolower($map_extension['extensionvalue'][0]['text']), $match_text))
							{
								$extension_d['extension_element'] = $map_extension['extensionauthorityused'][0]['text'];
								$extension_d['extension_value'] = $map_extension['extensionvalue'][0]['text'];
								$this->assets_model->insert_extensions($extension_d);
							}
						}
					}
					else if (strtolower($map_extension['extensionauthorityused'][0]['text']) != strtolower('AACIP Record Nomination Status'))
					{

						$extension_d['extension_element'] = $map_extension['extensionauthorityused'][0]['text'];
						if (isset($map_extension['extensionvalue'][0]['text']) && ! is_empty($map_extension['extensionvalue'][0]['text']))
						{
							$extension_d['extension_value'] = $map_extension['extensionvalue'][0]['text'];
						}
						$this->assets_model->insert_extensions($extension_d);
					}
				}
			}
		}
		// Asset Extension End //
	}

	/**
	 * Process Instantiation Elements and store into the database.
	 * @param type $asset_children
	 * @param type $asset_id 
	 */
	function import_instantiations($asset_children, $asset_id)
	{
		if (isset($asset_children['pbcoreinstantiation']))
		{
			foreach ($asset_children['pbcoreinstantiation'] as $pbcoreinstantiation)
			{
				if (isset($pbcoreinstantiation['children']) && ! is_empty($pbcoreinstantiation['children']))
				{
					$pbcoreinstantiation_child = $pbcoreinstantiation['children'];
					$instantiations_d = array();
					$instantiations_d['assets_id'] = $asset_id;
					// Instantiation Location Start //
					if (isset($pbcoreinstantiation_child['instantiationlocation'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationlocation'][0]['text']))
					{
						$instantiations_d['location'] = $pbcoreinstantiation_child['instantiationlocation'][0]['text'];
					}
					// Instantiation Location End //
					// Instantiation Standard Start //
					if (isset($pbcoreinstantiation_child['instantiationstandard'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationstandard'][0]['text']))
					{
						$instantiations_d['standard'] = $pbcoreinstantiation_child['instantiationstandard'][0]['text'];
					}
					// Instantiation Standard End //
					// Instantiation Media Type Start //
					if (isset($pbcoreinstantiation_child['instantiationmediatype'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationmediatype'][0]['text']))
					{
						$inst_media_type = $this->instant->get_instantiation_media_types_by_media_type($pbcoreinstantiation_child['instantiationmediatype'][0]['text']);
						if ( ! is_empty($inst_media_type))
						{
							$instantiations_d['instantiation_media_type_id'] = $inst_media_type->id;
						}
						else
						{
							$instantiations_d['instantiation_media_type_id'] = $this->instant->insert_instantiation_media_types(array("media_type" => $pbcoreinstantiation_child['instantiationmediatype'][0]['text']));
						}
					}
					// Instantiation Media Type End //
					// Instantiation File Size Start //
					if (isset($pbcoreinstantiation_child['instantiationfilesize'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationfilesize'][0]['text']))
					{
						$instantiations_d['file_size'] = $pbcoreinstantiation_child['instantiationfilesize'][0]['text'];
						if (isset($pbcoreinstantiation_child['instantiationfilesize'][0]['attributes']['unitsofmeasure']) && ! is_empty($pbcoreinstantiation_child['instantiationfilesize'][0]['attributes']['unitsofmeasure']))
						{
							$instantiations_d['file_size_unit_of_measure'] = $pbcoreinstantiation_child['instantiationfilesize'][0]['attributes']['unitsofmeasure'];
						}
					}
					// Instantiation File Size End //
					// Instantiation Time Start Start //
					if (isset($pbcoreinstantiation_child['instantiationtimestart'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationtimestart'][0]['text']))
					{
						$instantiations_d['time_start'] = trim($pbcoreinstantiation_child['instantiationtimestart'][0]['text']);
					}
					// Instantiation Time Start End //
					// Instantiation Projected Duration Start //
					if (isset($pbcoreinstantiation_child['instantiationduration'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationduration'][0]['text']))
					{
						$instantiations_d['projected_duration'] = trim($pbcoreinstantiation_child['instantiationduration'][0]['text']);
					}
					// Instantiation Projected Duration End //
					// Instantiation Data Rate Start //
					if (isset($pbcoreinstantiation_child['instantiationdatarate'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationdatarate'][0]['text']))
					{
						$instantiations_d['data_rate'] = trim($pbcoreinstantiation_child['instantiationdatarate'][0]['text']);
						if (isset($pbcoreinstantiation_child['instantiationdatarate'][0]['attributes']['unitsofmeasure']) && ! is_empty($pbcoreinstantiation_child['instantiationdatarate'][0]['attributes']['unitsofmeasure']))
						{
							$data_rate_unit_d = $this->instant->get_data_rate_units_by_unit($pbcoreinstantiation_child['instantiationdatarate'][0]['attributes']['unitsofmeasure']);
							if (isset($data_rate_unit_d) && isset($data_rate_unit_d->id))
							{
								$instantiations_d['data_rate_units_id'] = $data_rate_unit_d->id;
							}
							else
							{
								$instantiations_d['data_rate_units_id'] = $this->instant->insert_data_rate_units(array("unit_of_measure" => $pbcoreinstantiation_child['instantiationdatarate'][0]['attributes']['unitsofmeasure']));
							}
						}
					}
					// Instantiation Data Rate End //
					// Instantiation Color Start //
					if (isset($pbcoreinstantiation_child['instantiationcolors'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationcolors'][0]['text']))
					{

						$inst_color_d = $this->instant->get_instantiation_colors_by_color($pbcoreinstantiation_child['instantiationcolors'][0]['text']);
						if (isset($inst_color_d) && ! is_empty($inst_color_d))
						{
							$instantiations_d['instantiation_colors_id'] = $inst_color_d->id;
						}
						else
						{
							$instantiations_d['instantiation_colors_id'] = $this->instant->insert_instantiation_colors(array('color' => $pbcoreinstantiation_child['instantiationcolors'][0]['text']));
						}
					}
					// Instantiation Color End //
					// Instantiation Tracks Start //
					if (isset($pbcoreinstantiation_child['instantiationtracks'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationtracks'][0]['text']))
					{
						$instantiations_d['tracks'] = $pbcoreinstantiation_child['instantiationtracks'][0]['text'];
					}
					// Instantiation Tracks End //
					//Instantiation Channel Configuration Start //
					if (isset($pbcoreinstantiation_child['instantiationchannelconfiguration'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationchannelconfiguration'][0]['text']))
					{
						$instantiations_d['channel_configuration'] = $pbcoreinstantiation_child['instantiationchannelconfiguration'][0]['text'];
					}
					//Instantiation Channel Configuration End //
					//Instantiation Language Start //
					if (isset($pbcoreinstantiation_child['instantiationlanguage'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationlanguage'][0]['text']))
					{
						$instantiations_d['language'] = $pbcoreinstantiation_child['instantiationlanguage'][0]['text'];
					}
					//Instantiation Language End //
					//Instantiation Alternative Mode Start //
					if (isset($pbcoreinstantiation_child['instantiationalternativemodes'][0]['text']) && ! is_empty($pbcoreinstantiation_child['instantiationalternativemodes'][0]['text']))
					{
						$instantiations_d['alternative_modes'] = $pbcoreinstantiation_child['instantiationalternativemodes'][0]['text'];
					}
					//Instantiation Alternative Mode End //

					$insert_instantiation = TRUE;
					$instantiations_d['created'] = date("Y-m-d H:i:s");
					$instantiations_id = $this->instant->insert_instantiations($instantiations_d);
					// Instantiations Identifier Start //
					if (isset($pbcoreinstantiation_child['instantiationidentifier']))
					{

						foreach ($pbcoreinstantiation_child['instantiationidentifier'] as $pbcore_identifier)
						{
							$instantiation_identifier_d = array();
							$instantiation_identifier_d['instantiations_id'] = $instantiations_id;
							if (isset($pbcore_identifier['text']) && ! is_empty($pbcore_identifier['text']))
							{
								$instantiation_identifier_d['instantiation_identifier'] = $pbcore_identifier['text'];
								if (isset($pbcore_identifier['attributes']['source']) && ! is_empty($pbcore_identifier['attributes']['source']))
								{
									$instantiation_identifier_d['instantiation_source'] = $pbcore_identifier['attributes']['source'];
								}
								$instantiation_identifier_id = $this->instant->insert_instantiation_identifier($instantiation_identifier_d);
							}
						}
					}
					// Instantiations Identifier End //
					// Instantiations Date Start //
					if (isset($pbcoreinstantiation_child['instantiationdate']))
					{

						foreach ($pbcoreinstantiation_child['instantiationdate'] as $pbcore_date)
						{
							$instantiation_dates_d = array();
							$instantiation_dates_d['instantiations_id'] = $instantiations_id;
							if (isset($pbcore_date['text']) && ! is_empty($pbcore_date['text']))
							{
								$instantiation_dates_d['instantiation_date'] = str_replace(array('?', 'Unknown', 'unknown', '`', '[' . ']', 'N/A', 'N/A?', 'Jim Cooper', 'various', '.00', '.0', 'John Kelling', 'Roll in', 'interview'), '', $pbcore_date['text']);
								if (isset($instantiation_dates_d['instantiation_date']) && ! is_empty($instantiation_dates_d['instantiation_date']))
								{
									$date_check = $this->is_valid_date($instantiation_dates_d['instantiation_date']);
									if ($date_check === FALSE)
									{
										$instantiation_annotation_d = array();
										$instantiation_annotation_d['instantiations_id'] = $instantiations_id;
										$instantiation_annotation_d['annotation'] = $instantiation_dates_d['instantiation_date'];
										if (isset($pbcore_date['attributes']['datetype']) && ! is_empty($pbcore_date['attributes']['datetype']))
										{
											$instantiation_annotation_d['annotation_type'] = $pbcore_date['attributes']['datetype'];
										}

										$this->instant->insert_instantiation_annotations($instantiation_annotation_d);
									}
									else
									{
										if (isset($pbcore_date['attributes']['datetype']) && ! is_empty($pbcore_date['attributes']['datetype']))
										{
											$date_type = $this->instant->get_date_types_by_type($pbcore_date['attributes']['datetype']);
											if (isset($date_type) && isset($date_type->id))
											{
												$instantiation_dates_d['date_types_id'] = $date_type->id;
											}
											else
											{
												$instantiation_dates_d['date_types_id'] = $this->instant->insert_date_types(array('date_type' => $pbcore_date['attributes']['datetype']));
											}
										}
										$this->instant->insert_instantiation_dates($instantiation_dates_d);
									}
								}
							}
						}
					}
					// Instantiations Date End //
					// Instantiations Dimension Start //
					if (isset($pbcoreinstantiation_child['instantiationdimensions']))
					{

						foreach ($pbcoreinstantiation_child['instantiationdimensions'] as $pbcore_dimension)
						{
							$instantiation_dimension_d = array();
							$instantiation_dimension_d['instantiations_id'] = $instantiations_id;
							if (isset($pbcore_dimension['text']) && ! is_empty($pbcore_dimension['text']))
							{
								$instantiation_dimension_d['instantiation_dimension'] = $pbcore_dimension['text'];
								$instantiation_dimension_d['unit_of_measure'] = '';
								if (isset($pbcore_dimension['attributes']['unitofmeasure']) && ! is_empty($pbcore_dimension['attributes']['unitofmeasure']))
								{
									$instantiation_dimension_d['unit_of_measure'] = $pbcore_dimension['attributes']['unitofmeasure'];
								}
								$this->instant->insert_instantiation_dimensions($instantiation_dimension_d);
							}
						}
					}
					// Instantiations Dimension End //
					// Instantiations Format Start //
					if (isset($pbcoreinstantiation_child['instantiationphysical']))
					{

						foreach ($pbcoreinstantiation_child['instantiationphysical'] as $pbcore_physical)
						{
							if (isset($pbcore_physical['text']) && ! is_empty($pbcore_physical['text']))
							{
								$instantiation_format_physical_d = array();
								$instantiation_format_physical_d['instantiations_id'] = $instantiations_id;
								$instantiation_format_physical_d['format_name'] = $pbcore_physical['text'];
								$instantiation_format_physical_d['format_type'] = 'physical';
								$instantiation_format_physical_id = $this->instant->insert_instantiation_formats($instantiation_format_physical_d);
							}
						}
					}
					else if (isset($pbcoreinstantiation_child['instantiationdigital']))
					{

						foreach ($pbcoreinstantiation_child['instantiationdigital'] as $pbcore_digital)
						{
							if (isset($pbcore_digital['text']) && ! is_empty($pbcore_digital['text']))
							{
								$instantiation_format_digital_d = array();
								$instantiation_format_digital_d['instantiations_id'] = $instantiations_id;
								$instantiation_format_digital_d['format_name'] = $pbcore_digital['text'];
								$instantiation_format_digital_d['format_type'] = 'digital';
								$instantiation_format_digital_id = $this->instant->insert_instantiation_formats($instantiation_format_digital_d);
							}
						}
					}
					// Instantiations  Format End //
					// Instantiations  Generation Start //

					if (isset($pbcoreinstantiation_child['instantiationgenerations']) && ! is_empty($pbcoreinstantiation_child['instantiationgenerations']))
					{
						foreach ($pbcoreinstantiation_child['instantiationgenerations'] as $instantiation_generations)
						{
							if (isset($instantiation_generations['text']) && ! is_empty($instantiation_generations['text']))
							{
								$instantiation_format_generations_d = array();
								$instantiation_format_generations_d['instantiations_id'] = $instantiations_id;
								$generations_d = $this->instant->get_generations_by_generation($instantiation_generations['text']);
								if (isset($generations_d) && isset($generations_d->id))
								{
									$instantiation_format_generations_d['generations_id'] = $generations_d->id;
								}
								else
								{
									$instantiation_format_generations_d['generations_id'] = $this->instant->insert_generations(array("generation" => $instantiation_generations['text']));
								}
								$this->instant->insert_instantiation_generations($instantiation_format_generations_d);
							}
						}
					}
					// Instantiations  Generation End //
					// Instantiations  Annotation Start //
					if (isset($pbcoreinstantiation_child['instantiationannotation']))
					{
						foreach ($pbcoreinstantiation_child['instantiationannotation'] as $pbcore_annotation)
						{
							if (isset($pbcore_annotation['text']) && ! is_empty($pbcore_annotation['text']))
							{
								$instantiation_annotation_d = array();
								$instantiation_annotation_d['instantiations_id'] = $instantiations_id;
								$instantiation_annotation_d['annotation'] = $pbcore_annotation['text'];

								if (isset($pbcore_annotation['attributes']['annotationtype']) && ! is_empty($pbcore_annotation['attributes']['annotationtype']))
								{
									$instantiation_annotation_d['annotation_type'] = $pbcore_annotation['attributes']['annotationtype'];
								}
								$this->instant->insert_instantiation_annotations($instantiation_annotation_d);
							}
						}
					}
					// Instantiations  Annotation End //
					// Instantiations Relation Start  //
					if (isset($pbcoreinstantiation_child['pbcorerelation']))
					{
						foreach ($pbcoreinstantiation_child['pbcorerelation'] as $pbcorerelation)
						{

							if (isset($pbcorerelation['children']['pbcorerelationidentifier'][0]['text']) && ! is_empty($pbcore_creator['children']['pbcorerelationidentifier'][0]['text']))
							{
								$instantiation_relation_d = array();
								$instantiation_relation_d['instantiations_id'] = $instantiations_id;
								$instantiation_relation_d = $pbcorerelation['children']['pbcorerelationidentifier'][0]['text'];
								if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['text']) && ! is_empty($pbcore_creator['children']['pbcorerelationtype'][0]['text']))
								{
									$relation_type_info['relation_type'] = $pbcorerelation['children']['pbcorerelationtype'][0]['text'];
									if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['source']) && ! is_empty($pbcore_creator['children']['pbcorerelationtype'][0]['attributes']['source']))
									{
										$relation_type_info['relation_type_source'] = $pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['source'];
									}
									if (isset($pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['ref']) && ! is_empty($pbcore_creator['children']['pbcorerelationtype'][0]['attributes']['ref']))
									{
										$relation_type_info['relation_type_ref'] = $pbcorerelation['children']['pbcorerelationtype'][0]['attributes']['ref'];
									}
									$db_relations = $this->assets_model->get_relation_types($relation_type_info['relation_type']);
									if (isset($db_relations) && isset($db_relations->id))
									{
										$instantiation_relation_d['relation_types_id'] = $db_relations->id;
									}
									else
									{
										$instantiation_relation_d['relation_types_id'] = $this->assets_model->insert_relation_types($relation_type_info);
									}
									$this->instant->insert_instantiation_relation($instantiation_relation_d);
								}
							}
						}
					}
					// Instantiations Relation End  //
					// Instantiations Essence Tracks Start //
					if (isset($pbcoreinstantiation_child['instantiationessencetrack']))
					{
						foreach ($pbcoreinstantiation_child['instantiationessencetrack'] as $pbcore_essence_track)
						{
							if (isset($pbcore_essence_track['children']) && ! is_empty($pbcore_essence_track['children']))
							{
								$pbcore_essence_child = $pbcore_essence_track['children'];
								$essence_tracks_d = array();
								$essence_tracks_d['instantiations_id'] = $instantiations_id;
								// Essence Track Standard Start //
								if (isset($pbcore_essence_child['essencetrackstandard'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackstandard'][0]['text']))
								{
									$essence_tracks_d['standard'] = $pbcore_essence_child['essencetrackstandard'][0]['text'];
								}
								// Essence Track Standard End //
								// Essence Track Data Rate Start //

								if (isset($pbcore_essence_child['essencetrackdatarate'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackdatarate'][0]['text']))
								{
									$essence_tracks_d['data_rate'] = $pbcore_essence_child['essencetrackdatarate'][0]['text'];
									if (isset($pbcore_essence_child['essencetrackdatarate'][0]['attributes']['unitsofmeasure']) && ! is_empty($pbcore_essence_child['essencetrackdatarate'][0]['attributes']['unitsofmeasure']))
									{

										$data_rate_unit_d = $this->instant->get_data_rate_units_by_unit($pbcore_essence_child['essencetrackdatarate'][0]['attributes']['unitsofmeasure']);
										if (isset($data_rate_unit_d) && isset($data_rate_unit_d->id))
										{
											$essence_tracks_d['data_rate_units_id'] = $data_rate_unit_d->id;
										}
										else
										{
											$essence_tracks_d['data_rate_units_id'] = $this->instant->insert_data_rate_units(array("unit_of_measure" => $pbcore_essence_child['essencetrackdatarate'][0]['attributes']['unitsofmeasure']));
										}
									}
								}

								// Essence Track Data Rate End //
								// Essence Track Frame Rate Start //
								if (isset($pbcore_essence_child['essencetrackframerate'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackframerate'][0]['text']))
								{
									$frame_rate = explode(" ", $pbcore_essence_child['essencetrackframerate'][0]['text']);
									$essence_tracks_d['frame_rate'] = trim($frame_rate[0]);
								}
								// Essence Track Frame Rate End //
								// Essence Track Play Back Speed Start //
								if (isset($pbcore_essence_child['essencetrackplaybackspeed'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackplaybackspeed'][0]['text']))
								{
									$essence_tracks_d['playback_speed'] = $pbcore_essence_child['essencetrackplaybackspeed'][0]['text'];
								}
								// Essence Track Play Back Speed End //
								// Essence Track Sampling Rate Start //
								if (isset($pbcore_essence_child['essencetracksamplingrate'][0]['text']) && ! is_empty($pbcore_essence_child['essencetracksamplingrate'][0]['text']))
								{
									$essence_tracks_d['sampling_rate'] = $pbcore_essence_child['essencetracksamplingrate'][0]['text'];
								}
								// Essence Track Sampling Rate End //
								// Essence Track bit depth Start //
								if (isset($pbcore_essence_child['essencetrackbitdepth'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackbitdepth'][0]['text']))
								{
									$essence_tracks_d['bit_depth'] = $pbcore_essence_child['essencetrackbitdepth'][0]['text'];
								}
								// Essence Track bit depth End //
								// Essence Track Aspect Ratio Start //
								if (isset($pbcore_essence_child['essencetrackaspectratio'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackaspectratio'][0]['text']))
								{
									$essence_tracks_d['aspect_ratio'] = $pbcore_essence_child['essencetrackaspectratio'][0]['text'];
								}
								// Essence Track Aspect Ratio End //
								// Essence Track Time Start //
								if (isset($pbcore_essence_child['essencetracktimestart'][0]['text']) && ! is_empty($pbcore_essence_child['essencetracktimestart'][0]['text']))
								{
									$essence_tracks_d['time_start'] = $pbcore_essence_child['essencetracktimestart'][0]['text'];
								}
								// Essence Track Time End //
								// Essence Track Duration Start //

								if (isset($pbcore_essence_child['essencetrackduration'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackduration'][0]['text']))
								{
									$essence_tracks_d['duration'] = $pbcore_essence_child['essencetrackduration'][0]['text'];
								}
								// Essence Track Duration End //
								// Essence Track Language Start //

								if (isset($pbcore_essence_child['essencetracklanguage'][0]['text']) && ! is_empty($pbcore_essence_child['essencetracklanguage'][0]['text']))
								{
									$essence_tracks_d['language'] = $pbcore_essence_child['essencetracklanguage'][0]['text'];
								}
								// Essence Track Language Start //
								// Essence Track Type Start //
								if (isset($pbcore_essence_child['essencetracktype'][0]['text']) && ! is_empty($pbcore_essence_child['essencetracktype'][0]['text']))
								{

									$essence_track_type_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_types, array('essence_track_type' => $pbcore_essence_child['essencetracktype'][0]['text']), TRUE);
									if (isset($essence_track_type_d) && isset($essence_track_type_d->id))
									{
										$essence_tracks_d['essence_track_types_id'] = $essence_track_type_d->id;
									}
									else
									{
										$essence_tracks_d['essence_track_types_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_types, array('essence_track_type' => $pbcore_essence_child['essencetracktype'][0]['text']));
									}
								}
								// Essence Track Type End //
								// Essence Track Frame Size Start //
								if (isset($pbcore_essence_child['essencetrackframesize'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackframesize'][0]['text']))
								{
									$frame_sizes = explode("x", strtolower($pbcore_essence_child['essencetrackframesize'][0]['text']));
									if (isset($frame_sizes[0]) && isset($frame_sizes[1]))
									{

										$track_frame_size_d = $this->pbcore_model->get_one_by($this->pbcore_model->table_essence_track_frame_sizes, array('width' => trim($frame_sizes['width']), 'height' => trim($frame_sizes['height'])));
										if ($track_frame_size_d)
										{
											$essence_tracks_d['essence_track_frame_sizes_id'] = $track_frame_size_d->id;
										}
										else
										{
											$essence_tracks_d['essence_track_frame_sizes_id'] = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_frame_sizes, array("width" => $frame_sizes[0], "height" => $frame_sizes[1]));
										}
									}
								}
								if (isset($essence_tracks_d['essence_track_types_id']) && ! empty($essence_tracks_d['essence_track_types_id']) && $essence_tracks_d['essence_track_types_id'] != NULL)
								{
									// Essence Track Frame Size End //
									$essence_tracks_id = $this->pbcore_model->insert_record($this->pbcore_model->table_essence_tracks, $essence_tracks_d);
									$insert_essence_track = TRUE;
									// Essence Track Identifier Start //
									if (isset($pbcore_essence_child['essencetrackidentifier'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackidentifier'][0]['text']))
									{

										$essence_track_identifiers_d = array();
										$essence_track_identifiers_d['essence_tracks_id'] = $essence_tracks_id;
										$essence_track_identifiers_d['essence_track_identifiers'] = $pbcore_essence_child['essencetrackidentifier'][0]['text'];
										if (isset($pbcore_essence_child['essencetrackidentifier'][0]['attributes']['source']) && ! is_empty($pbcore_essence_child['essencetrackidentifier'][0]['attributes']['source']))
										{
											$essence_track_identifiers_d['essence_track_identifier_source'] = $pbcore_essence_child['essencetrackidentifier'][0]['attributes']['source'];
										}
										$this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_identifiers, $essence_track_identifiers_d);
									}
									// Essence Track Identifier End //
									// Essence Track Encoding Start //
									if (isset($pbcore_essence_child['essencetrackencoding'][0]['text']) && ! is_empty($pbcore_essence_child['essencetrackencoding'][0]['text']))
									{

										$essence_track_standard_d = array();
										$essence_track_standard_d['essence_tracks_id'] = $essence_tracks_id;
										$essence_track_standard_d['encoding'] = $pbcore_essence_child['essencetrackencoding'][0]['text'];
										if (isset($pbcore_essence_child['essencetrackencoding'][0]['attributes']['ref']) && ! is_empty($pbcore_essence_child['essencetrackencoding'][0]['attributes']['ref']))
										{
											$essence_track_standard_d['encoding_source'] = $pbcore_essence_child['essencetrackencoding'][0]['attributes']['ref'];
										}
										$this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_encodings, $essence_track_standard_d);
									}
									// Essence Track Encoding End //
									// Essence Track Annotation Start //
									if (isset($pbcore_essence_child['essencetrackannotation']) && ! is_empty($pbcore_essence_child['essencetrackannotation']))
									{
										foreach ($pbcore_essence_child['essencetrackannotation'] as $trackannotation)
										{
											if (isset($trackannotation['text']) && ! is_empty($trackannotation['text']))
											{
												$essencetrackannotation = array();
												$essencetrackannotation['essence_tracks_id'] = $essence_tracks_id;
												$essencetrackannotation['annotation'] = $trackannotation['text'];

												if (isset($trackannotation['attributes']['type']) && ! is_empty($trackannotation['attributes']['type']))
												{
													$essencetrackannotation['annotation_type'] = $trackannotation['attributes']['type'];
												}
												$this->pbcore_model->insert_record($this->pbcore_model->table_essence_track_annotations, $essencetrackannotation);
											}
										}
									}
									// Essence Track Annotation End //
								}
							}
						}
					}
					// Instantiations Essence Tracks End //
					// Asset Extension Start //

					if (isset($asset_children['pbcoreextension']) && ! is_empty($asset_children['pbcoreextension']))
					{
						foreach ($asset_children['pbcoreextension'] as $pbcore_extension)
						{
							$map_extension = $pbcore_extension['children']['extensionwrap'][0]['children'];
							if (isset($map_extension['extensionauthorityused'][0]['text']) && ! is_empty($map_extension['extensionauthorityused'][0]['text']))
							{
								$nomination_d = array();
								$nomination_d['instantiations_id'] = $instantiations_id;
								if (strtolower($map_extension['extensionauthorityused'][0]['text']) == strtolower('AACIP Record Nomination Status'))
								{
									if (isset($map_extension['extensionvalue'][0]['text']) && ! is_empty($map_extension['extensionvalue'][0]['text']))
									{
										$nomunation_status = $this->assets_model->get_nomination_status_by_status($map_extension['extensionvalue'][0]['text']);
										if (isset($nomunation_status) && ! is_empty($nomunation_status))
										{
											$nomination_d['nomination_status_id'] = $nomunation_status->id;
										}
										else
										{
											$nomination_d['nomination_status_id'] = $this->assets_model->insert_nomination_status(array("status" => $map_extension['extensionvalue'][0]['text']));
										}
									}
								}
								if (strtolower($map_extension['extensionauthorityused'][0]['text']) == strtolower('AACIP Record Tags'))
								{

									if (isset($map_extension['extensionvalue'][0]['text']) && ! is_empty($map_extension['extensionvalue'][0]['text']))
									{
										if (preg_match('/historical value/', strtolower($map_extension['extensionvalue'][0]['text']), $match_text))
										{

											$nomination_d['nomination_reason'] = $map_extension['extensionvalue'][0]['text'];
										}
										else if (preg_match('/risk of loss/', strtolower($map_extension['extensionvalue'][0]['text']), $match_text))
										{

											$nomination_d['nomination_reason'] = $map_extension['extensionvalue'][0]['text'];
										}
										else if (preg_match('/local cultural value/', strtolower($map_extension['extensionvalue'][0]['text']), $match_text))
										{

											$nomination_d['nomination_reason'] = $map_extension['extensionvalue'][0]['text'];
										}
										else if (preg_match('/potential to repurpose/', strtolower($map_extension['extensionvalue'][0]['text']), $match_text))
										{

											$nomination_d['nomination_reason'] = $map_extension['extensionvalue'][0]['text'];
										}
									}
								}
								if (isset($nomination_d['nomination_status_id']))
								{
									$nomination_d['created'] = date("Y-m-d H:i:s");
									$this->assets_model->insert_nominations($nomination_d);
								}
							}
						}
					}
					// Asset Extension End //
				}
			}
		}
	}

	/**
	 * Display the output.
	 * @global type $argc
	 * @param type $s 
	 */
	function myLog($s)
	{
		global $argc;
		if ($argc)
			$s.="\n";
		else
			$s.="<br>\n";
		echo date('Y-m-d H:i:s') . ' >> ' . $s;
		flush();
	}

	/**
	 * Check the process status
	 * 
	 * @param type $pid
	 * @return boolean 
	 */
	function checkProcessStatus($pid)
	{
		$proc_status = false;
		try
		{
			$result = shell_exec("/bin/ps $pid");
			if (count(preg_split("/\n/", $result)) > 2)
			{
				$proc_status = TRUE;
			}
		}
		catch (Exception $e)
		{
			
		}
		return $proc_status;
	}

	/**
	 * Check the process count.
	 * 
	 * @return type 
	 */
	function procCounter()
	{
		foreach ($this->arrPIDs as $pid => $cityKey)
		{
			if ( ! $this->checkProcessStatus($pid))
			{
				$t_pid = str_replace("\r", "", str_replace("\n", "", trim($pid)));
				unset($this->arrPIDs[$pid]);
			}
			else
			{
				
			}
		}
		return count($this->arrPIDs);
	}

	/**
	 * Run a new process
	 * 
	 * @param type $cmd
	 * @param type $pidFilePath
	 * @param type $outputfile 
	 */
	function runProcess($cmd, $pidFilePath, $outputfile = "/dev/null")
	{
		$cmd = escapeshellcmd($cmd);
		@exec(sprintf("%s >> %s 2>&1 & echo $! > %s", $cmd, $outputfile, $pidFilePath));
	}

	/**
	 * Check the date format
	 * 
	 * @param type $value
	 * @return boolean 
	 */
	function is_valid_date($value)
	{
		$date = date_parse($value);
		if ($date['error_count'] == 0 && $date['warning_count'] == 0)
		{
			return date("Y-m-d", strtotime($value));
		}
		return FALSE;
	}

}
