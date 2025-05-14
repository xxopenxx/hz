<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\GameSettings;

error_reporting(0);

class checkHideoutRoomActivityFinished{
    
    public function __request($player, $skip = 0){

		$hideout_room_id = getField('hideout_room_id', FIELD_NUM);
		$hideout_room = HideoutRoom::find(function($q) use($hideout_room_id, $player) { $q->where('id', $hideout_room_id)->where('hideout_id', $player->hideout->id); });
		
		if(!$player->hideout)
			return Core::setError('failed');
		
		if(!$hideout_room)
			return Core::setError('failed');	
		
		
		$rooms = GameSettings::getConstant("hideout_rooms");
		$id = $rooms[$hideout_room->identifier];
		
		if($hideout_room->ts_activity_end <= time() || $skip == 1){
		
		if($hideout_room->status == 3){
		
			$hideout_room->level += 1;
			
			if($hideout_room->identifier == "main_building"){
			$hideout_room->status = 6;
			$levels = $id['levels'][$hideout_room->level];
			$player->hideout->max_resource_glue = $levels['passiv_bonus_amount_1'];
			$player->hideout->max_resource_stone = $levels['passiv_bonus_amount_2'];
			}
			
			if($id['type'] == "resource_production"){
			$hideout_room->status = 6;
			$hideout_room->current_resource_amount = $levels['min_till_max_resource'];
			}
			
			if($id['type'] == "battle"){
			$hideout_room->status = 1;
			$player->hideout->max_attacker_units = $levels['resource_production_max'];
			$hideout_room->max_resource_amount = $levels['resource_production_max'];
			}
			
		}

		if($hideout_room->status == 2){

			$levels = $id['levels'][$hideout_room->level];
		
			if($id['type'] == "main_building"){
			$hideout_room->status = 6;
			$player->hideout->max_resource_glue = $levels['passiv_bonus_amount_1'];
			$player->hideout->max_resource_stone = $levels['passiv_bonus_amount_2'];
			}
			
			if($id['type'] == "resource_production"){
			$hideout_room->status = 6;
			$hideout_room->current_resource_amount = $levels['min_till_max_resource'];
			$hideout_room->max_resource_amount = $levels['resource_production_max'];
			}
			
			if($id['type'] == "battle"){
			$hideout_room->status = 1;
			$player->hideout->max_attacker_units = $levels['resource_production_max'];
			$hideout_room->max_resource_amount = $levels['resource_production_max'];
			}
			
		}
		}

	if($id['type'] == "resource_production" OR $id['type'] == "main_building"){
		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
			"hideout" => [
			"id" => $player->hideout->id,
			"current_level" => $player->hideout->current_level,
			"idle_worker_count" => $player->hideout->idle_worker_count,
			"max_resource_glue" => $player->hideout->max_resource_glue,
			"max_resource_stone" => $player->hideout->max_resource_stone,
			"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh
			],
			"hideout_rooms" => [
			[
			"id" => $hideout_room->id,
			"status" => $hideout_room->status,
			"current_resource_amount" => $hideout_room->current_resource_amount,
			"max_resource_amount" => $hideout_room->max_resource_amount,
			"ts_last_resource_change" => $hideout_room->ts_last_resource_change,
			"level" => $hideout_room->level
			]
			]
		];
	} else {
		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
			"hideout" => [
			"id" => $player->hideout->id,
			"current_level" => $player->hideout->current_level,
			"idle_worker_count" => $player->hideout->idle_worker_count,
			"max_attacker_units" => $player->hideout->max_resource_glue,
			"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh
			],
			"hideout_rooms" => [
			[
			"id" => $hideout_room->id,
			"status" => 1,
			"max_resource_amount" => $hideout_room->max_resource_amount,
			"ts_last_resource_change" => $hideout_room->ts_last_resource_change
			]
			]
		];
	
	}
    }
}