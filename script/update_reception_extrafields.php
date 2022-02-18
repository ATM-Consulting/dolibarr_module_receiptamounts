<?php

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

dol_include_once('receiptamounts/lib/receiptamounts.lib.php');

$sql = "SELECT r.rowid FROM ".MAIN_DB_PREFIX."reception r";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."reception_extrafields re on re.fk_object = r.rowid";
$sql.= " WHERE re.total_ht is null";
$sql.= " OR re.total_ht = 0";

$resql = $db->query($sql);
if ($resql)
{
	require_once DOL_DOCUMENT_ROOT.'/reception/class/reception.class.php';
	require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';

	while ($obj = $db->fetch_object($resql))
	{
		$receipt = new Reception($db);
		$resfetch = $receipt->fetch($obj->rowid);

		if ($resfetch > 0 && !empty($receipt->lines))
		{
			foreach ($receipt->lines as $line)
			{
				if (empty($line->array_options)) $line->fetch_optionals();

				$supplierorderline = new CommandeFournisseurLigne($db);
				$ret = $supplierorderline->fetch($line->fk_commandefourndet);

				if ($ret > 0)
				{
					$line->array_options['options_orderline_total_ht'] = $supplierorderline->total_ht;
					$line->array_options['options_orderline_qty'] = $supplierorderline->qty;
					$line->insertExtraFields();
				}
			}
		}
		calculateReceiptTotal($receipt);
	}
}
