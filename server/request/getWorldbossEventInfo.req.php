<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\WorldbossAttack;

class getWorldbossEventInfo {
    public function __request($player) {
        $event = $player->getActiveWorldbossEvent();
        if (!$event) {
            return Core::setError('errNoEvent');
        }
        $coins = WorldbossAttack::sum('coin_reward', function($q) use ($player, $event) {
            $q->where('character_id', $player->character->id)
              ->where('worldboss_event_id', $event->id);
        });
        $xp = WorldbossAttack::sum('xp_reward', function($q) use ($player, $event) {
            $q->where('character_id', $player->character->id)
              ->where('worldboss_event_id', $event->id);
        });
        $rewardCfg = Config::get('constants.worldboss_reward_per_attack');
        $eventData = $event->getData();
        $eventData['coin_reward_total'] = intval($coins);
        $eventData['xp_reward_total'] = intval($xp);
        $eventData['coin_reward_next_attack'] = $rewardCfg['coins'];
        $eventData['xp_reward_next_attack'] = $rewardCfg['xp'];
        $eventData['ranking'] = 0;
        Core::req()->data = [
            'character' => $player->character,
            'worldboss_event_character_data' => [ $eventData ]
        ];
    }
}
