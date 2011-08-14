<?php
/**
 * @package pragyan
 * @author Boopathi Rajaa
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

class form2 implements module {
	private $userId;
	private $moduleComponentId;
	private $action;
	private $pageId;
	
	/*
	 * @function getHTML gateway through which the cms interacts with the module
	 */
	public function getHTML($guid, $gmoduleComponentId, $gaction) {
		$this->userId = $guid;
		$this->moduleComponentId = $gmoduleComponentId;
		$this->action = $gaction;
		$this->pageId = getPageIdFromModuleComponentId("form2",$gmoduleComponentId);
		
		switch($this->action) {
			case "edit":
				return $this->actionEdit();
				break;
			default: 
				return $this->actionView();
		}
	}
	
	public function actionView() {
		return "adfadsf";
	}

	public function actionEdit() {
		$html = "";
		
		$html .= "<table><tr><td>";
		
		$html .= "<input type='button' value='+' onclick='addNewFormField()' />";
		
		$html .= <<<Script
		<script type="text/javascript">
			function newFormField() {
					
			}
			function addNewFormField(){
				
			}
		</script>
Script;
		
		$html .= "</td></tr><table>";
	}
	
	public function createModule($compId) {
		return true;
	}
	public function deleteModule($moduleComponentId) {
		return true;
	}
	public function copyModule($mid, $newId){
		return true;
	}
}

class Element {
	
	private $type;
	private $html;
	
	public function __construct($t="text") {
		$this->type = $t;
		$this->html="";
	}

	function getHTML($new) {
		$this->gen();
		return $this->html;
	}
	
	private function gen() {
		$this->html = "";
		$temp = "";
	}
}
