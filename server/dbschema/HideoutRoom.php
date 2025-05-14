<?php
namespace Schema;

use Srv\Config;
use Srv\Record;
use ReflectionObject;
use ReflectionProperty;
use JsonSerializable;

class HideoutRoom extends Record implements JsonSerializable{
    protected static $_TABLE = 'hideout_room';
    
    public function jsonSerialize() {
        return array_merge($this->getData(), get_public_vars($this));
    }
    
    protected static $_FIELDS = [
		"id" => 0,
		"hideout_id" => 0,
		"ts_creation" => 0,
		"identifier" => "",
		"status" => 2,
		"level" => 1,
		"current_resource_amount" => 0,
		"max_resource_amount" => 0,
		"ts_last_resource_change" => 0,
		"ts_activity_end" => 0,
		"current_generator_level" => 0,
		"additional_value_1" => 0,
		"additional_value_2" => ""
    ];
}