<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\Duel;
use Schema\Battle;
use Srv\DB;
use Cls\Player;
use PDO;

class getMissedDuelsNew{
    public function __request($player){	
		$duel = Duel::findAll(function($q)use($player){ $q->where('character_b_id', $player->character->id)->where('character_b_status',1)->orderBy('ts_creation', 'ASC'); });		
		shuffle ($duel);	
		
		$duelData = [];	
		$oppData = [];		
		
		foreach($duel as $val){
        $opp = DB::sql("SELECT `user_id` FROM `character` WHERE `id`={$val->character_a_id}")->fetch(PDO::FETCH_NUM);
		$battle = Battle::find(function($q)use($val){ $q->where('id', $val->battle_id); });		

		$duelData[] = [
				"id" => $val->id,
				"ts_creation" => $val->ts_creation,
				"winner" => $battle->winner,
				"character_b_rewards" => $val->character_b_rewards,
				"opponent_id" => $val->character_a_id,
				"unread" => $val->unread
		];
   $val->unread = 'false';
            $o = Player::findByUserId($opp[0]);
            $o->loadForDuel();
            $oppData[] = [
                "id" => $o->character->id,
    			"name" => $o->character->name,
				"gender" => $o->character->gender,
    			"level" => $o->character->level,
    			"stat_base_stamina" => $o->character->stat_total_stamina,
    			"stat_base_strength" => $o->character->stat_total_strength,
    			"stat_base_critical_rating" => $o->character->stat_total_critical_rating,
    			"stat_base_dodge_rating" => $o->character->stat_total_dodge_rating,
    			"stat_total_stamina" => $o->character->stat_total_stamina,
    			"stat_total_strength" => $o->character->stat_total_strength,
    			"stat_total_critical_rating" => $o->character->stat_total_critical_rating,
    			"stat_total_dodge_rating" => $o->character->stat_total_dodge_rating,
    			"stat_weapon_damage" => $o->character->stat_weapon_damage,
    			"honor" => $o->character->honor,
				"league_points" => $o->character->league_points,
				"league_group_id" => $o->character->league_group_id,
    			"online_status" => $o->character->online_status
            ];			
		}
		
		Core::req()->data = array(
			'user'=>$player->user,
		    'character'=>$player->character,
			'missed_duel_data'=>$duelData,
			'missed_duel_opponents'=>$oppData
		);
		
    }
}