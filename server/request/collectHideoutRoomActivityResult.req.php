<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\GameSettings;

error_reporting(0);
class collectHideoutRoomActivityResult{
    
    public function __request($player){

		$hideout_room_id = getField('hideout_room_id', FIELD_NUM);
		$hideout_room = HideoutRoom::find(function($q) use($hideout_room_id, $player) { $q->where('id', $hideout_room_id)->where('hideout_id', $player->hideout->id); });
		
		if(!$player->hideout)
			return Core::setError('failed');
		
		if(!$hideout_room)
			return Core::setError('failed');	
		
		$rooms = GameSettings::getConstant("hideout_rooms");
		$id = $rooms[$hideout_room->identifier];
		$levels = $id['levels'][$hideout_room->level];		
		
		/*$hideout_room->current_resource_amount = 220;
		$hideout_room->max_resource_amount = 220;
		$hideout_room->ts_last_resource_change = time();*/

		$hideout_room->ts_last_resource_change = time();

		if($id['type'] == 'main_building'){
		$hideout_room->status = 6;
		$player->character->game_currency += $hideout_room->current_resource_amount;
		$hideout_room->current_resource_amount = 0;
		}

		if($identifier == 'stone_production'){
		$hideout_room->status = 6;
		$player->hideout->current_resource_stone += $hideout_room->current_resource_amount;
		$hideout_room->current_resource_amount = 0;
		}
		
		if($identifier == 'glue_production'){
		$hideout_room->status = 6;
		$player->hideout->current_resource_glue += $hideout_room->current_resource_amount;
		$hideout_room->current_resource_amount = 0;
		}



		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
			"hideout" => [
			"id" => $player->hideout->id,
			"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh
			],
			"hideout_room" => [
			"id" => $hideout_room->id,
			"current_resource_amount" => $hideout_room->current_resource_amount,
			"ts_last_resource_change" => $hideout_room->ts_last_resource_change
			]
			//"hideout_rooms"=>$player->hideout_rooms
		];
    }
}