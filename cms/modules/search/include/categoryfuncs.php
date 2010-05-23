<?php 

function get_categories_view() {
	global $sph_mysql_table_prefix;
	$categories['main_list'] = sql_fetch_all('SELECT * FROM '.$sph_mysql_table_prefix.'categories WHERE parent_num=0 ORDER BY category');
		
	if (is_array($categories['main_list'])) {
		foreach ($categories['main_list'] as $_key => $_val) {
			$categories['main_list'][$_key]['sub'] =  sql_fetch_all('SELECT * FROM '.$sph_mysql_table_prefix.'categories WHERE parent_num='.$_val['category_id']);
		}
	}
	return $categories;
}

function get_category_info($catid) {
	global $sph_mysql_table_prefix;
	$categories['main_list'] = sql_fetch_all("SELECT * FROM ".$sph_mysql_table_prefix."categories ORDER BY category");
	
	if (is_array($categories['main_list'])) {
		foreach($categories['main_list'] as $_val) {
			$categories['categories'][$_val['category_id']] = $_val;
			$categories['subcats'][$_val['parent_num']][] = $_val;
		}
	}
	
	$categories['subcats'] = $categories['subcats'][$_REQUEST['catid']];
	
	/* count sites */
	if (is_array($categories['subcats'])) {
		foreach ($categories['subcats'] as $_key => $_val) {
			$categories['subcats'][$_key]['count'] = sql_fetch_all('SELECT count(*) FROM '.$sph_mysql_table_prefix.'site_category WHERE 	category_id='.(int)$_val['category_id']);
		}
	}
		
	/* make tree */	
	$_parent = $catid;
	while ($_parent) {
		$categories['cat_tree'][] = $categories['categories'][$_parent];
		$_parent = $categories['categories'][$_parent]['parent_num'];
	}
	$categories['cat_tree'] = array_reverse($categories['cat_tree']);
	
	
	/* list category sites */
	$categories['cat_sites'] = sql_fetch_all('SELECT url, title, short_desc FROM '.$sph_mysql_table_prefix.'sites, '.$sph_mysql_table_prefix.'site_category WHERE category_id='.$catid.' AND '.$sph_mysql_table_prefix.'sites.site_id='.$sph_mysql_table_prefix.'site_category.site_id order by title');
	
	return $categories;
}


?>