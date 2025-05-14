<?php
namespace Schema;

use Srv\Record;
use JsonSerializable;

/*
    public class ItemQuality 
    {

        public static const Unknown:int = 0;
        public static const Common:int = 1;
        public static const Rare:int = 2;
        public static const Epic:int = 3;

    }
*/

class Sidekicks extends Record implements JsonSerializable{
    protected static $_TABLE = 'sidekicks';
    
    public function jsonSerialize(){
        return $this->getData();
    }
    
    protected static $_FIELDS = [
        'id'=>0,
        'identifier'=>'',
        'level'=>1,
        'character_id'=>0,
        'xp'=>0,
        'quality'=>0,
        'status'=>1,
        'stat_base_stamina'=>0,
        'stat_base_strength'=>0,
        'stat_base_critical_rating'=>0,
        'stat_base_dodge_rating'=>0,
        'stat_stamina'=>0,
        'stat_strength'=>0,
        'stat_critical_rating'=>0,
        'stat_dodge_rating'=>0,
        'name'=>'',
        'stage1_skill_id'=>0,
        'stage2_skill_id'=>0,
        'stage3_skill_id'=>0
    ];
}