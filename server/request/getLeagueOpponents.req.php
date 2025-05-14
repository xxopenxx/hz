<?php
namespace Request;

use Srv\Core;
use Schema\Opponent;
use Cls\Player;
use Srv\DB;
use PDO;

class getLeagueOpponents{
    
    public function __request($player){
				
        $opp = DB::sql("SELECT `user_id` FROM `character` WHERE `league_group_id` <= {$player->character->league_group_id} AND `league_group_id` >= 0 AND `level` <= {$player->character->level} AND `id`<>{$player->character->id} ORDER BY `league_group_id` DESC LIMIT 3")->fetchALL(PDO::FETCH_NUM);
        
        if(count($opp) < 2)
            $opp = DB::sql("SELECT `user_id` FROM `character` WHERE `id`<>{$player->character->id} ORDER BY `league_group_id` ASC LIMIT 3")->fetchALL(PDO::FETCH_NUM);
        
        shuffle ( $opp );
        
        $oppData = [];
        foreach($opp as $val){
            $o = Player::findByUserId($val[0]);
            $o->loadForDuel();
			$opEq = $o->getOnlyEquipedItems();
            $oppData[] = [
			"opponent" => [
				"id" => $o->character->id,
				"name" => $o->character->name,
				"gender" => $o->character->gender,
				"level" => $o->character->level,
				"stat_base_stamina" => $o->character->stat_base_stamina,
				"stat_base_strength" => $o->character->stat_base_strength,
				"stat_base_critical_rating" => $o->character->stat_base_critical_rating,
				"stat_base_dodge_rating" => $o->character->stat_base_dodge_rating,
				"stat_total_stamina" => $o->character->stat_total_stamina,
				"stat_total_strength" => $o->character->stat_total_strength,
				"stat_total_critical_rating" => $o->character->stat_total_critical_rating,
				"stat_total_dodge_rating" => $o->character->stat_total_dodge_rating,
				"stat_weapon_damage" => $o->character->stat_weapon_damage,
				"honor" => $o->character->honor,
				"league_points" => $o->character->league_points,
				"league_group_id" => $o->character->league_group_id,
				"online_status" => $o->character->online_status,
				"appearance_skin_color" => $o->character->appearance_skin_color,
				"appearance_hair_color" => $o->character->appearance_hair_color,
				"appearance_hair_type" => $o->character->appearance_hair_type,
				"appearance_head_type" => $o->character->appearance_head_type,
				"appearance_eyes_type" => $o->character->appearance_eyes_type,
				"appearance_eyebrows_type" => $o->character->appearance_eyebrows_type,
				"appearance_nose_type" => $o->character->appearance_nose_type,
				"appearance_mouth_type" => $o->character->appearance_mouth_type,
				"appearance_facial_hair_type" => $o->character->appearance_facial_hair_type,
				"appearance_decoration_type" => $o->character->appearance_decoration_type,
				"show_mask" => $o->character->show_mask
			],
			"opponent_inventory" => $opEq['inventory'],
			"opponent_inventory_items" => $opEq['items']
            ];
        }
        
        Core::req()->data = array(
			'user'=>$player->user,
            'character'=>$player->character,
            'league_opponents'=>$oppData
        );
		
		//WZROST
		if($player->character->league_points >= 49 && $player->character->league_points <= 99 && $player->character->league_group_id == 100000){
			Core::req()->data['league_division_change'] = "2_1";  //Brąz II
			$player->character->league_group_id = 200000;
		}
		//SPADEK
		if($player->character->league_points < 49 && $player->character->league_group_id == 200000){
			Core::req()->data['league_division_change'] = "1_2"; //Brąz III
			$player->character->league_group_id = 100000;
		}	
		//WZROST
		if($player->character->league_points >= 100 && $player->character->league_points <= 199 && $player->character->league_group_id == 200000){
			Core::req()->data['league_division_change'] = "3_2"; //Brąz I
			$player->character->league_group_id = 300000;
		}
		//SPADEK
		if($player->character->league_points < 100 && $player->character->league_group_id == 300000){
			Core::req()->data['league_division_change'] = "2_3"; //Brąz II
			$player->character->league_group_id = 200000;
		}	
		//WZROST
		if($player->character->league_points >= 200 && $player->character->league_points <= 299 && $player->character->league_group_id == 300000){
			Core::req()->data['league_division_change'] = "4_3"; // Srebro III
			$player->character->league_group_id = 400000;
		}		
		//SPADEK
		if($player->character->league_points < 200 && $player->character->league_group_id == 400000){
			Core::req()->data['league_division_change'] = "3_4"; // Brąz I
			$player->character->league_group_id = 300000;
		}		
		//WZROST
		if($player->character->league_points >= 300 && $player->character->league_points <= 399 && $player->character->league_group_id == 400000){
			Core::req()->data['league_division_change'] = "5_4"; // Srebro II
			$player->character->league_group_id = 500000;
		}		
		//SPADEK
		if($player->character->league_points <=300 && $player->character->league_group_id == 500000){
			Core::req()->data['league_division_change'] = "4_3"; // Srebro III
			$player->character->league_group_id = 400000;
		}	
		//WZROST
		if($player->character->league_points >= 400 && $player->character->league_points <= 524 && $player->character->league_group_id == 500000){
			Core::req()->data['league_division_change'] = "6_5"; // Srebro I
			$player->character->league_group_id = 600000;
		}		
		//SPADEK
		if($player->character->league_points < 400 && $player->character->league_group_id == 600000){
			Core::req()->data['league_division_change'] = "5_4"; // Srebro II
			$player->character->league_group_id = 500000;
		}			
		//WZROST
		if($player->character->league_points >= 525 && $player->character->league_points <= 649 && $player->character->league_group_id == 600000){
			Core::req()->data['league_division_change'] = "7_6"; // Gold III
			$player->character->league_group_id = 700000;
		}	
		//SPADEK
		if($player->character->league_points < 525 && $player->character->league_group_id == 700000){
			Core::req()->data['league_division_change'] = "6_5"; // Srebro I
			$player->character->league_group_id = 600000;
		}			
		//WZROST
		if($player->character->league_points >= 650 && $player->character->league_points <= 774 && $player->character->league_group_id == 700000){
			Core::req()->data['league_division_change'] = "8_7"; // Gold II
			$player->character->league_group_id = 800000;
		}			
		//SPADEK
		if($player->character->league_points < 650 && $player->character->league_group_id == 800000){
			Core::req()->data['league_division_change'] = "7_6"; // Gold III
			$player->character->league_group_id = 700000;
		}	
		//WZROST
		if($player->character->league_points >= 775 && $player->character->league_points <= 899 && $player->character->league_group_id == 800000){
			Core::req()->data['league_division_change'] = "9_8"; // Gold I
			$player->character->league_group_id = 900000;
		}		
		//SPADEK
		if($player->character->league_points < 775 && $player->character->league_group_id == 900000){
			Core::req()->data['league_division_change'] = "8_7"; // Gold II
			$player->character->league_group_id = 800000;
		}			
		//WZROST
		if($player->character->league_points >= 900 && $player->character->league_group_id == 900000){
			Core::req()->data['league_division_change'] = "10_9"; // Champion
			$player->character->league_group_id = 1000000;
		}		
		//SPADEK
		if($player->character->league_points < 900 && $player->character->league_group_id == 1000000){
			Core::req()->data['league_division_change'] = "9_8"; // Gold I
			$player->character->league_group_id = 900000;
		}			
		
    }
    
}