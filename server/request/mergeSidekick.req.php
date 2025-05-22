<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;
use Cls\GameSettings;

class mergeSidekick{
    public function __request($player){

        $source_sidekick_id = intval(getField('source_sidekick_id', FIELD_NUM));
        $target_sidekick_id = intval(getField('target_sidekick_id', FIELD_NUM));
        $skill_stage = intval(getField('skill_stage', FIELD_NUM));
        $confirm = getField('confirm', FIELD_BOOL)==='true';

        $sidekick_source = Sidekicks::find(function($q) use($source_sidekick_id, $player) { $q->where('id', $source_sidekick_id)->where('character_id', $player->character->id); });

        $sidekick_target = Sidekicks::find(function($q) use($target_sidekick_id, $player) { $q->where('id', $target_sidekick_id)->where('character_id', $player->character->id); });
        
        if(!$sidekick_source || !$sidekick_target || $source_sidekick_id == $target_sidekick_id)
            return Core::setError("errGetTheFuckOutOfHere");

        $skill_stage = "stage{$skill_stage}_skill_id";

        if($confirm){
            $cost = GameSettings::getConstant('sidekick_merge_premium_currency_amount');
            if($player->getPremium() < $cost) return Core::setError("errRemovePremiumCurrencyNotEnough");

            $player->givePremium(-$cost);

            $sidekick_target->$skill_stage = $sidekick_source->$skill_stage;
            $sidekick_target->level = 1;
            $sidekick_target->xp = 0;

            $sidekick_data = json_decode($player->inventory->sidekick_data)->orders;

            foreach ($sidekick_data as $key => $data) {
                if($data == $source_sidekick_id){
                    array_splice($sidekick_data, $key, 1);//if the array value is equal to the sidekicks id, unset it since its going bye bye :(
                }
            }

            $player->inventory->sidekick_data = json_encode(array("orders" => $sidekick_data));

            Sidekicks::delete(function($q)use($source_sidekick_id){ $q->where('id',$source_sidekick_id); });
        } else {
            /*
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
            */

            $sidekick_copy = [
                'id'=>$sidekick_target->id,
                'identifier'=>$sidekick_target->identifier,
                'level'=>1,
                'character_id'=>$sidekick_target->character_id,
                'xp'=>0,
                'quality'=>$sidekick_target->quality,
                'status'=>1,
                'stat_base_stamina'=>$sidekick_target->stat_base_stamina,
                'stat_base_strength'=>$sidekick_target->stat_base_strength,
                'stat_base_critical_rating'=>$sidekick_target->stat_base_critical_rating,
                'stat_base_dodge_rating'=>$sidekick_target->stat_base_dodge_rating,
                'stat_stamina'=>$sidekick_target->stat_stamina,
                'stat_strength'=>$sidekick_target->stat_strength,
                'stat_critical_rating'=>$sidekick_target->stat_critical_rating,
                'stat_dodge_rating'=>$sidekick_target->stat_dodge_rating,
                'name'=>'',
                'stage1_skill_id'=>$sidekick_target->stage1_skill_id,
                'stage2_skill_id'=>$sidekick_target->stage2_skill_id,
                'stage3_skill_id'=>$sidekick_target->stage3_skill_id
            ];

            $sidekick_copy->$skill_stage = $sidekick_source->$skill_stage;

            Core::req()->data = [
                "merged_sidekick"=>$sidekick_copy
            ];
        }

        Core::req()->data['character'] = $player->character;

        $firstSidekickMerged = $player->getCurrentGoalValue('first_sidekick_merged');
        if($firstSidekickMerged == 0) {
            $player->updateCurrentGoalValue('first_sidekick_merged', 1);
        }

        $sidekickMerged = $player->getCurrentGoalValue('sidekick_merged');
        $player->updateCurrentGoalValue('sidekick_merged', $sidekickMerged + 1);
    }
}