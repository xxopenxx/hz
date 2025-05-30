<?php
namespace Request;

use Srv\Core;
use Cls\Utils;

class improveCharacterStat{
    
    public function __request($player){
        $stat_type = intval(getField('stat_type', FIELD_NUM));
        $stat_name = Utils::getStatById($stat_type);
        if(!$stat_type || !$stat_name)
            return Core::setError('invExceptionType');
        
        if($player->character->stat_points_available > 0){ //free
            $player->character->stat_points_available--;
            $player->character->{"stat_base_$stat_name"}++;
        }else{
            $statMoney = Utils::calcNeededCoins($player->character->{"stat_bought_".$stat_name});
			if($player->getMoney() < $statMoney)
				return Core::setError("errRemoveGameCurrencyNotEnough");
			$player->character->{"stat_bought_$stat_name"}++;
			$player->character->{"stat_base_$stat_name"}++;
			$player->giveMoney(-$statMoney);

            $statPointBought = $player->getCurrentGoalValue('stat_point_bought');
            $player->updateCurrentGoalValue('stat_point_bought', $statPointBought + 1);
        }
        $player->calculateStats();

        $minStatValue = min(
            $player->character->stat_total_stamina,
            $player->character->stat_total_strength,
            $player->character->stat_total_critical_rating,
            $player->character->stat_total_dodge_rating
        );
        
        $player->updateCurrentGoalValue('all_stats_value_reached', $minStatValue);

        if(!$player->getTutorialFlag('stats_spent') && $player->character->stat_points_available == 0){
            $player->setTutorialFlag('stats_spent', true);
            Utils::getStartingItems($player);
            Core::req()->data = array(
                'character'=>$player->character,
                'inventory'=>$player->inventory,
                'items'=>$player->items
            );
        }else{
            Core::req()->data = array(
                'character'=>$player->character
            );
        }
    }
}