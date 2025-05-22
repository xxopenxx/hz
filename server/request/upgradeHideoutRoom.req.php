<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;
class upgradeHideoutRoom{
    
    public function __request($player){
		
		$hideout_room_id = getField('hideoutRoomId', FIELD_NUM);

		if(!$player->hideout)
			return Core::setError('errHideoutNotFound');

		if(!$player->hideout_rooms)
			return Core::setError('errHideoutRoomsNotFound');

        if ($player->hideout->idle_worker_count <= 0)
            return Core::setError('errHideoutNoIdleWorker');

		foreach ($player->hideout_rooms as $room) {
			if ($room->id == $hideout_room_id) {
				$hideout_room = $room;
				break;
			}
		}

		if (!$hideout_room) 
			return Core::setError('errInvalidRoomId');
		
		// TODO: Checki na statusy

		$hideout_rooms = GameSettings::getConstant("hideout_rooms.{$hideout_room->identifier}");

		if (!$hideout_rooms)
			return Core::setError('errInvalidRoomSettings');

		if ($hideout_rooms['type'] == 'main_building') {
			$hideout_room_upgrade = GameSettings::getConstant("hideout_room_upgrade")[$hideout_room->level + 1];
			
			if ($player->character->level < $hideout_room_upgrade['character_min_level']) {
				return Core::setError('errLevelTooLow');
			}
		}

		$levels = $hideout_rooms['levels'][$hideout_room->level + 1] ?? null;

		if (!$levels)
			return Core::setError('errInvalidRoomLevel');

		if($player->hideout->current_resource_stone < $levels['price_stone'])
			return Core::setError('errRemoveStoneNotEnough');

		if($player->hideout->current_resource_glue < $levels['price_glue'])
			return Core::setError('errRemoveGlueNotEnough');

		if($player->character->game_currency < $levels['price_gold'])
			return Core::setError('errRemoveGameCurrencyNotEnough');	

		$time = time();

		$player->hideout->current_resource_stone -= $levels['price_stone'];
		$player->hideout->current_resource_glue -= $levels['price_glue'];
		$player->giveMoney(-$levels['price_gold']);

		$hideout_room->ts_activity_end = $time + ($levels['build_time'] * 60);
		$hideout_room->status = HideoutUtils::HideoutBuildStatus['Upgrading'];
		$hideout_room->max_resource_amount = $levels['min_till_max_resource'];

		$player->hideout->idle_worker_count -= 1;

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