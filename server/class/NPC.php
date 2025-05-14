<?php
namespace Cls;

use Cls\Entity;
use Srv\Config;
use Cls\Utils\QuestList;

class NPC extends Entity{
    
    public $identifier = '';

    public function loadDungeonNPC($data, $mode){
        $multiplier = 1;

        if($mode == 2) $multiplier = $data["repeatable_difficulty_factor"]; //if repeatable, set multiplier to repeatable diff factor
        //exit(var_dump($multiplier));

        if($mode > 0) $mode = "hard";
        else $mode = "normal";  

        //$diffName = QuestList::$DIFFICULTY[$difficulty];
        $this->level = round($data["npc_level_{$mode}"] * $multiplier);
        $this->stamina = round($data["npc_stat_stamina_{$mode}"] * $multiplier);
        $this->strength = round($data["npc_stat_strength_{$mode}"] * $multiplier);
        $this->criticalrating = round($data["npc_stat_critical_rating_{$mode}"] * $multiplier);
        $this->dodgerating = round($data["npc_stat_dodge_rating_{$mode}"] * $multiplier);
        $this->weapondamage = round($data["npc_stat_weapon_damage_{$mode}"] * $multiplier);
        $this->hitpoints = $this->stamina * Config::get('constants.battle_hp_scale');
        $this->damage_normal = $this->strength + $this->weapondamage;
        $this->damage_bonus = $this->damage_normal;
		$this->sidekicks = false;
    }
    
    public function loadNPC($identifier){
        $this->identifier = $identifier;
		$this->sidekicks = false;
    }

    public function randomiseQuestStats($player, $difficulty){
        $diffName = QuestList::$DIFFICULTY[$difficulty];
        $percMin = Config::get("constants.fight_quest_npc_stat_percentage_min_{$diffName}");
        $percMax = Config::get("constants.fight_quest_npc_stat_percentage_max_{$diffName}");
        $this->level = $player->getLVL();
        $this->stamina = round($player->character->stat_total_stamina * random($percMin, $percMax));
        $this->strength = round($player->character->stat_total_strength * random($percMin, $percMax));
        $this->criticalrating = round($player->character->stat_total_critical_rating * random($percMin, $percMax));
        $this->dodgerating = round($player->character->stat_total_dodge_rating * random($percMin, $percMax));
        $this->weapondamage = 0;
        $this->hitpoints = $this->stamina * Config::get('constants.battle_hp_scale');
        $this->damage_normal = $this->strength + $this->weapondamage;
        $this->damage_bonus = $this->damage_normal;
		$this->sidekicks = false;
    }
    
}