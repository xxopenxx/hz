<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\GameSettings;
use Cls\Utils\HideoutUtils;
use Srv\Debug;


class buildHideoutRoom {
    public function __request($player) {
        $identifier = getField('identifier', FIELD_IDENTIFIER);
        Debug::add(['buildHideoutRoom_start', 'player_id' => $player->character->id, 'identifier' => $identifier, 'level_slot' => $level, 'slot_pos' => $slot]);
        $level = (int) getField('level', FIELD_NUM);
        $slot = (int) getField('slot', FIELD_NUM);
        $time = time();

        if (!$player->hideout) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errHideoutNotFound', 'player_id' => $player->character->id]);
			return Core::setError('errHideoutNotFound');
        }

        if (!$identifier) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errInvalidIdentifier', 'player_id' => $player->character->id]);
			return Core::setError('errInvalidIdentifier');
        }

        if ($player->hideout->idle_worker_count <= 0) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errHideoutNoIdleWorker', 'player_id' => $player->character->id]);
            return Core::setError('errHideoutNoIdleWorker');
        }

        $roomConfig = GameSettings::getConstant("hideout_rooms.{$identifier}");

        if (!$roomConfig) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errInvalidRoomConfig', 'player_id' => $player->character->id]);
			return Core::setError('errInvalidRoomConfig');
        }

        $roomCount = 0;
        foreach ($player->hideout_rooms as $r) {
            if ($r->identifier === $identifier) $roomCount++;
        }

        if ($roomCount >= $roomConfig['limit']) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errRoomLimitReached', 'player_id' => $player->character->id]);
            return Core::setError('errRoomLimitReached');
        }

        $slotKeyBase = "room_slot_{$level}_";
        for ($i = 0; $i < $roomConfig['size']; $i++) {
            $currentSlot = $slot + $i;
            $slotKey = $slotKeyBase . $currentSlot;
            if ($player->hideout->{$slotKey} != 0) {
                Debug::add(['buildHideoutRoom_error', 'error_code' => 'errSlotsNotAvailable', 'player_id' => $player->character->id]);
                return Core::setError('errSlotsNotAvailable');
            }
        }

       /* foreach ($player->hideout_rooms as $r) {
            if ($r->identifier === 'main_building') {
                $mainBuilding = $r;
                break;
            }
        } */

        $mainBuilding = null;
        foreach ($player->hideout_rooms as $r) {
            if ($r->identifier === 'main_building') {
                $mainBuilding = $r;
                break;
            }
        }

        $isMainInStore = true;
        if ($mainBuilding) {
            for ($i = 0; $i < HideoutUtils::MAX_LEVELS; $i++) {
                for ($j = 0; $j < HideoutUtils::MAX_SLOTS; $j++) {
                    if ($player->hideout->{'room_slot_' . $i . '_' . $j} == $mainBuilding->id) {
                        $isMainInStore = false;
                        break 2;
                    }
                }
            }
        }

		if ($identifier != 'main_building') {
			$requiredLevel = $roomConfig["unlock_with_mainbuilding_" . ($roomCount + 1)] ?? 0;
			if (!$mainBuilding || ($mainBuilding->level < $requiredLevel) || $isMainInStore) {
                Debug::add(['buildHideoutRoom_error', 'error_code' => 'errMainBuildingLevelTooLow', 'player_id' => $player->character->id]);
				return Core::setError('errMainBuildingLevelTooLow');
			}
		}

		$price = $roomConfig['levels'][1];
		if (!$price) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errInvalidRoomLevel', 'player_id' => $player->character->id]);
			return Core::setError('errInvalidRoomLevel');
		}

        Debug::add(['buildHideoutRoom_resource_check', 'hideout_id' => $player->hideout->id, 'needed_glue' => $price['price_glue'], 'needed_stone' => $price['price_stone'], 'needed_gold' => $price['price_gold']]);
        if ($player->hideout->current_resource_glue < $price['price_glue'] ||
            $player->hideout->current_resource_stone < $price['price_stone'] ||
            $player->character->game_currency < $price['price_gold']) {
            Debug::add(['buildHideoutRoom_error', 'error_code' => 'errNotEnoughResources', 'player_id' => $player->character->id]);
            return Core::setError('errNotEnoughResources');
        }

        $player->hideout->current_resource_glue -= $price['price_glue'];
        $player->hideout->current_resource_stone -= $price['price_stone'];
        $player->giveMoney(-$price['price_gold']);
        Debug::add(['buildHideoutRoom_resource_deducted', 'hideout_id' => $player->hideout->id, 'new_glue' => $player->hideout->current_resource_glue, 'new_stone' => $player->hideout->current_resource_stone]);

        $buildTime = 60 * $price['build_time'];
        $newRoom = new HideoutRoom([
            'hideout_id' => $player->hideout->id,
            'identifier' => $identifier,
            'status' => HideoutUtils::HideoutBuildStatus['Building'],
            'level' => 1,
            'ts_creation' => $time,
            'ts_activity_end' => $time + $buildTime
        ]);
        Debug::add(['buildHideoutRoom_presave_room', 'hideout_id' => $player->hideout->id, 'room_data' => $newRoom->getData()]);
        $newRoom->save();
        Debug::add(['buildHideoutRoom_postsave_room', 'hideout_id' => $player->hideout->id, 'room_id' => $newRoom->id]);

        $slotUpdates = [];
        for ($i = 0; $i < $roomConfig['size']; $i++) {
            $currentSlot = $slot + $i;
            $slotKey = "room_slot_{$level}_{$currentSlot}";
            $player->hideout->{$slotKey} = $newRoom->id;
            $slotUpdates[$slotKey] = $newRoom->id;
        }

        $player->hideout->idle_worker_count -= 1;
        Debug::add(['buildHideoutRoom_presave_hideout', 'hideout_id' => $player->hideout->id, 'hideout_data' => $player->hideout->getData()]);
        $player->hideout->save();
        Debug::add(['buildHideoutRoom_postsave_hideout', 'hideout_id' => $player->hideout->id]);

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character,
            'hideout' => array_merge([
                'id' => $player->hideout->id,
                'current_resource_glue' => $player->hideout->current_resource_glue,
                'current_resource_stone' => $player->hideout->current_resource_stone,
                'idle_worker_count' => $player->hideout->idle_worker_count
            ], $slotUpdates),
            'hideout_room' => $newRoom
        ];
    }
}