<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\GameSettings;
use Srv\DB;
use PDO;
use Schema\Items;
use Schema\PatternItems;

class collectItemPatternReward {
    public function __request($player) {
    	$identifier = getField('identifier');
    	$value = getField('value');

        $date = date('Y-m-d H:i:s');

        $itemPattern = GameSettings::getConstant("item_pattern.{$identifier}");
        if (!$itemPattern) {
            return Core::setError('errInvalidItemPatternIdentifier');
        }

        $collected = null;
        if (!empty($player->collected_item_pattern)) {
            foreach ($player->collected_item_pattern as $key => $data) {
                foreach ($data as $k => $v) {
                    if ($k == $identifier && $v['value'] == $value) {
                        $collected = $data;
                        break 2;
                    }
                }
            }
        }

        if ($collected) {
            return Core::setError('errCollectItemPatternAlreadyExists');
        }

        $xd = $itemPattern['values'][$value];
        
        switch ($xd['reward_type']) {
            case 1: # StatPoint
                $player->character->stat_points_available += $xd['reward_factor'];
                break;

            case 2: # Item
              $freeSlot = $player->findEmptyInventorySlot();
                if ($freeSlot === null) {
                    return Core::setError('errInventoryNoEmptySlot');
                }

                $patternItem = PatternItems::find(function($q)use($identifier,$player){ 
                    $q->where('pattern_identifier', $identifier)
                        ->where('character_id', $player->character->id);
                });

                if (!$patternItem) {
                    return Core::setError('errPatternItemNotFound');
                }
                
                $item = new Items([
                    'character_id'=>$player->character->id,
                    'identifier'=>$patternItem->identifier,
                    'type'=>$patternItem->type,
                    'quality'=>$patternItem->quality,
                    'required_level'=>$patternItem->required_level,
                    'charges'=>$patternItem->charges,
                    'item_level'=>$patternItem->item_level,
                    'ts_availability_start'=>$patternItem->ts_availability_start,
                    'ts_availability_end'=>$patternItem->ts_availability_end,
                    'premium_item'=>$patternItem->premium_item,
                    'buy_price'=>$patternItem->buy_price,
                    'sell_price'=>$patternItem->sell_price,
                    'stat_stamina'=>$patternItem->stat_stamina,
                    'stat_strength'=>$patternItem->stat_strength,
                    'stat_critical_rating'=>$patternItem->stat_critical_rating,
                    'stat_dodge_rating'=>$patternItem->stat_dodge_rating,
                    'stat_weapon_damage'=>$patternItem->stat_weapon_damage
                ]);
                $item->save();
                $player->items[] = $item;
                $player->inventory->{$freeSlot} = $item->id;

                PatternItems::delete(function($q)use($patternItem){ $q->where('id',$patternItem->id); });
                break;

            case 3: # Training_Sessions
                $player->character->training_count += $xd['reward_factor'];
                break;

            case 4: # Quest_Energy
                $player->character->quest_energy += $xd['reward_factor'];
                break;
        }

        $player->collected_item_pattern[] = [
            $identifier => [
                'value' => intval($value),
                'date' => $date
            ]
        ];

        DB::sql("UPDATE `character` SET `collected_item_pattern` = ? WHERE `id` = ?", [
            json_encode($player->collected_item_pattern),
            $player->character->id
        ]);

		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
            'inventory'=>$player->inventory,
            'items'=>$player->items,
            'collected_item_pattern' => [
                [
                    $identifier => [
                        'value' => intval($value),
                        'date' => $date
                    ]
                ]
            ]
		];
    }
}