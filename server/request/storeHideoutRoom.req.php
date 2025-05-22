<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;


class storeHideoutRoom {
    
    public function __request($player) {

// TODO: {"data":[],"error":"errStoreResourceLeftInRoom"}

        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->hideout_rooms) 
            return Core::setError('errHideoutRoomNotFound');

        if ($player->hideout->idle_worker_count <= 0)
            return Core::setError('errHideoutNoIdleWorker');

        $hideout_store_duration_per_level = GameSettings::getConstant("hideout_store_duration_per_level");

        $hideoutRoomId = getField('hideoutRoomId', FIELD_NUM);

        $hideoutRoom = null;
        foreach ($player->hideout_rooms as $room) {
            if ($room->id == $hideoutRoomId) {
                $hideoutRoom = $room;
                break;
            }
        }

        if ($hideoutRoom == null) 
            return Core::setError('errHideoutRoomNotFound');

        if (!in_array($hideoutRoom->status, [HideoutUtils::HideoutBuildStatus['Idle'], HideoutUtils::HideoutBuildStatus['Producing']]))
            return Core::setError('errHideoutRoomNotIdle');

        $hideoutRoom->status = HideoutUtils::HideoutBuildStatus['Storing'];
        $hideoutRoom->ts_activity_end = time() + (($hideout_store_duration_per_level * $hideoutRoom->level) * 60);
        $hideoutRoom->current_resource_amount = 0;
        $hideoutRoom->ts_last_resource_change = time();

        $player->hideout->idle_worker_count -= 1;

        Core::req()->data = [
            'hideout' => [
                'id' => $player->hideout->id,
                'idle_worker_count' => $player->hideout->idle_worker_count,
                'current_resource_stone' => $player->hideout->current_resource_stone,
                'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh
            ],
            'hideout_room' => [
                'id' => $hideoutRoom->id,
                'status' => $hideoutRoom->status,
                'current_resource_amount' => $hideoutRoom->current_resource_amount,
                'ts_last_resource_change' => $hideoutRoom->ts_last_resource_change,
                'ts_activity_end' => $hideoutRoom->ts_activity_end
            ]
        ];
    }
}