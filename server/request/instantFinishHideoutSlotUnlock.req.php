<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\Utils;
use Cls\GameSettings;
use Cls\Utils\HideoutUtils;

class instantFinishHideoutSlotUnlock {
    public function __request($player) {

        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->hideout_rooms)
            return Core::setError('errHideoutRoomsNotFound');

		$level = getField('level', FIELD_NUM);
		$slot = getField('slot', FIELD_NUM);

        if ($level < 0 || $level > HideoutUtils::MAX_LEVELS) 
            return Core::setError('errHideoutLevelInvalid');

        if ($slot < 0 || $slot > HideoutUtils::MAX_SLOTS)
            return Core::setError('errHideoutSlotInvalid');

        $slot_name = "room_slot_{$level}_{$slot}";

        if ($player->hideout->$slot_name >= -1) 
            return Core::setError('errHideoutSlotNotFound');

        $tsEnd = abs($player->hideout->$slot_name);
        
        if ($tsEnd <= time()) 
            return Core::setError('errAlreadyFinished');

        $time_remaining = $tsEnd - time();
        
        $skipCost = HideoutUtils::getInstantFinishPremiumAmount($time_remaining);
        
		if($player->user->premium_currency < $skipCost)
			return Core::setError("errRemovePremiumCurrencyNotEnough");
		
		$player->givePremium(-$skipCost);

        $player->hideout->$slot_name = 0;
        $player->hideout->idle_worker_count += 1;

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character,
            'hideout' => [
                'id' => $player->hideout->id,
                'idle_worker_count' => $player->hideout->idle_worker_count,
                'current_resource_glue' => $player->hideout->current_resource_glue,
                'current_resource_stone' => $player->hideout->current_resource_stone,
                'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,		
                $slot_name => 0,
            ],
        ];
    }
}