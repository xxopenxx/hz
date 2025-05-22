<?php
namespace Request;

use Srv\Core;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;

class unlockHideoutRoomSlot {
    public function __request($player){
        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->hideout_rooms)
            return Core::setError('errHideoutRoomsNotFound');

        if ($player->hideout->idle_worker_count <= 0)
            return Core::setError('errHideoutNoIdleWorkers');

		$level = getField('level', FIELD_NUM);
		$slot = getField('slot', FIELD_NUM);

        if ($level < 0 || $level > HideoutUtils::MAX_LEVELS)
            return Core::setError('errHideoutLevelInvalid');

        if ($slot < 0 || $slot > HideoutUtils::MAX_SLOTS)
            return Core::setError('errHideoutSlotInvalid');

        $room_name = "room_slot_{$level}_{$slot}";

        if ($player->hideout->{$room_name} != -1)
            return Core::setError('errHideoutRoomSlotAlreadyUnlocked');

        $unlocked_slots = 0;
        for ($i = 0; $i < HideoutUtils::MAX_LEVELS; $i++) {
            for ($j = 0; $j < HideoutUtils::MAX_SLOTS; $j++) {
                if ($player->hideout->{"room_slot_{$i}_{$j}"} > -1) $unlocked_slots++;
            }
        }

        $hideout_expansion = GameSettings::getConstant('hideout_expansion');
        $hideout_expansion = $hideout_expansion[$unlocked_slots + 1];
        
        if ($hideout_expansion == null)
            return Core::setError('errHideoutRoomSlotNotFound');

        if ($player->hideout->current_resource_glue < $hideout_expansion['price_glue'])
            return Core::setError('errRemoveGlueNotEnough');

        if ($player->hideout->current_resource_stone < $hideout_expansion['price_stone'])
            return Core::setError('errRemoveStoneNotEnough');

        if ($player->character->game_currency < $hideout_expansion['price_gold'])
            return Core::setError('errRemoveGameCurrencyNotEnough');

        $player->hideout->current_resource_glue -= $hideout_expansion['price_glue'];
        $player->hideout->current_resource_stone -= $hideout_expansion['price_stone'];
        $player->giveMoney(-$hideout_expansion['price_gold']);

        $time = time() + ($hideout_expansion['duration'] * 60);
        $room_name = "room_slot_{$level}_{$slot}";
        $player->hideout->{$room_name} = $time * -1;
        $player->hideout->idle_worker_count--;

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character,
            'hideout' => [
                'id' => $player->hideout->id,
                'idle_worker_count' => $player->hideout->idle_worker_count,
                'current_resource_glue' => $player->hideout->current_resource_glue,
                'current_resource_stone' => $player->hideout->current_resource_stone,
                'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,
                "room_slot_{$level}_{$slot}" => -$time
            ]
        ];
    }
}