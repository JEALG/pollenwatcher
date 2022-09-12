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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class pollenwatcher_Template
{
	public static function getTemplate()
	{
		$return = array('info' => array('numeric' => array()));
		$return['info']['numeric']['Niveau Risque Allergie'] = array(
			'template' => 'tmplmultistate',
			'replace' => array('#_desktop_width_#' => '40'),
			'test' => array(
				array('operation' => '#value# == 0', 'state_light' => '<i class=\'icon far fa-circle \'></i>'),
				array('operation' => '#value# == 1', 'state_light' => '<i class=\'icon_green icon fas fa-circle\'></i>'),
				array('operation' => '#value# == 2', 'state_light' => '<i class=\'icon_yellow icon fas fa-circle\'></i>'),
				array('operation' => '#value# == 3', 'state_light' => '<i class=\'icon_orange icon fas fa-circle\'></i>'),
				array('operation' => '#value# == 4', 'state_light' => '<i class=\'icon_red icon fas fa-circle\'></i>'),
				array('operation' => '#value# == 5', 'state_light' => '<i class=\'icon_red icon fas fa-circle\'></i>')
			)
		);
		return $return;
	}
}
