<?php

/*
 * This file is a part of WalkingParticles.
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
namespace WalkingParticles\economy;

use WalkingParticles\WalkingParticles;
use WalkingParticles\base\BaseEconomy;
use WalkingParticles\events\PlayerApplyPackEvent;
use pocketmine\Player;

class Economyapi extends BaseEconomy{

	public function applyPack(Player $player, $pack){
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event = new PlayerApplyPackEvent($this->getPlugin(), $player, $pack, 1, 2));
		if($event->isCancelled()){
			return false;
		}
		$money = $this->getPlugin()->getEco()->getInstance()->myMoney($player);
		if($money < $this->getConfig()->get("apply-pack-fee")){
			$player->sendMessage($this->getPlugin()->colorMessage("&cYou don't have enough money to apply the pack!\n&cYou need " . $this->getConfig()->get("apply-pack-fee")));
			return false;
		}
		if($this->getPlugin()->packExists($pack) !== true){
			$player->sendMessage($this->getPlugin()->colorMessage("&cPack doesn't exist!"));
			return false;
		}
		$this->getPlugin()->getEco()->getInstance()->reduceMoney($player, $this->getPlugin()->getConfig()->get("apply-pack-fee"));
		$this->getPlugin()->activatePack($player, $pack);
		$player->sendMessage($this->getPlugin()->colorMessage("&aYou applied &b" . $pack . " &apack successfully!"));
		$player->sendMessage("Bank : -$" . $this->getConfig()->get("apply-pack-fee") . " | $" . $this->getPlugin()->getEco()->getInstance()->myMoney($player) . " left");
		return true;
	}

	public function tryPlayer(Player $player, Player $player2){
		$money = $this->getPlugin()->getEco()->getInstance()->myMoney($player);
		if($money < $this->getConfig()->get("try-player-fee")){
			$player->sendMessage($this->getPlugin()->colorMessage("&cYou don't have enough money to try the player's WalkingParticles!"));
			return false;
		}
		$this->getPlugin()->getEco()->getInstance()->reduceMoney($player, $this->getPlugin()->getConfig()->get("try-player-fee"));
		$this->getPlugin()->tryPlayerParticle($player, $player2);
		$player->sendMessage("Bank : -$" . $this->getConfig()->get("try-player-fee") . " | $" . $this->getPlugin()->getEco()->getInstance()->myMoney($player) . " left");
	}

}