<?php
namespace Request;

use Srv\Core;
use Cls\Player;
use Schema\User;
use Schema\Dungeons;
use Schema\Sidekicks;
use Schema\Hideout;
use Schema\HideoutRoom;

class loginUser{
    
    public function __request($player=null, $uid=false, $exssid = false){
        if(!$exssid || !$uid){
        	$email = getField('email', FIELD_EMAIL);
        	if(!User::exists(function($q)use($email){ $q->where('email',$email); }))
        		return Core::setError('errLoginNoSuchUser');
        	$pass = getField('password');
        	if(!$email || !$pass || !($player = Player::login($email, $pass)))
        		return Core::setError('errLoginInvalid');
        }else
        	if(!($player = Player::findBySSID($uid, $exssid)))
        		return Core::setError('errLoginNoSuchSessionId');
        		
        $player->user->session_id = md5(microtime());
        setcookie("ssid", $player->user->session_id, time() + 63072000, '/');
        $player->user->last_login_ip = getclientip();
        $player->user->ts_last_login = time();
        $player->user->login_count++;
        
        $dailyLogin = $player->getDailyBonuses();
        
        if(empty($player->dungeons)){
            for($i = 1; $i <= 9; $i++){
                $dungeons = new Dungeons([
                    'character_id'=>$player->character->id,
                    'identifier'=>'dungeon'.$i,
                    'status'=>2,
                ]);
                $dungeons->save();
                $player->dungeons[] = $dungeons;
            }
        }

        if(empty($player->sidekicks) && $player->getLVL() >= 60 && !$player->character->received_sidekick){
            $skills = randomSidekickSkills();
            $q = new Sidekicks([
                'character_id'=>$player->character->id,
                'identifier'=>"sidekick_dog1",
                'quality'=>3,
                'stat_base_stamina'=>60,
                'stat_base_strength'=>100,
                'stat_base_critical_rating'=>40,
                'stat_base_dodge_rating'=>23,
                'stat_stamina'=>60,
                'stat_strength'=>100,
                'stat_critical_rating'=>40,
                'stat_dodge_rating'=>23,
                'stage1_skill_id'=>$skills[0],
                'stage2_skill_id'=>$skills[1],
                'stage3_skill_id'=>$skills[2]
            ]);
            $q->save();

            $sidekick_data = array();
            $sidekick_data[] = $q->id;
            $player->character->received_sidekick = 1;
            $player->inventory->sidekick_data = json_encode(array("orders" => $sidekick_data));
        }

        Core::req()->data = array(
            "user"=>$player->user,
            "character"=>$player->character,
            "bank_inventory"=>$player->bankinv,
            "inventory"=>$player->inventory, //eq
            "items"=>$player->items, //itemy
            "quests"=>$player->quests, //questy
            "dungeons"=>$player->dungeons, //dungeons
            "dungeon_quests"=>$player->dungeon_quests, //dungeon quests
            //
            "advertisment_info"=>$this->advInfo(),
            "bonus_info"=>$this->bonusInfo(),
            "campaigns"=>array(),
            "collected_goals"=>array(),
            "collected_item_pattern"=>array(),
            "current_goal_values"=>$this->currGoal(),
            "current_item_pattern_values"=>$this->itemPatt(),
            "item_offers"=>array(),
            "league_locked"=>false,
            "league_season_end_timestamp"=>0,
            "local_notification_settings"=>$this->notif(),
			"missed_hideout_attacks"=>0,
            "login_count"=>$player->user->login_count,
            "missed_duels"=>0,
            "missed_league_fights"=>0,
			"new_messages"=>0,
            "new_guild_log_entries"=>0,
            "new_version"=>false,
            "reskill_enabled"=>false,
            "server_timestamp_offset"=>Core::getTimestampOffset(),
            "show_advertisment"=>false,
            "show_preroll_advertisment"=>false,
            "special_offers"=>array(),
            "tos_update_needed"=>false,
			"ad_provider_keys"=>array(),
            "tournament_end_timestamp"=>0,
            "user_geo_location"=>"xX",
            "worldboss_event_character_data"=>array()
        );
        if($player->guild != null){
        	Core::req()->data['guild']= $player->guild;
        	Core::req()->data['guild_members']=$player->guild->getMembers();
        	if(count($player->guild->getBattleRewards()))
        		Core::req()->data['guild_battle_rewards'] = $player->guild->getBattleRewards();
        	if(($finishedAttack = $player->guild->getFinishedAttack()) != NULL){
        		Core::req()->data['finished_guild_battle_attack'] = $finishedAttack->battle->getDataForAttacker();
        		Core::req()->data['guild_battle_guilds'][] = $finishedAttack->gDefender;
        	}
        	if(($finishedDefense = $player->guild->getFinishedDefense()) != NULL){
        		Core::req()->data['finished_guild_battle_defense'] = $finishedDefense->battle->getDataForDefender();
        		Core::req()->data['guild_battle_guilds'][] = $finishedDefense->gAttacker;
        	}
        	if(($pendingAttack = $player->guild->getPendingAttack()) != NULL){
        		Core::req()->data['pending_guild_battle_attack'] = $pendingAttack->battle->getDataForAttacker();
        		Core::req()->data['guild_battle_guilds'][] = $pendingAttack->gDefender;
        	}
        	if(($pendingDefense = $player->guild->getPendingDefense()) != NULL){
        		Core::req()->data['pending_guild_battle_defense'] = $pendingDefense->battle->getDataForDefender();
        		Core::req()->data['guild_battle_guilds'][] = $pendingDefense->gAttacker;
        	}
			
        	/*if(($pendingDungeonAttack = $player->guild->getPendingDungeon()) != NULL){
        		Core::req()->data['pending_guild_dungeon_battle'] = $pendingDungeonAttack->battle->getGuildDungeon();
        	}	
			
			if(($finishedDungeonAttack = $player->guild->getFinishedDungeon()) != NULL){
        		Core::req()->data['finished_guild_dungeon_battle_id'] = $pendingDungeonAttack->battle->getGuildDungeonFinished();
        	}	*/
			
        }
        if($player->character->active_work_id)
        	Core::req()->data["work"]= $player->work;
        if($player->character->active_training_id)
        	Core::req()->data["training"]= $player->training;
        if($player->inventory->sidekick_id)
            Core::req()->data["sidekick"]= $player->sidekicks;
        if($player->hideout)
            Core::req()->data["hideout"] = $player->hideout;
        if($player->hideout_room)
            Core::req()->data["hideout_room"] = $player->hideout_room;
        if($player->hideout_rooms)
            Core::req()->data["hideout_rooms"] = $player->hideout_rooms;
		
		Core::req()->data["missed_duels"] = $player->getMissedDuels();
		Core::req()->data["missed_league_fights"] = $player->getMissedLeagueFights();
	   //
        //Core::req()->data += array('missed_duels'=>Core::db()->query('SELECT COUNT(*) FROM '.DataBase::getTable('duel').' WHERE `character_b_status` = 1 AND `character_b_id`='.$this->player->characterID)->fetch(PDO::FETCH_NUM)[0]);
        //
        if($player->battle)
        	Core::req()->data['battle'] = $player->battle;
        if($player->character->active_duel_id)
        	Core::req()->data['duel'] = $player->duel;
        if($player->character->active_league_fight_id)
        	Core::req()->data['league_fight'] = $player->league_fight;
        if(count($player->battles))
        	Core::req()->data['battles'] = $player->battles;
        //
        Core::req()->data['new_messages'] = $player->getUnreadedMessagesCount();
        //
        if($dailyLogin !== FALSE){
        	Core::req()->data['daily_login_bonus_rewards'] = $dailyLogin;
        	Core::req()->data['daily_login_bonus_day'] = $player->character->daily_login_bonus_day;
        }
    }
    
    private function advInfo(){
        $adv = [
			"show_advertisment"=> true,
			"show_preroll_advertisment"=> false,
			"show_left_skyscraper_advertisment"=> false,
			"show_pop_under_advertisment"=> false,
			"show_footer_billboard_advertisment"=> false,
			"advertisment_refresh_rate"=> 15,
			"mobile_interstitial_cooldown"=> 1800,
			"remaining_video_advertisment_cooldown__1"=> 0,
			"video_advertisment_blocked_time__1"=> 1800,
			"remaining_video_advertisment_cooldown__2"=> 0,
			"video_advertisment_blocked_time__2"=> 1800,
			"remaining_video_advertisment_cooldown__3"=> 0,
			"video_advertisment_blocked_time__3"=> 1800,
			"remaining_video_advertisment_cooldown__4"=> 0,
			"video_advertisment_blocked_time__4"=> 1800,
			"remaining_video_advertisment_cooldown__5"=> 0,
			"video_advertisment_blocked_time__5"=> 7200
		];
		return $adv;
    }
    
    private function bonusInfo(){
        $b = array(
				"quest_energy"=> 0,//$this->characterData["quest_energy"],
				"duel_stamina"=> 0,//$this->characterData["duel_stamina"],
				"league_stamina"=> 0,//$this->characterData["league_stamina"],
				"training_count"=> 0,//$this->characterData["training_count"]
			);
		return $b;
    }
    
	private function currGoal(){
		$b = array(
			"level_reached" => [
			"value" => 1,
			"current_value" => 1
			],
			"stage_reached" => [
			"value" => 1,
			"current_value" => 1
			],
			"second_quests_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"quests_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"second_duel_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"duels_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"second_day_logged_in" => [
			"value" => 1,
			"current_value" => 1
			],
			"days_logged_in" => [
			"value" => 1,
			"current_value" => 1
			],
			"shop_refresh" => [
			"value" => 0,
			"current_value" => 0
			],
			"shop_refreshed" => [
			"value" => 0,
			"current_value" => 0
			],
			"time_worked" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_training_absolved" => [
			"value" => 0,
			"current_value" => 0
			],
			"trainings_absolved" => [
			"value" => 0,
			"current_value" => 0
			],
			"honor_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"all_stats_value_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"duels_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"duels_won_in_row" => [
			"value" => 0,
			"current_value" => 0
			],
			"duels_lost" => [
			"value" => 0,
			"current_value" => 0
			],
			"fight_quests_won_hard" => [
			"value" => 0,
			"current_value" => 0
			],
			"player_profile_visit" => [
			"value" => 0,
			"current_value" => 0
			],
			"leaderboard_visit" => [
			"value" => 0,
			"current_value" => 0
			],
			"stat_point_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"rare_item_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"epic_item_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"mission_booster_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"stats_booster_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"work_booster_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"character_full_equipped" => [
			"value" => 0,
			"current_value" => 0
			],
			"energy_bought" => [
			"value" => 0,
			"current_value" => 0
			],
			"item_sold" => [
			"value" => 0,
			"current_value" => 0
			],
			"tutorial_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"coins_spent_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_fight1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_fight2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_fight3" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage1_time5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_fight6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_fight10" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage2_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_fight4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_fight6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage3_time12" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage4_fight3" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage4_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage4_time8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage4_time15" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage4_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage5_fight1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage5_fight2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage5_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage5_time11" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage5_time15" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time15" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage6_time19" => [
			"value" => 0,
			"current_value" => 0
			],
			"duels_started_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"shop_refreshed_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"missles_fired" => [
			"value" => 0,
			"current_value" => 0
			],
			"donuts_spent" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_joined" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_donated" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_visited" => [
			"value" => 0,
			"current_value" => 0
			],
			"account_confirmed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_guild_battle_fought" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_battles_fought" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_battles_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_battles_lost" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_guild_artifact_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_artifacts_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time12" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_fight1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_fight2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_fight6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage7_fight9" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_worldboss_attack_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"worldboss_attacks_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_worldboss_event_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_fight3" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time12" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time15" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time18" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage8_time20" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon1_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon1_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon1_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon2_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon2_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon2_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon3_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon3_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon3_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon4_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon4_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon4_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon5_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon5_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon5_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_item_washed" => [
			"value" => 0,
			"current_value" => 0
			],
			"item_washed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_item_sewed" => [
			"value" => 0,
			"current_value" => 0
			],
			"item_sewed" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_fight1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_fight5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time10" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage9_time18" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_fight1" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time9" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage10_time20" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_guild_dungeon_fought" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_dungeons_fought" => [
			"value" => 0,
			"current_value" => 0
			],
			"guild_dungeons_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"tournament_attended" => [
			"value" => 0,
			"current_value" => 0
			],
			"tournament_top10_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"tournament_top3_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_fight4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time12" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time16" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time18" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage11_time21" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_refresh" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_refreshed" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_refreshed_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"booster_sense_use" => [
			"value" => 0,
			"current_value" => 0
			],
			"booster_sense_used" => [
			"value" => 0,
			"current_value" => 0
			],
			"booster_sense_used_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_sidekick_collected" => [
			"value" => 0,
			"current_value" => 0
			],
			"sidekick_collected" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_sidekick_maxed" => [
			"value" => 0,
			"current_value" => 0
			],
			"sidekick_maxed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon6_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon6_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon6_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_surprise_box_opened" => [
			"value" => 0,
			"current_value" => 0
			],
			"surprise_box_opened" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon7_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon7_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon7_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_sidekick_merged" => [
			"value" => 0,
			"current_value" => 0
			],
			"sidekick_merged" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time3" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time5" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time9" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time12" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_fight8" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_fight9" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage12_time17" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon8_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon8_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon8_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"league_points_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"league_fights_won" => [
			"value" => 0,
			"current_value" => 0
			],
			"league_fights_won_in_row" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_league_fight_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"league_fights_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"league_fights_started_a_day" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_time2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_time6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_time14" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_fight2" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_fight4" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_fight6" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_stage13_fight7" => [
			"value" => 0,
			"current_value" => 0
			],
			"herobook_first_objetive_finished" => [
			"value" => 0,
			"current_value" => 0
			],
			"herobook_objectives_finished" => [
			"value" => 0,
			"current_value" => 0
			],
			"different_sidekick_collected" => [
			"value" => 1,
			"current_value" => 1
			],
			"dungeon9_unlocked" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon9_normal_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"dungeon9_hard_completed" => [
			"value" => 0,
			"current_value" => 0
			],
			"first_quest_resource_request_accepted" => [
			"value" => 0,
			"current_value" => 0
			],
			"quest_resource_request_accepted" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_glue_collected" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_stone_collected" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_room_upgraded" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_unlock_room_slot" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_hideout_points_reached" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_units_produced" => [
			"value" => 0,
			"current_value" => 0
			],
			"hideout_build_generator" => [
			"value" => 0,
			"current_value" => 0
			]		
		);
		return $b;
	}	
	
    private function itemPatt(){
        $patt = [
		"biker" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"costume" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"disco" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"doctor" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"movie" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"robinhood" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"superherozero" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"superheroset1" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"superheroset2" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"superheroset3" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"olympia_2016_rio" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"asian" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"frogman1" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"ironman1" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"movienew" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"musketeer" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"overall" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"powerset1" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"powerset2" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"safari" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"nano" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"pirates" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"wrestling" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"octoberfest" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"halloween" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"superhero" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"work" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"league_custom1" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"league_custom2" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"xmas" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"carnival" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		],
		"st_patricks_day" => [
		"value" => 0,
		"collected_items" => null,
		"is_new" => false
		]
		];
		return $patt;
    }
    
    private function notif(){
        $t = array(
		"mission_finished" => [
		"id" => 1,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Twoja misja zosta\\u0142a zako\\u0144czona."
		],
		"training_finished" => [
		"id" => 2,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Tw\\u00f3j trening zosta\\u0142 zako\\u0144czony."
		],
		"work_finished" => [
		"id" => 3,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Twoja praca jest zako\\u0144czona."
		],
		"free_duel_available" => [
		"id" => 4,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Znowu masz wystarczaj\\u0105co du\\u017co odwagi na\\u00a0swobodny atak."
		],
		"worldboss_attack_finished" => [
		"id" => 5,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Tw\\u00f3j atak na\\u00a0\\u0142otra zosta\\u0142 wykonany"
		],
		"hideout_room_build" => [
		"id" => 6,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Bohaterska kryj\\u00f3wka: zbudowano pomieszczenie"
		],
		"hideout_room_stored" => [
		"id" => 7,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Bohaterska kryj\\u00f3wka: zmagazynowano pomieszczenie"
		],
		"hideout_room_placed" => [
		"id" => 8,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Bohaterska kryj\\u00f3wka: umieszczono pomieszczenie"
		],
		"hideout_room_upgraded" => [
		"id" => 9,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Bohaterska kryj\\u00f3wka: rozbudowano pomieszczenie"
		],
		"hideout_room_slot_unlocked" => [
		"id" => 10,
		"active" => true,
		"vibrate" => false,
		"title" => "Hero Zero (pl1)",
		"body" => "Bohaterska kryj\\u00f3wka: odblokowano plac budowy"
		]
			);
		return $t;
    }
}