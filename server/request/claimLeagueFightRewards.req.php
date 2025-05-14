<?php
namespace Request;

use Srv\Core;

class claimLeagueFightRewards{
    
    public function __request($player){
        if($player->character->active_league_fight_id == 0)
            return Core::setError('errClaimDuelRewardsNoActiveDuel');
            
        $player->giveRewards($player->league_fight->character_a_rewards);
        
        $player->character->active_league_fight_id = 0;
        $player->league_fight->character_a_status = 3;
        
        if($player->inventory->sidekick_id){
            Core::req()->data['sidekick'] = $player->sidekicks;
		}	

        Core::req()->data = array(
            "user" => array(),
			"character" => $player->character,
			"league_fight" => [
				"id" => $player->league_fight->id,
				"character_a_status" => 3
			]
        );
		//Core::req()->data['league_division_change'] = "2_3";  //3 -> 1, 2 -> 2, 3 -> 3
		
		/* Bez komentarza xD */

		//WZROST
		if($player->character->league_points >= 49 && $player->character->league_points <= 99 && $player->character->league_group_id == 100000){
			Core::req()->data['league_division_change'] = "2_1";  //Brąz II
			$player->character->league_group_id = 200000;
		}
		//SPADEK
		if($player->character->league_points <= 49 && $player->character->league_group_id == 200000){
			Core::req()->data['league_division_change'] = "1_2"; //Brąz III
			$player->character->league_group_id = 100000;
		}	
		//WZROST
		if($player->character->league_points >= 100 && $player->character->league_points <= 199 && $player->character->league_group_id == 200000){
			Core::req()->data['league_division_change'] = "3_2"; //Brąz I
			$player->character->league_group_id = 300000;
		}
		//SPADEK
		if($player->character->league_points <= 100 && $player->character->league_group_id == 300000){
			Core::req()->data['league_division_change'] = "2_3"; //Brąz II
			$player->character->league_group_id = 200000;
		}	
		//WZROST
		if($player->character->league_points >= 200 && $player->character->league_points <= 299 && $player->character->league_group_id == 300000){
			Core::req()->data['league_division_change'] = "4_3"; // Srebro III
			$player->character->league_group_id = 400000;
		}		
		//SPADEK
		if($player->character->league_points <= 200 && $player->character->league_group_id == 400000){
			Core::req()->data['league_division_change'] = "3_4"; // Brąz I
			$player->character->league_group_id = 300000;
		}		
		//WZROST
		if($player->character->league_points >= 300 && $player->character->league_points <= 399 && $player->character->league_group_id == 400000){
			Core::req()->data['league_division_change'] = "5_4"; // Srebro II
			$player->character->league_group_id = 500000;
		}		
		//SPADEK
		if($player->character->league_points <= 300 && $player->character->league_group_id == 500000){
			Core::req()->data['league_division_change'] = "4_3"; // Srebro III
			$player->character->league_group_id = 400000;
		}	
		//WZROST
		if($player->character->league_points >= 400 && $player->character->league_points <= 524 && $player->character->league_group_id == 500000){
			Core::req()->data['league_division_change'] = "6_5"; // Srebro I
			$player->character->league_group_id = 600000;
		}		
		//SPADEK
		if($player->character->league_points <= 400 && $player->character->league_group_id == 600000){
			Core::req()->data['league_division_change'] = "5_4"; // Srebro II
			$player->character->league_group_id = 500000;
		}			
		//WZROST
		if($player->character->league_points >= 525 && $player->character->league_points <= 649 && $player->character->league_group_id == 600000){
			Core::req()->data['league_division_change'] = "7_6"; // Gold III
			$player->character->league_group_id = 700000;
		}	
		//SPADEK
		if($player->character->league_points <= 525 && $player->character->league_group_id == 700000){
			Core::req()->data['league_division_change'] = "6_5"; // Srebro I
			$player->character->league_group_id = 600000;
		}			
		//WZROST
		if($player->character->league_points >= 650 && $player->character->league_points <= 774 && $player->character->league_group_id == 700000){
			Core::req()->data['league_division_change'] = "8_7"; // Gold II
			$player->character->league_group_id = 800000;
		}			
		//SPADEK
		if($player->character->league_points <= 650 && $player->character->league_group_id == 800000){
			Core::req()->data['league_division_change'] = "7_6"; // Gold III
			$player->character->league_group_id = 700000;
		}	
		//WZROST
		if($player->character->league_points >= 775 && $player->character->league_points <= 899 && $player->character->league_group_id == 800000){
			Core::req()->data['league_division_change'] = "9_8"; // Gold I
			$player->character->league_group_id = 900000;
		}		
		//SPADEK
		if($player->character->league_points <= 775 && $player->character->league_group_id == 900000){
			Core::req()->data['league_division_change'] = "8_7"; // Gold II
			$player->character->league_group_id = 800000;
		}			
		//WZROST
		if($player->character->league_points >= 900 && $player->character->league_group_id == 900000){
			Core::req()->data['league_division_change'] = "10_9"; // Champion
			$player->character->league_group_id = 1000000;
		}		
		//SPADEK
		if($player->character->league_points <= 900 && $player->character->league_group_id == 1000000){
			Core::req()->data['league_division_change'] = "9_8"; // Gold I
			$player->character->league_group_id = 900000;
		}			
		
        //TODO: remove missile item
        //if($player->getItemFromSlot('missiles_item_id') != null)
        //    Core::req()->data += array("items"=>array($player->getItemFromSlot('missiles_item_id')));
    }
}