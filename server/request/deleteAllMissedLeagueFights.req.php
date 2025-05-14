<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\LeagueFight;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class deleteAllMissedLeagueFights{
    public function __request($player){	
		$league_figt = LeagueFight::find(function($q)use($player){ $q->where('character_b_id', $player->character->id); });		
		
		Battle::delete(function($q)use($league_figt){ $q->where('id', $league_figt->battle_id); });	
		LeagueFight::delete(function($q)use($player){ $q->where('character_b_id', $player->character->id); });	
    }
}