<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/*
 * For a module to get integrated to Pragyan CMS properly table structures of two tables need to be modified 
 * (for now, planning to change to a better way),
 * _pages:
 * 	page_module column is of enumerated datatype, add your module name to the list
 * _permisssionlist:
 * 	same as mentioned before, alter the column to add your module name to the list
 * 
 * then insert a row to _permissionlist table with a unique permission id and perm_action as create and page_module as module name
 * insert a row for each action with a unique permission id with respective action name and page_module as module name
 * 
 * NOTE:  If your module requires some styling, then put it in templates/common/other.css and DO NOT hardcode it in this file.
 * NOTE:  Also for styling purposes you must create classes/IDs of the HTML elements defined in here with module name in it so it doesn't conflict 
 * with other output Pragyan CMS will create.
 */

class /*Module Name here*/ implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == /*Action Name here*/)
			return $this->action/*Action Name with first character as Upper Case*/;
		else if($this->action == /*Next Action Name here*/)
			return $this->action/*As said before*/;
		return $this->actionView;		//this is the default action
		/*
		 * for each of the action in your module a function with name 
		 * action followed by the action name with first character upper case
		 * is to be created inside this class which will be called when
		 * some user is trying to perform that action on an instance of your module.
		 */
	}
	
	public function actionView() {		//as said before view will be default action, hence this function is mandatory in every module
		// dump whatever you want to display to user into some variable and return it
	}
	
	public function createModule(&$moduleComponentId) {
		/* 
		 * This is also a necessary function
		 * it'll be called when a new instance of your module is going to be created
		 * you can think of it like a constructer (though it is not technically)
		 * you can do all your initialisation in this function
		 * Dont forget to assign $moduleComponentId with a id otherthan -1
		 * if -1 is assigned, it'll be assumed that there was some problem in creation
		 */

	}

	public function deleteModule($moduleComponentId) {
		/*
		 * This is also a necessary function
		 * it'll be called when an instance of your module is going to get deleted
		 * you can do your clean up works for the module instance here
		 * return true in case of successful deletion, else false
		 */
		 
	}
	
	public function copyModule($moduleComponentId) {
		/*
		 * This is also a necessary function
		 * it'll be called when a module is to be copied
		 * return true when copied successfully, else false
		 */
	}
}


?>
