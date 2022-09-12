<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/pollenwatcher.inc.php';


class pollenwatcher extends eqLogic
{
	/*     * *************************Attributs****************************** */

	/*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */
	public static $_widgetPossibility = array();

	/*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

	/*     * ***********************Methode static*************************** */
	public static function deadCmd()
	{
		$return = array();
		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			foreach ($pollenwatcher->getCmd() as $cmd) {
				preg_match_all("/#([0-9]*)#/", $cmd->getConfiguration('infoName', ''), $matches);
				foreach ($matches[1] as $cmd_id) {
					if (!cmd::byId(str_replace('#', '', $cmd_id))) {
						$return[] = array('detail' => __('pollenwatcher', __FILE__) . ' ' . $pollenwatcher->getHumanName() . ' ' . __('dans la commande', __FILE__) . ' ' . $cmd->getName(), 'help' => __('Nom Information', __FILE__), 'who' => '#' . $cmd_id . '#');
					}
				}
				preg_match_all("/#([0-9]*)#/", $cmd->getConfiguration('calcul', ''), $matches);
				foreach ($matches[1] as $cmd_id) {
					if (!cmd::byId(str_replace('#', '', $cmd_id))) {
						$return[] = array('detail' => __('pollenwatcher', __FILE__) . ' ' . $pollenwatcher->getHumanName() . ' ' . __('dans la commande', __FILE__) . ' ' . $cmd->getName(), 'help' => __('Calcul', __FILE__), 'who' => '#' . $cmd_id . '#');
					}
				}
			}
		}
		return $return;
	}
	/*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  */

	/*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  */

	/*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  */

	/*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  */
	public static function cron15()
	{

		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			if ($pollenwatcher->getIsEnable()) {
				log::add(__CLASS__, 'debug', '================= CRON 15 ==================');
				$pollenwatcher->getInformations();
			}
		}
	}

	/*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  */
	public static function cron30()
	{

		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			if ($pollenwatcher->getIsEnable()) {
				log::add(__CLASS__, 'debug', '================= CRON 30 ==================');
				$pollenwatcher->getInformations();
			}
		}
	}

	/*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  */
	public static function cronHourly()
	{

		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			if ($pollenwatcher->getIsEnable()) {
				log::add(__CLASS__, 'debug', '================= CRON Hourly ==================');
				$pollenwatcher->getInformations();
			}
		}
	}

	/*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  */
	public static function cronDaily()
	{

		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			if ($pollenwatcher->getIsEnable()) {
				log::add(__CLASS__, 'debug', '================= CRON DAILY ==================');
				$pollenwatcher->getInformations();
			}
		}
	}
	// Fonction chargement Template
	public static function templateWidget()
	{
		return pollenwatcher_Template::getTemplate();
	}


	/*     * *********************Méthodes d'instance************************* */
	public function refresh()
	{
		foreach ($this->getCmd() as $cmd) {
			$s = print_r($cmd, 1);
			log::add(__CLASS__, 'debug', 'refresh  cmd: ' . $s);
			$cmd->execute();
		}
	}

	public function preInsert()
	{
	}

	public function postInsert()
	{

		$_eqName = $this->getName();
		$templatecore_V4  = 'core::';
		$template = 'pollenwatcher::Niveau Risque Allergie';
		log::add(__CLASS__, 'debug', '┌───────── Création des commandes standards : ' . $_eqName);

		//$this->AddCommand('Département', 'countyName', 'info', 'string', $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 'default',  1, '0', false);
		//$this->AddCommand('Numéro Département', 'countyNumber', 'info', 'string', $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 'default',  2, '0', false);
		$this->AddCommand('Valeur Maximale', 'max_value', 'info', 'numeric', $template, null, null, 1, 'default', 'default', 'default',  3, 1, false, 0, 1);
		//$this->AddCommand('Niveau de risque', 'riskLevel', 'info', 'numeric', $template, null, null, 1, 'default', 'default', 'default',  4, 1, false,0,1);
		log::add(__CLASS__, 'debug', '└─────────');
	}

	public function preSave()
	{
	}
	public function postSave()
	{

		$_eqName = $this->getName();
		log::add(__CLASS__, 'debug', 'Sauvegarde de l\'équipement [postSave()] : ' . $_eqName);
		if ($this->getIsEnable() == 0)
			return;

		log::add(__CLASS__, 'debug', '┌───────── Création des commandes si besoin : ' . $_eqName);
		$templatecore_V4  = 'core::';
		// Ajout des types d'allergie
		$result = pollenwatcher::getData($this->getConfiguration("region_id"));
		$order = 40;
		$template = 'pollenwatcher::Niveau Risque Allergie';
		foreach ($result['risks'] as $result) {
			$this->AddCommand($result['pollenName'], $result['pollenName'], 'info', 'numeric', $template, null, null, 1, 'default', '0', '5',  $order, '0', false);
			$order--;
		}

		// Commande Standard (recréation si necessaire)
		$this->AddCommand('Département', 'countyName', 'info', 'string', $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 'default',  1, '0', false);
		$this->AddCommand('Numéro Département', 'countyNumber', 'info', 'string', $templatecore_V4 . 'line', null, null, 0, 'default', 'default', 'default',  2, '0', false);
		$this->AddCommand('Valeur Maximale surveillée', 'riskLevel_max', 'info', 'numeric', $template, null, null, 1, 'default', 'default', 'default',  3, 1, false, 0, 1);
		$this->AddCommand('Niveau de risque', 'riskLevel', 'info', 'numeric', $template, null, null, 1, 'default', 'default', 'default',  4, 1, false, 0, 1);
		log::add(__CLASS__, 'debug', '└─────────');

		// Mise a jour des données
		$this->getInformations();

		// Get Max Value command
		/* VOIR SI CES FONCTIONS SERVENT
		$cmd = $this->getCmd(null, 'max_value');
		$value = is_object($cmd) ? $cmd->execCmd() : 0;

		// Only at first save (max_value not set yet)
		if (strlen($value) <= 0) {
			//	$this->getInformations();
		}
		*/
	}

	public function preUpdate()
	{
		if (!$this->getIsEnable()) return;

		if ($this->getConfiguration('region_id') == '') {
			throw new Exception(__('Veuillez sélectionner une région pour l\'équipement : ' . $this->getName(), __FILE__));
			log::add(__CLASS__, 'error', '│ Veuillez sélectionner une région pour l\'équipement : ' . $this->getName());
		}
	}

	public function postUpdate()
	{
		//$this->getInformations();
	}

	public function preRemove()
	{
	}

	public function postRemove()
	{
	}

	public function AddCommand($Name, $_logicalId, $Type = 'info', $SubType = 'numeric', $Template = null, $unite = null, $generic_type = null, $IsVisible = 1, $icon = 'default', $valuemin = 'default', $valuemax = 'default', $_order = null, $IsHistorized = '0', $repeatevent = false, $forceLineB = 'default', $forceLineA = 'default')
	{
		$Command = $this->getCmd(null, $_logicalId);
		if (!is_object($Command)) {
			log::add(__CLASS__, 'debug', '│ Name : ' . $Name . ' -- Type : ' . $Type . '/' . $SubType  . ' -- LogicalID : ' . $_logicalId . ' -- Template Widget / Ligne : ' . $Template . ' / ' . '-- Type de générique : ' . $generic_type . ' -- Icône : ' . $icon . ' -- Min/Max : ' . $valuemin . '/' . $valuemax .  ' -- Ordre : ' . $_order);
			$Command = new pollenwatcherCmd();
			$Command->setId(null);
			$Command->setLogicalId($_logicalId);
			$Command->setEqLogic_id($this->getId());
			$Command->setName($Name);

			$Command->setType($Type);
			$Command->setSubType($SubType);

			if ($Template != null) {
				$Command->setTemplate('dashboard', $Template);
				$Command->setTemplate('mobile', $Template);
			}

			if ($unite != null && $SubType == 'numeric') {
				$Command->setUnite($unite);
			}

			$Command->setIsVisible($IsVisible);
			$Command->setIsHistorized($IsHistorized);

			if ($icon != 'default') {
				$Command->setdisplay('icon', '<i class="' . $icon . '"></i>');
			}
			if ($generic_type != null) {
				$Command->setGeneric_type($generic_type);
			}

			if ($repeatevent == true && $Type == 'info') {
				$Command->setconfiguration('repeatEventManagement', 'never');
				log::add(__CLASS__, 'debug', '│ No Repeat pour l\'info avec le nom : ' . $Name);
			}
			if ($valuemin != 'default') {
				$Command->setconfiguration('minValue', $valuemin);
			}
			if ($valuemax != 'default') {
				$Command->setconfiguration('maxValue', $valuemax);
			}
			if ($forceLineB != 'default') {
				$Command->setdisplay('forceReturnLineBefore', 1);
			}
			if ($forceLineA != 'default') {
				$Command->setdisplay('forceReturnLineAfter', 1);
			}

			if ($_order != null) {
				$Command->setOrder($_order);
			}
			$Command->save();
		}

		$createRefreshCmd = true;
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = cmd::byEqLogicIdCmdName($this->getId(), __('Rafraichir', __FILE__));
			if (is_object($refresh)) {
				$createRefreshCmd = false;
			}
		}
		if ($createRefreshCmd) {
			if (!is_object($refresh)) {
				$refresh = new pollenwatcherCmd();
				$refresh->setLogicalId('refresh');
				$refresh->setIsVisible(1);
				$refresh->setName(__('Rafraichir', __FILE__));
			}
			$refresh->setType('action');
			$refresh->setSubType('other');
			$Command->setOrder('0');
			$refresh->setEqLogic_id($this->getId());
			$refresh->save();
		}
		return $Command;
	}

	/*  **********************Getteur Setteur*************************** */
	public function getData($country)
	{
		# Use the Curl extension to get details
		$url = 'https://pollens.fr/risks/thea/counties/' . sprintf("%02d", $country);
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		log::add('pollenwatcher', 'debug', '│ URL : ' . $url);
		log::add('pollenwatcher', 'debug', '│ Result : ' . $html);
		$result = json_decode($html, true);
		curl_close($ch);

		return $result;
	}
	public function getInformations()
	{
		if (!$this->getIsEnable()) return;
		$_eqName = $this->getName();
		log::add(__CLASS__, 'debug', '┌───────── MISE A JOUR : ' . $_eqName);
		$result = pollenwatcher::getData($this->getConfiguration("region_id"));


		// Risque par Polen
		$risk_list = null;
		$riskLevel_max_Value = 0;
		foreach ($result['risks'] as $result_risks) {
			$this->checkAndUpdateCmd($result_risks['pollenName'], $result_risks['level']);
			$allergyCmd = $this->getCmd('info',  $result_risks['pollenName']);
			// Probleme sur cette fonction en attente
			//$allergyCmd = $allergyCmd->getIsVisible();
			$allergyCmd = true;
			if ($allergyCmd == 1 && ($result_risks['level'] > $riskLevel_max_Value)) {
				log::add(__CLASS__, 'debug', '│ Commande Visible pour : ' . $result_risks['pollenName']);
				$riskLevel_max_Value = $result_risks['level'];
			}
			// Prépration Log
			if ($risk_list == null) {
				$risk_list = 'pollenName ' . $result_risks['pollenName'] . ' Level ' . $result_risks['level'];
			} else {
				$risk_list = ' / ' . 'pollenName ' . $result_risks['pollenName'] . ' Level ' . $result_risks['level'];
			}
		}
		log::add('pollenwatcher', 'debug', '│ ' . $risk_list);
		log::add('pollenwatcher', 'debug', "│ Valeur Maxi surveillée : " . $riskLevel_max_Value);



		foreach ($this->getCmd('info') as $Command) {
			if (is_object($Command)) {
				switch ($Command->getLogicalId()) {
					case 'countyName':
						$this->checkAndUpdateCmd($Command->getLogicalId(), $result['countyName']);
						break;
					case 'countyNumber':
						$this->checkAndUpdateCmd($Command->getLogicalId(), $result['countyNumber']);
						break;
					case 'riskLevel':
						$this->checkAndUpdateCmd($Command->getLogicalId(), $result['riskLevel']);
						break;
					case 'riskLevel_max':
						$this->checkAndUpdateCmd($Command->getLogicalId(), $riskLevel_max_Value);
						break;
				}
			}
		}

		/* ANCIENNE FONCTION DESACTIVE => Voir si cela doit rester ou pas

		$json = file_get_contents("https://www.pollens.fr/risks/thea/counties/" . sprintf("%02d", $this->getConfiguration("region_id")));
		$data = json_decode($json, true);

		log::add('pollenwatcher', 'debug', "│ riskLevel = " . $data['riskLevel']);


		# Use the Curl extension to get details
		$url = 'https://pollens.fr/risks/thea/counties/' . sprintf("%02d", $this->getConfiguration("region_id"));
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		$resultJson = json_decode($html, true);
		curl_close($ch);

		log::add('pollenwatcher', 'debug', '│ Result : ' . $html);

		# Create a DOM parser object
		$dom = new DOMDocument();

		# Parse the HTML
		# The @ before the method call suppresses any warnings that
		# loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		# Iterate over all the <rect> tags
		$index = 0;
		$changed = false;
		foreach ($dom->getElementsByTagName('rect') as $link) {
			$value = 0;
			$width = $link->getAttribute('width');
			if ($width > 0 && $width <= 30)
				$value = 1;
			else if ($width > 30 && $width <= 60)
				$value = 2;
			else if ($width > 60 && $width <= 90)
				$value = 3;
			else if ($width > 90 && $width <= 140)
				$value = 4;
			else if ($width > 140)
				$value = 5;
			# Show the <rect width>
			//echo $this->getPollens()[$index] . ' ' . $link->getAttribute('width') . ' ' . $value . "<br />";
			// Update Info command
			$changed = $this->checkAndUpdateCmd($this->getPollens()[$index], $value) || $changed;
			$index += 1;
		}
*/
		$this->refreshWidget();
		log::add(__CLASS__, 'debug', '└─────────');
	}

	/* Widget désactivé
	public function toHtml($_version = 'dashboard')
	{


		//$replace = $this->preToHtml($_version);
		//$replace = $this->preToHtml($_version,array(), True);

		 Désactivation widget global le temps des essais
		if (!is_array($replace)) {
			return $replace;
		}

		$version = jeedom::versionAlias($_version);
		*/

	// *********************************
	// Get global style template

	/* Désactivation widget global le temps des essais
		$globalStyle = $this->getConfiguration("global_style");
		if ($globalStyle == null)
			$globalStyle = 'global_style_circle_thin';

		$globalTemplate = '';
		if ($globalTemplate != 'none')
			$globalTemplate = getTemplate('core', $version, $globalStyle, 'pollenwatcher');
		$replace["#global_style#"] = $globalTemplate;
*/

	// *********************************
	//  Prepare allergy list
	/* Désactivation widget global le temps des essais
		$ordererArray = null;
		$maxLevel = 0;
		foreach ($this->getPollens() as $key) {
			$allergyCmd = $this->getCmd(null,  $key);
			if ($allergyCmd->getIsVisible() == 0)
				continue;
			$level = is_object($allergyCmd) ? $allergyCmd->execCmd() : 0;
			if ($level > $maxLevel)
				$maxLevel = $level;
			$ordererArray[$level][] = $allergyCmd->getName();
		}

		$data = '';
		for ($i = 5; $i > 0; $i--) {
			if (!array_key_exists($i, $ordererArray))
				continue;
			foreach ($ordererArray[$i] as $key) {
				if (strlen($data) > 0)
					$data .=  "<br/>";
				$data .= "<span><i class='fa fa-circle' style='font-size : 1em;color:" . $this->getAllergyColor($i) . "'></i>&nbsp;&nbsp;" . $key . "</span>";
			}
		}
		$replace["#data#"] 		= $data;

		// *********************************
		//  Prepare global level (update CMD if needed)

		$status = $this->getCmd(null, 'max_value');
		if (is_object($status) && ($status->getIsVisible() == 1)) {
			if ($maxLevel != $status->execCmd()) {
				$status->setValue($maxLevel);
				$status->save();
			}
			$replace["#global_color#"]	= $this->getAllergyColor($maxLevel);
			$replace["#global_level#"]	= $maxLevel;
		} else {
			$replace["#global_color#"]	= '';
			$replace["#global_level#"]	= '';
			$replace["#global_style#"]	= '';
		}


		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'main', 'pollenwatcher')));
	}
	private function getAllergyColor($level)
	{
		if ($level == 1)
			return "#C1E9C1";
		else if ($level == 2)
			return "#00B050";
		else if ($level == 3)
			return "#FFFF00";
		else if ($level == 4)
			return "#FFA329";
		else if ($level == 5)
			return "#DF2B2F";
		return "#FFFFFF";
	}
}*/
}


/*     * **********************pollenwatcherCmd*************************** */

class pollenwatcherCmd extends cmd
{
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	//public static $_widgetPossibility = array('custom' => true);

	public function dontRemoveCmd()
	{
		if ($this->getLogicalId() == 'refresh') {
			return true;
		}
		return false;
	}

	public function execute($_options = array())
	{
		if ($this->getLogicalId() == 'refresh') {
			log::add('pollenwatcher', 'debug', ' ─────────> ACTUALISATION MANUELLE');
			$this->getEqLogic()->getInformations();
			log::add('pollenwatcher', 'debug', ' ─────────> FIN ACTUALISATION MANUELLE');
		}
		return;
	}
}
