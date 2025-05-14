<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\LeagueFightX;
use Cls\Player;

class startLeagueFight{
    
    public function __request($player){
        //Core::setError('errStartDuelAttackNotAllowed'); limit ataków 3 dziennie
        if($player->character->active_league_fight_id != 0)
            return Core::setError('errStartDuelActiveDuelFound');

		$max_fights = Config::get('constants.league_max_daily_league_fights');
        if($player->character->league_fight_count >= $max_fights)
            return Core::setError('errStartDuelActiveDuelFound');
            
        $use_premium = getField('use_premium',FIELD_BOOL,FALSE)=='true';
        
        if($use_premium){
            $cost = Config::get('constants.league_stamina_cost_premium');
            if($player->getPremium() < $cost)
                return Core::setError('errRemovePremiumCurrencyNotEnough');
        }else{
            $cost = Config::get('constants.league_stamina_cost');
            if($player->character->league_stamina < $cost)
                return Core::setError('errRemoveDuelStaminaNotEnough');
        }
        
        $opponentID = intval(getField("character_id", FIELD_NUM));
        if(!$opponentID)
            return Core::setError('errNoSuchUser');
        
        if($opponentID == $player->character->id)
			return Core::setError("errSelfAttackIsNotAllowed");
        
        $opponent = Player::findByCharacterId($opponentID);
        $opponent->loadForDuel();
        if(!$opponent)
            return Core::setError('errNoSuchUser');
            
        $leaguefight = new LeagueFightX($player, $opponent);
        $leaguefight->start();
        $leaguefight->save();
        
        $opponent->giveRewards($leaguefight->duel->character_b_rewards);
        $opponentEq = $opponent->getOnlyEquipedItems();
        
        $player->character->active_league_fight_id = $leaguefight->duel->id;
        $player->character->ts_last_league_fight = time();
		$player->character->league_fight_count += 1;
		
        //var_dump($use_premium);
        if($use_premium)
            $player->givePremium(-$cost);
        else{
            $player->character->ts_last_league_stamina_change = time();
            $player->character->league_stamina -= $cost;
        }
        
        Core::req()->data = array(
            "user" => $use_premium?$player->user:[],
			"character" => $player->character,
			"league_fight" => $leaguefight->duel,
			"battle" => $leaguefight->battle,
			"opponent" => $opponent->character,
			"opponent_inventory" => $opponentEq['inventory'],
			"opponent_inventory_items" => $opponentEq['items'],
			"inventory" => $player->inventory
        );
    }
}