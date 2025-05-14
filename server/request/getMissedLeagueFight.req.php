<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\LeagueFight;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class getMissedLeagueFight{
    public function __request($player){	
	
		$league_fight_id = getField('league_fight_id', FIELD_NUM);
		$league_fight = LeagueFight::find(function($q)use($player, $league_fight_id){ $q->where('id', $league_fight_id)->where('character_b_id',$player->character->id); });		
		$battle = Battle::find(function($q)use($league_fight){ $q->where('id', $league_fight->battle_id); });		
		//$league_fight->unread = 'false';
		
		Core::req()->data = array(
			'user'=>$player->user,
		    'character'=>$player->character,
			'missed_league_fight'=>$league_fight,
			'missed_league_fight_battle'=>$battle
		);
		
    }
}