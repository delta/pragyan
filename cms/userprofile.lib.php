<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @author Boopathi Rajaa
 * @copyright (c) 2011 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

class Item {
	
	public $visible;
	
	function __construct() {
		$this->visible = false;
	}
	
	public function getField($fieldType) {
		
	}
	
}

function generatePublicProfile($userId) {
	$username = getUserName($userId);
	
}
