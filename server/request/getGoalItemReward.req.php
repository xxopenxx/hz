<?php

namespace Request;

use Srv\Core;
use Cls\GameSettings;
use Cls\Utils;
use Cls\Utils\ItemsList;
use Cls\Utils\Item;
use Srv\DB;
use PDO;
use Schema\GoalItems;
use Schema\Items;

class getGoalItemReward {
    public function __request($player) {
        $identifier = getField('identifier');
        $value = getField('value', FIELD_NUM);

        $goals = GameSettings::getConstant("goals.{$identifier}");
        if ($goals == null) {
            return Core::setError('errNoGoalItemReward');
        }

        if (!isset($goals['values'][$value])) {
            return Core::setError('errNoGoalItemReward');
        }

        $currGoalValue = DB::sql("SELECT * FROM `current_goal_values` WHERE `character_id` = :character_id AND `identifier` = :identifier", [
            'character_id' => $player->character->id,
            'identifier' => $identifier
        ])->fetch(PDO::FETCH_ASSOC);

        if ($currGoalValue['value'] < $value) {
            return Core::setError('errGoalNotFound');
        }

        $collected = DB::sql("SELECT * FROM `collected_goals` WHERE `character_id` = :character_id AND `identifier` = :identifier AND `value` = :value",[
            'character_id' => $player->character->id,
            'identifier' => $identifier,
            'value' => $value
        ])->fetch(PDO::FETCH_ASSOC);

        if ($collected) {
            return Core::setError('errGoalAlreadyCollected');
        }

        $values = $goals['values'][$value];

        if ($values == null) {
            return Core::setError('errNoGoalItemReward');
        }

        if ($values['reward_type'] != 5 && $values['reward_type'] != 9) {
            return Core::setError('errNoGoalItemReward');
        }

        $goalItem = GoalItems::find(function($q) use ($identifier, $player) { 
            $q->where('goal_identifier', $identifier)
              ->where('character_id', $player->character->id);
        });

        if ($values['reward_type'] == 9) {
            if (!$goalItem) {
                $itemLvl = $values['estimated_level'];
                $type = 9;
                $item = ItemsList::$ITEMS[Item::$TYPE[$type]][mt_rand(0, count(ItemsList::$ITEMS[Item::$TYPE[$type]])-1)];

                $item['character_id'] = $player->character->id;
                $item['goal_identifier'] = $identifier;
                $goalItem = new GoalItems($item);
                $goalItem->save();

                $goalItem = Utils::randomiseItem($goalItem, $itemLvl);
                $goalItem->stat_weapon_damage = 0;
                
                $quality = 3;
                $goalItem->quality = $quality;
                $goalItem->stat_critical_rating *= $quality;
                $goalItem->stat_dodge_rating *= $quality;
                $goalItem->stat_stamina *= $quality;
                $goalItem->stat_strength *= $quality;
                $goalItem->stat_weapon_damage *= $quality;
            }
        } else {
            if (!$goalItem) {
                $itemLvl = $values['estimated_level'];
                $type = null;
                $quality = null;

                switch ($values['reward_identifier']) {
                    case 'random_common':
                        $quality = 1;
                        break;
                    case 'random_rare':
                        $quality = 2;
                        break;
                    case 'random_epic':
                        $quality = 3;
                        break;
                    case 'random_epic_gadget':
                        $quality = 3;
                        $type = Item::$TYPE_ID['gadget'];
                        break;
                    case 'random_epic_weapon':
                        $quality = 3;
                        $type = Item::$TYPE_ID['weapon'];
                        break;
                    case 'random_common_missiles':
                        $quality = 1;
                        $type = Item::$TYPE_ID['missiles'];
                        break;
                }

                do {
                    if (!$type) $type = mt_rand(1, 7);
                    $items = ItemsList::$ITEMS[Item::$TYPE[$type]] ?? [];
                    if (empty($items)) continue;
                    $item = $items[mt_rand(0, count($items)-1)];
                } while (($item['quality'] != $quality || $item['required_level'] > $itemLvl));

                $item['character_id'] = $player->character->id;
                $item['goal_identifier'] = $identifier;

                $goalItem = new GoalItems($item);
                $goalItem->save();

                if ($item['type'] == Item::$TYPE_ID['missiles']) {
                    $goalItem->charges = 100;
                    $goalItem->stat_critical_rating = $goalItem->stat_dodge_rating = $goalItem->stat_stamina = $goalItem->stat_strength = $goalItem->stat_weapon_damage = 0;
                } else {
                    $goalItem = Utils::randomiseItem($goalItem, $itemLvl);
        
                    if ($item['type'] == Item::$TYPE_ID['weapon']) {
                        $goalItem->stat_weapon_damage = round($goalItem->item_level * GameSettings::getConstant('item_weapon_damage_factor'));
                    } else {
                        $goalItem->stat_weapon_damage = 0;
                    }
                }
            }
        }

        Core::req()->data = array(
            'user' => $player->user,
            'character' => $player->character,
            'item' => [
                'id' => $goalItem->id,
                'character_id' => $goalItem->character_id,
                'identifier' => $goalItem->identifier,
                'type' => $goalItem->type,
                'quality' => $goalItem->quality,
                'required_level' => $goalItem->required_level,
                'charges' => $goalItem->charges,
                'item_level' => $goalItem->item_level,
                'ts_availability_start' => $goalItem->ts_availability_start,
                'ts_availability_end' => $goalItem->ts_availability_end,
                'premium_item' => $goalItem->premium_item,
                'buy_price' => $goalItem->buy_price,
                'sell_price' => $goalItem->sell_price,
                'stat_stamina' => $goalItem->stat_stamina,
                'stat_strength' => $goalItem->stat_strength,
                'stat_critical_rating' => $goalItem->stat_critical_rating,
                'stat_dodge_rating' => $goalItem->stat_dodge_rating,
                'stat_weapon_damage' => $goalItem->stat_weapon_damage
            ]
        );
    }
}