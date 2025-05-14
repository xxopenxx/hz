<?php
namespace Request;

use Srv\Core;
use Schema\GuildBattleRewards;
use Cls\Utils\GuildLogType;

class joinGuildDungeonBattle{
    public function __request($player){
        if($player->character->guild_id == 0)
            return Core::setError('errCharacterNoGuild');
            
        $attack = getField('attack',FIELD_BOOL)=='true';
        
        //if($player->guild->getPendingDungeon() != NULL)
        //    return Core::setError('errInvalidStateToPendingBattle');
        
        $pending = $player->guild->getPendingDungeon();
        //if( !(($attack && $pending->playerHasAcceptedAttack($player)) || (!$attack && $pending->playerHasAcceptedDefense($player))))
        //    return Core::setError('errAddCharacterIdAlreadyJoined');
        if(GuildBattleRewards::find(function($q)use($pending,$player){ $q->where('guild_battle_id',$pending->battle->id)->where('character_id',$player->character->id); }))
            return Core::setError('errJoinGuildBattleAlreadyFought');
        
       // if($attack){
            if(!$pending->battle->addPlayerToDungeonBattle($player))
                return Core::setError('errAddCharacterIdAlreadyJoined');
            $player->guild->addLog($player, GuildLogType::GuildDungeonBattle_Joined);
     //   }else{
           // if(!$pending->battle->addPlayerToBattleDefense($player))
              //  return Core::setError('errAddCharacterIdAlreadyJoined');
           // $player->guild->addLog($player, GuildLogType::GuildBattle_JoinedDefense);
      //  }
        
        Core::req()->data = array(
            "pending_guild_dungeon_battle"=>$pending->battle->getGuildDungeon()
        );
        
        //Blokada zaraz po przyłączeniu się do drużyny
        //Core::setError('errAddCharacterNoPermission');
        
        //Gdy drużyna zostanie usunięta, a chcemy obraniać
        //Core::setError('errAddCharacterInvalidGuild');
        
        //Ta walka drużynowa została już odbyta
        //Core::setError('errJoinGuildBattleInvalidGuildBattle');
        
        //Nie jesteś członkiem drużyny
        //Core::setError('errCharacterNoGuild');
        
        //Nie możesz wziąć udziału, ponieważ brałeś już w innej
        //Core::setError('errJoinGuildBattleAlreadyFought');
    }
}