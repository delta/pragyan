<?php

function getBillingForm($moduleComponentId) {
	global $urlRequestRoot, $sourceFolder, $moduleFolder;
	$billingForm = <<<BILLINGFORM
		<script language="javascript" type="text/javascript" src="$urlRequestRoot/$sourceFolder/$moduleFolder/billing/billing.js"></script>
		<form name="billingform" method="POST" action="./+view" onsubmit="return validateBillingForm(this)">
			<br />
			<fieldset style="padding:8px; margin: 8px">
				<legend>Cart</legend>
			<table cellpadding="2px" cellspacing="2px" width="100%" class="billingtable" id="itemdetails">
				<tr class="shopname"><th style="text-align: left"></th><th style="text-align: left">Item Name</th><th style="text-align: left">Price (Rs.)</th><th style="text-align: left">Quantity</th><th style="text-align: left">Amount</th></tr>
BILLINGFORM;

	$itemsQuery = 'SELECT `billing_articleid`, `billing_shopname`, `billing_shopcouponcolor`, `billing_articlename`, `billing_price`, `billing_availability` FROM `billing_article` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' ORDER BY `billing_shopname`, `billing_price`';
	$itemsResult = mysql_query($itemsQuery);

	for($i = 1; $i < 11; ++$i)
		$qttySelect .= '<option value="' . $i . '" onchange="evaluateAmount(this)">' . $i . '</option>';

	$prevShopName = '';

	$jsItemsList = array();
	$couponColors = array();
	while($itemsRow = mysql_fetch_assoc($itemsResult)) {
		if($itemsRow['billing_shopname'] != $prevShopName) {
			$billingForm .= '<tr class="shopname"><td colspan="5"><h3>' . $itemsRow['billing_shopname'] . '</h3></td></tr>';
//			$billingForm .= ($prevShopName == '' ? '' : '</table>') . '<hr /><br /><h3>' . $itemsRow['billing_shopname'] . '</h3><br /><table width="100%">';
			$prevShopName = $itemsRow['billing_shopname'];
		}
		$htmlElementId = 'Item' . $itemsRow['billing_articleid'];
		$jsItemsList[$itemsRow['billing_shopname']][] = $htmlElementId;
		$couponColors[$itemsRow['billing_shopname']] = $itemsRow['billing_shopcouponcolor'];
		$rowClassName = ($itemsRow['billing_availability'] == 1 ? 'itemavailable' : 'itemsoldout');
		if($itemsRow['billing_availability'] == 1) {
			$billingForm .= <<<BILLINGFORM
				<tr class="$rowClassName">
					<td><input type="checkbox" name="chk{$htmlElementId}" id="chk{$htmlElementId}" value="{$itemsRow['billing_articleid']}" onclick="evaluateAmount(this)" /></td>
					<td><label for="chk{$htmlElementId}"><span style="display: block">{$itemsRow['billing_articlename']}</span></label></td>
					<td><span id="prc$htmlElementId">{$itemsRow['billing_price']}</span></td>
					<td><select name="sel{$htmlElementId}" id="sel{$htmlElementId}" onchange="evaluateAmount(this)">{$qttySelect}</select></td>
					<td><span id="amt{$htmlElementId}"></span></td>
				</tr>
BILLINGFORM;

		}
	}

	$jsShopsList = array_keys($jsItemsList);
	$jsShopsArray = 'openStalls = new Array(\'' . implode("', '", $jsShopsList) . '\');';
	$jsShopsList = array_flip($jsShopsList);
	$jsItemsArray = 'stallItems = new Array();';
	$jsItemsArray = array();
	foreach($jsItemsList as $jsShopName => $jsItemName) {
		for($i = 0; $i < count($jsItemName); $i++)
			$jsItemsArray[] = "[{$jsShopsList[$jsShopName]}, '{$jsItemName[$i]}']"; //"stallItems.push(new Array({$jsShopsList[$jsShopName]}, {$jsItemName[$i]}));\n";
	}
	$jsItemsArray = "stallItems = new Array(" . implode(', ', $jsItemsArray) . ");\n";

	$tdShopsList = '';
	$tdShopHeaderList = '';
	foreach($couponColors as $shopName => $couponColor) {
		$tdShopsList .= '<td id="td' . $shopName . '" style="background-color: #' . $couponColor . '"></td>';
		$tdShopHeaderList .= '<th id="td' . $shopName . 'Header"></th>'; ///  . $shopName . '</td>';
	}

	$billingForm .= <<<BILLINGFORM
				</table>
				<br />
				<table id="salestotals" cellpadding="4px" cellspacing="4px">
					<tr>
						$tdShopHeaderList
					</tr>
					<tr>
						$tdShopsList
					</tr>
				</table>

				<br />
				<h3>Total: Rs. <span id="spanTotal">0</span></h3>
				<br />
			</fieldset>

			<fieldset style="padding: 8px; margin: 8px">
				<legend>Purchase Information</legend>
				<table>
					<tr>
						<td>Payment Method:</td>
						<td>
							<label><input type="radio" onclick="document.getElementById('txtRollNumber').disabled=false;" name="optPaymentMethod" value="messbill" checked="checked" />Mess Bill</label>
							<label><input type="radio" onclick="document.getElementById('txtRollNumber').disabled=true;" name="optPaymentMethod" value="cash" />Cash</label>
						</td>
					</tr>

					<tr>
						<td>Roll Number:</td>
						<td><input type="text" name="txtRollNumber" id="txtRollNumber" value="" /></td>
					</tr>
				</table>
			</fieldset>

			<input type="submit" name="btnSubmit" value="Submit" />
		</form>

		<br />
		<a href="./+view&subaction=viewaccount">View My Account</a>
BILLINGFORM;

	$billingForm = '<script language="javascript" type="text/javascript">' . $jsShopsArray . "\n" . $jsItemsArray . '</script>' . $billingForm;

	return $billingForm;
}

?>
