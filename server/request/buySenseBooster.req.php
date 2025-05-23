<?php
namespace Request;

use Srv\Core;
use Srv\Config;

class buySenseBooster{
    
    public function __request($player){
        $cost = Config::get('constants.booster_sense_costs_premium_currency_amount');
        if($player->getPremium() < $cost)
            return Core::setError('errRemovePremiumCurrencyNotEnough');
        $player->givePremium(-$cost);
        
        if($player->character->ts_active_sense_boost_expires == 0)
            $time = time();
        else
            $time = $player->character->ts_active_sense_boost_expires;
        $player->character->ts_active_sense_boost_expires = $time + Config::get('constants.booster_sense_duration');
        
        Core::req()->data = array(
            'character'=>$player->character
        );

        $player->updateCurrentGoalValue('booster_sense_use', 1);

        $currentBooster = $player->getCurrentGoalValue('booster_sense_used');
        $player->updateCurrentGoalValue('booster_sense_used', $currentBooster + 1);

        $currentBoosterDay = $player->getCurrentGoalValue('booster_sense_used_a_day');
        $player->updateCurrentGoalValue('booster_sense_used_a_day', $currentBoosterDay + 1);
    }
    
}