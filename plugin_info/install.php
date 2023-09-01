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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function pollenwatcher_install()
{
    jeedom::getApiKey('pollenwatcher');
    config::save('functionality::cron15::enable', 0, 'pollenwatcher');
    config::save('functionality::cron30::enable', 0, 'pollenwatcher');
    config::save('functionality::cronHourly::enable', 0, 'pollenwatcher');
    config::save('functionality::cronDaily::enable', 1, 'pollenwatcher');
*/
    $cron = cron::byClassAndFunction('pollenwatcher', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
    // Voir si besoin d'un message
    //message::add('Surveillances Allergo Pollinique, 'Merci pour l\'installation du plugin.');
}

function pollenwatcher_update()
{
    jeedom::getApiKey('pollenwatcher');
    $cron = cron::byClassAndFunction('pollenwatcher', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }

    if (config::byKey('functionality::cron15::enable', 'pollenwatcher', -1) == -1) {
        config::save('functionality::cron15::enable', 0, 'pollenwatcher');
    }
    if (config::byKey('functionality::cron30::enable', 'pollenwatcher', -1) == -1) {
        config::save('functionality::cron30::enable', 0, 'pollenwatcher');
    }
    if (config::byKey('functionality::cronHourly::enable', 'pollenwatcher', -1) == -1) {
        config::save('functionality::cronHourly::enable', 0, 'pollenwatcher');
    }
    if (config::byKey('functionality::cronDaily::enable', 'pollenwatcher', -1) == -1) {
        config::save('functionality::cronDaily::enable', 1, 'pollenwatcher');
    }

    $plugin = plugin::byId('pollenwatcher');
    $eqLogics = eqLogic::byType($plugin->getId());
    foreach ($eqLogics as $eqLogic) {
        // Exemple
        //updateLogicalId($eqLogic, 'humidityabs', null, '2');

    }

    //resave eqLogics for new cmd:
    try {
        $eqs = eqLogic::byType('pollenwatcher');
        foreach ($eqs as $eq) {
            $eq->save();
        }
    } catch (Exception $e) {
        $e = print_r($e, 1);
        log::add('pollenwatcher', 'error', 'pollenwatcher update ERROR : ' . $e);
    }

    // Voir si besoin d'un message
    //message::add('Surveillances Allergo Pollinique', 'Merci pour la mise à jour de ce plugin, consultez le changelog.');

    foreach (eqLogic::byType('pollenwatcher') as $pollenwatcher) {
        $pollenwatcher->getInformations();
    }
}

function updateLogicalId($eqLogic, $from, $to, $_historizeRound = null, $name = null, $unite = null)
{
    $command = $eqLogic->getCmd(null, $from);
    if (is_object($command)) {
        if ($to != null) {
            $command->setLogicalId($to);
        }
        if ($_historizeRound != null) {
            log::add('pollenwatcher', 'debug', 'Correction arrondi pour : ' . $from . 'Par :' . $_historizeRound);
            $command->setConfiguration('historizeRound', $_historizeRound);
        }
        if ($name != null) {
            //$command->setName($name);
        }
        if ($unite != null) {
            if ($unite == 'DELETE') {
                $unite = null;
            }
            $command->setUnite($unite);
        }
        $command->save();
    }
}

function pollenwatcher_remove()
{
    $cron = cron::byClassAndFunction('pollenwatcher', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}
