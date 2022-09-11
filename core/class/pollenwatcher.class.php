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

	public static $_widgetPossibility = array('custom' => true);

	//Fonction exécutée automatiquement tous les jours par Jeedom cronDaily

	public static function cronDaily()
	{

		foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
			if ($pollenwatcher->getIsEnable()) {
				log::add(__CLASS__, 'debug', '================= CRON DAILY ==================');
				$pollenwatcher->getInformations();
			}
		}
	}
	// Template
	public static function templateWidget()
	{
		return pollenwatcher_Template::getTemplate();
	}

	public static function getPollens()
	{
		return array(
			"Cyprès",
			"Saule",
			"Frêne",
			"Peuplier",
			"Charme",
			"Bouleau",
			"Platane",
			"Chêne",
			"Graminées",
			"Oseille",
			"Urticacées",
			"Châtaignier",
			"Armoise",
			"Aulne",
			"Noisetier",
			"Plantain",
			"Olivier",
			"Ambroisies",
			"Tilleul"
		);
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

		// pollenwatcher Info Creation
		foreach ($this->getPollens() as $key) {
			$this->createPollenInfo($key, $key);
		}

		// Max Value info		
		$this->createPollenInfo("max_value", "Valeur Maximale", True);

		// Refresh command		
		$command = pollenwatcherCmd::byEqLogicIdAndLogicalId($this->getId(), "refresh");
		if (!is_object($command))
			$command = new pollenwatcherCmd();
		$command->setName("Rafraichir");
		$command->setLogicalId("refresh");
		$command->setEqLogic_id($this->getId());
		$command->setType("action");
		$command->setSubType("other");
		$command->save();
	}


	public function preUpdate()
	{
		if ($this->getConfiguration('region_id') == '') {
			throw new Exception(__('Veuillez sélectionner une région', __FILE__));
		}
	}


	public function postSave()
	{

		$_eqName = $this->getName();
		log::add(__CLASS__, 'debug', 'Sauvegarde de l\'équipement [postSave()] : ' . $_eqName);
		if ($this->getIsEnable() == 0)
			return;

		// Get Max Value command
		$cmd = $this->getCmd(null, 'max_value');
		$value = is_object($cmd) ? $cmd->execCmd() : 0;

		// Only at first save (max_value not set yet)
		if (strlen($value) <= 0)
			$this->getInformations();
	}
	public function postUpdate()
	{
		$this->getInformations();
	}

	public function preRemove()
	{
	}

	public function postRemove()
	{
	}


	private function createPollenInfo($logicalId, $name, $visibility = True)
	{

		log::add('pollenwatcher', 'debug', '│ createPollenInfo: ' . $logicalId);

		$Command = pollenwatcherCmd::byEqLogicIdAndLogicalId($this->getId(), $logicalId);
		if (is_object($Command))
			return;
		$Command = new pollenwatcherCmd();
		log::add(__CLASS__, 'debug', '│ Name : ' . $name . ' -- LogicalID : ' . $logicalId . ' -- Min/Max : ' . '0' . '/' . '5');
		$Command->setName($name);
		$Command->setLogicalId($logicalId);
		$Command->setEqLogic_id($this->getId());
		$Command->setType("info");
		$Command->setSubType("numeric");
		$Command->setConfiguration('minValue', 0);
		$Command->setConfiguration('maxValue', 5);
		//$info->setDisplay('generic_type', 'POLLEN');
		if ($visibility == False)
			$Command->setIsVisible(False);
		$Command->save();

		return $Command;
	}

	/*  **********************Getteur Setteur*************************** */
	public function getInformations()
	{

		if (!$this->getIsEnable()) return;
		$_eqName = $this->getName();
		log::add(__CLASS__, 'debug', '┌───────── MISE A JOUR : ' . $_eqName);
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
		// Update Value
		foreach ($resultJson['risks'] as $result) {
			log::add('pollenwatcher', 'debug', '│ pollenName : ' . $result['pollenName'] . ' - Level : ' . $result['level']);
			$this->checkAndUpdateCmd($result['pollenName'], $result['level']);
		}
		$changed = $this->updateMaxValue() || $changed;
		log::add('pollenwatcher', 'debug', "│ Data updated for Region: " . $this->getConfiguration("region_id"));

		if ($changed)
			$this->refreshWidget();
		log::add(__CLASS__, 'debug', '└─────────');
	}



	// *****************************************
	// Update the Max Value Command

	public function updateMaxValue()
	{
		//log::add('pollenwatcher', 'debug', "│  updateMaxValue");
		$maxValue = 0;
		foreach ($this->getPollens() as $key) {
			$allergyCmd = $this->getCmd(null,  $key);
			$value = $allergyCmd->execCmd();
			if (($allergyCmd->getIsVisible() == 1) && ($value > $maxValue))
				$maxValue = $value;
		}
		log::add('pollenwatcher', 'debug', "│ updateMaxValue: " . $maxValue);
		return $this->checkAndUpdateCmd('max_value', $maxValue);
	}

	public function toHtml($_version = 'dashboard')
	{


		//$replace = $this->preToHtml($_version);
		//$replace = $this->preToHtml($_version,array(), True);

		/* Désactivation widget global le temps des essais
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
	*/
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
}



/*     * **********************pollenwatcherCmd*************************** */

class pollenwatcherCmd extends cmd
{
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public static $_widgetPossibility = array('custom' => true);

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
