<?php
namespace Request;

use Srv\Core;
use Cls\Guild;

class getGuild{
    public function __request($player){
        $guild_id = intval(getField('guild_id',FIELD_NUM));
        
        $guild = Guild::find(function($q)use($guild_id){ $q->where('id',$guild_id); });
        
        if(!$guild)
            return Core::setError('errNoSuchGuild');
        
        Core::req()->data = array(
			"user" => $player->user,
			"character" => $player->character,
			"requested_guild" => [
				"ts_creation" => $guild->ts_creation,
				"name" => $guild->name,
				"description" => $guild->description,
				"status" => $guild->status,
				"accept_members" => $guild->accept_members,
				"locale" => "pl_PL",
				"honor" => $guild->honor,
				"artifact_ids" => $guild->artifact_ids,
				"battles_fought" => $guild->battles_fought,
				"battles_attacked" => $guild->battles_attacked,
				"battles_defended" => $guild->battles_defended,
				"battles_won" => $guild->battles_won,
				"battles_lost" => $guild->battles_lost,
				"dungeon_battles_fought" => $guild->dungeon_battles_fought,
				"dungeon_battles_won" => $guild->dungeon_battles_won,
				"missiles_fired" => $guild->missiles_fired,
				"auto_joins_used" => $guild->auto_joins_used,
				"artifacts_won" => $guild->artifacts_won,
				"artifacts_lost" => $guild->artifacts_lost,
				"artifacts_owned_max" => $guild->artifacts_owned_max,
				"artifacts_owned_current" => $guild->artifacts_owned_current,
				"forum_page" => $guild->forum_page,
				"stat_guild_capacity" => $guild->stat_guild_capacity,
				"stat_character_base_stats_boost" => $guild->stat_character_base_stats_boost,
				"stat_quest_xp_reward_boost" => $guild->stat_quest_xp_reward_boost,
				"stat_quest_game_currency_reward_boost" => $guild->stat_quest_game_currency_reward_boost,
				"arena_background" => $guild->arena_background,
				"emblem_background_shape" => $guild->emblem_background_shape,
				"emblem_background_color" => $guild->emblem_background_color,
				"emblem_background_border_color" => $guild->emblem_background_border_color,
				"emblem_icon_shape" => $guild->emblem_icon_shape,
				"emblem_icon_color" => $guild->emblem_icon_color,
				"emblem_icon_size" => $guild->emblem_icon_size,
				"min_apply_level" => $guild->min_apply_level,
				"min_apply_honor" => $guild->min_apply_honor,
				"active_training_booster_id" => $guild->active_training_booster_id,
				"ts_active_training_boost_expires" => $guild->ts_active_training_boost_expires,
				"active_quest_booster_id" => $guild->active_quest_booster_id,
				"ts_active_quest_boost_expires" => $guild->ts_active_quest_boost_expires,
				"active_duel_booster_id" => $guild->active_duel_booster_id,
				"ts_active_duel_boost_expires" => $guild->ts_active_duel_boost_expires,
				"apply_open" => true,
				"guild_competition_reward_boost_factor" => 0,
				"ts_guild_competition_reward_boost_expires" => 0,
				"id" => $guild->id,
				"server_id" => "pl1"
			],
			"requested_guild_members" => $guild->getMembers()
		);
    }
}