<?php
/*
 * Created on Apr 21, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */



class search implements module {
	public function actionView() {
		if($_GET['query']) {
			return $this->doSearch();
		}
		else {
			return $this->getSearchBoxHtml();
		}
	}

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		switch($action) {
			case 'view':
				return $this->actionView();
				break;
		}
	}

	public function doSearch() {
		global $sourceFolder, $moduleFolder;
		$resultHTML = '';
		$this->globalizeSettings();

		include_once("$sourceFolder/$moduleFolder/search/search.php");

		$resultCSS = <<<RESULTCSS
		<style type="text/css">
		#result_report {
			text-align:center ;
			padding:3px;
			background-color:#e5ecf9; 
			font-weight: bold;
			margin-bottom:14px;
			margin-right: 10px;
		}
		</style>

RESULTCSS;

		return $resultCSS . $this->getSearchBoxHtml('Search again?') . $resultHTML;
	}

	private function getSearchBoxHtml($again = false) {
		$searchHeader = 'Search' . ($again ? ' Again?' : '');
		$searchBoxTitle = $again ?
			'' :
			'<p align="left">Enter your query in the box below, and click search.</p>';

		$searchBox = <<<SEARCHBOXHTML

	<style type="text/css">
		table.searchBox{border:1px solid #113377}
	</style>

	<h3>$searchHeader</h3>
	$searchBoxTitle
	<center>
	<table cellspacing="1" cellpadding="5" class="searchBox">
		<tr>
			<td align="center">
				<table>
					<tr>
						<td><div align="left"><input type="text" size="40" id="query" name="query"/></td>
						<td><input type="button" value="Search" onclick="doSearch()" /></td>
					</tr>
				</table>

				<script language="javascript" type="text/javascript">
					document.getElementById('query').onkeyup = function(e) {
						var enterKey = 13;
						if (e.keyCode == enterKey) {
							doSearch();
							return false;
						}
					};
					function doSearch() {
						window.location = "./+view&query=" + document.getElementById('query').value + "&search=1";
					}
				</script>
			</td>
		</tr>
	</table>
	</center>
SEARCHBOXHTML;

		return $searchBox;
	}

	private function globalizeSettings() {
		global $sourceFolder, $moduleFolder;
		include_once("$sourceFolder/$moduleFolder/search/settings.php");
		$sph_messages = array();
		include_once("$sourceFolder/$moduleFolder/search/languages/{$GLOBALS['sph_language']}-language.php");
		$GLOBALS['sph_messages'] = $sph_messages;
		include_once("$sourceFolder/$moduleFolder/search/settings/database.php");
	}

	public function deleteModule($moduleComponentId) {
		
	}

	public function copyModule($moduleComponentId) {
		
	}

	public function createModule(&$moduleComponentId) {
		$moduleComponentId = 1;
	}
}

