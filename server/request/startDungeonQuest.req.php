<?php
namespace Request;

use Srv\Core;
use Cls\QuestBattle;
use Cls\NPC;
use Cls\GameSettings;

class startDungeonQuest{
    
    public function __request($player){
        $dungeon_quest_id = intval(getField('dungeon_quest_id', FIELD_NUM));
        
        $dungeon_quest = $player->getDungeonQuestById($dungeon_quest_id);
        $dungeon = $player->getDungeonById($dungeon_quest->dungeon_id);
        $dungeon_time = $player->character->ts_last_dungeon_quest_fail + 3600;

        if($dungeon_quest == null)
            return Core::setError('errNoActiveQuest');

        if($dungeon_quest->status == 3)
            return Core::setError('errQuestAlreadyFinished');

        if(!$player->hasMultitasking() && $player->character->active_quest_id != 0)
            return Core::setError('errStartQuestActiveQuestFound');
        if($player->character->active_work_id != 0)
            return Core::setError('errStartQuestActiveWorkFound');
        if(!$player->hasMultitasking() && $player->character->active_training_id != 0)
            return Core::setError('errStartQuestActiveTrainingFound');

        if($dungeon_time > time() && $player->getPremium() < 2)
            return Core::setError('errStartDungeonQuestActiveCooldown');
            
        if($dungeon_time > time()) $player->givePremium(-2);

		$dungeon_quest->status = 2;
        /*
        $dungeon->current_dungeon_quest_id = $player->generateQuestAtDungeon($dungeon->id, $dungeon_id, 1);
        */
        
        $dungeon_quest_data = GameSettings::getConstant("dungeon_quest_templates.{$dungeon_quest->identifier}");
        $npc = new NPC($dungeon_quest_data["npc_identifier"]);
        $npc->loadDungeonNPC($dungeon_quest_data, $dungeon->mode);
        $questbattle = new QuestBattle($player, $npc);
        $questbattle->start();
        $questbattle->save();

        if($questbattle->battle->winner == 'b'){
            $rewards = json_decode($dungeon_quest->rewards, true);
            $rewards['coins'] = 0;
            $rewards['xp'] = 0;
            $rewards = json_encode($rewards);
            $dungeon_quest->rewards = $rewards;
        }

        $dungeon_quest->battle_id = $questbattle->battle->id;
        $player->character->active_dungeon_quest_id = $dungeon_quest->id;

        Core::req()->data = array(
		    'character'=>$player->character,
            'dungeon_quest'=>$dungeon_quest,
            'battle'=>$questbattle->battle
		);
    }
}