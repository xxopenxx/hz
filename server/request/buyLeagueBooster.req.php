<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\Utils;

class buyLeagueBooster{
    
    public function __request($player){
		
        $booster_id = getField('id');
		
		if($booster_id == "booster_league1"){
			$timeAdd = time() + Config::get('constants.booster_league1_duration');
			$player->character->ts_active_league_boost_expires = $timeAdd;
			$player->character->max_league_stamina = Config::get('constants.booster_league1_max_league_stamina');
			$player->character->active_league_booster_id = "booster_league1";
		}
		
		if($booster_id == "booster_league2"){
		    if($player->getPremium() < 10)
				return Core::setError("errRemoveGameCurrencyNotEnough");	
			$player->givePremium(-10);
			$timeAdd = time() + Config::get('constants.booster_league2_duration');
			$player->character->ts_active_league_boost_expires = $timeAdd;
			$player->character->max_league_stamina = Config::get('constants.booster_league2_max_league_stamina');
			$player->character->active_league_booster_id = "booster_league2";
		}
			
		$player->refreshLeagueStamina();
		
		Core::req()->data = array(
            'character'=>$player->character
	    );
    }
}