<?php
namespace Schema;

use Srv\Record;
use JsonSerializable;

/*
    public class DungeonStatus 
    {

        public static const Unknown:int = 0;
        public static const Locked:int = 1;
        public static const OpenIntro:int = 2;
        public static const Open:int = 3;
        public static const Finished:int = 4;

    }

    public class DungeonMode 
    {

        public static const Normal:int = 0;
        public static const Hard:int = 1;
        public static const Repeatable:int = 2;

    }
*/

class Dungeons extends Record implements JsonSerializable{
    protected static $_TABLE = 'dungeons';
    
    public function jsonSerialize(){
        return $this->getData();
    }
    
    protected static $_FIELDS = [
        'id'=>0,
        'character_id'=>0,
        'identifier'=>'',
        'status'=>0,
        'current_dungeon_quest_id'=>0,
        'progress_index'=>0,
        'mode'=>0,
        'ts_last_complete'=>0
    ];
}