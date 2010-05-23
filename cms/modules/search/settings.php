<?php

$settings = array(
	'sph_version_nr' => '1.3.3',
	'sph_language' => 'en',
	'sph_template' => 'standard',
	'sph_admin_email' => 'admin@localhost',
	'sph_print_results' => 1,
	'sph_tmp_dir' => 'tmp',
	'sph_keep_log' => 0,
	'sph_log_dir' => 'log',
	'sph_log_format' => 'html',
	'sph_email_log' => 0,
	'sph_min_words_per_page' => 10,
	'sph_min_word_length' => 3,
	'sph_word_upper_bound' => 100,
	'sph_index_numbers' => 1,
	'sph_index_host' => 0,
	'sph_index_meta_keywords' => 1,
	'sph_index_pdf' => 0,
	'sph_index_doc' => 0,
	'sph_index_xls' => 0,
	'sph_index_ppt' => 0,
	'sph_pdftotext_path' => 'c:\temp\pdftotext.exe',
	'sph_catdoc_path' => 'c:\temp\catdoc.exe',
	'sph_xls2csv_path' => 'c:\temp\xls2csv',
	'sph_catppt_path' => 'c:\temp\catppt',
	'sph_user_agent' => 'Sphider',
	'sph_min_delay' => 0,
	'sph_stem_words' => 0,
	'sph_strip_sessids' => 1,
	'sph_results_per_page' => 10,
	'sph_cat_columns' => 2,
	'sph_bound_search_result' => 0,
	'sph_length_of_link_desc' => 0,
	'sph_links_to_next' => 9,
	'sph_show_meta_description' => 1,
	'sph_advanced_search' => 0,
	'sph_show_query_scores' => 1,
	'sph_show_categories' => 1,
	'sph_desc_length' => 250,
	'sph_merge_site_results' => 0,
	'sph_did_you_mean_enabled' => 0,
	'sph_suggest_enabled' => 0,
	'sph_suggest_history' => 1,
	'sph_suggest_keywords' => 0,
	'sph_suggest_phrases' => 0,
	'sph_suggest_rows' => 10,
	'sph_title_weight' => 20,
	'sph_domain_weight' => 60,
	'sph_path_weight' => 10,
	'sph_meta_weight' => 5,

/// DATABASE SETTINGS
	'sph_database' => MYSQL_DATABASE,
	'sph_mysql_user' => MYSQL_USERNAME,
	'sph_mysql_password' => MYSQL_PASSWORD, 
	'sph_mysql_host' => MYSQL_SERVER,
	'sph_mysql_table_prefix' => 'sphider_',


/// ARBID STUFF
	'sph_regex_consonant' => '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)',
	'sph_regex_vowel' => '(?:[aeiou]|(?<![aeiou])y)'
);

if($unsetEm) {
	foreach($settings as $settingName => $settingValue) {
		if(isset($GLOBALS[$settingName])) {
			unset($GLOBALS[$settingName]);
		}
	}
}
else {
	foreach($settings as $settingName => $settingValue)
		$GLOBALS[$settingName] = $settingValue;
}

?>