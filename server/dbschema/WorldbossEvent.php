<?php
namespace Schema;

use Srv\Record;
use JsonSerializable;

class WorldbossEvent extends Record implements JsonSerializable {
    protected static $_TABLE = 'worldboss_event';
    protected static $_FIELDS = [
        'id' => 0,
        'identifier' => '',
        'npc_identifier' => '',
        'stage' => 1,
        'min_level' => 0,
        'max_level' => 0,
        'status' => 0,
        'ts_start' => 0,
        'ts_end' => 0,
        'npc_hitpoints_total' => 0,
        'npc_hitpoints_current' => 0,
        'top_attacker_name' => '',
        'top_attacker_count' => 0,
        'winning_attacker_name' => '',
        'reward_top_rank_item_identifier' => '',
        'reward_top_pool_item_identifier' => ''
    ];

    public function jsonSerialize() {
        return $this->getData();
    }
} 