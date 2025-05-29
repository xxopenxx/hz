<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\WorldbossAttack;

class attackWorldboss {
    public function __request($player) {
        $event = $player->getActiveWorldbossEvent();
        if (!$event || $player->character->level < $event->min_level || $player->character->level > $event->max_level) {
            return Core::setError('errNoEvent');
        }
        if ($player->character->active_worldboss_attack_id != 0)
            return Core::setError('errAlreadyInWorldbossAttack');
        if ($player->character->active_quest_id != 0)
            return Core::setError('errQuestInProgress');
        if ($player->character->active_duel_id != 0)
            return Core::setError('errDuelInProgress');
        if (!$player->hasMultitasking() && $player->character->active_training_id != 0)
            return Core::setError('errTrainingInProgress');
        $duration = Config::get('constants.worldboss_attack_duration');
        $attack = new WorldbossAttack([
            'character_id' => $player->character->id,
            'worldboss_event_id' => $event->id,
            'status' => 1,
            'ts_start' => time(),
            'ts_complete' => time() + $duration,
            'duration' => $duration,
            'duration_raw' => $duration,
            'battle_id' => 0,
            'total_damage' => 0,
            'coin_reward' => 0,
            'xp_reward' => 0
        ]);
        $attack->save();
        $player->character->active_worldboss_attack_id = $attack->id;
        Core::req()->data = [
            'character' => $player->character,
            'worldboss_attack' => [
                'id' => $attack->id,
                'worldboss_event_id' => $event->id,
                'status' => 1,
                'duration' => $duration,
                'ts_complete' => $attack->ts_complete,
                'battle_id' => 0,
                'total_damage' => 0
            ]
        ];
    }
} 