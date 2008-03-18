<?php
/*
 * Created on Feb 23, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class billing implements module {
	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		if($this->action=="view")
			return $this->actionView();
		if($this->action=="edititem")
			return $this->actionEdititem();
		if($this->action == 'account')
			return $this->actionAccount();
		if($this->action == 'adminaccount')
			return $this->actionAdminaccount();
	}

	private function subactionSubmitView() {
		global $sourceFolder, $moduleFolder;
		require_once("$sourceFolder/$moduleFolder/billing/billing_sales.php");

		$paymentMethod = 'messbill';
		if(isset($_POST['optPaymentMethod']) && ($_POST['optPaymentMethod'] == 'messbill' || $_POST['optPaymentMethod'] == 'cash')) {			
			if($_POST['optPaymentMethod'] == 'cash') $paymentMethod = 'cash';
		}
		else return;

		$buyer = isset($_POST['txtRollNumber']) ? $_POST['txtRollNumber'] : '';
		if($buyer != '') {
			if(!validateRollNumber($buyer)) {
				displayerror('Invalid roll number entered.');
				return;
			}
		}
		if($paymentMethod == 'messbill' && $buyer == '') {
			displayerror('Error, payment method was specified as messbill, but no roll number was provided.');
			return;
		}

		$articleList = getArticleList($this->moduleComponentId);
		$articleQuantities = array();
		foreach($articleList as $articleId => $articleDetails) {
			if(
				isset($_POST['chkItem' . $articleId]) && 
				isset($_POST['selItem' . $articleId]) &&
				is_numeric($_POST['selItem' . $articleId]) &&
				$_POST['selItem' . $articleId] > 0 &&
				$_POST['selItem' . $articleId] <= 10
			) {
				$articleQuantities[] = array($articleId, $_POST['selItem' . $articleId]);
			}
		}

		saveTransaction($this->moduleComponentId, $this->userId, $buyer, $paymentMethod, $articleQuantities);
	}

	private function getSellerId($moduleComponentId, $transactionId) {
		$sellerQuery = "SELECT `billing_sellerid` FROM `billing_transactions` WHERE `page_modulecomponentid` = $moduleComponentId AND `billing_transactionid` = $transactionId";
		$sellerResult = mysql_query($sellerQuery);
		if(!$sellerResult) {
			displayerror($sellerQuery . '<br />' . mysql_error());
		}
		$sellerRow = mysql_fetch_row($sellerResult);

		return $sellerRow[0];
	}

	public function actionView() {
		if(isset($_GET['subaction']) && $_GET['subaction'] == 'viewaccount') {
			return $this->actionUserAccount();
		}

		global $sourceFolder, $moduleFolder;
		require_once("$sourceFolder/$moduleFolder/billing/billing_view.php");

		if(isset($_POST['btnSubmit'])) {
			$this->subactionSubmitView();
		}

		return getBillingForm($this->moduleComponentId);
	}

	public function actionUserAccount() {
		global $urlRequestRoot, $sourceFolder, $moduleFolder, $templateFolder;
		include_once("$sourceFolder/$moduleFolder/billing/billing_sales.php");

		if(
			isset($_GET['subsubaction']) && 
			($_GET['subsubaction'] == 'invalidatetransaction') &&
			isset($_GET['transactionid']) && is_numeric($_GET['transactionid'])
		) {
			$transactionId = $_GET['transactionid'];

			if($this->getSellerId($this->moduleComponentId, $transactionId) != $this->userId) {
				displayerror('You cannot modify transactions that you did not make.');
			}
			else {
				setTransactionValidity($this->moduleComponentId, $transactionId, false);
			}
		}

		$transactionsList = getTransactionList($this->moduleComponentId, $this->userId);
		$transactionForm = '<table cellpadding="2px" border="1"><tr><th></th><th>Buyer</th><th>Amount Paid</th><th>Payment Method</th><th>Time</th><th>Items Purchased</th></tr>';

		$articlesAvailable = getArticleList($this->moduleComponentId);
		$deleteImage = "<img style=\"padding:2px\" src=\"$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Invalidate Transaction\" />";
		$invalidImage = "<img style=\"padding: 2px\" src=\"$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/actions/mail-mark-junk.png\" alt=\"This transaction has been marked as invalid\" />";

		foreach($transactionsList as $transactionId => $transactionDetails) {
			$transactionForm .= '<tr><td>';
			if ($transactionDetails['transactionstatus'] == 1) {
				$transactionForm .= "<a href=\"./+view&subaction=viewaccount&subsubaction=invalidatetransaction&transactionid=$transactionId\">$deleteImage</a>";
			}
			else {
				$transactionForm .= $invalidImage;
			}
			$transactionForm .= '</td>';
			$purchaseDetails = '';
			foreach($transactionDetails['articlesbought'] as $articleId => $quantity) {
				$purchaseDetails .= $articlesAvailable[$articleId]['shopname'] . '-' . $articlesAvailable[$articleId]['articlename'] . 'x' . $quantity . '<br />';
			}

			$transactionForm .= '<td>' . $transactionDetails['buyer'] . '</td><td>' . $transactionDetails['amountpaid'] . '</td><td>' . $transactionDetails['paymentmethod'] . '</td><td>' . $transactionDetails['transactiontime'] . '</td><td>' . $purchaseDetails . '</td></tr>';
		}

		$transactionForm .= '</table>';
		return '<br />' . $transactionForm . '<br /><a href="./+view">Back to Sales</a><br />';
	}

	public function actionAccount() {
		$adminDisplay = '<table width="50%"><tr><th style="text-align: left">Shop Name</th><th style="text-align: right" width="30%">Amount Collected</th></tr>';
		$days = array('2008-03-02', '2008-03-01', '2008-02-29', '2008-02-28', '2008-02-27', '2008-02-26');

		for($i = 0; $i < count($days); $i++) {
			$adminDisplay .= "<tr><td colspan=\"2\">&nbsp;</td></tr>\n<tr><td colspan=\"2\"><h3>Day: {$days[$i]}</h3></td></tr>\n";
			$amountQuery = 'SELECT `billing_shopname`, SUM(`billing_articlecost` * `billing_articlequantity`) AS `amount` ' . 
					'FROM `billing_transactiondetails`, `billing_transactions`, `billing_article` ' . 
					'WHERE ' . 
					'`billing_transactiondetails`.`page_modulecomponentid` = 1 AND ' .
					'`billing_transactiondetails`.`billing_articleid` = `billing_article`.`billing_articleid` AND ' .
					'`billing_transactiondetails`.`page_modulecomponentid` = `billing_article`.`page_modulecomponentid` = `billing_transactions`.`page_modulecomponentid` AND ' .
					'`billing_transactions`.`billing_transactionid` = `billing_transactiondetails`.`billing_transactionid` AND ' .
					'`billing_transaction_status` = 1 AND ' .
					"DATE(`billing_transactiontime`) = '{$days[$i]}' GROUP BY `billing_shopname`";
			$amountResult = mysql_query($amountQuery);
			if(!$amountResult) {
				displayerror('Database error. Invalid query.');
				return '';
			}

			$j = 1;
			if(mysql_num_rows($amountResult) == 0) {
				$adminDisplay .= "<tr " . ($j % 2 == 1 ? 'style="background-color: #F1F1F1"' : '') . "'><td colspan=\"2\">No transactions made on this day</td>";
				++$j;
				continue;
			}

			while($amountRow = mysql_fetch_assoc($amountResult)) {
				$adminDisplay .= "<tr " . ($j % 2 == 1 ? 'style="background-color: #F1F1F1"' : '') . "'><td>{$amountRow['billing_shopname']}</td><td style=\"text-align: right\">{$amountRow['amount']}</td></tr>\n";
				++$j;
			}
		}
		$adminDisplay .= '</table>';
		return $adminDisplay;
	}

	public function actionEdititem() {
		global $sourceFolder;
		require_once("$sourceFolder/edittable.lib.php");

		$tablename = "forum_threads";
		$page_modulecomponentid = 1;
		$allowDelete = true;
		$allowEdit = true;
		$primaryField = array();
		return editDb($tablename, $page_modulecomponentid, $primaryField,$allowDelete, $allowEdit);
	}

	public function deleteModule($moduleComponentId) {

	}

	public function copyModule($moduleComponentId) {

	}

	public function createModule(&$moduleComponentId) {
		global $sourceFolder, $moduleFolder;
		$query = 'SELECT MAX(`page_modulecomponentid`) FROM `billing_article`';
		$result = mysql_query($query) or die(mysql_error() . 'form.lib L:149');
		$row = mysql_fetch_row($result);
		$moduleComponentId = 1;
		if(!is_null($row[0])) $moduleComponentId = $row[0] + 1;
	}
}

?>