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
include_file('core', 'orangetv', 'config', 'orangetv');

class orangetv extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

	public static function deamon_info() {
		$return = array();
		$return['log'] = 'orangetv';
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('orangetv') . '/orangetvd.pid';
		if (file_exists($pid_file)) {
			$pid = trim(file_get_contents($pid_file));
			if (is_numeric($pid) && posix_getsid($pid)) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null;rm -rf ' . $pid_file . ' 2>&1 > /dev/null;');
			}
		}
		$return['launchable'] = 'ok';
		return $return;
	}	
	
	public static function deamon_start() {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}

		$orangetv_path = realpath(dirname(__FILE__) . '/../../resources/orangetvd');
		$cmd = '/usr/bin/python ' . $orangetv_path . '/orangetvd.py';
		$cmd .= ' --socketport ' . config::byKey('socketport', 'orangetv');
		$cmd .= ' --cycle ' . config::byKey('cycle', 'orangetv');
		$cmd .= ' --freq_actu ' . config::byKey('freq_actu', 'orangetv');
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('orangetv'));
		$cmd .= ' --apikey ' . jeedom::getApiKey('orangetv');
		$cmd .= ' --pid ' . jeedom::getTmpFolder('orangetv') . '/orangetvd.pid';
		$cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/orangetv/core/php/orangetv.inc.php';
		
		log::add('orangetv', 'info', 'Lancement démon orangetvd : ' . $cmd);
		exec($cmd . ' >> ' . log::getPathToLog('orangetv') . ' 2>&1 &');
		$i = 0;
		while ($i < 30) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 30) {
			log::add('orangetv', 'error', 'Impossible de lancer le démon orangetv, vérifiez le log', 'unableStartDeamon');
			return false;
		}
		message::removeAll('orangetv', 'unableStartDeamon');
		sleep(2);
		self::sendIdToDeamon();
		config::save('include_mode', 0, 'orangetv');
		log::add('orangetv', 'info', 'Démon orangetv lancé');
		return true;
	}
	
	public static function deamon_stop() {
		$pid_file = jeedom::getTmpFolder('orangetv') . '/orangetvd.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		system::kill('orangetvd.py');
		system::fuserk(config::byKey('socketport', 'orangetv'));
		sleep(1);
	}

	public static function sendIdToDeamon() {
		foreach (self::byType('orangetv') as $eqLogic) {
			$eqLogic->allowDevice();
			usleep(300);
		}
	}
	
	public function allowDevice() {
		$value = array('apikey' => jeedom::getApiKey('orangetv'), 'cmd' => 'add');
		$value['device'] = array(
			'id' => $this->getLogicalId(),
		);
		$value = json_encode($value);
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'orangetv'));
		socket_write($socket, $value, strlen($value));
		socket_close($socket);
	}
    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */
	public function MaJ_JSON() {
		foreach (eqLogic::byType('orangetv') as $orangetv) {
			$orangetv->ActionInfo($orangetv->getConfiguration('box_ip'));
		}
	}
	
	public function autoMaJCommande() {
		
		global $listCmdorangetv;
		
		foreach ($this->getCmd() as $cmd) {
			foreach ($listCmdorangetv as $cmd_config) {
				if (($cmd->getName()==$cmd_config['name']) AND ($cmd->getConfiguration('code_touche')!=$cmd_config['configuration']['code_touche'])){
					$cmd->setConfiguration('code_touche', $cmd_config['configuration']['code_touche']);
					$cmd->save();
				}
			}
		}

	log::add('orangetv', 'debug', 'update des commandes OK');		
   
    }
	public function lecture_json($param_sortie, $param_entree, $localisation, $comp_entree) {
		// param -> id / nom / canal / logo / categorie
		$json_liste = file_get_contents(realpath(dirname(__FILE__) . '/../../core/config/chaines.json'));
		$json_chaines = json_decode($json_liste, true);
		
		foreach ($json_chaines['localisation'] as $key => $val) {
			if ($localisation == $val['code']) {
				foreach ($val['liste'] as $key => $val) {
					if ($comp_entree == $val[$param_entree]) {
						$retour = $val[$param_sortie];
					}
				}
			}
		}
		if ($retour == '') {
			$retour = 'blank';
		}
		return $retour;
	}
	
	public function ActionCommande($box_ip, $code_touche, $code_mode) {
		// construction de la commande
		$cmd_html = 'curl -s "http://'.$box_ip.':8080/remoteControl/cmd?operation=01&key='.$code_touche.'&mode='.$code_mode.'" > /dev/null 2>&1';
		
		// execution de la commande
		$retour_action = shell_exec($cmd_html);
		return;
	}
	
	public function ActionInfo($box_ip) {
		$localisation = orangetv::getConfiguration('localisation');

		// etat du decodeur
		$cmd_retour = 'curl -s "http://'.$box_ip.':8080/remoteControl/cmd?operation=10"';
		// execution de la commande
		$retour_action = shell_exec($cmd_retour);	
		
		// lecture du json depuis le décodeur
		$retour = json_decode($retour_action, true);
		log::add('orangetv', 'debug', ' *** DEBUT RETOUR JSON POUR LE DECODEUR - ' . orangetv::getName() . ' - ***');
		
		if (isset($retour['result']['responseCode'])) {
			log::add('orangetv', 'debug', 'DECODEUR INFO - ResponseCode : ' . $retour['result']['responseCode']);
		}
		if (isset($retour['result']['data']['activeStandbyState'])) {
			log::add('orangetv', 'debug', 'DECODEUR INFO - activeStandbyState : ' . $retour['result']['data']['activeStandbyState']);
		}
		if (isset($retour['result']['data']['osdContext'])) {
			log::add('orangetv', 'debug', 'DECODEUR INFO - osdContext : ' . $retour['result']['data']['osdContext']);
		}
		if (isset($retour['result']['data']['playedMediaId'])) {
			log::add('orangetv', 'debug', 'DECODEUR INFO - playedMediaId : ' . $retour['result']['data']['playedMediaId']);
		}		

		log::add('orangetv', 'debug', ' **** FIN RETOUR JSON POUR LE DECODEUR - ' . orangetv::getName() . ' - ****');
		
		if ($retour['result']['responseCode'] == '0') {
			foreach (eqLogic::getCmd() as $info) {
				
				if ($info->getName() == 'Etat Decodeur') {

					$retour_etat = $retour['result']['data']['activeStandbyState'];
					
					if ( $retour_etat == '0' ) {
						$etat_decodeur = 1;
					} elseif ( $retour_etat == '1' ){
						$etat_decodeur = 0;
					} elseif ( $retour_etat == '' ){
						$etat_decodeur = $info->getConfiguration('etat_decodeur');
					} else {
						$etat_decodeur = $info->getConfiguration('etat_decodeur');
					}
					
					if ($info->getConfiguration('etat_decodeur') != $etat_decodeur) {
						$info->setConfiguration('etat_decodeur', $etat_decodeur);
						//$info->setValue($etat_decodeur);
						$info->save();
						$info->event($etat_decodeur);
						orangetv::refreshWidget();
					}
				}
				
				if ($info->getName() == 'Fonction') {
					
					if (isset($retour['result']['data']['playedMediaState'])){
						$retour_fonction = $retour['result']['data']['playedMediaState'];
					} else {
						$retour_fonction = "null";
					}
					
									
					if ($info->getConfiguration('fonction') != $retour_fonction) {
						$info->setConfiguration('fonction', $retour_fonction);
						//$info->setValue($retour_fonction);
						$info->save();
						$info->event($retour_fonction);
						orangetv::refreshWidget();
					}
					
				}
					
				if ($info->getName() == 'Chaine Actuelle') {
						
					if($retour['result']['data']['osdContext'] == 'HOMEPAGE'){
						$chaine_actu = 'home';
					}
					elseif ($retour['result']['data']['osdContext'] == 'VOD'){
						$chaine_actu = 'vod';
					}
					elseif ($retour['result']['data']['osdContext'] == 'LIVE'){
						$chaine_actu = strval($retour['result']['data']['playedMediaId']);
						if ($chaine_actu != '-1') {
							$chaine_actu = $retour['result']['data']['playedMediaId'];
							$chaine_actu = $this->lecture_json('logo', 'id', $localisation, $chaine_actu);

						}
					}
					else {
						$chaine_actu = 'blank';
					}
					if ($info->getConfiguration('chaine_actuelle') != $chaine_actu) {
						$info->setConfiguration('chaine_actuelle', $chaine_actu);
						//$info->setValue($chaine_actu);
						$info->save();
						$info->event($chaine_actu);
						orangetv::refreshWidget();
					}
					}
			}
		} else {
			log::add('orangetv', 'debug', 'DECODEUR ERROR - ResponseCode : ' . $retour['result']['responseCode']);
			log::add('orangetv', 'debug', 'Le décodeur ne donne pas de réponse');
		}
		return;
	}
	
	public function Telecommande_Mosaique() {
		orangetv::refreshWidget();
		return;
	}
	
    public function autoAjoutCommande() {
		
		global $listCmdorangetv;
		
        foreach ($listCmdorangetv as $cmd) {
			   if (cmd::byEqLogicIdCmdName($this->getId(), $cmd['name']))
					return;
				
			   if ($cmd) {
					$orangetvCmd = new orangetvCmd();
					$orangetvCmd->setName(__($cmd['name'], __FILE__));
					$orangetvCmd->setEqLogic_id($this->id);
					$orangetvCmd->setLogicalId($cmd['logicalId']);
					$orangetvCmd->setConfiguration('code_touche', $cmd['configuration']['code_touche']);
					$orangetvCmd->setConfiguration('mosaique_chaine', $cmd['configuration']['mosaique_chaine']);
					$orangetvCmd->setConfiguration('telecommande', $cmd['configuration']['telecommande']);
					$orangetvCmd->setConfiguration('etat_decodeur', 0);
					$orangetvCmd->setConfiguration('chaine_actuelle', $cmd['configuration']['chaine_actuelle']);					
					$orangetvCmd->setConfiguration('fonction', $cmd['configuration']['fonction']);
					$orangetvCmd->setType($cmd['type']);
					$orangetvCmd->setSubType($cmd['subType']);
					$orangetvCmd->setOrder($cmd['order']);
					$orangetvCmd->setIsVisible($cmd['isVisible']);
					$orangetvCmd->setDisplay('generic_type', $cmd['generic_type']);
					$orangetvCmd->setDisplay('forceReturnLineAfter', $cmd['forceReturnLineAfter']);
					$orangetvCmd->setValue(0);
					$orangetvCmd->save();
			   }

        }        
    }
	
    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
		if (!$this->getId())
          return;
		$this->Telecommande_Mosaique();        
    }

    public function preUpdate() {
		if ($this->getConfiguration('box_ip') == '') {
            throw new Exception(__('Merci de renseigner IP du décodeur.',__FILE__));	
        }
        $this->autoAjoutCommande();        
    }

    public function postUpdate() {
		$this->Telecommande_Mosaique();
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    // Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
    public function toHtml($_version = 'dashboard') {
		$localisation = orangetv::getConfiguration('localisation');

		//foreach (eqLogic::byType('orangetv') as $orangetv) {
			if (config::byKey('widget', 'orangetv') == 0) {
				return parent::toHtml($_version);
			}
		//}
		
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		
		$_version = jeedom::versionAlias($_version);
		foreach ($this->getCmd('info') as $inf) {
			
			if ($inf->getName() == 'Etat Decodeur') {
			$etat_decodeur = $inf->getConfiguration('etat_decodeur');
				if ($etat_decodeur == 0 or $etat_decodeur == 1){
					$replace['#etat_decodeur#'] = $etat_decodeur;
				}
			}
			
			if ($inf->getName() == 'Chaine Actuelle') {
			if ($inf->getConfiguration('chaine_actuelle')=='home' OR $inf->getConfiguration('chaine_actuelle')=='vod') {
				$replace['#cmd_chaine_act#'] = $inf->getConfiguration('chaine_actuelle');
			}
			else {
				$replace['#cmd_chaine_act#'] = $this->lecture_json('logo', 'logo', $localisation, $inf->getConfiguration('chaine_actuelle'));
			}
			}
						
		}
		
		foreach ($this->getCmd('action') as $cmd) {
			$replace['#cmd_' . $cmd->getName() . '_id#'] = $cmd->getId();
			$replace['#mos_'.str_replace(' ', '_', $cmd->getName()).'#'] = $cmd->getConfiguration('mosaique_chaine');
			$replace['#mos_'.str_replace(' ', '_', $cmd->getName()).'_logo#'] = $this->lecture_json('logo', 'logo', $localisation, $cmd->getConfiguration('mosaique_chaine'));
			$replace['#mos_'.str_replace(' ', '_', $cmd->getName()).'_nom#'] = $this->lecture_json('nom', 'logo', $localisation, $cmd->getConfiguration('mosaique_chaine'));
			$replace['#mos_'.str_replace(' ', '_', $cmd->getName()).'_id#'] = $cmd->getId();
			$replace['#mos_Telecommande_id#'] = $cmd->getId();
			$tel_mos = $cmd->getConfiguration('telecommande');
		}
		
		if ($tel_mos == 1) {
			$html = template_replace($replace, getTemplate('core', $_version, 'current','orangetv'));
		}
		
		if ($tel_mos == 0) {
			$html = template_replace($replace, getTemplate('core', $_version, 'mosaique','orangetv'));
		}		
		
		return $html;
     }
    

    /*     * **********************Getteur Setteur*************************** */
}

class orangetvCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
		
		$eqLogic = $this->getEqLogic();
		$action_mosaique = preg_match("#Mosaique #", $this->getName());
		$box_ip = $eqLogic->getConfiguration('box_ip');
		$localisation = $eqLogic->getConfiguration('localisation');
		$code_mode = 0;
		
		if ($this->getName() == "Telecommande") {
			
			$act_mos = $this->getConfiguration('telecommande');
			$eqLogic->Telecommande_Mosaique($act_mos);
			
			if ($act_mos == 1) {
				$this->setConfiguration('telecommande', 0);
				$this->save();
				$this->event(0);
			}
			if ($act_mos == 0) {
				$this->setConfiguration('telecommande', 1);
				$this->save();
				$this->event(1);
			}
		}
		
		else {
			
			if ($action_mosaique == 0) {
				if ($this->getName() == "Refresh") {
					log::add('orangetv', 'debug', 'Refresh : '. $this->getName());
					//$eqLogic->ActionInfo($box_ip);
				}
				else {
				$code_touche = $this->getConfiguration('code_touche');
					if ($code_touche != "") {
						log::add('orangetv', 'debug', 'Action executée Jee IP : ' . $box_ip . ' - touche : ' . $code_touche . ' - mode : ' . $code_mode);
						$eqLogic->ActionCommande($box_ip, $code_touche, $code_mode);
					}
					else {
						log::add('orangetv', 'debug', 'Action non executée pour IP ' . $box_ip . ' car code touche vide vérifier paramètres des touches');
					}
				}
			}

		
			if ($action_mosaique == 1) {
				$mos_chaine = $this->getConfiguration('mosaique_chaine');
				$mos_num = $eqLogic->lecture_json('canal', 'logo', $localisation, $mos_chaine);
				$mos_touche = str_split($mos_num);
				
				log::add('orangetv', 'debug', 'Mosaique Chaine : ' . $mos_chaine . ' Numéro de la chaine : '. $mos_num);
				foreach ($mos_touche as $touche) {
					foreach ($eqLogic->getCmd() as $action) {
						if ($touche == $action->getName()) {
							$code_touche = $action->getConfiguration('code_touche');
							if ($code_touche != "") {
								$eqLogic->ActionCommande($box_ip, $code_touche, $code_mode);
							}
							else {
								log::add('orangetv', 'debug', 'Action non executée pour IP ' . $box_ip . ' car code touche vide vérifier paramètres des touches');
							}							
						}
					}
				}
			}
			
			sleep(3);
			$eqLogic->ActionInfo($box_ip);
		}

		return;        
    }

    /*     * **********************Getteur Setteur*************************** */
}