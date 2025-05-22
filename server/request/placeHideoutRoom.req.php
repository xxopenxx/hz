<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;


class placeHideoutRoom {
    
    public function __request($player) {

        $hideoutRoomId = getField('hideoutRoomId', FIELD_NUM);
        $slot = getField('slot', FIELD_NUM);
        $level = getField('level', FIELD_NUM);

        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->hideout_rooms) 
            return Core::setError('errHideoutRoomNotFound');

        if ($player->hideout->idle_worker_count <= 0)
            return Core::setError('errHideoutNoIdleWorker');

        if ($slot < 0 || $slot > HideoutUtils::MAX_SLOTS)
            return Core::setError('errInvalidSlot');

        if ($level < 0 || $level > HideoutUtils::MAX_LEVELS)
            return Core::setError('errInvalidLevel');

        $hideoutRoom = null;
        foreach ($player->hideout_rooms as $room) {
            if ($room->id == $hideoutRoomId) {
                $hideoutRoom = $room;
                break;
            }
        }

        if ($hideoutRoom == null) 
            return Core::setError('errHideoutRoomNotFound');

        for ($i = 0; $i < HideoutUtils::MAX_LEVELS; $i++) {
            for ($j = 0; $j < HideoutUtils::MAX_SLOTS; $j++) {
                $room_name = "room_slot_{$i}_{$j}";
                if ($player->hideout->{$room_name} == $hideoutRoom->id) {
                    return Core::setError('errSlotsNotAvailable');
                }
            }
        }

        $hideout_rooms = GameSettings::getConstant("hideout_rooms.{$hideoutRoom->identifier}");

        if (!$hideout_rooms)
            return Core::setError('errInvalidRoomSettings');

        $size = $hideout_rooms['size'];
        for ($index = $slot; $index < $slot + $size; $index++) {
            $room_name = "room_slot_{$level}_{$index}";
            if ($player->hideout->{$room_name} != 0) {
                return Core::setError('errSlotsNotAvailable');
            }
        }

        $player->hideout->idle_worker_count -= 1;

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character,
            'hideout' => [
                'id' => $player->hideout->id,
                'idle_worker_count' => $player->hideout->idle_worker_count,
                'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh
            ]
        ];

        $hideout_place_duration_per_level = GameSettings::getConstant("hideout_place_duration_per_level");

        $hideoutRoom->status = HideoutUtils::HideoutBuildStatus['Placing'];
        $hideoutRoom->ts_activity_end = time() + (($hideout_place_duration_per_level * $hideoutRoom->level) * 60);

        for ($index = $slot; $index < $slot + $size; $index++) {
            $room_name = "room_slot_{$level}_{$index}";
            $player->hideout->{$room_name} = $hideoutRoom->id;
            Core::req()->data['hideout'][$room_name] = $player->hideout->{$room_name};
        }

        Core::req()->data['hideout_rooms'][] = [
            'id' => $hideoutRoom->id,
            'status' => $hideoutRoom->status,
            'ts_last_resource_change' => $hideoutRoom->ts_last_resource_change,
            'ts_activity_end' => $hideoutRoom->ts_activity_end,
        ];
    }
}