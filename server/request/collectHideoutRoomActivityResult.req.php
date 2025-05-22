<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils\HideoutUtils;

class collectHideoutRoomActivityResult{
	
	public function __request($player){

		$hideout_room_id = getField('hideout_room_id', FIELD_NUM);

		if (!$player->hideout) 
			return Core::setError('errHideoutNotFound');

		if (!$player->hideout_rooms)
			return Core::setError('errHideoutRoomsNotFound');

		foreach ($player->hideout_rooms as $room) {
			if ($room->id == $hideout_room_id) {
				$hideout_room = $room;
				break;
			}
		}

		if (!$hideout_room) 
			return Core::setError('errInvalidRoomId');

		if ($hideout_room->current_resource_amount < 1)
			return Core::setError('errNoResourceToCollect');

		switch ($hideout_room->identifier) {
			case 'main_building':
				$player->giveMoney($hideout_room->current_resource_amount);
				$hideout_room->status = HideoutUtils::HideoutBuildStatus['Producing'];
				$hideout_room->current_resource_amount = 0;
				$hideout_room->ts_last_resource_change = time();

				Core::req()->data = [
					'character' => [
						'id' => $player->character->id,
						'game_currency' => $player->character->game_currency
					],
					'hideout' => [
						'id' => $player->hideout->id,
						'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh
					],
					'hideout_room' => [
						'id' => $hideout_room->id,
						'current_resource_amount' => $hideout_room->current_resource_amount,
						'ts_last_resource_change' => $hideout_room->ts_last_resource_change
					]
				];
				break;

			case 'stone_production':
				$available_space = $player->hideout->max_resource_stone - $player->hideout->current_resource_stone;
				$amount_to_collect = min($hideout_room->current_resource_amount, $available_space);

				//$player->hideout->current_resource_stone += $amount_to_collect;
				$player->giveHideoutStone($amount_to_collect);
				if ($amount_to_collect < $hideout_room->current_resource_amount) {
					$hideout_room->current_resource_amount -= $amount_to_collect;
				} else {
					$hideout_room->current_resource_amount = 0;
				}

				$hideout_room->ts_last_resource_change = time();

				Core::req()->data = [
					'hideout' => [
						'id' => $player->hideout->id,
						'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,
						'current_resource_stone' => $player->hideout->current_resource_stone
					],
					'hideout_room' => [
						'id' => $hideout_room->id,
						'current_resource_amount' => $hideout_room->current_resource_amount,
						'ts_last_resource_change' => $hideout_room->ts_last_resource_change
					]
				];
				break;

			case 'glue_production':
				$available_space = $player->hideout->max_resource_glue - $player->hideout->current_resource_glue;
				$amount_to_collect = min($hideout_room->current_resource_amount, $available_space);

				//$player->hideout->current_resource_glue += $amount_to_collect;
				$player->giveHideoutGlue($amount_to_collect);
				if ($amount_to_collect < $hideout_room->current_resource_amount) {
					$hideout_room->current_resource_amount -= $amount_to_collect;
				} else {
					$hideout_room->current_resource_amount = 0;
				}

				$hideout_room->ts_last_resource_change = time();

				Core::req()->data = [
					'hideout' => [
						'id' => $player->hideout->id,
						'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,
						'current_resource_glue' => $player->hideout->current_resource_glue
					],
					'hideout_room' => [
						'id' => $hideout_room->id,
						'current_resource_amount' => $hideout_room->current_resource_amount,
						'ts_last_resource_change' => $hideout_room->ts_last_resource_change
					]
				];
				break;

			case 'attacker_production':
				$available_space = $player->hideout->max_attacker_units - $player->hideout->current_attacker_units;
				$amount_to_collect = min($hideout_room->current_resource_amount, $available_space);
				$player->hideout->current_attacker_units += $amount_to_collect;

				if ($amount_to_collect < $hideout_room->current_resource_amount) {
					$hideout_room->current_resource_amount -= $amount_to_collect;
				} else {
					$hideout_room->current_resource_amount = 0;
					$hideout_room->status = HideoutUtils::HideoutBuildStatus['Idle'];
				}

				$hideout_room->ts_last_resource_change = time();

				Core::req()->data = [
					'hideout' => [
						'id' => $player->hideout->id,
						'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,
						'current_attacker_units' => $player->hideout->current_attacker_units
					],
					'hideout_room' => [
						'id' => $hideout_room->id,
						'status' => $hideout_room->status,
						'current_resource_amount' => $hideout_room->current_resource_amount,
						'max_resource_amount' => $hideout_room->max_resource_amount,
						'ts_last_resource_change' => $hideout_room->ts_last_resource_change
					]
				];
				break;

			case 'defender_production':
				$player->hideout->current_defender_units += $hideout_room->current_resource_amount;
				$hideout_room->status = HideoutUtils::HideoutBuildStatus['Idle'];
				$hideout_room->current_resource_amount = 0;
				$hideout_room->ts_last_resource_change = time();

				Core::req()->data = [
					'hideout' => [
						'id' => $player->hideout->id,
						'ts_last_opponent_refresh' => $player->hideout->ts_last_opponent_refresh,
						'current_defender_units' => $player->hideout->current_defender_units
					],
					'hideout_room' => [
						'id' => $hideout_room->id,
						'current_resource_amount' => $hideout_room->current_resource_amount,
						'ts_last_resource_change' => $hideout_room->ts_last_resource_change,
						'status' => $hideout_room->status,
						'max_resource_amount' => $hideout_room->max_resource_amount,
						'ts_activity_end' => $hideout_room->ts_activity_end
					]
				];
				break;

			default:
				return Core::setError('errInvalidRoomId');
		}
	}
}
