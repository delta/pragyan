<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * Uploads the file
 * @param $moduleComponentId page_modulecomponentid
 * @param $moduleName The module which is calling this function
 * @param $uploadFormName The name of the variable used in forms to upload the file
 * @param $userId The user uploading the file
 * @return $uploadedFiles An array of the names of the files uploaded. The file name is mysql_escaped and then uploaded
 *
 *
 * TODO : when called by a module check if it exists in enum field in DB if not give error.
 */
function upload($moduleComponentId, $moduleName, $userId, $uploadFormName, $maxFileSizeInBytes=false, $uploadableFileTypesArray = false) {
	if($maxFileSizeInBytes===false) $maxFileSizeInBytes = 2*1024*1024;
	/**
	 * TODO: the following is only until PHP 2.3 comes
	 * In php 2.3 we can call static function directly without making an instance of the class.
	 */
	global $sourceFolder;
	global $uploadFolder;
	$uploadDir = $sourceFolder . "/" . $uploadFolder;

	$defaultUploadableFileTypes = '/\.(css|gif|png|jpe?g|js|html|xml|pdf|doc|ods|odt|oft|pps|ppt|std|stc|sti|stw|svgz?|sxc|sx.|tex|tiff|txt|chm|mp3|mp2|wave?|ogg|mpe?g|wmv|wma|wmf|rm|avi|gzip|gz|rar|bmp|psd|bz2|tar|zip|swf|fla|flv|eps|ico|xcf|m3u|lit|bcf|xls|mov|xlr|exe|7?z)$/i';
	if($uploadableFileTypesArray === false)
		$uploadFileTypesRegexp = $defaultUploadableFileTypes;
	else {
		if(gettype($uploadableFileTypesArray)!="array" || count($uploadableFileTypesArray)==0) {
			displayerror("Error in the uploadable types given.");
			return false;
		}
		$uploadFileTypesRegexp = '/\.('.join($uploadableFileTypesArray,"|").')$/i';
	}

	/// Checking for existing directory named as the module and creating it if doesn't exist
	if (!file_exists($uploadDir)) {
		displaywarning("The folder $uploadDir does not exist. Trying to creating it.");
		mkdir($uploadDir, 0755);
		if (!file_exists($uploadDir)) {
			displayerror("Creation of directory failed");
			return false;
		}
		else
			displayinfo("Created $uploadDir.");
	}
	if (!file_exists($uploadDir . '/' . $moduleName)) {
		displaywarning("The folder ".$uploadDir.'/'.$moduleName." does not exist. Trying to create it");
		mkdir($uploadDir . '/' . $moduleName, 0755);
		if (!file_exists($uploadDir. '/' . $moduleName)) {
			displayerror("Creation of directory failed");
			return false;
		}
		else
			displayinfo("Created ".$uploadDir. '/' . $moduleName);
	}

	$uploadedFiles = array();
	//displayinfo( "$uploadDir/$moduleName is " . (is_writable($uploadDir."/".$moduleName) ? "" : "not ") . " now writable<br>");
	if (isset ($_FILES[$uploadFormName])) {
		if(is_array($_FILES[$uploadFormName]['error'])) {
			foreach ($_FILES[$uploadFormName]['error'] as $key => $error) {
				if ($error == UPLOAD_ERR_OK) {
					$tmp_name = $_FILES[$uploadFormName]['tmp_name'][$key];
					$upload_filename = $_FILES[$uploadFormName]['name'][$key];
					$upload_filetype = $_FILES[$uploadFormName]['type'][$key];

					if(preg_match($uploadFileTypesRegexp , $upload_filename , $matches) == 0) {
						displayerror("Error while uploading file $upload_filename. Upload of files of this type not allowed.");
						continue;
					}
					if($_FILES[$uploadFormName]['size'][$key]>$maxFileSizeInBytes) {
						displayerror("Error while uploading file $upload_filename. Max file size of $maxFileSizeInBytes bytes exceeded.");
						continue;
					}

					$uploadedFilename = saveUploadedFile(
						$moduleComponentId, $moduleName, $userId, $upload_filename, $tmp_name,
						$upload_filetype, $uploadDir
					);

					if($uploadedFilename) {
						$uploadedFiles[] = $uploadedFilename;
					}
				}
				else {
					if($error == UPLOAD_ERR_NO_FILE) continue;
					displayerror("Unable to upload file. ".getFileUploadError($error));
				}
			}
		}
		else {
			$uploadTrue = true;
			$upload_filename = $_FILES[$uploadFormName]['name'];
			if(preg_match($uploadFileTypesRegexp , $upload_filename , $matches) == 0) {
				displayerror("Error while uploading file $upload_filename. Upload of files of this type not allowed.");
				$uploadTrue = false;
			}
			if($uploadTrue && $_FILES[$uploadFormName]['size']>$maxFileSizeInBytes) {
				displayerror("Error while uploading file $upload_filename. Max file size of $maxFileSizeInBytes bytes exceeded.");
				$uploadTrue = false;
			}
			if($uploadTrue) {
				$uploadedFilename = saveUploadedFile(
						$moduleComponentId,$moduleName, $userId, $_FILES[$uploadFormName]['name'],
						$_FILES[$uploadFormName]['tmp_name'], $_FILES[$uploadFormName]['type'], $uploadDir
					);
			}
			if($uploadedFilename) {
				$uploadedFiles[] = $uploadedFilename;
			}
		}
	}
	else {
		echo "Sorry, there was a problem uploading your file. UPLOAD L:63 $uploadFormName";
	}

	return $uploadedFiles;
}

function saveUploadedFile($moduleComponentId,$moduleName, $userId, $uploadFileName, $tempFileName, $uploadFileType, $uploadDir) {
	$query = 'SELECT MAX(`upload_fileid`) FROM `' . MYSQL_DATABASE_PREFIX . 'uploads`';
	$result = mysql_query($query) or die(mysql_error() . 'upload.lib L:43');
	$row = mysql_fetch_row($result);
	$upload_fileid = 1;
	if(!is_null($row[0])) {
		$upload_fileid = $row[0] + 1;
	}

	$finalName = str_pad($upload_fileid, 10, '0', STR_PAD_LEFT) . '_' . $uploadFileName;
	move_uploaded_file($tempFileName, "$uploadDir/$moduleName/$finalName");

	if(strpbrk($uploadFileName, '"')) {
		displayerror("Double quotes (\") are not allowed in the file name.");
		return false;
	}
	else {
		$duplicateCheckQuery = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` " .
				"WHERE `page_modulecomponentid` = $moduleComponentId AND `page_module` = '$moduleName'" .
				" AND upload_filename = '$uploadFileName'";
		$duplicateCheckResult = mysql_query($duplicateCheckQuery);
		if(mysql_num_rows($duplicateCheckResult) >= 1) {
			displayerror("A file with the name $uploadFileName already exists. Please use a different filename.");
			return false;
		}
		$query = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'uploads` ' .
				'(`page_modulecomponentid`, `page_module`, `upload_fileid`, `upload_filename`, `upload_filetype`, `user_id`) ' .
				"VALUES ($moduleComponentId, '$moduleName', $upload_fileid, " .
				"'" . mysql_escape_string($uploadFileName) . "', '$uploadFileType', $userId)";
		mysql_query($query) or die(mysql_error() . "upload.lib L:36<br />");
		return $uploadFileName;
	}
}


/**
 * Return the files uploaded for this module with this module component id.
 * Return an array of file names
 */
function getUploadedFiles($moduleComponentId, $moduleName) {
	$query = "SELECT `upload_filename`, `upload_filetype`, `upload_time`, `user_id` FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE `page_modulecomponentid` =" . $moduleComponentId . " AND `page_module` = '" . $moduleName . "'";
	$result = mysql_query($query);
	$fileArray = array ();
	while ($row = mysql_fetch_assoc($result))
		$fileArray[] = $row;

	return $fileArray;
}


/**
 * @return $copied true if copied, false if not copied successfully
 */
function fileCopy($sourcePage_modulecomponentid,$sourcePage_module,$sourceFile_name,
					$destinationPage_modulecomponentid,$destinationPage_module,$destinationFile_name,$user_id) {

	global $sourceFolder, $uploadFolder;
	$uploadDir = $sourceFolder . "/" . $uploadFolder;

	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE `page_modulecomponentid` =" . $sourcePage_modulecomponentid . " AND `upload_filename` =" . mysql_escape_string($sourceFile_name) . "'";
	$result = mysql_query($query);
	$array1 = mysql_fetch_assoc($result);
	$tmp_name = $uploadDir . "/" . $sourcePage_module . "/" . str_repeat("0", (10 - strlen((string) $array1['upload_fileid']))) . $array1['upload_fileid'] . "_" . $sourceFile_name;
	$query1= "SELECT MAX(upload_fileid) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "uploads` ";
	$result1 = mysql_query($query) or die(mysql_error() . "upload.lib");
	$row1 = mysql_fetch_assoc($result);
	$upload_fileid = $row1['MAX'] + 1;
	$finalname = str_repeat("0", (10 - strlen((string) $upload_fileid))) . $upload_fileid . "_" . $destinationFile_name;
	if(!copy($tmp_name, $uploadDir . "/" . $destinationPage_module . "/" . $finalname))
	   return false;
	$query2 = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "uploads` (`page_modulecomponentid` ,`page_module` , `upload_fileid` , `upload_filename` ," .
							" `upload_filetype` , `user_id`) VALUES ('$destinationPage_modulecomponentid', '$destinationPage_module','$upload_fileid'," .
							" '" . mysql_escape_string($destinationFile_name) . "', '{$array1['upload_filetype']}','$user_id')";
	$result2 = mysql_query($query2);


}

function fileMove($sourcePage_modulecomponentid,$sourcePage_module,$sourceFile_name,
					$destinationPage_modulecomponentid,$destinationPage_module,$destinationFile_name,$user_id) {
	global $sourceFolder, $uploadFolder;
	$uploadDir = "$sourceFolder/$uploadFolder";

	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE `page_modulecomponentid` = $sourcePage_modulecomponentid AND `upload_filename` ='" . mysql_escape_string($sourceFile_name) . "'";
	$result = mysql_query($query);
	$array1 = mysql_fetch_assoc($result);
	$oldname = "$uploadDir/$sourcePage_module/" . str_repeat('0', (10 - strlen((string) $array1['upload_fileid']))) . $array1['upload_fileid'] . "_$sourceFile_name";
	$finalname = "$uploadDir/$destinationPage_module/" . str_repeat('0', (10 - strlen((string) $array1['upload_fileid']))) . $array1['upload_fileid'] . "_$destinationFile_name";
	rename($oldname,$finalname);
	$query2 = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "uploads` (`page_modulecomponentid`, `page_module`, `upload_fileid`, `upload_filename`, " .
							"`upload_filetype`, `user_id`) VALUES ('$destinationPage_modulecomponentid', '$destinationPage_module', '$upload_fileid', " .
							" '" . mysql_escape_string($destinationFile_name) . "', '{$array1['upload_filetype']}','$user_id')";
	$result2 = mysql_query($query2);
}


/**
 * Return file name, given a file id ---- will never get used --- a module is supposed to have no knowledge of the file id.
 * also check if the particular file id exists for that particular module and component id
 */
function getFileName($moduleComponentId, $page_module, $upload_fileid) {
	$query = " SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE `page_modulecomponentid` =$moduleComponentId AND `page_module` =$page_module AND `upload_fileid` =$upload_fileid";
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
		$name = mysql_fetch_assoc($result);
		return $name['upload_filename'];
	} else
		return false;
}

/**
 * Deletes the file
 */
function deleteFile( $moduleComponentId, $page_module, $upload_filename) {
	global $uploadFolder;
	global $sourceFolder;
	$upload_filename = stripslashes($upload_filename);
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads`WHERE `page_modulecomponentid` =$moduleComponentId AND `page_module` = '$page_module' AND `upload_filename`= '" . mysql_escape_string($upload_filename) . "'";

	$result = mysql_query($query) or die(mysql_error() . "upload L:85");
	$row = mysql_fetch_assoc($result);
	$upload_fileid = $row['upload_fileid'];

	$filename = str_repeat("0", (10 - strlen((string) $upload_fileid))) . $upload_fileid . "_" . $upload_filename;
	if (@ unlink($sourceFolder . "/" . $uploadFolder . "/" . $page_module . "/" . $filename)) {
	} else
		displayerror("File data has  NOT been deleted from the SERVER");
	$query = "DELETE FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE `upload_fileid`=$upload_fileid";
	mysql_query($query);
	if (mysql_affected_rows() > 0) {
	//	displayinfo("File data has been deleted from the database");
		return true;
	} else {
		displayerror("File data has  NOT been deleted from the database");
		return false;
	}

}

function getUploadedFilePreviewDeleteForm($moduleComponentId, $moduleName, $deleteFormAction = './+edit') {
	global $uploadedFormNumber;
	if(!isset($uploadedFormNumber)) $uploadedFormNumber=1;
	$uploadedFormNumber += 1;

	if(isset($_POST['file_deleted']) && ($_POST['file_deleted'] == "form_$uploadedFormNumber")) {
		if(isset($_GET['deletefile'])) {
			if(deleteFile($moduleComponentId,$moduleName,escape($_GET['deletefile'])))
				displayinfo("The file ".escape($_GET['deletefile'])." has been removed");
			else
				displayinfo("Unable to remove the file.");
		}
	}

	$uploadedFiles = getUploadedFiles($moduleComponentId, $moduleName);
	$uploadedFilesString = "";
	foreach($uploadedFiles as $file) {
		$uploadedUserEmail = getUserEmail($file['user_id']);
		$fileDelete=addslashes($file['upload_filename']);

		$uploadedFilesString .= <<<UPLOADEDFILESSTRING
		<tr>
			<td><a href="./{$file['upload_filename']}"   onMouseOver="javascript:showPath('$fileDelete')"  target="previewIframe_$uploadedFormNumber">{$file['upload_filename']}</a></td>
			<td>$uploadedUserEmail</td>
			<td>{$file['upload_time']}</td>
			<td><input type='submit' value='Delete'  onclick="return checkDeleteUpload(this, '$fileDelete');"></td>
		</tr>
UPLOADEDFILESSTRING;
	}
	global $urlRequestRoot;
	global $cmsFolder;
	if(count($uploadedFiles)>0) {
		$uploadedFilesString =<<<UPLOADEDFILESSTRING
	<form action="$deleteFormAction" method="POST" name="deleteFile">
		<script language="javascript">
	    	function showPath(fileName) {
	    		path = document.location.pathname;
				path = path.split('+');
				path = path[0].split('&');
				document.getElementById("preview_uploadedfile_$uploadedFormNumber").setAttribute('value',path[0]+fileName);
			}
			function checkDeleteUpload(butt,fileDel) {
				if(confirm('Are you sure you want to delete '+fileDel+'?')) {
					butt.form.action+='&deletefile='+fileDel;
					butt.form.submit();
				}
				else
					return false;
			}
			
	    </script>
	    	<style type="text/css" title="currentStyle">
			@import "$urlRequestRoot/$cmsFolder/modules/datatables/css/demo_page.css";
			@import "$urlRequestRoot/$cmsFolder/modules/datatables/css/demo_table_jui.css";
			@import "$urlRequestRoot/$cmsFolder/modules/datatables/themes/smoothness/jquery-ui-1.7.2.custom.css";
		</style>
		<script type="text/javascript" language="javascript" src="$urlRequestRoot/$cmsFolder/modules/datatables/js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="$urlRequestRoot/$cmsFolder/modules/datatables/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				oTable = $('#filestable').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers"
				});
			} );
		</script>
		<table border="1" width="100%">
				<tr>
					

					<td  height="100" width="100%" style="overflow:scroll">
					<center>Preview (only for images)</center>
					<iframe name="previewIframe_$uploadedFormNumber" width="100%" style="min-height:200px" ></iframe>
					</td>
					
				</tr>
			<tr>
				<td>
					<b>Click</b> for preview
				</td>
				
			</tr>
			<tr>
				<td>
					<table class="display" id="filestable" border="1" width="100%">
						<thead>
						<tr>
							<th>File</th>
							<th>Uploaded By</th>
							<th>Upload Time</th>
							<th>Delete</th>
						</tr>
						</thead>
						<tbody>
						$uploadedFilesString
						</tbody>
					</table>

				</td>

				
			</tr>
			<tr>
				<td align="right">Path for file (move mouse over name):
				
					<input type="text" style="width:97%" readonly="readonly" id="preview_uploadedfile_$uploadedFormNumber" value="Copy the path from here" />
				</td>
			</tr>
			</table>
				<input type="hidden" name="file_deleted" value="form_$uploadedFormNumber">
			</form>
UPLOADEDFILESSTRING;
	}
	else
		$uploadedFilesString = "No files associated with this page.";
	return $uploadedFilesString;
}

/**
 * @return mixed :  false if failed, true if no file found/ nothing to upload, otherwise array of filenames uploaded
 */
	function submitFileUploadForm($moduleComponentId, $moduleName, $userId, $maxFileSizeInBytes = false, $uploadableFileTypesArray = false, $uploadFieldName = 'fileUploadField') {
		if($maxFileSizeInBytes===false) $maxFileSizeInBytes = 2*1024*1024;
		if(isset($_FILES[$uploadFieldName]['error'][0])) {
			$errorCode = $_FILES[$uploadFieldName]['error'][0];
			if($errorCode == UPLOAD_ERR_NO_FILE) return true;
			if($errorCode != 0) {
				displayerror("Error in uploading file. ".getFileUploadError($errorCode));
				return true;
			}
			$uploadedFiles = upload($moduleComponentId, $moduleName, $userId, $uploadFieldName, $maxFileSizeInBytes, $uploadableFileTypesArray);
			if(is_array($uploadedFiles) && count($uploadedFiles) > 0)
				displayinfo ( "Successfully uploaded file(s) ".join($uploadedFiles,"; ").".");
			return $uploadedFiles ;
		}
		else
			return true;
	}

	function getFileUploadForm($moduleComponentId, $moduleName, $uploadFormAction = './+edit', $maxFileSizeInBytes = false, $uploadFieldCount = 5, $uploadFieldName = 'fileUploadField' ) {
		$uploadFormString = <<<UPLOAD
		<form action="$uploadFormAction" method="post" enctype="multipart/form-data">
			<style type="text/css">
				.upload { display : none; }
				.show { display : block; }
			</style>
			<script  language="javascript" type="text/javascript">
				function toggleuploadfiles(gett) {
					if(gett.nextSibling.nextSibling.className != "show")
					{
						gett.nextSibling.nextSibling.className = "show";
						gett = gett.nextSibling.nextSibling;
					}
					else
					{
						gett.nextSibling.nextSibling.className = "upload";
						gett = gett.nextSibling.nextSibling;
					}
				}
			</script>
UPLOAD;
			$uploadFormString .= getFileUploadField($uploadFieldName,$moduleName,$maxFileSizeInBytes);
			if($uploadFieldCount >= 2) {
				$uploadFormString .= '<input type="button" value="Upload more files" onclick="javascript:toggleuploadfiles(this);" />
		      	<span class="upload">';
				for($i=2;$i<=$uploadFieldCount;$i++) {
		      		$uploadFormString .= "<input name=\"".$uploadFieldName."[]\" type=\"file\" />";
		      		if($i!=$uploadFieldCount) $uploadFormString .= '<br />';
		      	}
	      		$uploadFormString .= '</span>';
			}
	      	$uploadFormString .= '<input value="Upload" type="submit" />
	   		</form>';
		return $uploadFormString;
	}

/**
 * Gets a only the text box for upload
 *
 * @param string $validCheck used by form for field required javascript
 */
	function getFileUploadField($uploadFieldName,$moduleName, $maxFileSizeInBytes = false, $validCheck = "") {
		if($maxFileSizeInBytes===false) $maxFileSizeInBytes = 2*1024*1024;
		$uploadFormString ='<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxFileSizeInBytes.'" />' .
			'<input name="'.$uploadFieldName.'[]" type="file" '.$validCheck.'  />
	      	<input type="hidden" name="FileUploadForm" value="'.$uploadFieldName.'" />';
	    return $uploadFormString;

	}

	function getFileUploadError($i) {
		$errorcodes = array(UPLOAD_ERR_OK => "There is no error, the file uploaded with success.",
							 UPLOAD_ERR_INI_SIZE=> "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
							 UPLOAD_ERR_FORM_SIZE=> "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
							 UPLOAD_ERR_PARTIAL=>"The uploaded file was only partially uploaded.",
							 UPLOAD_ERR_NO_FILE=> "No file was uploaded.",
							 UPLOAD_ERR_NO_TMP_DIR=>"Missing a temporary folder.",
							 UPLOAD_ERR_CANT_WRITE=>"Failed to write file to disk.",
							 UPLOAD_ERR_EXTENSION=>"File upload stopped by extension.");
		//if($i!="" && $i != "")
		return($errorcodes[$i]);
	}

