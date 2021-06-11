<div class="row">
	<div class="span9" style="margin-left: 250px;" id="ins_view_detail">
		<div><h2>Add Asset</h2></div>
		<hr/>
		<form class="form-horizontal" method="POST" action="<?php echo site_url('asset/add/'); ?>" id="edit_asset_form">
			<table cellPadding="8" class="record-detail-table">
				
				<?php
				if ( ! $this->is_station_user)
				{
					?>
					<tr>
						<td class="record-detail-page">
							<label><b>Organization: <span class="label_star"> *</span> </b></label>
						</td>
						<td>
							<p>
								<select id="organization" name="organization">
									<?php
									foreach ($organization as $row)
									{
										?>
										<option value="<?php echo $row->id; ?>"><?php echo $row->station_name; ?></option>
									<?php }
									?>
								</select>
							</p>

						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Indicates the broad editorial format of the assets contents. AssetType describes the PBCore record as a whole and at its highest level. Though a record may contain many instantiations of different formats and generations, for example, assetType may be used to indicate that they all represent a “program” or a “clip.”"><i class="icon-question-sign"></i></a>
							<b> Asset Type:</b></label>
					</td>
					<td>
						<div id="main_type">
							<div id="remove_type_0" class="remove_type">
								<div class="edit_form_div">
									<div><p>Asset Type:</p></div>
									<div><p>
											<select id="asset_type_0" name="asset_type[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_types as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>"><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>


							</div>
							<div class="add-new-element" onclick="addElement('#main_type', 'type');"><i class="icon-plus-sign icon-white"></i><span id="add_type"> ADD ANOTHER TYPE</span></div>
					</td>
				</tr>
				<tr>

					<td class="record-detail-page">
						<label><b> Asset Date:</b></label>
					</td>
					<td>
						<div id="main_date">

							<div id="remove_date_0" class="remove_date">
								<div class="edit_form_div">
									<div>
										<p>Asset Date:</p>
										<p>
											<input type="text" id="asset_date_0" name="asset_date[]" value="" />
										</p>
									</div>
									<div>
										<p>Asset Date Type:</p>
										<p>
											<select id="asset_date_type_0" name="asset_date_type[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_date_types as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>"><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>


						</div>
						<div class="add-new-element" onclick="addElement('#main_date', 'date');"><i class="icon-plus-sign icon-white"></i><span id="add_date"> ADD ANOTHER DATE</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Identifier: Used to reference or identify the entire record of metadata descriptions for a media item. In contrast to the Unique Identifier, this identifier is used to identify the CONTENT of the asset. So it links together all copies of a particular episode of NOVA or This American Life by assigning them all the same code.<br/><br/>Identifier Source: Used in combination with the identifier for a media item. Provides the name of the agency or institution who assigned it, or system used."><i class="icon-question-sign"></i></a>
							<b>Local ID:</b></label>
					</td>
					<td>
						<div id="main_local_id">

							<div id="remove_local_0" class="remove_local_id">
								<div class="edit_form_div">
									<div>
										<p>Local ID: <span class="label_star"> *</span> </p>
										<p>
											<input type="text" id="asset_identifier_0" name="asset_identifier[]" value="" />
										</p>
									</div>
									<div>
										<p>ID Source: <span class="label_star"> *</span> </p>
										<p>
											<input type="text" id="asset_identifier_source_0" name="asset_identifier_source[]" value="" />
										</p>
									</div>
									<div>
										<p>ID Ref:</p>
										<p>
											<input type="text" id="asset_identifier_ref_0" name="asset_identifier_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>


						</div>
						<div class="add-new-element" onclick="addElement('#main_local_id', 'local_id');"><i class="icon-plus-sign icon-white"></i><span id="add_local_id"> ADD LOCAL ID</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Title: The descriptor title is a name given to the media item you are cataloging.<br/><br/>Title Type: a companion metadata field associated with the descriptor title. For a title you give to a media item, this allows you to inform end users what type of title it is."><i class="icon-question-sign"></i></a>
							<b>Title:</b></label>
					</td>
					<td>
						<div id="main_title">

							<div id="remove_title_0" class="remove_title">
								<div class="edit_form_div">
									<div>
										<p>Title: <span class="label_star"> *</span> </p>
										<p>
											<textarea id="asset_title_0" name="asset_title[]"></textarea>
										</p>
									</div>
									<div>
										<p>
											Title Type:
										</p>
										<p>
											<select id="asset_title_type_0" name="asset_title_type[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_title_types as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>"><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
									<div>
										<p>Title Source:</p>
										<p>
											<input type="text" id="asset_title_source_0" name="asset_title_source[]" value="" />
										</p>
									</div>
									<div>
										<p>Title Ref:</p>
										<p>
											<input type="text" id="asset_title_ref_0" name="asset_title_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_title', 'title');"><i class="icon-plus-sign icon-white"></i><span id="add_title"> ADD ANOTHER TITLE</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Subject: Used to assign topical headings or keywords that portray the intellectual content of the media item. Controlled vocabularies, authorities, or formal classification schemes may be employed when assigning descriptive subject terms (rather than using random or ad hoc terminology).<br/><br/>Subject Authority Used: If subjects are assigned to a media item using the descriptor subject and the terms used are derived from a specific authority or classification scheme, use this field to identify whose vocabularies and terms were used."><i class="icon-question-sign"></i></a>
							<b> Subject:</b></label>
					</td>
					<td>
						<div id="main_subject">

							<div id="remove_subject_0" class="remove_subject">
								<div class="edit_form_div">
									<div>
										<p>Subject:</p>
										<p>
											<input type="text" id="asset_subject_0" name="asset_subject[]" value=""/>
										</p>
									</div>
									<div>
										<p>
											Subject Type:
										</p>
										<p>
											<select id="asset_subject_type_0" name="asset_subject_type[]">
												<option value="">Select</option>
												<?php
												foreach ($pbcore_asset_subject_types as $row)
												{
													?>
													<option value="<?php echo $row->id; ?>" ><?php echo $row->subject_type; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
									<div>
										<p>Subject Source:</p>
										<p>
											<input type="text" id="asset_subject_source_0" name="asset_subject_source[]" value="" />
										</p>
									</div>
									<div>
										<p>Subject Ref:</p>
										<p>
											<input type="text" id="asset_subject_ref_0" name="asset_subject_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_subject', 'subject');"><i class="icon-plus-sign icon-white"></i><span id="add_subject"> ADD ANOTHER SUBJECT</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Description: Uses free-form text or a narrative to report general notes, abstracts, or summaries about the intellectual content of a media item. May also consist of outlines, lists, bullet points, rundowns, edit decision lists, indexes, or tables of content.<br/><br/>Description Type: A companion metadata field to the description. The purpose of descriptionType is to identify the nature of the actual description and flag the form of presentation for the information."><i class="icon-question-sign"></i></a>
							<b> Description:</b></label>
					</td>
					<td>
						<div id="main_description">

							<div id="remove_description_0" class="remove_description">
								<div class="edit_form_div">
									<div>
										<p>Description: <span class="label_star"> *</span> </p>
										<p>
											<textarea id="asset_description_0" name="asset_description[]"></textarea>
										</p>
									</div>
									<div>
										<p>
											Description Type:
										</p>
										<p>
											<select id="asset_description_type_0" name="asset_description_type[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_description_types as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>

								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>

							</div>
							<div class="add-new-element" onclick="addElement('#main_description', 'description');"><i class="icon-plus-sign icon-white"></i><span id="add_description"> ADD ANOTHER DESCRIPTION</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Genre: Describes the manner in which the intellectual content of a media item is presented, viewed or heard by a user. It indicates the structure of the presentation, as well as the topical nature of the content in a generalized form.<br/><br/>Genre Authority Used: If genre keywords are assigned to a media item using the descriptor genre and the terms used are derived from a specific authority or classification scheme, use genreAuthorityUsed to identify whose vocabularies and terms were used. PBcore supplies its own picklist of terms, but others may be employed as long as the authority for a picklist is identified. (If selecting from the drop down in “genre” — you are using the PBCore pbcoreGenre authority)."><i class="icon-question-sign"></i></a>
							<b> Genre:</b></label>
					</td>
					<td>
						<div id="main_genre">

							<div id="remove_genre_0" class="remove_genre">
								<div class="edit_form_div">
									<div>
										<p>Genre:</p>
										<p><input type="text" id="asset_genre_0" name="asset_genre[]" value="" /></p>
									</div>
									<div>
										<p>Genre Source:</p>
										<p><input type="text" id="asset_genre_source_0" name="asset_genre_source[]" value="" /></p>
									</div>
									<div>
										<p>Genre Ref:</p>
										<p><input type="text" id="asset_genre_ref_0" name="asset_genre_ref[]" value="" /></p>
										<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
									</div>

								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_genre', 'genre');"><i class="icon-plus-sign icon-white"></i><span id="add_genre"> ADD ANOTHER GENRE</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Coverage: Uses keywords to identify a span of space or time that is expressed by the intellectual content of a media item. Coverage in intellectual content may be expressed spatially by geographic location. Actual place names may be used. Numeric coordinates and geo-spatial data are also allowable, if useful or supplied. Coverage in intellectual content may also be expressed temporally by a date, period, era, or time-based event. The PBCore metadata element coverage houses the actual spatial or temporal keywords. The companion descriptor coverageType is used to identify the type of keywords that are being used.<br/><br/>Coverage Type: Used to identify the actual type of keywords that are being used by its companion metadata element coverage. coverageType provides a picklist of types, namely spatial or temporal, because coverage in intellectual content may be expressed spatially by geographic location or it may also be expressed temporally by a date, period, era, or time-based event."><i class="icon-question-sign"></i></a>
							<b> Coverage:</b></label>
					</td>
					<td>
						<div id="main_coverage">

							<div id="remove_coverage_0" class="remove_coverage">
								<div class="edit_form_div">
									<div>
										<p>Coverage:</p>
										<p><input type="text" id="asset_coverage_0" name="asset_coverage[]" value="" /></p>
									</div>
									<div>
										<p>Coverage Type:</p>
										<p><select id="asset_coverage_type_0" name="asset_coverage_type[]">
												<option value="">Select</option>
												<option value="spatial"  >spatial</option>
												<option value="temporal" >temporal</option>
											</select></p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_coverage', 'coverage');"><i class="icon-plus-sign icon-white"></i><span id="add_coverage"> ADD ANOTHER COVERAGE</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label><b> Audience Level:</b></label>
					</td>
					<td>
						<div id="main_audience_level">

							<div id="remove_audience_level_0" class="remove_audience_level">
								<div class="edit_form_div">
									<div>
										<p>
											Audience Level:
										</p>
										<p>
											<select id="asset_audience_level_0" name="asset_audience_level[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_audience_level as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
									<div>
										<p> Audience Level Source:</p>
										<p>
											<input type="text" id="asset_audience_level_source_0" name="asset_audience_level_source[]" value="" />
										</p>
									</div>
									<div>
										<p> Audience Level Ref:</p>
										<p>
											<input type="text" id="asset_audience_level_ref_0" name="asset_audience_level_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>


								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_audience_level', 'audience_level');"><i class="icon-plus-sign icon-white"></i><span id="add_audience_level"> ADD ANOTHER AUDIENCE LEVEL</span></div>

					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label><b> Audience Rating:</b></label>
					</td>
					<td>
						<div id="main_audience_rating">

							<div id="remove_audience_rating_0" class="remove_audience_rating">
								<div class="edit_form_div">
									<div>
										<p>
											Audience Rating:
										</p>
										<p>
											<select id="asset_audience_rating_0" name="asset_audience_rating[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_audience_rating as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>
										</p>
									</div>
									<div>
										<p> Audience Rating Source:</p>
										<p>
											<select id="asset_audience_rating_source_0" name="asset_audience_rating_source[]">
												<option value="">Select</option>
												<option value="MPAA"  >MPAA</option>
												<option value="TV Parental Guidelines" >TV Parental Guidelines</option>
											</select>

										</p>
									</div>
									<div>
										<p> Audience Rating Ref:</p>
										<p>
											<select id="asset_audience_rating_ref_0" name="asset_audience_rating_ref[]">
												<option value="">Select</option>
												<option value="http://www.filmratings.com"  >http://www.filmratings.com</option>
												<option value="http://www.tvguidelines.org/ratings.htm">http://www.tvguidelines.org/ratings.htm</option>
											</select>

										</p>
									</div>


								</div>


								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_audience_rating', 'audience_rating');"><i class="icon-plus-sign icon-white"></i><span id="add_audience_level"> ADD ANOTHER AUDIENCE RATING</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label><b> Annotation:</b></label>
					</td>
					<td>
						<div id="main_annotation">

							<div id="remove_annotation_0" class="remove_annotation">
								<div class="edit_form_div">
									<div>
										<p>
											Annotation:
										</p>
										<p>
											<input type="text" id="asset_annotation_0" name="asset_annotation[]" value="" />
										</p>
									</div>
									<div>
										<p> Annotation Type:</p>
										<p>
											<input type="text" id="asset_annotation_type_0" name="asset_annotation_type[]" value="" />

										</p>
									</div>
									<div>
										<p> Annotation Ref:</p>
										<p>
											<input type="text" id="asset_annotation_ref_0" name="asset_annotation_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>


								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_annotation', 'annotation');"><i class="icon-plus-sign icon-white"></i><span id="add_annotation"> ADD ANOTHER ANNOTATION</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label><b> Relation:</b></label>
					</td>
					<td>
						<div id="main_relation">

							<div id="remove_relation_0" class="remove_relation">
								<div class="edit_form_div">
									<div>
										<p>
											Relation:
										</p>
										<p>
											<input type="text" id="asset_relation_identifier_0" name="asset_relation_identifier[]" value="" />
										</p>
									</div>
									<div>
										<p> Relation Type:</p>
										<p>
											<select id="asset_relation_type_0" name="asset_relation_type[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_relation_types as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>

										</p>
									</div>
									<div>
										<p> Relation Source:</p>
										<p>
											<input type="text" id="asset_relation_source_0" name="asset_relation_source[]" value="" />
										</p>
									</div>
									<div>
										<p> Relation Ref:</p>
										<p>
											<input type="text" id="asset_relation_ref_0" name="asset_relation_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_relation', 'relation');"><i class="icon-plus-sign icon-white"></i><span id="add_relation"> ADD ANOTHER RELATION</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Creator: Identifies a person or organization primarily responsible for creating a media item. The creator may be considered an author and could be one or more people, a business, organization, group, project or service.<br/><br/>Creator Role: Identifies the role played by the person or group identified in the companion descriptor Creator."><i class="icon-question-sign"></i></a>
							<b> Creator:</b></label>
					</td>
					<td>
						<div id="main_creator">

							<div id="remove_creator_0" class="remove_creator">
								<div class="edit_form_div">
									<div>
										<p>
											Creator:
										</p>
										<p>
											<input type="text" id="asset_creator_name_0" name="asset_creator_name[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Creator Affiliation:
										</p>
										<p>
											<input type="text" id="asset_creator_affiliation_0" name="asset_creator_affiliation[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Creator Ref:
										</p>
										<p>
											<input type="text" id="asset_creator_ref_0" name="asset_creator_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
									<div>
										<p> Creator Role:</p>
										<p>
											<select id="asset_creator_role_0" name="asset_creator_role[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_creator_roles as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>

										</p>
									</div>
									<div>
										<p> Creator Role Source:</p>
										<p>
											<input type="text" id="asset_creator_role_source_0" name="asset_creator_role_source[]" value="" />
										</p>
									</div>
									<div>
										<p> Creator Role Ref:</p>
										<p>
											<input type="text" id="asset_creator_role_ref_0" name="asset_creator_role_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_creator', 'creator');"><i class="icon-plus-sign icon-white"></i><span id="add_creator"> ADD ANOTHER CREATOR</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Contributor: Identifies a person or organization that has made substantial creative contributions to the intellectual content within a media item. This contribution is considered to be secondary to the primary author(s) (person or organization) identified in the descriptor Creator.<br/><br/>Contributor Role: Identifies the role played by the person or group identified in the companion descriptor Contributor."><i class="icon-question-sign"></i></a>
							<b> Contributor:</b></label>
					</td>
					<td>
						<div id="main_contributor">

							<div id="remove_contributor_0" class="remove_contributor">

								<div class="edit_form_div">
									<div>
										<p>
											Contributor:
										</p>
										<p>
											<input type="text" id="asset_contributor_name_0" name="asset_contributor_name[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Contributor Affiliation:
										</p>
										<p>
											<input type="text" id="asset_contributor_affiliation_0" name="asset_contributor_affiliation[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Contributor Ref:
										</p>
										<p>
											<input type="text" id="asset_contributor_ref_0" name="asset_contributor_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
									<div>
										<p> Contributor Role:</p>
										<p>
											<select id="asset_contributor_role_0" name="asset_contributor_role[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_contributor_roles as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>

										</p>
									</div>
									<div>
										<p> Contributor Role Source:</p>
										<p>
											<input type="text" id="asset_contributor_role_source_0" name="asset_contributor_role_source[]" value="" />
										</p>
									</div>
									<div>
										<p> Contributor Role Ref:</p>
										<p>
											<input type="text" id="asset_contributor_role_ref_0" name="asset_contributor_role_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>

								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_contributor', 'contributor');"><i class="icon-plus-sign icon-white"></i><span id="add_contributor"> ADD ANOTHER CONTRIBUTOR</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="Publisher: Identifies a person or organization primarily responsible for distributing or making a media item available to others. The publisher may be a person, a business, organization, group, project or service.<br/><br/>Publisher Role: Identifies the role played by the specific publisher or publishing entity identified in the companion descriptor Publisher."><i class="icon-question-sign"></i></a>
							<b> Publisher:</b></label>
					</td>
					<td>
						<div id="main_publisher">

							<div id="remove_publisher_0" class="remove_publisher">
								<div class="edit_form_div">
									<div>
										<p>
											Publisher:
										</p>
										<p>
											<input type="text" id="asset_publisher_0" name="asset_publisher[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Publisher Affiliation:
										</p>
										<p>
											<input type="text" id="asset_publisher_affiliation_0" name="asset_publisher_affiliation[]" value="" />
										</p>
									</div>
									<div>
										<p>
											Publisher Ref:
										</p>
										<p>
											<input type="text" id="asset_publisher_ref_0" name="asset_publisher_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
									<div>
										<p> Publisher Role:</p>
										<p>
											<select id="asset_publisher_role_0" name="asset_publisher_role[]">
												<option value="">Select</option>
												<?php
												$commonly = $less = FALSE;
												foreach ($pbcore_asset_publisher_roles as $row)
												{
													if ($row->display_value == 1 && ! $commonly)
													{
														$commonly = TRUE;
														?>
														<optgroup label="Commonly Used">Commonly Used</optgroup>
														<?php
													}
													else if ($row->display_value == 2 && ! $less)
													{
														$less = TRUE;
														?>
														<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>
													<?php } ?>
													<option value="<?php echo $row->value; ?>" ><?php echo $row->value; ?></option>
												<?php }
												?>
											</select>

										</p>
									</div>
									<div>
										<p> Publisher Role Source:</p>
										<p>
											<input type="text" id="asset_publisher_role_source_0" name="asset_publisher_role_source[]" value="" />
										</p>
									</div>
									<div>
										<p> Publisher Role Ref:</p>
										<p>
											<input type="text" id="asset_publisher_role_ref_0" name="asset_publisher_role_ref[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>

								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_publisher', 'publisher');"><i class="icon-plus-sign icon-white"></i><span id="add_publisher"> ADD ANOTHER PUBLISHER</span></div>
					</td>

				</tr>
				<tr>

					<td class="record-detail-page">
						<label>
							<a data-placement="left" rel="tooltip" href="#" data-original-title="An all-purpose container field to identify information about copyrights and property rights held in and over a media item, whether they are open access or restricted in some way. If dates, times and availability periods are associated with a right, include them. End user permissions, constraints and obligations may also be identified, as needed."><i class="icon-question-sign"></i></a>
							<b> Right Summary:</b></label>
					</td>
					<td>
						<div id="main_right">

							<div id="remove_right_0" class="remove_right">
								<div class="edit_form_div">
									<div>
										<p>
											Right:
										</p>
										<p>
											<input type="text" id="asset_rights_0" name="asset_rights[]" value="" />
										</p>
									</div>
									<div>
										<p> Right Link:</p>
										<p>
											<input type="text" id="asset_right_link_0" name="asset_right_link[]" value="" />
											<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span>
										</p>
									</div>
								</div>

								<div class="clearfix" style="margin-bottom: 10px;"></div>
							</div>

						</div>
						<div class="add-new-element" onclick="addElement('#main_right', 'right');"><i class="icon-plus-sign icon-white"></i><span id="add_right"> ADD ANOTHER RIGHT</span></div>
					</td>

				</tr>
				<tr>
					<td colspan="2">
						<a class="btn" href="<?php echo site_url('records/index'); ?>">Cancel</a>
						<?php 
						// 20140925_kc begin prevent public user from adding assets
						if ($this->role_id != 20)
						{
						?>
						<input type="button" onclick="validateForm();" value="Create" class="btn btn-primary"/>
						<?php
						}
						// 20140925_kc end prevent public user from adding assets
						?>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<script type="text/javascript">
								var pbcoreAssetTypes =<?php echo json_encode($pbcore_asset_types); ?>;
								var pbcoreDateTypes =<?php echo json_encode($pbcore_asset_date_types); ?>;
								var pbcoreTitleTypes =<?php echo json_encode($pbcore_asset_title_types); ?>;
								var pbcoreSubjectTypes =<?php echo json_encode($pbcore_asset_subject_types); ?>;
								var pbcoreDescriptionTypes =<?php echo json_encode($pbcore_asset_description_types); ?>;
								var pbcoreAudienceLevel =<?php echo json_encode($pbcore_asset_audience_level); ?>;
								var pbcoreAudienceRating =<?php echo json_encode($pbcore_asset_audience_rating); ?>;
								var pbcoreRelationTypes =<?php echo json_encode($pbcore_asset_relation_types); ?>;
								var pbcoreCreatorRoles =<?php echo json_encode($pbcore_asset_creator_roles); ?>;
								var pbcoreContributorRoles =<?php echo json_encode($pbcore_asset_contributor_roles); ?>;
								var pbcorePublisherRoles =<?php echo json_encode($pbcore_asset_publisher_roles); ?>;

</script>
<script type="text/javascript" src="/js/edit_asset.js?<?php echo time(); ?>"></script>
<style type="text/css">
	.ui-datepicker,.ui-datepicker-group{
		width: 22em !important;
	}
</style>
