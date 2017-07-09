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
namespace WalkingParticles\events;

use pocketmine\Player;
use pocketmine\event\Cancellable;
use WalkingParticles\WalkingParticles;
use WalkingParticles\base\BaseEvent;

class PlayerApplyPackEvent extends BaseEvent implements Cancellable{

	const METHOD_ADMIN = 0;

	const METHOD_PURCHASE = 1;

	const ECONOMY_ECONOMYS = 2;

	const ECONOMY_POCKETMONEY = 3;

	const ECONOMY_MASSIVEECONOMY = 4;

	const ECONOMY_GOLDSTD = 5;

	public static $handlerList = null;

	private $player;

	private $pack;

	private $method;

	private $eco;

	public function __construct(WalkingParticles $plugin, Player $player, $pack, $method, $eco){
		$this->player = $player;
		$this->pack = $pack;
		$this->method = $method;
		$this->eco = $eco;
		parent::__construct($plugin);
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getPack(){
		return $this->pack;
	}

	public function getMethodID(){
		return (int) $this->method;
	}

	public function getEcoID(){
		return (int) $this->eco;
	}

}
?>