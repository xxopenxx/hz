<?php
namespace Schema;

use Srv\Config;
use Srv\Record;
use ReflectionObject;
use ReflectionProperty;
use JsonSerializable;

class HideoutBattle extends Record implements JsonSerializable{
    protected static $_TABLE = 'hideout_battle';
    
    public function jsonSerialize() {
        return array_merge($this->getData(), get_public_vars($this));
    }
    
    protected static $_FIELDS = [
		"id" => 0,
		"attacker_hideout_id" => 0,
		"defender_hideout_id" => 0,
		"attacker_status" => 0,
		"defender_status" => 0,
		"attacker_count" => 0,
		"attacker_profiles" => "",
		"defender_count" => 0,
		"defender_profiles" => "",
		"hideout_winner_id" => 0,
		"rounds" => "",
		"attacker_rewards" => "",
		"attacker_bonus_rewards" => "",
        "defender_rewards" => "",
        "destroyed_attacker" => 0,
        "destroyed_defender" => 0,
        "attacker_character_name" => "",
        "defender_character_name" => ""
    ];
}