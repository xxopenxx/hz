<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Cls\QuestBattle;
use Cls\NPC;
use Schema\WorldbossAttack;

class checkForWorldbossAttackComplete {
    public function __request($player) {
        $attackId = $player->character->active_worldboss_attack_id;
        if ($attackId == 0)
            return Core::setError('errNoActiveWorldbossAttack');
        $attack = $player->getWorldbossAttackById($attackId);
        if (!$attack)
            return Core::setError('errAttackNotFound');
        $event = $player->getActiveWorldbossEvent();
        if (!$event || $event->id != $attack->worldboss_event_id)
            return Core::setError('errEventMismatch');
        if (time() < $attack->ts_complete)
            return Core::setError('errAttackNotComplete');
        if ($event->status != 1) {
            $attack->status = 2;
            $attack->save();
            $player->character->active_worldboss_attack_id = 0;
            return Core::setError('errEventEnded');
        }
        // Symulacja walki
        $npc = new NPC();
        $npc->identifier = $event->npc_identifier;
        $npc->level = max($player->character->level, $event->max_level);
        $npc->stamina = $event->npc_hitpoints_current; // uproszczone, można rozwinąć
        $npc->strength = 1000; // przykładowa wartość, dostosuj do balansu
        $npc->criticalrating = 1000;
        $npc->dodgerating = 1000;
        $npc->weapondamage = 0;
        $npc->hitpoints = $event->npc_hitpoints_current;
        $npc->damage_normal = $npc->strength;
        $npc->damage_bonus = $npc->strength;
        $npc->sidekicks = false;
        $battle = new QuestBattle($player, $npc);
        $battle->start();
        $battle->save();
        // Oblicz obrażenia zadane bossowi
        $damageToBoss = 0;
        $rounds = $battle->fight->getRounds();
        foreach ($rounds as $round) {
            if (isset($round['v'])) {
                $damageToBoss += $round['v'];
            }
        }
        if ($damageToBoss > $event->npc_hitpoints_current) {
            $damageToBoss = $event->npc_hitpoints_current;
        }
        $event->npc_hitpoints_current = max(0, $event->npc_hitpoints_current - $damageToBoss);
        $bossDefeated = ($event->npc_hitpoints_current == 0);
        if ($bossDefeated) {
            $event->winning_attacker_name = ($player->character->name == $event->top_attacker_name)
                ? $player->character->name
                : "2_" . $player->character->name;
            $event->status = 2;
        }
        $playerAttacksBefore = $player->character->worldboss_event_attack_count;
        if ($playerAttacksBefore + 1 > $event->top_attacker_count) {
            $event->top_attacker_name = $player->character->name;
            $event->top_attacker_count = $playerAttacksBefore + 1;
        }
        $event->save();
        $coinsReward = Config::get('constants.worldboss_reward_per_attack')['coins'];
        $xpReward = Config::get('constants.worldboss_reward_per_attack')['xp'];
        $player->character->game_currency += $coinsReward;
        $player->character->xp += $xpReward;
        $attack->status = 3;
        $attack->battle_id = $battle->battle->id;
        $attack->total_damage = $damageToBoss;
        $attack->coin_reward = $coinsReward;
        $attack->xp_reward = $xpReward;
        $attack->save();
        $player->character->active_worldboss_attack_id = 0;
        $player->character->worldboss_event_attack_count = $playerAttacksBefore + 1;
        // Suma nagród gracza
        $totalCoins = \Schema\WorldbossAttack::sum('coin_reward', function($q) use($player, $event) {
            $q->where('worldboss_event_id', $event->id)->where('character_id', $player->character->id);
        });
        $totalXp = \Schema\WorldbossAttack::sum('xp_reward', function($q) use($player, $event) {
            $q->where('worldboss_event_id', $event->id)->where('character_id', $player->character->id);
        });
        $eventData = $event->getData();
        $eventData['coin_reward_total'] = $totalCoins;
        $eventData['xp_reward_total'] = $totalXp;
        $eventData['coin_reward_next_attack'] = $coinsReward;
        $eventData['xp_reward_next_attack'] = $xpReward;
        $eventData['ranking'] = 0;
        Core::req()->data = [
            'character' => $player->character,
            'worldboss_event_character_data' => [$eventData],
            'worldboss_attack' => [
                'id' => $attack->id,
                'worldboss_event_id' => $event->id,
                'status' => 3,
                'duration' => $attack->duration,
                'ts_complete' => $attack->ts_complete,
                'battle_id' => $attack->battle_id,
                'total_damage' => $attack->total_damage
            ],
            'battle' => $battle->battle
        ];
    }
} 
