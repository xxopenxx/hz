<?php
namespace Schema;

use Srv\Record;
use JsonSerializable;

/*
    public class DungeonQuestStatus 
    {

        public static const Unknown:int = 0;
        public static const Created:int = 1;
        public static const Fought:int = 2;
        public static const Finished:int = 3;
        public static const RewardsProcessed:int = 4;

    }

    public class DungeonMode 
    {

        public static const Normal:int = 0;
        public static const Hard:int = 1;
        public static const Repeatable:int = 2;

    }
*/

class DungeonQuests extends Record implements JsonSerializable{
    protected static $_TABLE = 'dungeon_quests';
    
    public function jsonSerialize(){
        return $this->getData();
    }
    
    protected static $_FIELDS = [
        'id'=>0,
        'character_id'=>0,
        'character_level'=>0,
        'identifier'=>'',
        'status'=>1,
        'battle_id'=>0,
        'rewards'=>'',
        'mode'=>0,
        'dungeon_id'=>0
    ];
}