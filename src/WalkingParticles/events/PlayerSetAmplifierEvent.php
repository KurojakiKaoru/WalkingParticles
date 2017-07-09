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

use WalkingParticles\events;
use pocketmine\Player;
use pocketmine\event\Cancellable;
use WalkingParticles\WalkingParticles;
use WalkingParticles\base\BaseEvent;

class PlayerSetAmplifierEvent extends BaseEvent implements Cancellable{

	public static $handlerList = null;

	private $player;

	private $amplifier_value;

	public function __construct(WalkingParticles $plugin, Player $player, $amplifier_value){
		$this->player = $player;
		$this->amplifier_value = $amplifier_value;
		parent::__construct($plugin);
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getAmplifier(){
		return $this->amplifier_value;
	}

}
?>