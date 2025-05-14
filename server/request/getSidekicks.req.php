<?php
namespace Request;

use Srv\Core;
use Schema\Sidekicks;
use Cls\GameSettings;

error_reporting(0);

class getSidekicks{
    
    public function __request($player){
        $sidekicks = [];
        $my_sidekicks = Sidekicks::findAll(function($q) use($player) { $q->where('character_id', $player->character->id); });
        $settings = GameSettings::getConstant('item_templates');
        $i = 0;
        foreach($settings as $key => $item){
        	$item_type = explode("_", $key);
        	if($item_type[0] == "sidekick") {
        		$i--;
        		$item["id"] = $i;
        		$item['identifier'] = $key;
        		$item["name"] = "";
        		$item["level"] = 1;
        		$item["xp"] = 0;
        		$item['stat_base_stamina'] = 0;
				$item['stat_base_strength'] = 0;
				$item['stat_base_critical_rating'] = 0;
				$item['stat_base_dodge_rating'] = 0;
				$item['stat_stamina'] = 0;
				$item['stat_strength'] = 0;
				$item['stat_critical_rating'] = 0;
				$item['stat_dodge_rating'] = 0;
				$item['stage1_skill_id'] = 0;
				$item['stage2_skill_id'] = 0;
				$item['stage3_skill_id'] = 0;
				$item['ever_owned'] = false;
				$item['now_owned'] = false;
				$item['sources'] = ["shop_general", "surprise_box_surprise_box_sysadminday", "worldboss_event_general", "guild_dungeon", "slotmachine"];
        		$sidekicks[] = $item;
        	} 	
        }
        Core::req()->data = array(
            'user'=>$player->user,
            'character'=>$player->character,
            'all_sidekicks'=>$sidekicks,
            'sidekicks'=>$my_sidekicks
        );
    }
}