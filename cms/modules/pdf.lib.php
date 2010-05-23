<?php

/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

class pdf implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if ($gotaction == 'download')
			return $this->actionDownload();
		

	}
	public function actionDownload() {
		global $CONTENT;
echo $CONTENT;		
	}

	
	public function createModule(& $moduleComponentId) {

	}

	public function deleteModule($moduleComponentId) {
		
	}

	public function copyModule($moduleComponentId) {
		
	}
}
