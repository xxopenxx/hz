<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\LeagueFight;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class deleteMissedLeagueFights{
    public function __request($player){	
		$league_fight_ids = getField("league_fight_ids");
		$league_fight = LeagueFight::find(function($q)use($player, $league_fight_ids){ $q->where('character_b_id', $player->character->id)->where('id',$league_fight_ids); });		
		$battle = Battle::find(function($q)use($league_fight){ $q->where('id', $league_fight->battle_id); });		
		
		Battle::delete(function($q)use($league_fight){ $q->where('id', $league_fight->battle_id); });	
		LeagueFight::delete(function($q)use($player, $league_fight_ids){ $q->where('character_b_id', $player->character->id)->where('id',$league_fight_ids); });	
    }
}