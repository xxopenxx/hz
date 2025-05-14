<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\Duel;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class deleteAllMissedDuels{
    public function __request($player){	
		$duel_ids = getField("duel_ids");
		$duel = Duel::find(function($q)use($player){ $q->where('character_b_id', $player->character->id); });		
		
		Battle::delete(function($q)use($duel){ $q->where('id', $duel->battle_id); });	
		Duel::delete(function($q)use($player){ $q->where('character_b_id', $player->character->id); });	
    }
}