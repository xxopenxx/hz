<?php
namespace Request;

use Srv\Core;
use Cls\Utils\HideoutUtils;

class instantFinishHideoutRoomActivity{
	public function __request($player){
		$hideout_room_id = getField('hideout_room_id', FIELD_NUM);

		if(!$player->hideout)
			return Core::setError('errHideoutNotFound');

		if(!$player->hideout_rooms)
			return Core::setError('errHideoutRoomsNotFound');

        $hideout_room = null;
        foreach ($player->hideout_rooms as $room) {
            if ($room->id == $hideout_room_id) {
                $hideout_room = $room;
                break;
            }
        }

        if (!$hideout_room) 
            return Core::setError('errInvalidRoomId');

		$time = time();
		$ts_activity_end = $hideout_room->ts_activity_end;

		$time_left = $ts_activity_end - $time;

		if ($time_left <= 0)
			return Core::setError('errActivityAlreadyFinished');

		$price = HideoutUtils::getInstantFinishPremiumAmount($time_left);

		if ($player->getPremium() < $price)
			return Core::setError('errRemovePremiumCurrencyNotEnough');

		$hideout_room->ts_activity_end = $time;
		$player->givePremium(-$price);

		Core::req()->data = array(
		    'user'=>$player->user,
			'hideout_room'=>[
				'id'=>$hideout_room->id,
				'ts_last_resource_change' => $hideout_room->ts_last_resource_change,
				'ts_activity_end' => $hideout_room->ts_activity_end
			]
		);
	}	
}