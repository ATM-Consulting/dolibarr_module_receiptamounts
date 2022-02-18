<?php
/* Copyright (C) 2022 SuperAdmin <eurochef.support@adepia.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    receiptamounts/lib/receiptamounts.lib.php
 * \ingroup receiptamounts
 * \brief   Library files with common functions for ReceiptAmounts
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function receiptamountsAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("receiptamounts@receiptamounts");

	$h = 0;
	$head = array();

	/*
	$head[$h][0] = dol_buildpath("/receiptamounts/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/receiptamounts/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/receiptamounts/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@receiptamounts:/receiptamounts/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@receiptamounts:/receiptamounts/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'receiptamounts@receiptamounts');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'receiptamounts@receiptamounts', 'remove');

	return $head;
}

/**
 * calculate total amount of a receipt and update reception extrafield
 * @param Reception $object
 *
 * @return void
 */
function calculateReceiptTotal(Reception $object)
{
	require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';

	if (empty($object->array_options)) $object->fetch_optionals();

        $object->array_options['options_total_ht'] = 0;
	if (!empty($object->lines))
	{
		
		foreach ($object->lines as $line)
		{
			if (empty($line->array_options)) $line->fetch_optionals();

			$addTotal = 0;
			$orderline_total_ht = floatval($line->array_options['options_orderline_total_ht']);
			$orderline_qty = floatval($line->array_options['options_orderline_qty']);

			if (!empty($orderline_total_ht) && !empty($orderline_qty))
			{
				$addTotal = $orderline_total_ht * $line->qty / $orderline_qty;
			}
			$object->array_options['options_total_ht']+= $addTotal;
		}
	}
	$object->insertExtraFields();

}
