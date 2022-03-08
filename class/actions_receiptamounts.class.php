<?php
/* Copyright (C) 2022 Alice Adminson <aadminson@example.com>
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
 * \file    receiptamounts/class/actions_receiptamounts.class.php
 * \ingroup receiptamounts
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsReceiptAmounts
 */
class ActionsReceiptAmounts
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		$TContext = explode(':',$parameters['context']);
		if (in_array('receptionlist', $TContext))
		{
			global $arrayfields;

			$arrayfields['elcf.fk_source'] = array('label'=>$langs->trans("SupplierOrder"), 'checked'=>1, 'position'=>10);

			$arrayfields = dol_sort_array($arrayfields, 'position');

			if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha'))
			{
				$_POST['search_commande'] = 0;
			}
		}

		if (!$error) {
//			$this->results = array('myreturn' => 999);
//			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
//			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Execute action printFieldListOption
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListOption($parameters, &$object, &$action, $hookmanager)
	{
		$arrayfields = $parameters['arrayfields'];

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext))
		{
			global $form;

			$search_commande = GETPOST('search_commande', 'int');

			if (!empty($arrayfields['elcf.fk_source']['checked'])) {
				$sql = "SELECT DISTINCT cf.rowid, cf.ref FROM ".MAIN_DB_PREFIX."commande_fournisseur cf";
				$resql = $this->db->query($sql);
				$this->resprints = '<td class="liste_titre center">';
				if ($resql)
				{
					$TCf = array();
					while ($obj = $this->db->fetch_object($resql))
					{
						$TCf[$obj->rowid] = $obj->ref;
					}
					$this->resprints.= $form->selectarray('search_commande', $TCf, $search_commande, 1);
				}
				$this->resprints.= '</td>';
			}
		}

		return 0;
	}

	/**
	 * Execute action printFieldListTitle
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListTitle($parameters, &$object, &$action, $hookmanager)
	{
		foreach ($parameters as $key => $value) $$key = $value;

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext))
		{
			if (!empty($arrayfields['elcf.fk_source']['checked'])) {
				$this->resprints = getTitleFieldOfList($arrayfields['elcf.fk_source']['label'], 0, $_SERVER["PHP_SELF"], "cf.ref", "", $param, "", $sortfield, $sortorder, 'center nowrap ');
			}
		}

		return 0;
	}

	/**
	 * Execute action printFieldListWhere
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
	{
		foreach ($parameters as $key => $value) $$key = $value;

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext))
		{
			$search_commande = GETPOST('search_commande', 'int');

			if ($search_commande > 0) $this->resprints = ' AND elcf.fk_source = '.$search_commande;
		}

		return 0;
	}

	/**
	 * Execute action printFieldListValue
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListValue($parameters, &$object, &$action, $hookmanager)
	{
		foreach ($parameters as $key => $value) $$key = $value;

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext))
		{
			if (!empty($arrayfields['elcf.fk_source']['checked'])) {
				require_once DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.commande.class.php";
				$this->resprints = '<td class="center nowrap">';

				$cf = new CommandeFournisseur($this->db);
				$res = $cf->fetch($obj->cf_rowid);
				if ($res > 0)
				{
					$this->resprints.= $cf->getNomUrl();
				}
				$this->resprints.= '</td>';
				if (!$i) {
					$parameters['totalarray']['nbfield']++;
				}
			}
		}

		return 0;
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
//		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
//			foreach ($parameters['toselect'] as $objectid) {
//				// Do action on each object id
//			}
//		}

		if (!$error) {
//			$this->results = array('myreturn' => 999);
//			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
//			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext)) // je hack les params ici parce que j'ai pas d'autre choix dans la list de réceptions
		{
			$search_commande = GETPOST('search_commande', 'int');

			if ($search_commande > 0)
			{
				global $param;
				$param .= "&search_commande=".urlencode($search_commande);
			}

		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
//		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
//		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
//		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
//		dol_syslog(get_class($this).'::executeHooks action='.$action);
//
//		/* print_r($parameters); print_r($object); echo "action: " . $action; */
//		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
//			// do something only for the context 'somecontext1' or 'somecontext2'
//		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

//		$langs->load("receiptamounts@receiptamounts");
//
//		$this->results = array();
//
//		$head = array();
//		$h = 0;
//
//		if ($parameters['tabfamily'] == 'receiptamounts') {
//			$head[$h][0] = dol_buildpath('/module/index.php', 1);
//			$head[$h][1] = $langs->trans("Home");
//			$head[$h][2] = 'home';
//			$h++;
//
//			$this->results['title'] = $langs->trans("ReceiptAmounts");
//			$this->results['picto'] = 'receiptamounts@receiptamounts';
//		}
//
//		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
//		$head[$h][1] = $langs->trans("CustomReports");
//		$head[$h][2] = 'customreports';
//
//		$this->results['head'] = $head;
//
//		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

//		if ($parameters['features'] == 'myobject') {
//			if ($user->rights->receiptamounts->myobject->read) {
//				$this->results['result'] = 1;
//				return 1;
//			} else {
//				$this->results['result'] = 0;
//				return 1;
//			}
//		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

//		if (!isset($parameters['object']->element)) {
//			return 0;
//		}
//		if ($parameters['mode'] == 'remove') {
//			// utilisé si on veut faire disparaitre des onglets.
//			return 0;
//		} elseif ($parameters['mode'] == 'add') {
//			$langs->load('receiptamounts@receiptamounts');
//			// utilisé si on veut ajouter des onglets.
//			$counter = count($parameters['head']);
//			$element = $parameters['object']->element;
//			$id = $parameters['object']->id;
//			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
//			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
//			if (in_array($element, ['context1', 'context2'])) {
//				$datacount = 0;
//
//				$parameters['head'][$counter][0] = dol_buildpath('/receiptamounts/receiptamounts_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
//				$parameters['head'][$counter][1] = $langs->trans('ReceiptAmountsTab');
//				if ($datacount > 0) {
//					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
//				}
//				$parameters['head'][$counter][2] = 'receiptamountsemails';
//				$counter++;
//			}
//			if ($counter > 0 && (int) DOL_VERSION < 14) {
//				$this->results = $parameters['head'];
//				// return 1 to replace standard code
//				return 1;
//			} else {
//				// en V14 et + $parameters['head'] est modifiable par référence
//				return 0;
//			}
//		}
	}

	/**
	 * Execute action printFieldListSelect
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListSelect(&$parameters, &$object, &$action, $hookmanager)
	{
		$TContext = explode(':',$parameters['context']);
		if (in_array('receptionlist', $TContext))
		{
			$this->resprints = ", elcf.fk_source as cf_rowid, cf.ref as refcf";
		}

		return 0;
	}

	/**
	 * Execute action printFieldListFrom
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	public function printFieldListFrom(&$parameters, &$object, &$action, $hookmanager)
	{
		$TContext = explode(':',$parameters['context']);
		if (in_array('receptionlist', $TContext))
		{
			$this->resprints = " LEFT JOIN ".MAIN_DB_PREFIX."element_element as elcf ON elcf.fk_target = e.rowid AND elcf.targettype = 'reception' and elcf.sourcetype = 'order_supplier'";
			$this->resprints.= " LEFT JOIN ".MAIN_DB_PREFIX."commande_fournisseur as cf ON elcf.fk_source = cf.rowid AND elcf.sourcetype = 'order_supplier'";
		}

		return 0;
	}

	/**
	 * Execute action printCommonFooter
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action        	Current action (if set). Generally create or edit or null
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function printCommonFooter(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		$TContext = explode(':',$parameters['context']);

		if (in_array('receptionlist', $TContext))
		{

			$defaultLang = strtr($langs->defaultlang, '_', '-');
			$currency = array_keys($langs->cache_currencies)[0];

			?>

			<script type="application/javascript">
				$(document).ready(function () {
					$('#massaction').parent().prepend('<span id="receipt_total_ht" class="receipt_total_ht" style="display: none;"><?php echo $langs->trans('totalSelectedReceipt'); ?>&nbsp;<span id="receipt_total_ht_value">0</span>&emsp;</span>')

					$("#checkforselects").click(function() {
						if (typeof initCheckForSelect == 'function') { initCheckForSelect(0, "receipt_total_ht", "checkforselect"); } else { console.log("No function initCheckForSelect found. Call won't be done."); }
					});

					initCheckForSelect(0, "receipt_total_ht", "checkforselect");
					$(".checkforselect").click(function() {
						initCheckForSelect(1, "receipt_total_ht", "checkforselect");
					});

					$(".checkforselect").change(function(){
						getTotal();
					});

					function getTotal() {
						let totalht = 0;

						$(".checkforselect:checked").each(function () {
							let lineTotal = $(this).parents('tr').children('td[data-key=total_ht]')[0].innerText;

							totalht += parseFloat(lineTotal.replace(' ', '').replace(',', '.'));
							// console.log(parseFloat(lineTotal.replace(' ', '').replace(',', '.')));
						});

						// console.log(totalht);
						$('#receipt_total_ht_value').html(new Intl.NumberFormat('<?php echo $defaultLang; ?>', {
							style: 'currency',
							currency: '<?php echo $currency; ?>'
						}).format(totalht));
						//parseFloat(('3 000,10').replace(' ', '').replace(',','.'))
					}

					getTotal();

				});
			</script>

			<?php
		}

		return 0;
	}

	/* Add here any other hooked methods... */
}
