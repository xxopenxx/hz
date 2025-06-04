<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\WorldbossEvent;

class startWorldbossEvent {
    public function __request($player = null) {
        $token = getField('token', FIELD_STR);
        if ($token !== Config::get('worldboss_admin_token')) {
            return Core::setError('errForbidden');
        }
        $eventKey = getField('event', FIELD_ALNUM);
        $eventsCfg = Config::get('worldboss_events');
        if (!isset($eventsCfg[$eventKey])) {
            return Core::setError('errInvalidEvent');
        }
        $cfg = $eventsCfg[$eventKey];
        $event = new WorldbossEvent([
            'identifier' => $eventKey,
            'npc_identifier' => $cfg['npc_identifier'],
            'min_level' => $cfg['min_level'],
            'max_level' => $cfg['max_level'],
            'status' => 1,
            'ts_start' => time(),
            'ts_end' => time() + $cfg['duration_hours'] * 3600,
            'npc_hitpoints_total' => $cfg['hp'],
            'npc_hitpoints_current' => $cfg['hp'],
            'top_attacker_name' => '',
            'top_attacker_count' => 0,
            'winning_attacker_name' => '',
            'reward_top_rank_item_identifier' => $cfg['reward_top_rank_item'],
            'reward_top_pool_item_identifier' => $cfg['reward_top_pool_item']
        ]);
        $event->save();
        // Reset danych graczy (przypisanie do eventu)
        \Srv\DB::sql("UPDATE `character` SET worldboss_event_id={$event->id}, worldboss_event_attack_count=0, active_worldboss_attack_id=0");
        Core::req()->data = ['worldboss_event_started' => $event->id];
    }
} 
