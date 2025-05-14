<?php
namespace Request;

use Srv\Core;
use Schema\LeagueFight;

class checkForLeagueFightComplete{
    
    public function __request($player){
        if($player->character->active_league_fight_id == 0)
			return Core::setError("errStartDuelActiveDuelFound");
		
		$duel = LeagueFight::find(function($q)use($player){
			$q->where('id', $player->character->active_league_fight_id);
		});
		$duel->character_a_status = 2;
		
		Core::req()->data = array(
			"league_fight" => [
				"id" => $duel->id,
				"character_a_status" => 2
			]
		);
    }
}