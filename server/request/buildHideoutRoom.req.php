<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\GameSettings;

error_reporting(0);

class buildHideoutRoom{
    
    public function __request($player){

		$identifier = getField('identifier', FIELD_IDENTIFIER);
		$level = getField('level', FIELD_NUM);
		$slot = getField('slot', FIELD_NUM);
		//return Core::setError('failed');
		if(!$player->hideout)
			return Core::setError('failed');
		
		if(!$identifier)
			return Core::setError('failed');

		//$lvl = $level > 0 ? $level : 1;
		$room = HideoutRoom::find(function($q)use($identifier, $player) { $q->where('hideout_id', $player->hideout->id)->where('identifier', $identifier); });
		$lvl = $room ? $room->level : 1;
		
		$rooms = GameSettings::getConstant("hideout_rooms");
		$id = $rooms[$identifier];
		$levels = $id['levels'][$lvl];
		//return Core::setError('failed '.$lvl.'');
		if($player->hideout->current_resource_stone < $levels['price_stone'])
			return Core::setError('failed');
		if($player->hideout->current_resource_glue < $levels['price_glue'])
			return Core::setError('failed');		
		if($player->character->game_currency < $levels['price_gold'])
			return Core::setError('failed');			
		
		$time = time() + 60;
		$timeAdd = $time + $levels['build_time'];
		
		$player->hideout->current_resource_stone -= $levels['price_stone'];
		$player->hideout->current_resource_glue -= $levels['price_glue'];
		$player->character->game_currency -= $levels['price_gold'];
		
        //Create hideout
		$hideout_room = new HideoutRoom([
			"id" => 0,
			"hideout_id" => $player->hideout->id,
			"ts_creation" => $time,
			"identifier" => $identifier,
			"status" => 2,
			"level" => $lvl,
			//"current_resource_amount" => $levels['min_till_max_resource'],
			"max_resource_amount" => $levels['min_till_max_resource'],
			"ts_last_resource_change" => 0,
			"ts_activity_end" => $timeAdd,
			"current_generator_level" => 0,
			"additional_value_1" => 0,
			"additional_value_2" => ""
        ]);
        $hideout_room->save();
        $player->hideout_room = $hideout_room;

		if($rooms[$identifier]['size'] == 1){
			$zm="room_slot_{$level}_{$slot}";
			$player->hideout->$zm = $player->hideout_room->id;
			Core::req()->data = [
				'user'=>$player->user,
				'character'=>$player->character,
					"hideout" => [
					"id" => $player->hideout->id,
					"idle_worker_count" => $player->hideout->idle_worker_count,
					"current_resource_glue" => $player->hideout->current_resource_glue,
					"current_resource_stone" => $player->hideout->current_resource_stone,
					"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh,		
					"room_slot_{$level}_{$slot}" => $hideout_room->id,
					],
					"hideout_room" => [
					"id" => $hideout_room->id,
					"hideout_id" => $hideout_room->hideout_id,
					"ts_creation" => $hideout_room->ts_creation,
					"identifier" => $hideout_room->identifier,
					"status" => $hideout_room->status,
					"level" => $hideout_room->level,
					"current_resource_amount" => $hideout_room->current_resource_amount,
					"max_resource_amount" => $hideout_room->max_resource_amount,
					"ts_last_resource_change" => $hideout_room->ts_last_resource_change,
					"ts_activity_end" => $hideout_room->ts_activity_end,
					"current_generator_level" => $hideout_room->current_generator_level,
					"additional_value_1" => $hideout_room->additional_value_1,
					"additional_value_2" => $hideout_room->additional_value_2
					]
			];			
		}
		
		if($rooms[$identifier]['size'] == 2){
			
			$slot_plus_1 = $slot + 1;
			
			$zm="room_slot_{$level}_{$slot}";
			$player->hideout->$zm = $player->hideout_room->id;
			
			$zm2="room_slot_{$level}_{$slot_plus_1}";
			$player->hideout->$zm2 = $player->hideout_room->id;
			
			Core::req()->data = [
				'user'=>$player->user,
				'character'=>$player->character,
					"hideout" => [
					"id" => $player->hideout->id,
					"idle_worker_count" => $player->hideout->idle_worker_count,
					"current_resource_glue" => $player->hideout->current_resource_glue,
					"current_resource_stone" => $player->hideout->current_resource_stone,
					"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh,		
					"room_slot_{$level}_{$slot}" => $hideout_room->id,
					"room_slot_{$level}_{$slot_plus_1}" => $hideout_room->id
					],
					"hideout_room" => [
					"id" => $hideout_room->id,
					"hideout_id" => $hideout_room->hideout_id,
					"ts_creation" => $hideout_room->ts_creation,
					"identifier" => $hideout_room->identifier,
					"status" => $hideout_room->status,
					"level" => $hideout_room->level,
					"current_resource_amount" => $hideout_room->current_resource_amount,
					"max_resource_amount" => $hideout_room->max_resource_amount,
					"ts_last_resource_change" => $hideout_room->ts_last_resource_change,
					"ts_activity_end" => $hideout_room->ts_activity_end,
					"current_generator_level" => $hideout_room->current_generator_level,
					"additional_value_1" => $hideout_room->additional_value_1,
					"additional_value_2" => $hideout_room->additional_value_2
					]
			];			
		}
		
		if($rooms[$identifier]['size'] == 3){			
			$slot_plus_1 = $slot + 1;
			$slot_plus_2 = $slot + 2;
			
			$zm="room_slot_{$level}_{$slot}";
			$player->hideout->$zm = $player->hideout_room->id;
			
			$zm2="room_slot_{$level}_{$slot_plus_1}";
			$player->hideout->$zm2 = $player->hideout_room->id;
			
			$zm3="room_slot_{$level}_{$slot_plus_2}";
			$player->hideout->$zm3 = $player->hideout_room->id;
			
			Core::req()->data = [
				'user'=>$player->user,
				'character'=>$player->character,
					"hideout" => [
					"id" => $player->hideout->id,
					"idle_worker_count" => $player->hideout->idle_worker_count,
					"current_resource_glue" => $player->hideout->current_resource_glue,
					"current_resource_stone" => $player->hideout->current_resource_stone,
					"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh,		
					"room_slot_{$level}_{$slot}" => $hideout_room->id,
					"room_slot_{$level}_{$slot_plus_1}" => $hideout_room->id,
					"room_slot_{$level}_{$slot_plus_2}" => $hideout_room->id
					],
					"hideout_room" => [
					"id" => $hideout_room->id,
					"hideout_id" => $hideout_room->hideout_id,
					"ts_creation" => $hideout_room->ts_creation,
					"identifier" => $hideout_room->identifier,
					"status" => $hideout_room->status,
					"level" => $hideout_room->level,
					"current_resource_amount" => $hideout_room->current_resource_amount,
					"max_resource_amount" => $hideout_room->max_resource_amount,
					"ts_last_resource_change" => $hideout_room->ts_last_resource_change,
					"ts_activity_end" => $hideout_room->ts_activity_end,
					"current_generator_level" => $hideout_room->current_generator_level,
					"additional_value_1" => $hideout_room->additional_value_1,
					"additional_value_2" => $hideout_room->additional_value_2
					]
			];			
		}		
		
    }
}