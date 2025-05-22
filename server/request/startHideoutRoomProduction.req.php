<?php
namespace Request;

use Srv\Core;
use Cls\Utils\HideoutUtils;
use Cls\GameSettings;

class startHideoutRoomProduction {
    public function __request($player) {

		if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->hideout_rooms) 
            return Core::setError('errHideoutRoomNotFound');

        $hideoutRoomId = getField('hideoutRoomId', FIELD_NUM);
        $productionCount = getField('productionCount', FIELD_NUM);

        if ($productionCount < 1) 
            return Core::setError('errHideoutRoomProductionCountInvalid');

        $hideoutRoom = null;
        foreach ($player->hideout_rooms as $room) {
            if ($room->id == $hideoutRoomId) {
                $hideoutRoom = $room;
                break;
            }
        }

        if ($hideoutRoom == null) 
            return Core::setError('errHideoutRoomNotFound');

        if (!HideoutUtils::isManuallyProductionRoom($hideoutRoom->identifier)) 
            return Core::setError('errHideoutRoomNotProduction');

        if ($hideoutRoom->status != HideoutUtils::HideoutBuildStatus['Idle']) 
            return Core::setError('errHideoutRoomNotIdle');

        if ($hideoutRoom->current_resource_amount >= $hideoutRoom->max_resource_amount)
            return Core::setError('errHideoutRoomAlreadyFull');

        if ($hideoutRoom->current_resource_amount + ($hideoutRoom->max_resource_amount * $productionCount) > $hideoutRoom->max_resource_amount)
            return Core::setError('errLimitProductionCount');

        $time = time();

        $unit_name = explode('_', $hideoutRoom->identifier)[0];

        $units = GameSettings::getConstant("hideout_units.{$unit_name}");

        if ($units == null) 
            return Core::setError('errHideoutRoomNotFound');

        $level_data = $units['levels'][$hideoutRoom->level];

        if ($level_data == null) 
            return Core::setError('errHideoutRoomNotFound');

        $total_price_glue = $level_data['price_glue'] * $productionCount;
        $total_price_stone = $level_data['price_stone'] * $productionCount;
        $total_price_gold = $level_data['price_gold'] * $productionCount;

        if ($total_price_glue > $player->hideout->current_resource_glue) 
            return Core::setError('errRemoveGlueNotEnough');

        if ($total_price_stone > $player->hideout->current_resource_stone) 
            return Core::setError('errRemoveStoneNotEnough');

        if ($total_price_gold > $player->character->game_currency)
            return Core::setError('errRemoveGameCurrencyNotEnough');

        $player->hideout->current_resource_glue -= $total_price_glue;
        $player->hideout->current_resource_stone -= $total_price_stone;
        $player->giveMoney(-$total_price_gold);

        $hideoutRoom->status = HideoutUtils::HideoutBuildStatus['Producing'];
        $hideoutRoom->ts_last_resource_change = $time;
        $hideoutRoom->ts_activity_end = $time + (($level_data['build_time'] * $productionCount) * 60);

		Core::req()->data = array(
            'user' => $player->user,
		    'character'=> $player->character,
            'hideout' => $player->hideout,
            'hideout_room' => [
                'id' => $hideoutRoom->id,
                'status' => $hideoutRoom->status,
                'ts_last_resource_change' => $hideoutRoom->ts_last_resource_change,
                'ts_activity_end' => $hideoutRoom->ts_activity_end
            ]
		);
    }
}

