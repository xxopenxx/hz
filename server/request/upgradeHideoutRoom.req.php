<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\GameSettings;
class upgradeHideoutRoom{
    
    public function __request($player){
		
		$hideout_room_id = getField('hideoutRoomId', FIELD_NUM);
		$hideout_room = HideoutRoom::find(function($q) use($hideout_room_id, $player) { $q->where('id', $hideout_room_id)->where('hideout_id', $player->hideout->id); });
		
		if(!$player->hideout)
			return Core::setError('failed');
		
		if(!$hideout_room)
			return Core::setError('failed');	

		$rooms = Config::get("constants.hideout_rooms");
		$id = $rooms[$hideout_room->identifier];
		$levels = $id['levels'][$hideout_room->level + 1];
		
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
		
		$hideout_room->ts_activity_end = $timeAdd;
		$hideout_room->status = 3;
		$hideout_room->max_resource_amount = $levels['min_till_max_resource'];
		//$hideout_room->current_resource_amount = $levels['min_till_max_resource'];

		
		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
			"hideout" => [
			"id" => $player->hideout->id,
			"idle_worker_count" => $player->hideout->idle_worker_count,
			"current_resource_glue" => $player->hideout->current_resource_glue,
			"current_resource_stone" => $player->hideout->current_resource_stone,
			"ts_last_opponent_refresh" => $player->hideout->ts_last_opponent_refresh
			],
			"hideout_room" => [
			"id" => $hideout_room->id,
			"status" => $hideout_room->status,
			"current_resource_amount" => $hideout_room->current_resource_amount,
			"ts_last_resource_change" => $hideout_room->ts_last_resource_change,
			"ts_activity_end" => $hideout_room->ts_activity_end,
			]
			];
    }
}