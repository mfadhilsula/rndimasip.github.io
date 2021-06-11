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
 * Pbcore_Model Class
 *
 * @category   Class
 * @package    CI
 * @subpackage Model
 * @author     Nouman Tayyab <nouman@avpreserve.com>
 * @copyright  Copyright (c) WGBH (http://www.wgbh.org/). All Rights Reserved.
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3
 * @link       https://ams.americanarchive.org
 */
class Pbcore_Model extends MY_Asset_Model
{

	/**
	 * constructor. set table name amd prefix
	 * 
	 */
	function __construct()
	{
		parent::__construct();
                $this->db->save_queries = FALSE;
	}

	function export_assets($real_time = FALSE)
	{
		$this->db->select("{$this->_assets_table}.id");
		$this->db->join($this->table_instantiations, "$this->table_instantiations.assets_id = $this->_assets_table.id", 'left');
		$this->db->join("identifiers", "$this->_assets_table.id = identifiers.assets_id AND identifiers.identifier_source!='http://americanarchiveinventory.org'", 'left');
		$this->db->join("identifiers guid", "$this->_assets_table.id = guid.assets_id AND guid.identifier_source='http://americanarchiveinventory.org'", 'left');
		$this->db->join($this->table_instantiation_formats, "$this->table_instantiation_formats.instantiations_id = $this->table_instantiations.id", 'left');
		$this->db->join($this->table_nominations, "$this->table_nominations.instantiations_id = $this->table_instantiations.id", 'left');
		$this->db->join($this->table_nomination_status, "$this->table_nomination_status.id = $this->table_nominations.nomination_status_id", 'left');
		$this->db->join('assets_subjects', "assets_subjects.assets_id = assets.id", 'left');
		$this->db->join('subjects', "subjects.id = assets_subjects.subjects_id", 'left');
		$this->db->join('coverages', "coverages.assets_id = assets.id", 'left');
		$this->db->join('assets_genres', "assets_genres.assets_id = assets.id", 'left');
		$this->db->join('genres', "genres.id = assets_genres.genres_id", 'left');
		$this->db->join('assets_publishers_role', "assets_publishers_role.assets_id = assets.id", 'left');
		$this->db->join('publisher_roles', "assets_publishers_role.publisher_roles_id = publisher_roles.id", 'left');
		$this->db->join('publishers', "assets_publishers_role.publishers_id = publishers.id", 'left');
		$this->db->join('asset_descriptions', "asset_descriptions.assets_id = assets.id", 'left');
		$this->db->join('description_types', "description_types.id = asset_descriptions.description_types_id", 'left');
		$this->db->join('assets_creators_roles', "assets_creators_roles.assets_id = assets.id", 'left');
		$this->db->join('creator_roles', "assets_creators_roles.creator_roles_id = creator_roles.id", 'left');
		$this->db->join('creators', "assets_creators_roles.creators_id = creators.id", 'left');
		$this->db->join('assets_contributors_roles', "assets_contributors_roles.assets_id = assets.id", 'left');
		$this->db->join('contributor_roles', "assets_contributors_roles.contributor_roles_id = contributor_roles.id", 'left');
		$this->db->join('contributors', "assets_contributors_roles.contributors_id = contributors.id", 'left');
		$this->db->join('instantiation_identifier', "instantiations.id = instantiation_identifier.instantiations_id", 'left');
		$this->db->join('instantiation_dimensions', "instantiation_dimensions.instantiations_id = instantiations.id", 'left');
		$this->db->join('essence_tracks', "essence_tracks.instantiations_id = instantiations.id", 'left');
		$this->db->join('essence_track_encodings', "essence_track_encodings.essence_tracks_id = essence_tracks.id", 'left');
		$this->db->join('essence_track_frame_sizes', "essence_track_frame_sizes.id = essence_tracks.essence_track_frame_sizes_id", 'left');
		$this->db->join('essence_track_annotations', "essence_track_annotations.essence_tracks_id = essence_tracks.id", 'left');
		$this->db->join('instantiation_annotations', "instantiation_annotations.instantiations_id = instantiations.id", 'left');
		$this->db->join('annotations', "annotations.assets_id = assets.id", 'left');
		$this->db->join('rights_summaries', "rights_summaries.assets_id = assets.id", 'left');
		$this->db->join('assets_asset_types', "assets_asset_types.assets_id = assets.id", 'left');
		$this->db->join('asset_types', "asset_types.id = assets_asset_types.asset_types_id", 'left');
		$this->db->join('asset_dates', "asset_dates.assets_id = assets.id", 'left');
		$this->db->join('assets_audience_levels', "assets_audience_levels.assets_id = assets.id", 'left');
		$this->db->join('audience_levels', "audience_levels.id = assets_audience_levels.audience_levels_id", 'left');
		$this->db->join('assets_audience_ratings', "assets_audience_ratings.assets_id = assets.id", 'left');
		$this->db->join('audience_ratings', "audience_ratings.id = assets_audience_ratings.audience_ratings_id", 'left');
		$this->db->join($this->asset_titles, "$this->asset_titles.assets_id	 = $this->table_instantiations.assets_id", 'left');
		$this->db->join($this->stations, "$this->stations.id = $this->_assets_table.stations_id", 'left');
		$this->db->join($this->table_instantiation_dates, "$this->table_instantiation_dates.instantiations_id = $this->table_instantiations.id", 'left');
		$this->db->join($this->table_date_types, "$this->table_date_types.id = $this->table_instantiation_dates.date_types_id", 'left');
		$this->db->join($this->table_instantiation_media_types, "$this->table_instantiation_media_types.id = $this->table_instantiations.instantiation_media_type_id", 'left');
		$this->db->join($this->table_instantiation_generations, "$this->table_instantiation_generations.instantiations_id = $this->table_instantiations.id", 'left');
		$this->db->join($this->table_generations, "$this->table_generations.id = $this->table_instantiation_generations.generations_id", 'left');
		$this->db->join($this->table_events, "$this->table_events.instantiations_id	 = $this->table_instantiations.id", 'left');
		$this->db->join($this->table_event_types, "$this->table_event_types.id	 = $this->table_events.event_types_id", 'left');

		$session = $this->session->userdata;
		if (isset($session['organization']) && $session['organization'] != '')
		{
			$station_name = explode('|||', trim($session['organization']));
			$this->db->where_in("$this->stations.station_name", $station_name);
		}
		if (isset($session['nomination']) && $session['nomination'] != '')
		{
			$nomination = explode('|||', trim($session['nomination']));
			$this->db->where_in("$this->table_nomination_status.status", $nomination);
		}
		if (isset($session['media_type']) && $session['media_type'] != '')
		{
			$media_type = explode('|||', trim($session['media_type']));
			$this->db->where_in("$this->table_instantiation_media_types.media_type", $media_type);
		}
		if (isset($session['physical_format']) && $session['physical_format'] != '')
		{
			$physical_format = explode('|||', trim($session['physical_format']));
			$this->db->where_in("$this->table_instantiation_formats.format_name", $physical_format);
		}
		if (isset($session['digital_format']) && $session['digital_format'] != '')
		{
			$digital_format = explode('|||', trim($session['digital_format']));
			$this->db->where_in("$this->table_instantiation_formats.format_name", $digital_format);
		}
		if (isset($session['generation']) && $session['generation'] != '')
		{
			$generation = explode('|||', trim($session['generation']));
			$this->db->where_in("$this->table_generations.generation", $generation);
		}
		if (isset($session['digitized']) && $session['digitized'] === '1')
		{
			$this->db->where("$this->table_instantiations.digitized", '1');
			$this->db->where("$this->table_instantiations.actual_duration IS NULL");
		}
		if (isset($session['migration_failed']) && $session['migration_failed'] === '1')
		{

			$this->db->where("$this->table_event_types.event_type", 'migration');
			$this->db->where("$this->table_events.event_outcome", '0');
		}

		if (isset($session['custom_search']) && $session['custom_search'] != '')
		{
			$facet_columns = array(
				'guid_identifier' => 'guid.identifier',
				'asset_title' => 'asset_titles.title',
				'asset_subject' => 'subjects.subject',
				'asset_coverage' => 'coverages.coverage',
				'asset_genre' => 'genres.genre',
				'asset_publisher_name' => 'publishers.publisher',
				'asset_description' => 'description_types.description_type',
				'asset_creator_name' => 'creators.creator_name',
				'asset_creator_affiliation' => 'creators.creator_affiliation',
				'asset_contributor_name' => 'contributors.contributor_name',
				'asset_contributor_affiliation' => 'contributors.contributor_affiliation',
				'instantiation_identifier' => 'instantiation_identifier.instantiation_identifier',
				'instantiation_source' => 'instantiation_identifier.instantiation_source',
				'instantiation_dimension' => 'instantiation_dimensions.instantiation_dimension',
				'unit_of_measure' => 'instantiation_dimensions.unit_of_measure',
				'standard' => 'instantiations.standard',
				'location' => 'instantiations.location',
				'file_size' => 'instantiations.file_size',
				'actual_duration' => 'instantiations.actual_duration',
				'track_duration' => 'essence_tracks.duration',
				'data_rate' => 'instantiations.data_rate',
				'track_data_rate' => 'essence_tracks.data_rate',
				'tracks' => 'instantiations.tracks',
				'channel_configuration' => 'instantiations.channel_configuration',
				'language' => 'instantiations.language',
				'track_language' => 'essence_tracks.language',
				'alternative_modes' => 'instantiations.alternative_modes',
				'ins_annotation' => 'instantiation_annotations.annotation',
				'track_annotation' => 'essence_track_annotations.annotation',
				'ins_annotation_type' => 'instantiation_annotations.annotation_type',
				'track_essence_track_type' => 'essence_track_annotations.annotation_type',
				'track_encoding' => 'essence_track_encodings.encoding',
				'track_standard' => 'essence_tracks.standard',
				'track_frame_rate' => 'essence_tracks.frame_rate',
				'track_playback_speed' => 'essence_tracks.playback_speed',
				'track_sampling_rate' => 'essence_tracks.sampling_rate',
				'track_bit_depth' => 'essence_tracks.bit_depth',
				'track_width' => 'essence_track_frame_sizes.width',
				'track_height' => 'essence_track_frame_sizes.height',
				'track_aspect_ratio' => 'essence_tracks.aspect_ratio',
			);

			$keyword_json = $session['custom_search'];
			$where = '(';
			foreach ($keyword_json as $index => $key_columns)
			{
				$count = 0;
				foreach ($key_columns as $keys => $keywords)
				{
					$keyword = trim($keywords->value);
					$_or = ' OR ';
					if ($count == 0)
						$_or = ' ';

					if ($index == 'all')
					{
						$counter = 0;

						foreach ($facet_columns as $column)
						{
							$_or = ' OR ';
							if ($counter == 0)
								$_or = ' ';
							$where .=" $_or $column LIKE '%$keyword%'";
							$counter ++;
						}
					}
					else
					{
						$where .=" $_or $index LIKE '%$keyword%'";
					}
					$count ++;
				}
			}
			$where .=' )';
			$this->db->where($where);
		}
		if (isset($session['date_range']) && $session['date_range'] != '')
		{
			$keyword_json = $this->session->userdata['date_range'];
			$where = '(';
			foreach ($keyword_json as $index => $key_columns)
			{
				$count = 0;
				foreach ($key_columns as $keys => $keywords)
				{
					$_or = ' OR ';
					if ($count == 0)
						$_or = ' ';

					$date_range = explode("to", $keywords->value);
					if (isset($date_range[0]) && trim($date_range[0]) != '')
					{
						$start_date = strtotime(trim($date_range[0]));
					}
					if (isset($date_range[1]) && trim($date_range[1]) != '')
					{
						$end_date = strtotime(trim($date_range[1]));
					}
					if ($start_date != '' && is_numeric($start_date) && isset($end_date) && is_numeric($end_date) && $end_date >= $start_date)
					{

						$where .="$_or ($this->table_instantiation_dates.instantiation_date >= $start_date AND $this->table_instantiation_dates.instantiation_date<= $end_date )";

						if ($index != 'All')
						{
							$where .=" AND $this->table_date_types.date_type LIKE '$index'";
						}
					}
					$count ++;
				}
			}
			$where .=' )';
			$this->db->where($where);
		}

		if ($this->is_station_user)
		{
			$this->db->where_in("$this->stations.station_name", $this->station_name);
		}

		$this->db->group_by("$this->_assets_table.id");
		$this->db->from($this->_assets_table);
		if ($real_time)
		{
			return $this->db->return_query();
		}
		$result = $this->db->get();

		if (isset($result) && ! empty($result))
		{

			return $result->result();
		}
		return false;
	}

}

?>