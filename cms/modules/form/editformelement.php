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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

 	function generateEditFormElementDescBody($moduleCompId, $elementId, $action = 'editform') {
		$myElement = new FormElement();

		$elementQuery = 'SELECT * FROM `form_elementdesc` WHERE ' .
										'`page_modulecomponentid` = \'' . $moduleCompId . '\' AND ' .
										'`form_elementid` = \'' . $elementId."'";

		if($elementResult = mysql_query($elementQuery)) {
			if($elementRow = mysql_fetch_assoc($elementResult)) {
				$myElement -> fromMysqlTableRow($elementRow);
				return $myElement -> toHtmlForm('elementDataForm', $action);
			}
		}

		return 'An error occurred while trying to process your request. ' .
		       'Could not load data for the given form element.';
 	}

 	function submitEditFormElementDescData($moduleCompId,$elementId) {
		$myElement = new FormElement();

		$myElement -> fromHtmlForm();
		$updateQuery = $myElement -> toMysqlUpdateQuery($moduleCompId);

		if(mysql_query($updateQuery)) {
			return true;
		}
		else {
			return false;
		}
 	}

 	/** Represents a single form element,
 * i.e. an object of this class will represent name, one will represent age etc.
 *
 * Use toHtmlForm to generate one row for editting a form element
 * Use
 */
	class FormElement {
	private $elementId;
	private $elementName;
	private $elementDescription;
	private $elementType;
	private $elementSize;
	private $typeOptions;
	private $defaultValue;
	private $moreThan;
	private $lessThan;
	private $checkInteger;
	private $toolTipText;
	private $isRequired;
	private $elementRank;

	private static $fieldMap = array(
								'form_elementid' => 'elementId',
								'form_elementname' => 'elementName',
								'form_elementdisplaytext' => 'elementDescription',
								'form_elementtype' => 'elementType',
								'form_elementsize' => 'elementSize',
								'form_elementtypeoptions' => 'typeOptions',
								'form_elementdefaultvalue' => 'defaultValue',
								'form_elementmorethan' => 'moreThan',
								'form_elementlessthan' => 'lessThan',
								'form_elementcheckint' => 'checkInteger',
								'form_elementtooltiptext' => 'toolTipText',
								'form_elementisrequired' => 'isRequired',
								'form_elementrank' => 'elementRank'
							);


	/**
	 * Loads properties of the element from a mysql table row (associative)
	 */
	public function fromMysqlTableRow($elementDescRow) {
		foreach(FormElement::$fieldMap as $key => $value) {
			$this->$value = $elementDescRow[$key];
		}

		$this->checkInteger = $this->checkInteger == 1 ? true : false;
		$this->isRequired = $this->isRequired == 1 ? true : false;
	}

	/**
	 * Loads properties of the element from a submitted form
	 */
	public function fromHtmlForm() {
		if($_POST['elementid'] == 'new' || !ctype_digit($_POST['elementid'])) {
			if(isset($this->elementId))
				unset($this->elementId);
		}
		else {
			$this->elementId = escape($_POST['elementid']);
		}
		
		$this->elementName = escape($_POST['txtElementName']);
		$this->elementDescription = escape($_POST['txtElementDesc']);
		$this->elementType = escape($_POST['selElementType']);
		$this->elementSize = escape($_POST['txtElementSize']);
		$this->typeOptions = isset($_POST['txtElementTypeOptions']) ? escape($_POST['txtElementTypeOptions']) : '';
		$this->defaultValue = escape($_POST['txtDefaultValue']);
		$this->checkInteger = (isset($_POST['optCheckNumber']) && $_POST['optCheckNumber'] == 'yes') ? true : false;
		$this->moreThan = isset($_POST['txtMinValue']) ? escape($_POST['txtMinValue']) : '';
		$this->lessThan = isset($_POST['txtMaxValue']) ? escape($_POST['txtMaxValue']) : '';
		$this->toolTipText = escape($_POST['txtToolTip']);
		$this->isRequired = escape($_POST['optRequired']) == 'yes' ? true : false;
	}

	
				
	/**
	 * Returns element properies as a row in an HTML table
	 */
	public function toHtmlTableRow($imagePath, $action='editform') {
		$checkNumber = $this->checkInteger == true ? 'Integer Only' : '';
		$required = $this->isRequired == true ? 'Required' : 'Not Required';
		$requiredClass = $this->isRequired == true ? 'formfieldred' : 'formfieldgreen';
	
	
		$otherInfo="<div class=\"formfieldextrainfo $requiredClass\">$required</div>";
		if($this->elementSize!=0)
			$otherInfo.="<div class=\"formfieldextrainfo formfieldinfo\"><span>Size</span><br/>{$this->elementSize}</div>";
		if($this->defaultValue!="")
			$otherInfo.="<div class=\"formfieldextrainfo formfieldinfo\"><span>Default</span><br/>{$this->defaultValue}</div>";
		if($checkNumber!="")
			$otherInfo.="<div class=\"formfieldextrainfo formfieldred\" title=\"Only in the case that the entered element should be a number\">$checkNumber</div>";
		if($this->checkInteger==true || $this->elementType=="date" || $this->elementType=="datetime")
			$otherInfo.="<div class=\"formfieldextrainfo formfieldinfo\" title=\"Minimum and Maximum value of date or number\"><span>Range</span><br/>{$this->moreThan}-{$this->lessThan}</div>";
		else if($this->elementType=="file")
			$otherInfo.="<div class=\"formfieldextrainfo formfieldred\" title=\"Maximum value of uploaded file\"><span>Upload Limit</span><br/>{$this->lessThan}</div>";
		
	
	
		
		
		$rowString = <<<ROWSTRING
		<script language="javascript">
			function gotopage(pagepath) {
				if(confirm("Are you sure you want to delete this form element?"))
					window.location = pagepath;
			}
	    </script>
		<tr>
			<td>
				<a href="./+$action&subaction=moveUp&elementid={$this->elementId}">
					<img src="$imagePath/common/icons/16x16/actions/go-up.png" alt="Move Up" title="Move Up"/>
				</a>
			
				<a href="./+$action&subaction=moveDown&elementid={$this->elementId}">
					<img src="$imagePath/common/icons/16x16/actions/go-down.png" alt="Move Down" title="Move Down"/>
				</a>
			
				<a href="./+$action&subaction=editformelement&elementid={$this->elementId}">
					<img src="$imagePath/common/icons/16x16/apps/accessories-text-editor.png" alt="Edit" title="Edit" />
				</a>
			
				<a style="cursor:pointer" onclick="return gotopage('./+$action&subaction=deleteformelement&elementid={$this->elementId}')">
					<img src="$imagePath/common/icons/16x16/actions/edit-delete.png" alt="Delete" title="Delete" />
					</a>
				</td>
				<td>{$this->elementName}</td>
				<td>{$this->elementDescription}</td>
				<td>{$this->elementType}</td>
				<td>{$this->toolTipText}</td>
				<td>
                                $otherInfo
                                </td>

				<td>{$this->typeOptions}</td>
			</tr>
ROWSTRING;
	
			return $rowString;
		}


		/**
	 * Returns element properties as an editable HTML form
	 */
	public function toHtmlForm($formHtmlId = 'elementDataForm', $action = 'editform') {
		global $cmsFolder;
		global $moduleFolder;
		global $urlRequestRoot;
		$calpath="$urlRequestRoot/$cmsFolder/$moduleFolder";

		if($formHtmlId == '') {
			$formHtmlId = 'elementDataForm';
		}
		if($action == '') {
			$action = 'editform';
		}

		$elemTypeBox = '';

		$elementTypes = array('text', 'textarea', 'radio', 'checkbox', 'select', 'password', 'file', 'date', 'datetime');
		if(isset($this->elementType)) {
			for($i = 0; $i < count($elementTypes); $i++) {
				$elemTypeBox .= '<option';
				if($elementTypes[$i] == $this->elementType) {
					$elemTypeBox .= ' selected="selected"';
				}
				$elemTypeBox .= '>' . $elementTypes[$i] . "</option>\n";
			}
		}
		else {
			$elemTypeBox = '<option>' . join($elementTypes, "</option>\n<option>") . '</option>';
		}

		$hiddenValue = isset($this->elementId) ? $this->elementId : 'new';

		$checkNumber = $this->checkInteger == true ? 'checked="checked"' : '';
		$checkNumberN = $this->checkInteger == true ? '' : 'checked="checked"';
		$required = $this->isRequired == true ? 'checked="checked"' : '';
		$requiredN = $this->isRequired == true ? '' : 'checked="checked"';

		$htmlOutput = <<<HTMLOUTPUT

		<script language="javascript" type="text/javascript">
		<!--
			var datetimeFormat = '%Y-%m-%d %H:%M';

			function checkNumberClicked(form) {
				if(form.optCheckNumber[0].checked == true && form.selElementType.value == 'text') {
					form.txtMaxValue.disabled = form.txtMinValue.disabled = false;
				}

				else if(form.selElementType.value == 'text')
					form.txtMaxValue.disabled = form.txtMinValue.disabled = true;
			}

			function elementTypeChanged(form) {
				var elemType = form.selElementType.value;

				if(elemType == 'checkbox' || elemType == 'radio' || elemType == 'select' || elemType == 'file') {
					form.txtElementTypeOptions.disabled = false;
				}
				else {
					form.txtElementTypeOptions.disabled = true;
				}

				if(elemType == 'text') {
					form.optCheckNumber[0].disabled = form.optCheckNumber[1].disabled = false;
				}
				else {
					form.optCheckNumber[0].disabled = form.optCheckNumber[1].disabled = true;
				}

				if(elemType == 'textarea' || elemType == 'text') {
					form.txtElementSize.disabled = false;
				}
				else {
					form.txtElementSize.disabled = true;
				}

				if(elemType == 'file') {
					form.txtDefaultValue.disabled = true;
				}
				else {
					form.txtDefaultValue.disabled = false;
				}

				if(elemType == 'date' || elemType == 'datetime' || elemType == 'text') {
					form.txtMaxValue.disabled = form.txtMinValue.disabled = false;
				}
				else if(elemType == 'file') {
					form.txtMaxValue.disabled = false;
					form.txtMinValue.disabled = true;
				}
				else {
					form.txtMaxValue.disabled = form.txtMinValue.disabled = true;
				}

				if(elemType == 'date' || elemType == 'datetime') {
					if(elemType == 'date') {
						datetimeFormat = '%Y-%m-%d';
					}
					else {
						datetimeFormat = '%Y-%m-%d %H:%M';
					}
					form.calDefaultValue.style.display = form.calMaxValue.style.display = form.calMinValue.style.display = 'inline';
				}
				else {
					form.calDefaultValue.style.display = form.calMaxValue.style.display = form.calMinValue.style.display = 'none';
				}
			}
		-->
		</script>
		<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
		<script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>

		<form id="$formHtmlId" action="./+$action&subaction=editformelement" method="POST">
			<br />
			<table cellspacing="12px">
				<tr>
					<td nowrap="nowrap">Name of the variable:</td><td><input type="text" name="txtElementName" value="{$this->elementName}" /></td>
				</tr>
				<tr>
					<td>Text displayed before this field:</td><td><textarea style="width:98%;" name="txtElementDesc" rows="5" cols="50">{$this->elementDescription}</textarea></td>
				</tr>
				<tr>
					<td>Element Type:</td>
					<td>
						<select name="selElementType" onchange="elementTypeChanged(this.form)">$elemTypeBox</select>
					</td>
				</tr>

				<tr>
					<td>Element Size:</td>
					<td><input type="text" name="txtElementSize" value="{$this->elementSize}" /></td>
				</tr>

				<tr>
					<td>Extra Options* (| separated values):</td>
					<td><input type="text" name="txtElementTypeOptions" value="{$this->typeOptions}" disabled="disabled" title="Used in the case of checkboxes, radio buttons and select fields." /></td>
				</tr>

				<tr>
					<td>Default Value:</td>
					<td><input type="text" name="txtDefaultValue" id="txtDefaultValue" value="{$this->defaultValue}" /><input name='calDefaultValue' type="reset" value=" ... " onclick="return showCalendar('txtDefaultValue', datetimeFormat, '24', true);" /></td>
				</tr>

				<tr>
					<td>Strictly a number?</td>
					<td>
						<label><input type="radio" onclick="checkNumberClicked(this.form)" name="optCheckNumber" value="yes" $checkNumber />Yes</label>
						<label><input type="radio" onclick="checkNumberClicked(this.form)" name="optCheckNumber" value="no" $checkNumberN />No</label>
					</td>
				</tr>

				<tr>
					<td>Minimum Value:</td>
					<td><input type="text" id="txtMinValue" name="txtMinValue" value="{$this->moreThan}" /><input name='calMinValue' type="reset" value=" ... " onclick="return showCalendar('txtMinValue', datetimeFormat, '24', true);" /></td>
				</tr>

				<tr>
					<td>Maximum Value:</td>
					<td><input type="text" id="txtMaxValue" name="txtMaxValue" value="{$this->lessThan}" /><input name='calMaxValue' type="reset" value=" ... " onclick="return showCalendar('txtMaxValue', datetimeFormat, '24', true);" /></td>
				</tr>

				<tr>
					<td>Tooltip Text:</td>
					<td><textarea style="width:98%;" name="txtToolTip" rows="5" cols="50">{$this->toolTipText}</textarea></td>
				</tr>

				<tr>
					<td>Required?</td>
					<td>
						<label><input type="radio" name="optRequired" value="yes" $required />Yes</label>
						<label><input type="radio" name="optRequired" value="no" $requiredN />No</label>
					</td>
				</tr>
			</table>

			<br /><br />
			* You can provide the different choices for checkboxes, radio buttons or select fields by
			typing the choices separated by pipe symbols (|) in the Extra Options box.<br />
			For file upload fields, you can specify the different acceptable file extensions separated by |. Maximum Value represents the maximum allowable file size in bytes.


			<br /><br />
			<input type="hidden" value="$hiddenValue" name="elementid" />
			<input type="submit" value="Update Field" name="btnSubmit" />
		</form>

		<script language="javascript" type="text/javascript">
		<!--
			elementTypeChanged(document.getElementById('$formHtmlId'));
		-->
		</script>

HTMLOUTPUT;


		return $htmlOutput;
	}

	public function toMysqlUpdateQuery($formId) {
		$updates = array();

		foreach(FormElement::$fieldMap as $key => $value) {
			if(isset($this->$value)) {
				$updates[] = "`$key` = '". $this->$value . "'";
			}
		}

		if(count($updates) > 0) {
			return 'UPDATE `form_elementdesc` SET ' . join($updates, ', ') . ' WHERE ' .
			       '`form_elementid` =\'' . $this->elementId . '\' AND `page_modulecomponentid` = \'' . $formId."'";
		}
		return '';
	}

	public function toMysqlInsertQuery($elementId = '', $elementRank = '') {
		if($elementRank != '' && ctype_digit($elementRank)) {
			$this->elementRank = $elementRank;
		}

		$keys = array();
		$values = array();

		foreach(FormElement::$fieldMap as $k => $v) {
			if($k != 'form_elementid') {
				$keys[] = $k;
				$elementValue = $this->$v;
				if($elementValue === true || $elementValue === false) {
					$elementValue = $elementValue == true ? 1 : 0;
				}

				if(!ctype_digit($elementValue)) {
					$values[] = "'$elementValue'";
				}
				else {
					$values[] = $elementValue;
				}
			}
			else if(ctype_digit($elementId)) {
				$keys[] = 'form_elementid';
				$values[] = $elementId;
			}
		}

		if(count($keys) > 0) {
			return 'INSERT INTO `form_elementdesc`(`'.join($keys, '`, `').'`) VALUES ('.join($values, ', ').')';
		}
		return '';
	}
}




/** TO ABHILASH :::: this is too confusing to use....
 *
 * 				instead of this, (or add to this a static function) have the functions
 * 					getFormElementInfo($moduleCompId) which gives the associative array of
 * 						all elementproperties
 * 					getFormElementRow($array) -> which converts the above thing to a row
 * 					and likewise....
 *
 */

