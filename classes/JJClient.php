<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 1,2,3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * JurassicJungle Client encapsulates all the functionality to access to jurassicjungle.com
 *
 * @author delpho
 */
class JJClient {
	
	const BASE_URL = 'http://www.jurassicjungle.com/';
	
	const BANK_ACTION_DEPOSIT  = 'deposit';
	const BANK_ACTION_WITHDRAW = 'withdraw';
	
	/**
	 * La cookie entregada por www.jurassicjungle.com
	 *
	 * @var string
	 */
	private $cookie_session;
	
	/**
	 * El email del jugador
	 *
	 * @var string
	 */
	private $email;
	
	/**
	 * Las estadisticas del jugador
	 *
	 * @var array
	 */
	private $stats;
	
	/**
	 * Indica si el jugador está entrenando actualmente
	 *
	 * @var bool
	 */
	private $training;
	
	/**
	 * Cliente HTTP
	 *
	 * @var HttpClient
	 */
	private $httpClient;
	
	
	/**
	 * Inicializa el cliente de Jurassic Jungle
	 * 
	 */
	public function __construct() {
		$this->httpClient = new HttpClient();
		$this->httpClient->setHeaders(array(
			'User-Agent: Mozilla/5.0 (Ubuntu; X11; Linux x86_64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1',
			'Host: www.jurassicjungle.com'
		));
		$this->stats = self::emptyStats();
		$this->training = false;
	}
	
	/**
	 * Retorna un arreglo vacío de estadisticas
	 *
	 * @return array
	 */
	private static function emptyStats() {
		return array(
			'personal' => array(
				'email'		=> null,
				'username'	=> null,
				'game_id'	=> null,
				'level'		=> null,
				'exp'		=> null,
				'exp_max'	=> null,
				'hp'		=> null,
				'hp_max'	=> null,
				'energy'	=> null,
				'energy_max'	=> null
			),
			'battle' => array(
				'reset_tokens'	=> null,
				'ap'		=> null,
				'agility'	=> null,
				'strength'	=> null,
				'defense'	=> null,
				'wisdom'	=> null
			),
			'currency' => array(
				'scales'	=> null,				
				'bank'		=> null,
				'rubidium'	=> null,
				'tentralae'	=> null,
				'pearl'		=> null,
				'senquin'	=> null
			),
			'food' => array (
			    'spiked_lemon'	=> null,
			    'spiked_lemon_url'	=> null,
			    'death_sweat'	=> null,
			    'death_sweat_url'	=> null,
			    'ox_blood'		=> null,
			    'ox_blood_url'	=> null,
			    'bones'		=> null,
			    'turns'		=> null	
			)
		);
	}
	
	/**
	 * Retorna la cantidad de scales que tiene el usuario en su bolsillo.
	 *
	 * @return int
	 */
	public function getScales () {
		return (int)$this->stats['currency']['scales'];
	}
	
	/**
	 * Retorna la cantidad de scales que tiene el usuario en el banco.
	 *
	 * @return int
	 */
	public function getBankScales () {
		return (int)$this->stats['currency']['bank'];
	}
	
	/**
	 * Retorna el valor HP actual del usuario
	 *
	 * @return int
	 */
	public function getCurrentHP () {
		return (int)$this->stats['personal']['hp'];
	}
	
	/**
	 * Retorna el valor HP maximo del usuario
	 *
	 * @return int
	 */
	public function getMaxHP () {
		return (int)$this->stats['personal']['hp_max'];
	}
	
	/**
	 * Retorna el valor de energia actual del usuario
	 *
	 * @return int
	 */
	public function getCurrentEnergy () {
		return (int)$this->stats['personal']['energy'];
	}
	
	/**
	 * Retorna el valor de energia maximo del usuario
	 *
	 * @return int
	 */
	public function getMaxEnergy () {
		return (int)$this->stats['personal']['energy_max'];
	}
	
	public function getRubidium () {
		return (int)$this->stats['currency']['rubidium'];
	}
	
	public function getBones () {
		return (int)$this->stats['food']['bones'];
	}
	
	public function getSpikedLemon () {
		return (int)$this->stats['food']['spiked_lemon'];
	}
	
	public function getSpikeLemonConsumeURL () {
		return $this->stats['food']['spiked_lemon_url'];
	}
	
	public function getDeathSweat () {
		return (int)$this->stats['food']['death_sweat'];
	}
	
	public function getDeathSweatConsumeURL () {
		return $this->stats['food']['death_sweat_url'];
	}
	
	public function getOxBlood () {
		return (int)$this->stats['food']['ox_blood'];
	}
	
	public function getOxBloodConsumeURL () {
		return $this->stats['food']['ox_blood_url'];
	}
	
	public function getTurns () {
		return (int)$this->stats['food']['turns'];
	}
	
	/**
	 * Intenta iniciar sesion en www.jurassicjungle.com usando el email y el
	 * password entregados
	 *
	 * @param string $email
	 * @param string $password
	 * @return bool True si se logró iniciar session. False si falló
	 */
	public function login($email, $password) {
		$url = 'http://www.jurassicjungle.com/login.php';
		$params = array(
		    'email' => $email,
		    'pass' => $password
		);
		try{
			$body = $this->httpClient->doPostRequest($url, $params);
			$headers = $this->httpClient->response_header;
			//Log::getInstance()->log($body);
			if ( strpos($body, 'Preloading Game Items - Please Wait') > 0 ) {
				$lines = explode("\n", $headers);
				foreach ($lines as $line ) {
					$pos = strpos ( $line, 'PHPSESSID' );
					$pos2 = strpos( $line, ';' );
					if ( $pos > 0 ) {
						$this->cookie_session = substr( $line, $pos + strlen('PHPSESSID='), $pos2-($pos + strlen('PHPSESSID=')) );
						$this->email = $email;
						$this->httpClient->setCookie('PHPSESSID', $this->cookie_session);
						return true;
					}
				}
			}
		} catch (Exception $ex) {
			Log::getInstance()->logException($ex);
			return false;
		} 
		return false;
	}
	
	/**
	 * Inicializa las estadisticas del usuario y reabastece el inventario
	 *  
	 * @param bool $displayStats True para mostrar las estadisticas del usuarios
	 */
	public function init ($displayStats = false) {
		Log::getInstance()->log("[init] Iniciando cliente...");
		return $this->updateStats($displayStats, true);
	}
	
	/**
	 * Actualiza las estadisticas del usuario
	 *
	 * @param bool $displayStats True para mostrar las estadisticas del usuario 
	 */
	private function updateStats ($displayStats = false , $updateFood = false) {
		//Log::getInstance()->log("[stats] Actualizando estadisticas del usuario");
		try {
			$body = $this->httpClient->doGetRequest('http://www.jurassicjungle.com/stats.php');
			
			/******************************************************/
			/**                     PERSONAL                     **/
			/******************************************************/
			//Email
			$this->stats['personal']['email'] = $this->email;
			
			//Game ID: <b>Game ID:</b> 267910<br>
			$this->stats['personal']['game_id'] = JJUtil::getSubStringValue($body, '<b>Game ID:</b> ', '<br>');
			
			//Username: <a href="view.php?view={game_id}">{username}</b></a>
			$this->stats['personal']['username'] = JJUtil::getSubStringValue($body, '<a href=view.php?view='.$this->stats['personal']['game_id'].'>', '</b>');

			//Level: <b>Level:</b> 32<br>
			$this->stats['personal']['level'] = JJUtil::getSubStringValue($body, '<b>Level:</b> ', '<br>');
			
			//Experience: <b>Experience:</b> 1081/63040 [1%]
			$valor = JJUtil::getSubStringValue($body, '<b>Experience:</b> ', ' [');
			list($cur, $max) = explode("/",trim($valor));
			$this->stats['personal']['exp'] = $cur;
			$this->stats['personal']['exp_max'] = $max;
			
			//HP: <b>HP:</b> 465/465<br>
			$valor = JJUtil::getSubStringValue($body, '<b>HP:</b> ', '<br>');
			list($cur, $max) = explode("/",trim($valor));
			$this->stats['personal']['hp'] = $cur;
			$this->stats['personal']['hp_max'] = $max;
			
			//Energy: <b>Energy:</b> 70/70 [100%]
			$valor = JJUtil::getSubStringValue($body, '<b>Energy:</b> ', ' [');
			list($cur, $max) = explode("/",trim($valor));
			$this->stats['personal']['energy'] = $cur;
			$this->stats['personal']['energy_max'] = $max;
			
			
			/******************************************************/
			/**                      BATTLE                      **/
			/******************************************************/
			//Reset Tokens: <b>Reset Tokens:</b> 0 [
			$this->stats['battle']['reset_tokens'] = JJUtil::getSubStringValue($body, '<b>Reset Tokens:</b> ', '[');
			
			//AP: <b>AP:</b> 0 [
			$this->stats['battle']['ap'] = JJUtil::getSubStringValue($body, '<b>AP:</b> ', '[');

			//Agility: <b>Agility:</b> 47.140<br>
			$this->stats['battle']['agility'] = JJUtil::getSubStringValue($body, '<b>Agility:</b> ', '<br>');
			
			//Strength: <b>Strength:</b> 33.784<br>
			$this->stats['battle']['strength'] = JJUtil::getSubStringValue($body, '<b>Strength:</b> ', '<br>');
			
			//Defense: <b>Defense:</b> 42.422<br>
			$this->stats['battle']['defense'] = JJUtil::getSubStringValue($body, '<b>Defense:</b> ', '<br>');
			
			//Wisdom: <b>Wisdom:</b> 39.257<br>
			$this->stats['battle']['wisdom'] = JJUtil::getSubStringValue($body, '<b>Wisdom:</b> ', '<br>');
			
			
			/******************************************************/
			/**                     CURRENCY                     **/
			/******************************************************/
			//Scales: <b>Scales:</b> 1,084<br>
			$valor = JJUtil::getSubStringValue($body, '<b>Scales:</b> ', '<br>');
			$this->stats['currency']['scales'] = trim(str_replace(',', '', $valor));
			
			//Bank: <b>Bank:</b> 18,256,773<br>
			$valor = JJUtil::getSubStringValue($body, '<b>Bank:</b> ', '<br>');
			$this->stats['currency']['bank'] = trim(str_replace(',', '', $valor));
			
			//Rubidium: <b>Rubidium:</b> 8<br>
			$valor = JJUtil::getSubStringValue($body, '<b>Rubidium:</b> ', '<br>');
			$this->stats['currency']['rubidium'] = trim(str_replace(',', '', $valor));
			
			//Tentralae: <b>Tentralae:</b> 0<br>
			$valor = JJUtil::getSubStringValue($body, '<b>Tentralae:</b> ', '<br>');
			$this->stats['currency']['tentralae'] = trim(str_replace(',', '', $valor));
			
			/*Pearl: <td>Pearl</td>
              <td><div align="right">
                  0                  <img
			*/
			$strposInicio = strpos($body, '<td>Pearl</td>')+strlen('<td>Pearl</td>');
			if ( $strposInicio > 0 ) {
				$strposInicio = strpos($body, '<td><div align="right">',$strposInicio)+strlen('<td><div align="right">');
				$strposFin = strpos($body, '<img', $strposInicio);
				$valor = substr($body, $strposInicio, $strposFin-$strposInicio);
				$this->stats['currency']['pearl'] = trim(str_replace(',', '', $valor));
			}
			/*Senquin: <td>Senquin</td>
              <td><div align="right">
                  0                  <img
			*/
			$strposInicio = strpos($body, '<td>Senquin</td>')+strlen('<td>Senquin</td>');
			if ( $strposInicio > 0 ) {
				$strposInicio = strpos($body, '<td><div align="right">',$strposInicio)+strlen('<td><div align="right">');
				$strposFin = strpos($body, '<img', $strposInicio);
				$valor = substr($body, $strposInicio, $strposFin-$strposInicio);
				$this->stats['currency']['senquin'] = trim(str_replace(',', '', $valor));
			}
			
			/******************************************************/
			/**                       FOOD                       **/
			/******************************************************/
			
			/*Bones: <td>Bones: </td>
              <td><div align="right">8,406 <img
			*/
			$strposInicio = strpos($body, '<td>Bones: </td>')+strlen('<td>Bones: </td>');
			if ( $strposInicio > 0 ) {
				$strposInicio = strpos($body, '<td><div align="right">',$strposInicio)+strlen('<td><div align="right">');
				$strposFin = strpos($body, '<img', $strposInicio);
				$valor = substr($body, $strposInicio, $strposFin-$strposInicio);
				$this->stats['food']['bones'] = trim(str_replace(',', '', $valor));
			}
			
			if ( $updateFood == true ) {
				$food = $this->httpClient->doGetRequest('http://www.jurassicjungle.com/inventory.php');
				$this->stats['food']['spiked_lemon'] = JJUtil::getSubStringValue($food, 'Spiked Lemon</a> (', ')');
				$this->stats['food']['spiked_lemon_url'] = JJUtil::getSubStringValue($food, 'Spiked Lemon</a> ('.$this->stats['food']['spiked_lemon'].') - [<a href=', '>Drink</a>');
				$this->stats['food']['death_sweat'] = JJUtil::getSubStringValue($food, 'Death Sweat</a> (', ')');
				$this->stats['food']['death_sweat_url'] = JJUtil::getSubStringValue($food, 'Death Sweat</a> ('.$this->stats['food']['death_sweat'].') - [<a href=', '>Drink</a>');
				$this->stats['food']['ox_blood'] = JJUtil::getSubStringValue($food, 'Ox Blood</a> (', ')');
				$this->stats['food']['ox_blood_url'] = JJUtil::getSubStringValue($food, 'Ox Blood</a> ('.$this->stats['food']['ox_blood'].') - [<a href=', '>Drink</a>');
				unset($food);
				$excavations = $this->httpClient->doGetRequest('http://www.jurassicjungle.com/excavation.php');
				$this->stats['food']['turns'] = JJUtil::getSubStringValue($excavations, '<td><b>Turns</b>:</td><td>', '</td>');
				unset($excavations);
			}
			
			unset($body);
			if ($displayStats) print_r($this->stats);
			
		} catch (Exception $ex) {
			Log::getInstance()->logException($ex);
		}
		return $this->stats;
	}
	
	/**
	 * Hace que el usuario ingrese a la jungle interna. Se intenta mantener al usuario el mayor tiempo
	 * posible de acuerdo a la cantidad de energia/hp y alimentos/bebidas que el usuario tenga en el inventario
	 */
	public function innerJungle () {
		if (file_exists(LOGS_DIR.'innerjungle.txt')) {
			Log::getInstance()->log("[jungle] El usuario ya está en la jungla");
			return;
		}
	
		touch(LOGS_DIR.'innerjungle.txt');
		
		$this->checkEnergyAndHP();
		$actions = array(
			'wait'		=> 'innerjungle.php?action=explore&do=wait',
			'take'		=> 'innerjungle.php?action=explore&do=take&colourhere=',
			'anger'		=> 'innerjungle.php?action=explore&do=anger',
			'ambush'	=> 'innerjungle.php?action=explore&do=ambush',
			'chase'		=> 'innerjungle.php?action=explore&do=chase',
			'dodge_left'	=> 'innerjungle.php?action=explore&do=left',
			'dodge_right'	=> 'innerjungle.php?action=explore&do=right',
			'fight_1'	=> 'innerjungle.php?action=explore&do=fight&number=1',
			'fight'		=> 'innerjungle.php?action=explore&do=fight',
			'fight_king'	=> 'innerjungle.php?action=explore&do=king',
			'push'		=> 'innerjungle.php?action=explore&do=push',
			'battle'	=> 'innerjungle.php?action=battle',
			'accept'	=> 'innerjungle.php?action=explore&do=yes',
			'attack'	=> 'innerjungle.php?action=explore&do=attack',
			'sneak'		=> 'innerjungle.php?action=explore&do=sneak',
			'sell'		=> 'innerjungle.php?action=explore&do=sell',
			'search'	=> 'innerjungle.php?action=explore&do=search',
			'explore'	=> 'innerjungle.php?action=explore'
		);
		$actionName = 'enter';
		$actionURL  = 'innerjungle.php';
		$scales = rand(500,1000);
		while ( $actionName != null ) {
			try {
				usleep(1000000+rand(0,500000));
				$this->checkEnergyAndHP();
				$body = $this->httpClient->doGetRequest(self::BASE_URL.$actionURL);
				if ( strpos($body, 'You try to explore, but the pain is too great.') > 0 ) {
					Log::getInstance()->log("[jungle][$actionName] Sin HP... saliendo de la jungla interna");
					break;
				} elseif ( strpos($body, 'You feel tired and stop for a rest.') > 0 ) {
					Log::getInstance()->log("[jungle][$actionName] Sin energia... saliendo de la jungla interna");
					break;
				}
				$this->checkEnergyAndHP();
				Log::getInstance()->log("[jungle][$actionName] Energia: {$this->getCurrentEnergy()}/{$this->getMaxEnergy()} ; HP: {$this->getCurrentHP()}/{$this->getMaxHP()} ;  Scales: {$this->getScales()} ;  Rubidium: {$this->getRubidium()}");
				//buscar la siguiente accion disponible
				$found = false;
				foreach ( $actions as $k => $v ) {
					if ( strpos($body, $v) > 0 ) {
						$found = true;
						$actionName = $k;
						$actionURL = $v;
						if ( $actionName == 'take' ) {
							//obtener el color
							$strposInicio = strpos($body, '>Pick ');
							if ( $strposInicio > 0 ) {
								$strposInicio += strlen('>Pick ');
								$strposFin = strpos($body, ' ', $strposInicio);
								$valor = substr($body, $strposInicio, $strposFin-$strposInicio);
								$actionURL .= trim($valor);
							} else {
								$actionName = 'explore';
								$actionURL = $actions[$actionName];
							}
						} if ( $actionName == 'dodge_left' ) {
							if ( rand(0,100) > 50 ) {
								$actionName = 'dodge_right';
								$actionURL = $actions[$actionName];
							}
						}
						break;
					}
				}
				//si no se encontró ninguna accion similar entonces volvemos a explorar
				if ( !$found ) {
					Log::getInstance()->log("[jungle] No se encontró ninguna acción.. entrando nuevamente");
					$actionName = 'enter';
					$actionURL = 'innerjungle.php';
				} else {
					//Log::getInstance()->log("[jungle] Siguiente accion: $actionName");
				}
				unset($body);
			} catch ( Exception $ex ) {
				Log::getInstance()->logException($ex);
				$actionName = null;
			}
			
			//depositamos lo que tenemos en el bolsillo si tenemos mas de 500
			if ( $this->getScales() > $scales ) {
				$this->bank(self::BANK_ACTION_DEPOSIT);
				$scales = rand(500,1000);
			}
		}
		Log::getInstance()->log("[innerjungle] FIN");
		unlink(LOGS_DIR.'innerjungle.txt');
		//depositamos lo que nos quede
		$this->bank(self::BANK_ACTION_DEPOSIT);
	}
	
	
	private function checkEnergyAndHP () {
		$this->updateStats();
		
		//HP
		$loops = ceil($this->getMaxHP() / 100);
		for ( $i = 0 ; $i < $loops ; $i++ ) {
			if ( ($this->getMaxHP() - 50) > $this->getCurrentHP() ) {
				Log::getInstance()->log("[hp] Tomando \"Ox Blood\" (+100hp) ");
				try{
					$this->httpClient->doGetRequest(self::BASE_URL.$this->getOxBloodConsumeURL());
					$this->updateStats();
				} catch (Exception $ex ) {
					Log::getInstance()->logException($ex);
					return false;
				}
			} else break;
		}
		
		//Energia
		if ( $this->getCurrentEnergy() < 15 ) {
			Log::getInstance()->log("[energy] Tomando \"Spiked Lemon\" (+45 energy) ");
			try {
				$this->httpClient->doGetRequest(self::BASE_URL.$this->getSpikeLemonConsumeURL());
				$this->updateStats();
			} catch (Exception $ex ) {
				Log::getInstance()->logException($ex);
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Entrena al usuario en la habilidad que menos puntaje tenga de manera
	 * automatica.
	 */
	public function trainingFacility () {
		$url = 'http://www.jurassicjungle.com/train.php?action=train';
		$train = array(
			'strength'	=> 'strength', 
			'agility'	=> 'agility', 
			'defense'	=> 'defense', 
			'wisdom'	=> 'wizdom'
		);
		//Determinamos que tenemos que entrenar...
		$min = 1000000000.0;
		$minItem = 'strength';
		foreach ( $this->stats['battle'] as $t => $v ) {
			if ( $t == 'ap' || $t == 'reset_tokens' ) continue;
			if ( (float)$v < $min ) {
				$min = (float)$v;
				$minItem = $t;
			}
		}
		$params = array(
			'train' => $train[$minItem],
			'completed' => 'true'	
		);
		$tired = false;
		$gained = 0;
		$times = 0;
		Log::getInstance()->log("[train] Entrenando $minItem ($min) . ");
		while ( !$tired && $times < 15 ) {
			try  {
				$times++;
				usleep(500000+rand(0,500000));
				$body = $this->httpClient->doPostRequest($url, $params);
				//Log::getInstance()->log($body);
				//Gained <b>0.11 Strength</b>
				$tired = (strpos($body, 'You look too tired') > 0);
				if ( !$tired ) {
					$gained += (float)JJUtil::getSubStringValue($body, 'Gained <b>',' ');
				}
			} catch ( Exception $ex ) {
				Log::getInstance()->logException($ex);
				$tired = true;
			}
		}
		Log::getInstance()->log("[train] Fin del entrenamiento, ganado $gained de $minItem");
	}
	
	/**
	 * Realiza un deposito de todas las scales que tiene el usuario o retira
	 * una cantidad de scales especificada
	 *
	 * @param string $action Puede ser JJClient::BANK_ACTION_DEPOSIT para deposito o JJClient::BANK_ACTION_WITHDRAW para retirar dinero
	 * @param int $amount Opcional, monto que se desea retirar del banco
	 * @return bool
	 */
	private function bank ($action, $amount = null) {
		if ( $action == self::BANK_ACTION_DEPOSIT ) {
			if ( $this->getScales() == 0 ) {
				return false;
			}
			$url = 'http://www.jurassicjungle.com/bank.php?action=deposit';
			$params = array(
			    'dep' => $this->getScales()
			);
			Log::getInstance()->log("[bank] Depositando {$this->getScales()} scales");
		} elseif ( $action == self::BANK_ACTION_WITHDRAW ) {
			if ( $amount === null || $amount == 0 ) {
				return false;
			}
			if ( $amount > $this->getBankScales() ) {
				return false;
			}
			$url = 'http://www.jurassicjungle.com/bank.php?action=withdraw';
			$params = array(
			    'with' => $amount
			);
			Log::getInstance()->log("[bank] Retirando $amount scales");
		} else {
			Log::getInstance()->log("[bank] Accion invalida");
			return false;
		}
		usleep(rand(0,500000));
		try {
			$this->httpClient->doPostRequest($url, $params);
		} catch ( Exception $ex ) {
			Log::getInstance()->logException($ex);
			return false;
		}
		Log::getInstance()->log("[bank] Operacion realizada");
		return true;
	}
	
	
	public function food () {
		//huesos destinados para comprar ox blood
		$for_ox_blood = 500;
		
		//spiked lemon
		if ( $this->getBones() > $for_ox_blood ) {
			$real = $this->getBones() - $for_ox_blood;
			$loops = floor($real/1250);
			if ( $loops > 0 ) {
				Log::getInstance()->log("[food] Comprando \"Death Sweat\"");
				$url = 'http://www.jurassicjungle.com/food.php?buy=8';
				$comprados = 0;
				for ( $i = 0 ; $i < $loops ; $i++ ) {
					try{
						usleep(rand(0,500000));
						$this->httpClient->doGetRequest($url);
						$comprados++;
					} catch (Exception $ex ) {
						Log::getInstance()->logException($ex);
					}
				}
				Log::getInstance()->log("[food] $comprados \"Death Sweat\" comprados");
			}
		} else {
			Log::getInstance()->log("[food] No hay huesos suficientes para comprar \"Death Sweat\"");
		}
		
		$this->updateStats(false, true);
		
		//Ox Blood
		if ( $this->getBones() >= 50 ) {
			Log::getInstance()->log("[food] Comprando \"Ox Blood\"");
			$loops = floor($this->getBones() / 50);
			$url = 'http://www.jurassicjungle.com/food.php?buy=6';
			$comprados = 0;
			for ( $i = 0 ; $i < $loops ; $i++ ) {
				try{
					usleep(rand(0,500000));
					$this->httpClient->doGetRequest($url);
					$comprados++;
				} catch (Exception $ex ) {
					Log::getInstance()->logException($ex);
				}
			}
			Log::getInstance()->log("[food] $comprados \"Ox Blood\" comprados");
		} else {
			Log::getInstance()->log("[food] No hay huesos suficientes para comprar \"Ox Blood\"");
		}
		
		$this->updateStats(false, true);
		
		//vender la comida
		//$this->sellDeathSweatFood();
		//$this->sellOxBloodFood();
	}
	
	
	public function sellDeathSweatFood () {
		$death_sweat = $this->getDeathSweat();
		$death_sweat_url = $this->getDeathSweatConsumeURL();
		//get the consumer id
		$id = substr($death_sweat_url, strpos($death_sweat_url, "="));
		$url = "http://www.jurassicjungle.com/food.php?sell$id";
		$i = 0;
		Log::getInstance()->log("[food] Vendiendo Death Sweat ($url)");
		while ( $i < $death_sweat ) {
			$this->httpClient->doGetRequest($url);
			if ( ($i%20) == 0 ) {
				$this->bank(self::BANK_ACTION_DEPOSIT);
			}
			$i++;
			usleep(rand(0,100000));
		}
		Log::getInstance()->log("[food] $i Death Sweat vendidas");
		$this->bank(self::BANK_ACTION_DEPOSIT);
	}
	
	public function sellOxBloodFood () {
		$food = $this->getOxBlood();
		$food_url = $this->getOxBloodConsumeURL();
		//get the consumer id
		$id = substr($food_url, strpos($food_url, "="));
		$url = "http://www.jurassicjungle.com/food.php?sell$id";
		$i = 0;
		Log::getInstance()->log("[food] Vendiendo Ox Blood ($url)");
		while ( $i < $food ) {
			$this->httpClient->doGetRequest($url);
			if ( ($i%20) == 0 ) {
				$this->bank(self::BANK_ACTION_DEPOSIT);
			}
			$i++;
			usleep(rand(0,100000));
		}
		Log::getInstance()->log("[food] $i Ox Blood vendidas");
		$this->bank(self::BANK_ACTION_DEPOSIT);
	}
	
	public function excavations () {
		$this->updateStats(false, true);
		if ( $this->getTurns() > 0 ) {
			Log::getInstance()->log("[excavations] Atacando excavaciones!");
			$attacked = 0;
			$bones = 0;	
			try {
				$page = rand(50,150);
				$body = $this->httpClient->doGetRequest('http://www.jurassicjungle.com/excavation.php?view=listing&pageindex='.$page);
				while ( $attacked < $this->getTurns() ) {
					$oid = JJUtil::getSubStringValue($body, '<a href=excavation.php?view=battle&action=battle&oid=', '>Attack!</a>');
					$attack = $this->httpClient->doGetRequest(self::BASE_URL.'excavation.php?view=battle&action=battle&oid='.$oid);
					$attacked++;
					if (strpos($attack, 'You do not have enough turns.') ) {
						$this->stats['food']['turns'] = 0;
						break;
					} else {
						$bones += (int)JJUtil::getSubStringValue($attack, 'You have stolen <img src=images/D.gif> <b>', '</b> Bones');
					}
					$body = substr($body, strpos($body, 'excavation.php?view=battle&action=battle&oid='.$oid));
				}
			} catch ( Exception $ex ) {
				Log::getInstance()->logException($ex);
			}
			Log::getInstance()->log("[excavations] $attacked excavaciones atacadas, $bones huesos capturados!");
		} else {
			Log::getInstance()->log("[excavations] No hay turnos disponibles");
		}
		$this->updateStats(false, true);
		$this->food();
	}
	
	
	public function quarters () {
		$rubidium = $this->getRubidium();
		if ( $rubidium > 0 ) {
			//but a stone
			try {
				$this->httpClient->doGetRequest(self::BASE_URL.'game_quarters.php?step=raises');
			} catch ( Exception $ex ) {
				Log::getInstance()->logException($ex);
			}			
		}
	}
}
