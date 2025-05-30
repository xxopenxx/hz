<?php
namespace Request;

use Srv\Core;
use Cls\Utils;

class refreshShopItems{
    
    public function __request($player){
        
        if($player->character->shop_refreshes > 0){
            if($player->getPremium() < 1)
    			return Core::setError("errRemovePremiumCurrencyNotEnough");
    		$player->givePremium(-1);
        }
        
        Utils::refreshShopItems($player);
        
        $player->character->ts_last_shop_refresh = time();
        $player->character->shop_refreshes++;
        
        if ($player->character->shop_refreshes == 1) {
            $player->updateCurrentGoalValue('shop_refresh', 1);
        }

        $player->updateCurrentGoalValue('shop_refreshed', $player->character->shop_refreshes);

        $todayShopRefreshes = $player->getCurrentGoalValue('shop_refreshed_a_day');
        $player->updateCurrentGoalValue('shop_refreshed_a_day', $todayShopRefreshes + 1);

        Core::req()->data = array(
            'character'=>$player->character,
            'inventory'=>$player->inventory,
            'items'=>$player->getItems()
        );
    }
}