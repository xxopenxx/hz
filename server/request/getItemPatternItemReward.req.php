<?php

namespace Request;

use Srv\Core;
use Cls\GameSettings;
use Cls\Utils;
use Cls\Utils\ItemsList;
use Cls\Utils\Item;
use Srv\DB;
use PDO;
use Schema\PatternItems;
use Schema\Items;

class getItemPatternItemReward {
    public function __request($player) {
        $identifier = getField('identifier');
        $value = getField('value', FIELD_NUM);

        $itemPattern = GameSettings::getConstant("item_pattern.{$identifier}");
        if ($itemPattern == null) {
            return Core::setError('errNoItemPatternReward');
        }

        if (!isset($itemPattern['values'][$value])) {
            return Core::setError('errNoItemPatternReward');
        }

        $currItemPatternValue = null;
        foreach ($player->current_item_pattern_values as $key => $data) {
            if ($key == $identifier) {
                $currItemPatternValue = $data;
                break;
            }
        }

        if ($currItemPatternValue == null) {
            return Core::setError('errItemPatternNotFound');
        }

        if ($currItemPatternValue['value'] < $value) {
            return Core::setError('errItemPatternNotFound');
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
            return Core::setError('errItemPatternAlreadyCollected');
        }

        $values = $itemPattern['values'][$value];
        if ($values['reward_type'] != 2) {
            return Core::setError('errNoItemPatternReward');
        }

        $patternItem = PatternItems::find(function($q) use ($identifier, $player) { 
            $q->where('pattern_identifier', $identifier)
              ->where('character_id', $player->character->id);
        });

        if (!$patternItem) {
            $itemLvl = $player->getLVL() + $values['reward_factor'];
            $type = null;
            $quality = null;

            switch ($values['reward_identifier']) {
                case 'random_epic':
                    $quality = 3;
                    break;
                case 'random_epic_weapon':
                    $quality = 3;
                    $type = Item::$TYPE_ID['weapon'];
                    break;
                case 'random_epic_gadget':
                    $quality = 3;
                    $type = Item::$TYPE_ID['gadget'];
                    break;
            }

            do {
                if (!$type) $type = mt_rand(1, 7);
                $items = ItemsList::$ITEMS[Item::$TYPE[$type]] ?? [];
                if (empty($items)) continue;
                $item = $items[mt_rand(0, count($items)-1)];
            } while (($item['quality'] != $quality || $item['required_level'] > $itemLvl));

            $item['character_id'] = $player->character->id;
            $item['pattern_identifier'] = $identifier;

            $patternItem = new PatternItems($item);
            $patternItem->save();

            if ($item['type'] == Item::$TYPE_ID['missiles']) {
                $patternItem->charges = 100;
                $patternItem->stat_critical_rating = $patternItem->stat_dodge_rating = $patternItem->stat_stamina = $patternItem->stat_strength = $patternItem->stat_weapon_damage = 0;
            } else {
                $patternItem = Utils::randomiseItem($patternItem, $itemLvl);
                if ($item['type'] == Item::$TYPE_ID['weapon']) {
                    $patternItem->stat_weapon_damage = round($patternItem->item_level * GameSettings::getConstant('item_weapon_damage_factor'));
                } else {
                    $patternItem->stat_weapon_damage = 0;
                }
            }
        }

        Core::req()->data = array(
            'user' => $player->user,
            'character' => $player->character,
            'item' => [
                'id' => $patternItem->id,
                'character_id' => $patternItem->character_id,
                'identifier' => $patternItem->identifier,
                'type' => $patternItem->type,
                'quality' => $patternItem->quality,
                'required_level' => $patternItem->required_level,
                'charges' => $patternItem->charges,
                'item_level' => $patternItem->item_level,
                'ts_availability_start' => $patternItem->ts_availability_start,
                'ts_availability_end' => $patternItem->ts_availability_end,
                'premium_item' => $patternItem->premium_item,
                'buy_price' => $patternItem->buy_price,
                'sell_price' => $patternItem->sell_price,
                'stat_stamina' => $patternItem->stat_stamina,
                'stat_strength' => $patternItem->stat_strength,
                'stat_critical_rating' => $patternItem->stat_critical_rating,
                'stat_dodge_rating' => $patternItem->stat_dodge_rating,
                'stat_weapon_damage' => $patternItem->stat_weapon_damage
            ]
        );
    }
}