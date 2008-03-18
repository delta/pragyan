<?php
/*
 * Created on Feb 26, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

define('MESSBILL_CAP', 500);

/**
 * Retrieves the list of articles available to be purchased
 * @param $moduleComponentId Module Component Id of the billing module
 * @return Associative Array; Array[articleId] => Array(shopname, articlename, articleprice) containing the available articles, 
 */
function getArticleList($moduleComponentId) {
	$articleQuery = 'SELECT `billing_articleid`, `billing_shopname`, `billing_articlename`, `billing_price` FROM `billing_article` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' ORDER BY `billing_shopname`';
	$articleResult = mysql_query($articleQuery);

	$articleList = array();
	while($articleRow = mysql_fetch_assoc($articleResult)) {
		$articleList[$articleRow['billing_articleid']] = 
				array(
					'shopname' => $articleRow['billing_shopname'],
					'articlename' => $articleRow['billing_articlename'],
					'articleprice' => $articleRow['billing_price']
				);
	}

	return $articleList;
}

function getFlippedArticleList($moduleComponentId) {
	$articleQuery = 'SELECT `billing_articleid`, `billing_shopname`, `billing_articlename` FROM `billing_article` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$articleResult = mysql_query($articleQuery);

	$articleList = array();
	while($articleRow = mysql_fetch_assoc($articleResult)) {
		if(!isset($articleList[$articleRow['billing_shopname']])) {
			$articleList[$articleRow['billing_shopname']] = array();
		}
		$articleList[$articleRow['billing_shopname']][$articleRow['billing_articlename']] = $articleRow['billing_articleid'];
	}

	return $articleList;		
}

function getArticlesBoughtInTransaction($moduleComponentId, $transactionId) {
	$articleQuery = 'SELECT `billing_transactiondetails`.`billing_articleid`, `billing_articlequantity` FROM `billing_transactiondetails`, `billing_article` ' .
			'WHERE `billing_transactiondetails`.`page_modulecomponentid` = ' . $moduleComponentId . ' AND `billing_transactionid` = ' . $transactionId . 
			' AND `billing_transactiondetails`.`billing_articleid` = `billing_article`.`billing_articleid` ORDER BY `billing_shopname`';
	$articleResult = mysql_query($articleQuery);
	if(!$articleResult) {
		displayerror($articleQuery . '<br />' . mysql_error());
		return '';
	}

	$articleList = array();
	while($articleRow = mysql_fetch_assoc($articleResult)) {
		$articleList[$articleRow['billing_articleid']] = $articleRow['billing_articlequantity'];
	}

	return $articleList;
}

function getTransactionList($moduleComponentId, $userId) {
	$transactionQuery = 'SELECT `billing_transactionid`, `billing_sellerid`, `billing_buyer`, `billing_amountpaid`, `billing_paymentmethod`, `billing_transactiontime`, `billing_transaction_status` ' .
			'FROM `billing_transactions` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `billing_sellerid` = ' . $userId . ' ORDER BY `billing_transactiontime` DESC';
	$transactionResult = mysql_query($transactionQuery);
	if(!$transactionResult) {
		displayerror('Coult not query database.');
		return false;
	}

	$transactionsList = array();
	while($transactionRow = mysql_fetch_assoc($transactionResult)) {
		$articlesBought = getArticlesBoughtInTransaction($moduleComponentId, $transactionRow['billing_transactionid']);
 
			$transactionsList[$transactionRow['billing_transactionid']] = 
				array(
					'buyer' => $transactionRow['billing_buyer'],
					'amountpaid' => $transactionRow['billing_amountpaid'],
					'paymentmethod' => $transactionRow['billing_paymentmethod'],
					'transactiontime' => $transactionRow['billing_transactiontime'],
					'transactionstatus' => $transactionRow['billing_transaction_status'],
					'articlesbought' => $articlesBought
				);
	}

	return $transactionsList;
}

function rollbackTransaction($moduleComponentId, $transactionId) {
	$deleteQuery = 'DELETE FROM `billing_transactions` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `billing_transactionid` = ' . $transactionId;
	$result1 = mysql_query($deleteQuery);

	$deleteQuery = 'DELETE FROM `billing_transactiondetails` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `billing_transactionid` = ' . $transactionId;
	$result2 = mysql_query($deleteQuery);

	return $result1 && $result2;
}

/**
 * Checks if the buyer is exceeding the mess bill cap
 */
function isBuyerExceedingCap($moduleComponentId, $buyer, $amountToBePaid) {
	$messbillCap = MESSBILL_CAP;
	if($moduleComponentId == 2) $messbillCap = 200;

	$amountQuery = "SELECT SUM(`billing_amountpaid`) FROM `billing_transactions` WHERE `page_modulecomponentid` = $moduleComponentId AND `billing_buyer` = '$buyer' AND `billing_transaction_status` = 1";
	$amountResult = mysql_query($amountQuery);

	if(!$amountResult) {
		displayerror('Could not validate the mess bill cap.');
		return true;
	}

	$amountRow = mysql_fetch_row($amountResult);
	if(!is_null($amountRow[0])) {
		if($amountRow[0] + $amountToBePaid > $messbillCap) {
			displayerror("Roll number $buyer will be exceeding his messbill cap of " . $messbillCap . " by making this transaction.");
			return true;
		}
		return false;
	}

	/// there was no sum => the user hasn't made any transactions as of now
	if($amountToBePaid > $messbillCap) {
		displayerror("Roll number $buyer will be exceeding his messbill cap of " . $messbillCap . " by making this transaction.");
		return true;
	}
	return false;
}


/**
 * @param $moduleComponentId
 * @param $sellerId User id of the person performing the transaction
 * @param $buyer Roll number of the person buying the articles
 * @param $paymentMethod String indicating the method of payment, must be either 'messbill' or 'cash'
 * @param $articleQuantities Array [i] => Array(articleid, quantity)
 * @return Boolean True indicating success, and false indicating failure
 */
function saveTransaction($moduleComponentId, $sellerId, $buyer, $paymentMethod, array $articleQuantities) {
	$idQuery = 'SELECT MAX(`billing_transactionid`) FROM `billing_transactions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$idResult = mysql_query($idQuery);
	$idRow = mysql_fetch_row($idResult);
	$transactionId = 1;
	if(!is_null($idRow[0])) {
		$transactionId = $idRow[0] + 1;
	}

	$articleList = getArticleList($moduleComponentId);
	$articleCount = count($articleQuantities);
	$amountPaid = 0.0;

	$transactionDetails = array();
	for($i = 0; $i < $articleCount; $i++) {
		$articlePrice = $articleList[$articleQuantities[$i][0]]['articleprice'];
		$amountPaid += $articleQuantities[$i][1] * $articlePrice;
		$transactionDetails[] = "($moduleComponentId, $transactionId, {$articleQuantities[$i][0]}, {$articleQuantities[$i][1]}, $articlePrice)";
	}

	if($paymentMethod == 'messbill') {
		if(isBuyerExceedingCap($moduleComponentId, $buyer, $amountPaid)) {
			return false;
		}
	}

	$transactionQuery = 'INSERT INTO `billing_transactions` ' .
			'(`page_modulecomponentid`, `billing_transactionid`, `billing_sellerid`, `billing_buyer`, `billing_amountpaid`, `billing_paymentmethod`) ' .
			"VALUES ($moduleComponentId, $transactionId, $sellerId, '$buyer', $amountPaid, '$paymentMethod')";
	$transactionResult = mysql_query($transactionQuery);

	if(!$transactionResult) {
		displayerror('Error: database error while trying to carry out the transaction.');
		return false;
	}

	$detailsQuery = 'INSERT INTO `billing_transactiondetails`(`page_modulecomponentid`, `billing_transactionid`, `billing_articleid`, `billing_articlequantity`, `billing_articlecost`) ' .
			'VALUES ' . implode(', ', $transactionDetails);
	$detailsResult = mysql_query($detailsQuery);

	if(!$detailsResult) {
		if(rollbackTransaction($moduleComponentId, $transactionId))
			displayerror('Could not save transaction details. The transaction has been rolled back.');
		else
			displayerror('Could not save transaction details. The transaction could not be rolled back.');
		displayerror($detailsQuery . '<br />' . mysql_error());
		return false;
	}

	displayinfo('Transaction successfully saved.');
	return true;
}

/**
 * Marks a transaction as valid or invalid.
 * @param $moduleComponentId
 * @param $transactionId
 * @param $valid Boolean, true indicating that the transaction is to be marked valid, and false, invalid  
 */
function setTransactionValidity($moduleComponentId, $transactionId, $valid) {
	$updateQuery = 'UPDATE `billing_transactions` SET `billing_transaction_status` = ' . ($valid == true ? 1 : 0) . ' WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `billing_transactionid` = ' . $transactionId;
	$updateResult = mysql_query($updateQuery);

	if(!$updateResult) {
		displayerror('Could not mark transaction as ' . ($valid == true ? 'valid' : 'invalid') . '.');
		return false;
	}
	return true;
}


function validateRollNumber($rollNumber) {
  $validateQuery = 'SELECT `name` FROM `pragyanV2_validrollnumbers` WHERE LCASE(`rollnumber`) = "' . strtolower($rollNumber).'"';
	$validateResult = mysql_query($validateQuery);

	if(mysql_num_rows($validateResult) == 1)
		return true;
	return false;
}

?>
