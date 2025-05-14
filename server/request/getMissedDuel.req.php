<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\Duel;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class getMissedDuel{
    public function __request($player){	
	
		$duel_id = getField('duel_id', FIELD_NUM);
		$duel = Duel::find(function($q)use($player, $duel_id){ $q->where('id', $duel_id)->where('character_b_id',$player->character->id); });		
		$battle = Battle::find(function($q)use($duel){ $q->where('id', $duel->battle_id); });		
		//$duel->unread = 'false';
		
		Core::req()->data = array(
			'user'=>$player->user,
		    'character'=>$player->character,
			'missed_duel'=>$duel,
			'missed_duel_battle'=>$battle
		);
		
    }
}