<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;

class checkHideoutRoomActivityFinished {
    
    public function __request($player) {
        $hideout_room_id = getField('hideout_room_id', FIELD_NUM);

        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');
        
        if (!$player->hideout_rooms) 
            return Core::setError('errHideoutRoomsNotFound');

        if (!$hideout_room_id) 
            return Core::setError('errInvalidRoomId');

        $hideout_room = null;
        foreach ($player->hideout_rooms as $room) {
            if ($room->id == $hideout_room_id) {
                $hideout_room = $room;
                break;
            }
        }

        if (!$hideout_room) 
            return Core::setError('errInvalidRoomId');

		if ($hideout_room->ts_activity_end > time())
			return Core::setError('errActivityNotFinished');

        $hideout_rooms = GameSettings::getConstant("hideout_rooms.{$hideout_room->identifier}");
        if (!$hideout_rooms)
            return Core::setError('errInvalidRoomSettings');

        $oldStatus = $hideout_room->status;

        switch ($hideout_room->status) {
            case HideoutUtils::HideoutBuildStatus['Building']:
            case HideoutUtils::HideoutBuildStatus['Upgrading']:
                if ($hideout_room->status == HideoutUtils::HideoutBuildStatus['Upgrading']) $hideout_room->level += 1;
                $levels = $hideout_rooms['levels'][$hideout_room->level];

                switch ($hideout_rooms['type']) {
                    case 'main_building':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        $player->hideout->max_resource_glue = $levels['passiv_bonus_amount_1'];
                        $player->hideout->max_resource_stone = $levels['passiv_bonus_amount_2'];
                        $hideout_room->max_resource_amount = round($levels['min_till_max_resource'] * $levels['resource_production_max']);
                        break;
                    case 'resource_production':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        $hideout_room->current_resource_amount = $levels['min_till_max_resource'];
                        $hideout_room->max_resource_amount = $levels['resource_production_max'];
                        break;
                    case 'battle':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Idle'];
                        switch ($hideout_room->identifier) {
                            case 'attacker_production':
                                $player->hideout->max_attacker_units += $levels['resource_production_max'];
								$hideout_room->max_resource_amount = $levels['resource_production_max'];
                                break;
                            case 'defender_production':
                                $player->hideout->max_defender_units = $levels['resource_production_max'];
								$hideout_room->max_resource_amount = $levels['resource_production_max'];
                                break;
                            case 'wall':
                                $player->hideout->current_wall_level = $levels['passiv_bonus_amount_1'];
                                break;
                            case 'barracks':
                                $player->hideout->max_barracks_level = $levels['passiv_bonus_amount_1'];
                                break;
                        }
                        break;
                }

                if (!HideoutUtils::isManuallyProductionRoom($hideout_room->identifier)) {
                    $hideout_room->current_resource_amount = $hideout_room->max_resource_amount;
                }

                $player->hideout->idle_worker_count += 1;

                $hideoutRoomUpgraded = $player->getCurrentGoalValue('hideout_room_upgraded');
                $player->updateCurrentGoalValue('hideout_room_upgraded', $hideoutRoomUpgraded + 1);
                break;
            case HideoutUtils::HideoutBuildStatus['Placing']:
                if ($hideout_room->ts_activity_end > time()) 
                    return Core::setError('errActivityNotFinished');

                $levels = $hideout_rooms['levels'][$hideout_room->level];
                switch ($hideout_rooms['type']) {
                    case 'main_building':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        break;
                    case 'resource_production':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        break;
                    case 'battle':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Idle'];
                        switch ($hideout_room->identifier) {
                            case 'attacker_production':
                                $player->hideout->max_attacker_units += $levels['resource_production_max'];
                                break;
                            case 'defender_production':
                                $player->hideout->max_defender_units = $levels['resource_production_max'];
                                break;
                            case 'wall':
                                $player->hideout->current_wall_level = $levels['passiv_bonus_amount_1'];
                                break;
                            case 'barracks':
                                $player->hideout->max_barracks_level = $levels['passiv_bonus_amount_1'];
                                break;
                        }
                        break;
                }

                $player->hideout->idle_worker_count += 1;

                Core::req()->data = [
                    'user' => $player->user,
                    'character' => $player->character,
                    'hideout' => [
                        'id' => $player->hideout->id,
                        'idle_worker_count' => $player->hideout->idle_worker_count,
                        'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh
                    ]
                ];

                Core::req()->data['hideout_rooms'][] = [
                    'id' => $hideout_room->id,
                    'status' => $hideout_room->status,
                    'max_resource_amount' => $hideout_room->max_resource_amount,
                    'ts_last_resource_change' => $hideout_room->ts_last_resource_change
                ];
                break;
            case HideoutUtils::HideoutBuildStatus['Storing']:
                if ($hideout_room->ts_activity_end > time()) 
                    return Core::setError('errActivityNotFinished');

                switch ($hideout_rooms['type']) {
                    case 'main_building':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        break;
                    case 'resource_production':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
                        break;
                    case 'battle':
                        $hideout_room->status = HideoutUtils::HideoutBuildStatus['Idle'];
                        switch ($hideout_room->identifier) {
                            case 'attacker_production':
                                $player->hideout->max_attacker_units -= $levels['resource_production_max'];
                                break;
                            case 'defender_production':
                                $player->hideout->max_defender_units = 0;
                                break;
                            case 'wall':
                                $player->hideout->current_wall_level = 0;
                                break;
                            case 'barracks':
                                $player->hideout->max_barracks_level = 0;
                                break;
                        }
                        break;
                }

                $player->hideout->idle_worker_count += 1;

                Core::req()->data = [
                    'user' => $player->user,
                    'character' => $player->character,
                    'hideout' => [
                        'id' => $player->hideout->id,
                        'idle_worker_count' => $player->hideout->idle_worker_count,
                        'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh
                    ]
                ];

                for ($i = 0; $i < HideoutUtils::MAX_LEVELS; $i++) {
                    for ($j = 0; $j < HideoutUtils::MAX_SLOTS; $j++) {
                        $slot_name = "room_slot_{$i}_{$j}";
                        if ($player->hideout->$slot_name == $hideout_room->id) {
                            $player->hideout->$slot_name = 0;
                            Core::req()->data['hideout'][$slot_name] = $player->hideout->$slot_name;
                        }
                    }
                }

                $hideout_room->ts_last_resource_change = time();
                Core::req()->data['hideout_rooms'][] = [
                    'id' => $hideout_room->id,
                    'status' => $hideout_room->status,
                    'ts_last_resource_change' => $hideout_room->ts_last_resource_change
                ];
                break;
        }

        # Upgrade/Build - Response
        if (in_array($oldStatus, [HideoutUtils::HideoutBuildStatus['Building'], HideoutUtils::HideoutBuildStatus['Upgrading']])) {
            Core::req()->data = [
                'user' => $player->user,
                'character' => $player->character,
                'hideout' => $player->hideout
            ];

            if (in_array($hideout_rooms['type'], ['main_building', 'resource_production'])) {
                Core::req()->data['hideout_rooms'][] = [
                    'id' => $hideout_room->id,
                    'status' => $hideout_room->status,
                    'current_resource_amount' => $hideout_room->current_resource_amount,
                    'max_resource_amount' => $hideout_room->max_resource_amount,
                    'ts_last_resource_change' => $hideout_room->ts_last_resource_change,
                    'level' => $hideout_room->level
                ];
            } else {
                Core::req()->data['hideout_rooms'][] = [
                    'id' => $hideout_room->id,
                    'status' => $hideout_room->status,
                    'max_resource_amount' => $hideout_room->max_resource_amount,
                    'ts_last_resource_change' => $hideout_room->ts_last_resource_change
                ];
            }
        }
    }
}

