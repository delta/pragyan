<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

class contest implements module, fileuploadable {
	private $moduleComponentId;
	private $userId;
	private $action;

	public function getHtml($userId, $moduleComponentId, $action) {
		$tihs->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		if ($action == 'view') {
			return $this->actionView();
		}
		else if ($action == 'edit') {
			return $this->actionEdit();
		}
	}

	public function actionView() {
		$cid = $this->moduleComponentId;
		$uid = $this->userId;

		/*
		 * View can be for:
		 *	viewing list of problems
		 *	given a problem id, viewing the problem
		 *	given subaction=submit, showing a submit box or submitting solution depending on post data
		 *	given subaction=status, showing submission status
		 *			filters: userid, problem
		 *	given subaction=ranklist, showing the ranklist for the contest.
		 */

		$subaction = '';
		if (isset($_GET['subaction']))
			$subaction = $_GET['subaction'];

		if ($subaction == 'showproblem')
			return $this->getProblemPage($cid);
		else if ($subaction == 'showranklist')
			return $this->getRanklist($cid);
		else if ($subaction == 'showstatus')
			return $this->getStatus($cid);
		else if ($subaction == 'submit')
			return $this->getSubmitPage($cid);

		return $this->getContestPage($cid);
	}

	public function actionEdit() {
	}

	private function getContestPage($contestId) {
		$problemQuery = "SELECT * FROM `contest_problem` WHERE `cid` = '$contestId' AND `testable` = 1 ORDER BY `pid`";
		$problemResult = mysql_query($problemQuery);

		$html = '<table border="0">';

		$className = array('even', 'odd');
		$parity = 0;

		while ($problemRow = mysql_fetch_assoc($problemResult)) {
			$pcode = $problemRow['pcode'];
			$ptitle = $problemRow['pname'];
			$html .= "<tr class=\"{$className[$parity]}\"><td><a href=\"./+view&subaction=showproblem&pcode=$pcode\">{$problemRow['pcode']}</a></td><td><a href=\"./+view&subaction=showproblem&pcode=$pcode\">{$problemRow['ptitle']}</a></td></tr>\n";
			$parity = 1 - $parity;
		}
		$html .= '</table>';

		return $html;
	}

	private function getProblemId($contestId, $pcode) {
		$idQuery = "SELECT `pid` FROM `contest_problem` WHERE `cid` = '$contestId' AND `pcode` = '$pcode'";
		$idResult = mysql_query($idQuery);
		if (!$idResult) {
			displayerror('MySQL error while attempting to fetch problem id, on line ' . __LINE__ . ', ' . __FILE__);
			return -1;
		}
		$idRow = mysql_fetch_row($idResult);
		if ($idRow) return $idRow[0];
		else return -1;
	}

	private function getProblemPage($contestId) {
		$pcode = '';
		if (isset($_GET['pcode']))
			$pcode = $_GET['pcode'];
		else {
			displayerror('Error. Problem code not specified.');
			return '';
		}

		$problemId = $this->getProblemId($contestId, $pcode);
		if ($problemId < 0) {
			displayerror('Error. Invalid problem code specified. Could not find a problem in the current contest with the given problem code.');
			return '';
		}

		global $sourceFolder, $moduleFolder;

		$problemPageHtml = file_get_contents("$sourceFolder/$moduleFolder/contest/problems/$contestId/$pcode.html");
		$problemPageHtml .= $this->getSubmitForm($contestId, $problemId);
		return $problemPageHtml;
	}

	private function getRanklist($contestId) {
	}

	private function getStatus($contestId) {
		
	}

	function getPaginatedContent($selectQuery, $countQuery, $itemsPerPage, &$pageNumber, $sortField, $sortDirection, &$pageCount) {
	        $startItem = 0;
	        if ($itemsPerPage <= 0) $itemsPerPage = 20;

	        $itemCountResult = mysql_query($countQuery);
	        if (!$itemCountResult) return false;
	        $itemCount = mysql_fetch_row($itemCountResult);
	        $itemCount = $itemCount[0];

	        $pageCount = ceil($itemCount / $itemsPerPage);

	        if ($pageNumber <= 0) $pageNumber = 1;
	        else if ($pageNumber > $pageCount) $pageNumber = $pageCount;

        	$startItem = ($pageNumber - 1) * $itemsPerPage;

	        if ($sortField != '' && ($sortDirection == 'ASC' || $sortDirection == 'DESC')) $selectQuery .= " ORDER BY `$sortField` $sortDirection";

	        $selectQuery .= " LIMIT $startItem, $itemsPerPage";
        	$selectResult = mysql_query($selectQuery);
	        if (!$selectResult) {
        	        log_error(__FILE__, __LINE__, 'MySQL Error in query ' . $selectQuery . ': ' . mysql_error());
	                return false;
        	}

	        $results = array();
	        while ($selectRow = mysql_fetch_array($selectResult)) {
        	        $results[] = $selectRow;
	        }
	        mysql_free_result($selectResult);

        	return $results;
	}

	function getSortableTableHeading($headings, $orderBy, $orderDir, $getData) {
	        $html = '<tr>';
	        for ($i = 0; $i < count($headings); ++$i) {
        	        $html .= '<th><a href="./&orderby=' . $headings[$i][0] . '&orderdir=';
	                if ($headings[$i][0] == $orderBy)
        	                $html .= ($orderDir == 'ASC' ? 'desc' : 'asc');
	                else
        	                $html .= 'asc';
                	$html .= $getData . '">' . $headings[$i][1] . '</a></th>';
	        }
	        $html .= "</tr>\n";
        	return $html;
	}

	function getPageNumberList($pageNumber, $pageCount, $getData) {
	        $ret = '<ul class="pagenumbers">';
	        for ($i = 1; $i <= $pageCount; ++$i)
	                $ret .= '<li><a href="./&page=' . $i . $getData . '">' . "$i</a></li>\n";
	        $ret .= "</ul>\n";
	        return $ret;
	}

}
?>
