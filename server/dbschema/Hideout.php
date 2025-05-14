<?php
namespace Schema;

use Srv\Config;
use Srv\Record;
use ReflectionObject;
use ReflectionProperty;
use JsonSerializable;

class Hideout extends Record implements JsonSerializable{
    protected static $_TABLE = 'hideout';
    
    public function jsonSerialize() {
        return array_merge($this->getData(), get_public_vars($this));
    }
    
    protected static $_FIELDS = [
		"id" => 0,
		"character_id" => 0,
		"hideout_points" => 0,
		"current_level" => 0,
		"idle_worker_count" => 1,
		"max_worker_count" => 1,
		"ts_time_worker_expires" => 0,
		"current_resource_glue" => 10,
		"max_resource_glue" => 10,
		"current_resource_stone" => 10,
		"max_resource_stone" => 10,
		"current_attacker_units" => 0,
		"max_attacker_units" => 0,
		"current_defender_units" => 0,
		"max_defender_units" => 0,
		"current_opponent_id" => 0,
		"current_opponent_chest_reward" => 0,
		"ts_last_opponent_refresh" => 0,
		"last_opponent_ids" => "",
		"active_battle_id" => 0,
		"ts_last_lost_attack" => 0,
		"current_worker_level" => 0,
		"current_wall_level" => 0,
		"current_barracks_level" => 0,
		"max_barracks_level" => 0,
		"current_gem_production_level" => 0,
		"current_broker_level" => 0,
		"current_robot_storage_level" => 0,
		"room_slot_0_0" => 0,
		"room_slot_0_1" => 0,
		"room_slot_0_2" => 0,
		"room_slot_0_3" => 0,
		"room_slot_0_4" => 0,
		"room_slot_1_0" => 0,
		"room_slot_1_1" => 0,
		"room_slot_1_2" => 0,
		"room_slot_1_3" => 0,
		"room_slot_1_4" => 0,
		"room_slot_2_0" => -1,
		"room_slot_2_1" => -1,
		"room_slot_2_2" => -1,
		"room_slot_2_3" => -1,
		"room_slot_2_4" => -1,
		"room_slot_3_0" => -1,
		"room_slot_3_1" => -1,
		"room_slot_3_2" => -1,
		"room_slot_3_3" => -1,
		"room_slot_3_4" => -1,
		"room_slot_4_0" => -1,
		"room_slot_4_1" => -1,
		"room_slot_4_2" => -1,
		"room_slot_4_3" => -1,
		"room_slot_4_4" => -1,
		"room_slot_5_0" => -1,
		"room_slot_5_1" => -1,
		"room_slot_5_2" => -1,
		"room_slot_5_3" => -1,
		"room_slot_5_4" => -1,
		"room_slot_6_0" => -1,
		"room_slot_6_1" => -1,
		"room_slot_6_2" => -1,
		"room_slot_6_3" => -1,
		"room_slot_6_4" => -1,
		"room_slot_7_0" => -1,
		"room_slot_7_1" => -1,
		"room_slot_7_2" => -1,
		"room_slot_7_3" => -1,
		"room_slot_7_4" => -1,
		"room_slot_8_0" => -1,
		"room_slot_8_1" => -1,
		"room_slot_8_2" => -1,
		"room_slot_8_3" => -1,
		"room_slot_8_4" => -1
    ];
}