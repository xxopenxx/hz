<?php
namespace Request;

use Srv\Core;
use Schema\GuildBattleRewards;

class claimGuildBattleReward{
    public function __request($player){
        $battleid = intval(getField('guild_battle_id', FIELD_NUM));
        
        $reward = GuildBattleRewards::find(function($q)use($battleid,$player){ $q->where('guild_battle_id',$battleid)->where('character_id',$player->character->id); });
        if(!$reward)
            return Core::setError('');
        
        $player->giveMoney($reward->game_currency);
        //TODO: dac item jezeli istnieje | moze byc error: errInventoryNoEmptySlot
        $reward->remove();
        
        $guild_battle = GuildBattle::find(function($q)use($battleid,$player){ $q->where('id',$battleid); });
        if ($guild_battle->guild_winner_id = $player->character->guild_id) {
            $guildBattlesWon = $player->getCurrentGoalValue('guild_battles_won');
            $player->updateCurrentGoalValue('guild_battles_won', $guildBattlesWon + 1);
        } else {
            $guildBattlesLost = $player->getCurrentGoalValue('guild_battles_lost');
            $player->updateCurrentGoalValue('guild_battles_lost', $guildBattlesLost + 1);
        }

        Core::req()->data = [
            'character'=>$player->character
        ];
        
        if($player->inventory->sidekick_id)
            Core::req()->data['sidekick'] = $player->sidekicks;

        $firstGuildBattleFought = $player->getCurrentGoalValue('first_guild_battle_fought');
        if ($firstGuildBattleFought == 0) {
            $player->updateCurrentGoalValue('first_guild_battle_fought', 1);
        }

        $guildBattlesFought = $player->getCurrentGoalValue('guild_battles_fought');
        $player->updateCurrentGoalValue('guild_battles_fought', $guildBattlesFought + 1);
    }
}