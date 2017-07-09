<?php

/*
 * This file is the main class of WalkingParticles.
 * Copyright (C) 2017 Ztech Network
 *
 * WalkingParticles is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WalkingParticles is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WalkingParticles. If not, see <http://www.gnu.org/licenses/>.
 */
namespace WalkingParticles;

use WalkingParticles\events\PlayerAddWPEvent;
use WalkingParticles\events\PlayerClearWPEvent;
use WalkingParticles\events\PlayerRemoveWPEvent;
use WalkingParticles\events\PlayerSetAmplifierEvent;
use WalkingParticles\events\PlayerSetDisplayEvent;
use WalkingParticles\events\PlayerSetWPEvent;
use WalkingParticles\events\PlayerSwitchRandommodeEvent;
use WalkingParticles\events\PlayerTryPlayerParticleEvent;
use WalkingParticles\events\PlayerApplyPackEvent;
use WalkingParticles\events\PlayerUsePlayerParticlesEvent;
use WalkingParticles\events\PlayerSwitchItemmodeEvent;
use WalkingParticles\listeners\PlayerListener;
use WalkingParticles\listeners\SignListener;
use WalkingParticles\task\ParticleShowTask;
use WalkingParticles\task\RandomModeTask;
use WalkingParticles\task\TryParticleTask;
use WalkingParticles\Particles;
use WalkingParticles\commands\WponCommand;
use WalkingParticles\commands\WpoffCommand;
use WalkingParticles\commands\WprandCommand;
use WalkingParticles\commands\WpitemCommand;
use WalkingParticles\commands\WppackCommand;
use WalkingParticles\commands\WplistCommand;
use WalkingParticles\commands\WpgetCommand;
use WalkingParticles\commands\WptryCommand;
use WalkingParticles\commands\AdminCommand;
use WalkingParticles\events\PlayerEffectsEnableEvent;
use WalkingParticles\events\PlayerEffectsDisableEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\item\ItemBlock;

class WalkingParticles extends PluginBase{

	const VERSION = "1.0.0";

	/**
	 *
	 * @var static $this|null
	 */
	private static $instance = null;

	/**
	 *
	 * @var EconomyAPI|PocketMoney|MassiveEconony|GoldStd|null
	 */
	private $eco = null;

	/**
	 *
	 * @var string
	 */
	public $random_mode = [];

	/**
	 *
	 * @var string
	 */
	public $item_mode = [];

	public function onEnable(){
		$this->getLogger()->info("Loading resources..");
		if(! is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->data = new Config($this->getDataFolder() . "players.yml", Config::YAML, array());
		$this->data2 = new Config($this->getDataFolder() . "particlepacks.yml", Config::YAML, array(
				"default1" => array(
						"block_90",
						"block_7",
						"block_8",
						"block_10"
				),
				"default2" => array(
						"item_264",
						"item_265",
						"item_266",
						"item_267"
				),
				"default3" => array(
						"largeexplode",
						"lava",
						"angryvillager"
				)
		));
		$this->data3 = new Config($this->getDataFolder() . "temp1.yml", Config::YAML, array());
		$this->updateConfig();
		if(! file_exists($this->getServer()->getDataPath() . "plugins/WalkingParticles_v" . self::VERSION . ".phar")){
			if(is_dir($this->getServer()->getDataPath() . "plugins/WalkingParticles-master")){
				$this->getLogger()->notice($this->colorMessage("Non-packaged WalkingParticles detected running on the server!"));
				$this->getLogger()->notice($this->colorMessage("Packaged(phar) is recommended to be used for production servers!"));
			} else{
				$this->getLogger()->notice($this->colorMessage("Non-official WalkingParticles package found using on this server!"));
				$this->getLogger()->notice($this->colorMessage("Please use WalkingParticles file downloaded in Github or PocketMine!"));
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return;
			}
		}
		$this->getLogger()->info("Loading economy plugins..");
		$plugins = [
				"EconomyAPI",
				"PocketMoney",
				"MassiveEconomy",
				"GoldStd"
		];
		foreach($plugins as $plugin){
			$pl = $this->getServer()->getPluginManager()->getPlugin($plugin);
			if($pl !== null){
				$this->eco = $pl;
				$this->getLogger()->info("Loaded with " . $plugin . "!");
			}
		}
		if($this->eco === null){
			$this->getLogger()->info("No economy plugin found!");
		}
		$this->getLogger()->info("Loading plugin..");
		$this->VanishNoPacket = $this->getServer()->getPluginManager()->getPlugin("VanishNP");
		if($this->VanishNoPacket !== null){
			$this->getLogger()->info("Loaded with VanishNoPacket!");
		}
		self::$instance = $this;
		$this->particles = new Particles($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new ParticleShowTask($this), 13);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RandomModeTask($this), 10);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new SignListener($this), $this);
		$this->getCommand("wprand")->setExecutor(new WprandCommand($this));
		$this->getCommand("wpitem")->setExecutor(new WpitemCommand($this));
		$this->getCommand("wppack")->setExecutor(new WppackCommand($this));
		$this->getCommand("wplist")->setExecutor(new WplistCommand($this));
		$this->getCommand("wpget")->setExecutor(new WpgetCommand($this));
		$this->getCommand("wptry")->setExecutor(new WptryCommand($this));
		$this->getCommand("wpon")->setExecutor(new WponCommand($this));
		$this->getCommand("wpoff")->setExecutor(new WpoffCommand($this));
		$this->getCommand("walkingparticles")->setExecutor(new AdminCommand($this));
		$this->getLogger()->info($this->colorMessage("&aLoaded Successfully!"));
	}

	private function pluginLoaded($plugin){
		return (bool) $plugin !== null;
	}

	private function updateConfig(){
		$this->getLogger()->info("Checking config file..");
		if($this->getConfig()->exists("v") !== true || $this->getConfig()->get("v") != self::VERSION){
			$this->getLogger()->info("Update found!  Updating configuration...");
			unlink($this->getDataFolder() . "config.yml");
			$this->saveDefaultConfig();
			$this->reloadConfig();
		}
	}

	public static function getInstance(){
		return self::instance;
	}

	public function getEco(){
		return $this->eco;
	}
	
	// For external use
	public function getData($file = "data"){
		switch(strtolower($file)):
			case "data":
				return $this->data;
			case "data2":
			case "particlepacks":
				return $this->data2;
			case "data3":
			case "temp":
				return $this->data3;
		endswitch
		;
		return false;
	}

	public function getParticles(){
		$particles = new Particles($this);
		return $particles;
	}

	public function colorMessage($message){
		return (string) str_replace("&", "§", $message);
	}

	/**
	 *
	 * @param Player $player        	
	 * @param Player $player2        	
	 *
	 * @return boolean
	 */
	public function tryPlayerParticle(Player $player, Player $player2){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerTryPlayerParticleEvent($this, $player, $player2));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$this->putTemp($player);
		$this->clearPlayerParticle($player);
		foreach($t[$player2->getName()]["particle"] as $pc){
			$this->addPlayerParticle($player, $pc);
		}
		$this->getServer()->getScheduler()->scheduleDelayedTask(new TryParticleTask($this, $player), 20 * 10);
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @param Player $player2        	
	 *
	 * @return boolean
	 */
	public function usePlayerParticles(Player $player, Player $player2){
		$t = $this->data->getAll();
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerUsePlayerParticlesEvent($this, $player, $player2));
		if($event->isCancelled()){
			return false;
		}
		$this->clearPlayerParticle($player);
		foreach($t[$player2->getName()]["particle"] as $pc){
			$this->addPlayerParticle($player, $pc);
		}
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function playerTempExists(Player $player){
		$temp = $this->data3->getAll();
		return isset($temp[$player->getName()]);
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function putTemp(Player $player){
		if($this->isCleared($player) !== true){
			$t = $this->data->getAll();
			$temp = $this->data3->getAll();
			foreach($t[$player->getName()]["particle"] as $pc){
				$temp[$player->getName()][] = $pc;
			}
			$this->data3->setAll($temp, $pc);
			$this->data3->save();
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function byeTemp(Player $player){
		$temp = $this->data3->getAll();
		if($this->playerTempExists($player) !== false){
			$this->clearPlayerParticle($player);
			foreach($temp[$player->getName()] as $pc){
				$this->addPlayerParticle($player, $pc);
			}
			unset($temp[$player->getName()]);
			$this->data3->setAll($temp);
			$this->data3->save();
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param Player $player        	
	 * @param string $particle        	
	 *
	 * @return boolean
	 */
	public function setPlayerParticle(Player $player, $particle){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerSetWPEvent($this, $player, $particle));
		if($event->isCancelled()){
			return false;
		}
		$this->clearPlayerParticle($player);
		$this->addPlayerParticle($player, $particle);
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @param string $particle        	
	 *
	 * @return boolean
	 */
	public function addPlayerParticle(Player $player, $particle){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerAddWPEvent($this, $player, $particle));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$t[$player->getName()]["particle"][] = $particle;
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @param string $particle        	
	 *
	 * @return boolean
	 */
	public function removePlayerParticle(Player $player, $particle){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerRemoveWPEvent($this, $player, $particle));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$p = array_search($particle, $t[$player->getName()]["particle"]);
		unset($t[$player->getName()]["particle"][$p]);
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function clearPlayerParticle(Player $player){
		$t = $this->data->getAll();
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerClearWPEvent($this, $player, $t[$player->getName()]["particle"]));
		if($event->isCancelled()){
			return false;
		}
		foreach($t[$player->getName()]["particle"] as $p){
			$pa = array_search($p, $t[$player->getName()]["particle"]);
			unset($t[$player->getName()]["particle"][$pa]);
		}
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return string
	 */
	public function getAllPlayerParticles(Player $player){
		$t = $this->data->getAll();
		$particles = $t[$player->getName()]["particle"];
		$p = "";
		foreach($particles as $ps){
			$p .= $ps . ", ";
		}
		return (string) substr($p, 0, - 2);
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function isCleared(Player $player){
		$t = $this->data->getAll();
		$array = $t[$player->getName()]["particle"];
		return (bool) count($array) < 1;
	}

	/**
	 *
	 * @param Player $player        	
	 * @return boolean
	 */
	public function enableEffects(Player $player){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerEffectsEnableEvent($this, $player));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$t[$player->getName()]["enabled"] = true;
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @return boolean
	 */
	public function disableEffects(Player $player){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerEffectsDisableEvent($this, $player));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$t[$player->getName()]["enabled"] = false;
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @return boolean
	 */
	public function isEffectsEnabled(Player $player){
		$t = $this->data->getAll();
		return (bool) $t[$player->getName()]["enabled"];
	}

	/**
	 *
	 * @param Player $player        	
	 * @param int $amplifier        	
	 *
	 * @return boolean
	 */
	public function setPlayerAmplifier(Player $player, $amplifier){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerSetAmplifierEvent($this, $player, $amplifier));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$t[$player->getName()]["amplifier"] = $amplifier;
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return integer
	 */
	public function getPlayerAmplifier(Player $player){
		$t = $this->data->getAll();
		return $t[$player->getName()]["amplifier"];
	}

	/**
	 *
	 * @param Player $player        	
	 * @param string $display        	
	 *
	 * @return boolean
	 */
	public function setPlayerDisplay(Player $player, $display){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerSetDisplayEvent($this, $player, $display));
		if($event->isCancelled()){
			return false;
		}
		$t = $this->data->getAll();
		$t[$player->getName()]["display"] = $display;
		$this->data->setAll($t);
		$this->data->save();
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return string
	 */
	public function getPlayerDisplay(Player $player){
		$t = $this->data->getAll();
		return $t[$player->getName()]["display"];
	}

	/*
	 * Packs
	 * API Part
	 */
	
	/**
	 *
	 * @param Player $player        	
	 * @param string $pack_name        	
	 *
	 * @return boolean
	 */
	public function activatePack(Player $player, $pack_name){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerApplyPackEvent($this, $player, $pack_name, 0, null));
		if($event->isCancelled()){
			return false;
		}
		$p = $this->data2->getAll();
		$this->clearPlayerParticle($player);
		foreach($p[$pack_name] as $pc){
			$this->addPlayerParticle($player, $pc);
		}
		return true;
	}

	/**
	 *
	 * @param string $pack_name        	
	 */
	public function createPack($pack_name){
		$p = $this->data2->getAll();
		$p[$pack_name][] = "";
		$this->data2->setAll($p);
		$this->data2->save();
	}

	/**
	 *
	 * @param string $pack_name        	
	 * @param string $particle        	
	 */
	public function addParticleToPack($pack_name, $particle){
		$p = $this->data2->getAll();
		$pa = array_search("", $p[$pack_name]);
		unset($p[$pack_name][$pa]);
		$p[$pack_name][] = $particle;
		$this->data2->setAll($p);
		$this->data2->save();
	}

	/**
	 *
	 * @param string $pack_name        	
	 *
	 * @return string
	 */
	public function getPack($pack_name){
		$p = $this->data2->getAll();
		return $p[$pack_name];
	}

	/**
	 *
	 * @param string $pack_name        	
	 */
	public function deletePack($pack_name){
		$p = $this->data2->getAll();
		unset($p[$pack_name]);
		$this->data2->setAll($p);
		$this->data2->save();
	}

	/**
	 *
	 * @param string $pack_name        	
	 *
	 * @return boolean
	 */
	public function packExists($pack_name){
		$p = $this->data2->getAll();
		return isset($p[$pack_name]);
	}

	/**
	 *
	 * @param string $pack_name        	
	 *
	 * @return string
	 */
	public function getPackParticles($pack_name){
		$p = $this->data2->getAll();
		$msg = "";
		foreach($p[$pack_name] as $ps){
			$msg .= $ps . ", ";
		}
		return (string) substr($msg, 0, - 2);
	}

	/**
	 *
	 * @return string
	 */
	public function listPacks(){
		$p = $this->data2->getAll();
		$array = array_keys($p);
		$msg = "";
		foreach($array as $pack_names){
			$msg .= $pack_names . ", ";
		}
		return (string) substr($msg, 0, - 2);
		;
	}

	/*
	 * RANDOM MODE
	 * API PART
	 */
	
	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function changeParticle(Player $player){
		$this->clearPlayerParticle($player);
		$this->addPlayerParticle($player, $this->particles->getRandomParticle());
		$this->addPlayerParticle($player, $this->particles->getRandomParticle());
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 * @param string $value        	
	 *
	 * @return boolean
	 */
	public function switchRandomMode(Player $player, $value = true){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerSwitchRandommodeEvent($this, $player, $value));
		if($event->isCancelled()){
			return false;
		}
		switch($value):
			case true:
				$this->random_mode[$player->getName()] = $player->getName();
				$this->putTemp($player);
			break;
			case false:
				unset($this->random_mode[$player->getName()]);
				$this->byeTemp($player);
			break;
		endswitch
		;
		return true;
	}

	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function isRandomMode(Player $player){
		return in_array($player->getName(), $this->random_mode);
	}

	/*
	 * ITEM MODE
	 * API PART
	 */
	
	/**
	 *
	 * @param Player $player        	
	 *
	 * @return boolean
	 */
	public function switchItemMode(Player $player, $value = true){
		$this->getServer()->getPluginManager()->callEvent($event = new PlayerSwitchItemmodeEvent($this, $player, $value));
		if($event->isCancelled()){
			return false;
		}
		if($value !== false){
			$this->item_mode[$player->getName()] = $player->getName();
			$this->putTemp($player);
			if($player->getInventory()->getItemInHand() instanceof ItemBlock){
				if((string) $player->getInventory()->getItemInHand()->getId() == "0"){
					$this->setPlayerParticle($player, "unknown_item");
					return true;
				} else{
					$this->setPlayerParticle($player, "block_" . $player->getInventory()->getItemInHand()->getId());
					return true;
				}
			} else{
				$this->setPlayerParticle($player, "item_" . $player->getInventory()->getItemInHand()->getId());
				return true;
			}
		} else if($value !== true){
			unset($this->item_mode[$player->getName()]);
			$this->byeTemp($player);
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param Player $player        	
	 * @return boolean
	 */
	public function isItemMode(Player $player){
		return in_array($player->getName(), $this->item_mode);
	}

}
?>