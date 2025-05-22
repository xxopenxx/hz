<?php
namespace Cls;

use Srv\Core;
use Srv\Config;
use Cls\Utils\Item;
use Cls\Entity;
use Cls\Guild;
use Schema\User;
use Schema\Character;
use Schema\Inventory;
use Schema\BankInventory;
use Schema\Quests;
use Schema\Dungeons;
use Schema\DungeonQuests;
use Schema\Sidekicks;
use Schema\Hideout;
use Schema\HideoutRoom;
use Schema\Items;
use Schema\GoalItems;
use Schema\Work;
use Schema\Training;
use Schema\Battle;
use Schema\Duel;
use Schema\LeagueFight;
use Schema\Messages;
use Cls\GameSettings;
use Cls\Bonus\SlotMachine;
use Cls\Bonus\ResourceType;
use Cls\Utils\HideoutUtils;

// for goals
use Srv\DB;
use PDO;

class Player extends Entity{

    public $user = null;
    public $character = null;
    public $inventory = null;
    public $bankinv = null;
    public $work = null;
    public $training = null;
    public $duel = null;
	public $leaguefight = null;
    public $battle = null;
    public $guild = null;
    public $sidekicks = null;
	public $hideout = null;
	public $hideout_room = null;
	public $hideout_rooms = [];

    public $current_goal_values = [];
    public $collected_goals = [];
    //
    public $items = [];
    public $quests = [];
    public $dungeons = [];
    public $dungeon_quests = [];
    public $battles = [];
    //
    public $ts_before_action = 0;
    
    public function loadPlayer(){
        Core::$PLAYER = $this;
        if(!$this->character)
            $this->character = Character::find(function($q){ $q->where('user_id', $this->user->id); });
        if($this->character){
            if($this->character->guild_id != 0 && !$this->guild){
                $this->guild = Guild::find(function($q){ $q->where('id',$this->character->guild_id); });
                $this->guild->loadGuild();
                if(($finishedAttack = $this->guild->getFinishedAttack()) != null)
                    $this->character->finished_guild_battle_attack_id = $finishedAttack->battle->id;
                if(($finishedDefense = $this->guild->getFinishedDefense()) != null)
                    $this->character->finished_guild_battle_defense_id = $finishedDefense->battle->id;
                if(($finishedDefense = $this->guild->getFinishedDungeon()) != null)
                    $this->character->finished_guild_dungeon_battle_id = $finishedDefense->battle->id;
            }
            $this->calculateLVL();
            $this->quests = Quests::findAll(function($q){ $q->where('character_id', $this->character->id)->where('status','<',5); });
            $this->updateQuestsPool();
            $this->items = Items::findAll(function($q){ $q->where('character_id', $this->character->id); });
            $this->dungeons = Dungeons::findAll(function($q) { $q->where('character_id', $this->character->id); });
            $this->dungeon_quests = DungeonQuests::findAll(function($q) { $q->where('character_id', $this->character->id); });
            $this->work = Work::find(function($q){ $q->where('character_id', $this->character->id)->where('status','<',5); });
            if($this->work)
                $this->character->active_work_id = $this->work->id;
            $this->training = Training::find(function($q){ $q->where('character_id', $this->character->id)->where('status','<',5); });
            if($this->training)
                $this->character->active_training_id = $this->training->id;
            $this->duel = Duel::find(function($q){ $q->where('character_a_id', $this->character->id)->where('character_a_status','<',3); });
            if($this->duel){
                $this->character->active_duel_id = $this->duel->id;
                $this->battle = Battle::find(function($q){ $q->where('id',$this->duel->battle_id); });
                $this->battles[] = $this->battle;
            }
            $this->league_fight = LeagueFight::find(function($q){ $q->where('character_a_id', $this->character->id)->where('character_a_status','<',3); });
            if($this->league_fight){
                $this->character->active_league_fight_id = $this->league_fight->id;
                $this->battle = Battle::find(function($q){ $q->where('id',$this->league_fight->battle_id); });
                $this->battles[] = $this->battle;
            }
            if(($battleQuest = $this->getBattleQuest()) != null){
                $battleId = $battleQuest->fight_battle_id;
                $this->battle = Battle::find(function($q)use($battleId){ $q->where('id',$battleId); });
                $this->battles[] = $this->battle;
            }
            if(($battleDungeon = $this->getBattleDungeons()) != null){
                $battleId = $battleDungeon->battle_id;
                $this->battle = Battle::find(function($q)use($battleId){ $q->where('id',$battleId); });
                $this->battles[] = $this->battle;
            }
            if(!$this->inventory)
                $this->inventory = Inventory::find(function($q){ $q->where('character_id', $this->character->id); });
            if(!$this->bankinv)
                $this->bankinv = BankInventory::find(function($q){ $q->where('character_id', $this->character->id); });
            if($this->inventory->sidekick_id > 0) $this->sidekicks = Sidekicks::find(function($q) { $q->where('id', $this->inventory->sidekick_id); });
			if(!$this->hideout)
				$this->hideout = Hideout::find(function($q) { $q->where('character_id', $this->character->id); });
			if(!$this->hideout_room && $this->hideout)
				$this->hideout_room = HideoutRoom::find(function($q) { $q->where('hideout_id', $this->hideout->id); });
			if(!$this->hideout_rooms && $this->hideout)
				$this->hideout_rooms = HideoutRoom::findAll(function($q) { $q->where('hideout_id', $this->hideout->id); });
            //TODO: Check event timestamp (event exists)
            $this->character->current_slotmachine_spin = SlotMachine::countCurrentSpins($this);
        }
        if($this->character){
            $this->ts_before_action = $this->character->ts_last_action;
            if(Utils::isNotToday($this->character->ts_last_action))
                $this->regenerateSometime();
            $this->character->ts_last_action = time();
            $this->character->online_status = time() < $this->character->ts_last_action + 60? 1 : 2;
            $this->refreshDuelStamina();
			$this->refreshLeagueStamina();
            if($this->character->ts_active_sense_boost_expires < time())
                $this->character->ts_active_sense_boost_expires = 0;
            $this->calculateStats();
            $this->calculateEntity();
        }

        if ($this->hideout_rooms) {
            $this->currentCalculatedResourceAmount();
        }

        if ($this->character) {
            $this->checkCurrentGoalValues();
            $this->checkCollectedGoals();
        }
    }
    
    //LOADING//
    public function loadForDuel(){
        if(!$this->character)
            $this->character = Character::find(function($q){ $q->where('user_id', $this->user->id); });
        if($this->character->guild_id != 0 && !$this->guild)
            $this->guild = Guild::find(function($q){ $q->where('id',$this->character->guild_id); });
        $this->character->online_status = time() < $this->character->ts_last_action + 60? 1 : 2;
        $this->calculateLVL();
        $this->inventory = Inventory::find(function($q){ $q->where('character_id', $this->character->id); });
        $this->items = Items::findAll(function($q){ $q->where('character_id', $this->character->id); });
        if($this->inventory->sidekick_id > 0) $this->sidekicks = Sidekicks::find(function($q) { $q->where('id', $this->inventory->sidekick_id); });
        //var_dump($this->sidekicks);
        //var_dump($this->inventory);
        if($this->character->guild_id != 0)
            $this->playerLoadFightGuild();
        $this->calculateStats();
        $this->calculateEntity();
    }
    
    public function loadForGuild(){
        $this->character->online_status = time() < $this->character->ts_last_action + 60? 1 : 2;
        $this->calculateLVL();
        $this->inventory = Inventory::find(function($q){ $q->where('character_id', $this->character->id); });
        $this->items = Items::findAll(function($q){ $q->where('character_id', $this->character->id); });
        if($this->character->guild_id != 0)
            $this->playerLoadFightGuild();
        $this->calculateStats();
        $this->calculateEntity();
    }
    
    public function loadForCharacterView(){
        if(!$this->guild && $this->character->guild_id != 0)
            $this->guild = Guild::find(function($q){ $q->where('id',$this->character->guild_id); });
        $this->items = Items::findAll(function($q){ $q->where('character_id', $this->character->id); });
        if(!$this->inventory)
            $this->inventory = Inventory::find(function($q){ $q->where('character_id', $this->character->id); });
        if(!$this->sidekicks)
            $this->sidekicks = Sidekicks::find(function($q) { $q->where('id', $this->inventory->sidekick_id); });
        if(!$this->hideout)
            $this->hideout = Hideout::find(function($q) { $q->where('character_id', $this->character->id); });
        $this->calculateStats();
    }
    
    private function playerLoadFightGuild(){
        $gid = $this->character->guild_id;
        if(isset(Core::$GUILDS[$gid]))
            $this->guild = Core::$GUILDS[$gid];
        else{
            $this->guild = Guild::find(function($q){ $q->where('id',$this->character->guild_id); });
            $this->guild->loadGuildForBattle();
        }
    }
    //END LOADING//
    
    public function getUnreadedMessagesCount(){
        return Messages::count(function($q){
            $q->where('character_to_ids','LIKE',"%;{$this->character->id};%");
            $q->where('readed',0);
        });
    }
    
    public function getMissedDuels(){
        return Duel::count(function($q){
            $q->where('character_b_id', $this->character->id);
            $q->where('character_b_status', 1);
			$q->where('unread', 'true');
        });
    }	
	
    public function getMissedLeagueFights(){
        return LeagueFight::count(function($q){
            $q->where('character_b_id', $this->character->id);
            $q->where('character_b_status', 1);
			$q->where('unread', 'true');
        });
    }	
	
    public function calculateEntity(){
        $this->hitpoints = $this->character->stat_total_stamina * Config::get('constants.battle_hp_scale');
        $this->level = $this->character->level;
        $this->stamina = $this->character->stat_total_stamina;
        $this->total_stamina = $this->stamina;
        $this->strength = $this->character->stat_total_strength;
        $this->criticalrating = $this->character->stat_total_critical_rating;
        $this->dodgerating = $this->character->stat_total_dodge_rating;
        $this->weapondamage = $this->character->stat_weapon_damage;
        $this->damage_normal = $this->strength + $this->weapondamage;
        $this->damage_bonus = $this->damage_normal;
        $this->setMissile($this->getItemFromSlot('missiles_item_id'));
    }
    
    public function __endRequest(){
        //Change missiles
        $missile = $this->getItemFromSlot('missiles_item_id');
        if($missile == null || $missile->charges <= 0){
            if($missile != null)
                $missile->remove();
            $slotname = '';
            for($i=1; $i <= 4; $i++){
                $slotname = "missiles{$i}_item_id";
                $newMissile = $this->getItemFromSlot($slotname);
                if($newMissile != null){
                    if($newMissile->charges <= 0){
                        $newMissile->remove();
                        continue;
                    }
                    $this->setItemInInventory(null, $slotname);
                    $this->setItemInInventory($newMissile, 'missiles_item_id');
                    Core::req()->data['inventory']['missiles_item_id'] = $this->inventory->missiles_item_id;
                    Core::req()->data['inventory'][$slotname] = $this->inventory->{$slotname};
                    break;
                }
            }
            
        }
    }
    
    public function haveSlotmachineFreeSpin(){
        return $this->getUnusedResource(ResourceType::FreeSlotMachineSpin) >= Config::get('constants.resource_free_slotmachine_spin_usage_amount')
            || $this->getUnusedResource(ResourceType::SlotMachineJetons) >= Config::get('constants.resource_slotmachine_jeton_usage_amount');
    }
    
    public function isStorageUpgraded(){
        return $this->user->hasSetting('storage_upgraded');
    }
    
    public function maximumTrainingStorage(){
        if($this->isStorageUpgraded())
            return Config::get('constants.maximum_training_storage_amount_upgraded');
        return Config::get('constants.maximum_training_storage_amount');
    }
    
    public function maximumEnergyStorage(){
        if($this->isStorageUpgraded())
            return Config::get('constants.maximum_energy_storage_amount_upgraded');
        return Config::get('constants.maximum_energy_storage_amount');
    }
    
    public function getDailyBonuses(){
        $dateDiff = Utils::diffDate($this->character->ts_last_daily_login_bonus);

        // Reset daily goals
        if ($dateDiff < 0) {
            $this->updateCurrentGoalValue('coins_spent_a_day', 0);
            $this->updateCurrentGoalValue('duels_started_a_day', 0);
            $this->updateCurrentGoalValue('shop_refreshed_a_day', 0);
            $this->updateCurrentGoalValue('quest_refreshed_a_day', 0);
            $this->updateCurrentGoalValue('booster_sense_used_a_day', 0);
            $this->updateCurrentGoalValue('league_fights_started_a_day', 0);
        }

        if($dateDiff == -1){ //Yesterday -1 day
            $this->character->daily_login_bonus_day++;
            $this->character->ts_last_daily_login_bonus = time();

            $daysLoggedIn = $this->getCurrentGoalValue('days_logged_in');
            $this->updateCurrentGoalValue('days_logged_in', $daysLoggedIn + 1);

            if (($daysLoggedIn + 1) == 2) {
                $this->updateCurrentGoalValue('second_day_logged_in', 2);
            }
        }else if($dateDiff < -1){ //-x days
            $this->character->daily_login_bonus_day = 1;
            $this->character->ts_last_daily_login_bonus = time();

            $this->updateCurrentGoalValue('days_logged_in', 0);
        }else
            return FALSE;
        //Get bonuses
        $rewards = Config::get("constants.daily_login_bonus_rewards");
        $rewards_pools = Config::get("constants.daily_login_bonus_rewards_pool");
        $pool_count = count($rewards_pools);
        $fixedDays = Config::get('constants.daily_login_bonus_reward_fixed_days');
        $currentDay = $this->character->daily_login_bonus_day;
        $dailyLogin = [];
        for($i = 1; $i <= $fixedDays; $i++){
            $day = $i;
            if($currentDay > 5){
                $day = ($currentDay - 2 + $i - 1);
                if($day > $currentDay) break;
                if($day < 6)
                    $bonus = $rewards[$day];
                else
                    $bonus = $rewards_pools[($day % $pool_count)];
            }else
                $bonus = $rewards[$day];
            $dailyLogin[$day] = [
                'rewardType1'=> $bonus['reward_type1'],
                'rewardType2'=> $bonus['reward_type2']
            ];
            if($currentDay == $day){
                //Calculate rewards and give to player
                Utils::calculateDailyBonus($this, $bonus, $amount1, $amount2);
                $dailyLogin[$day]['rewardType1Amount']= $amount1;
                $dailyLogin[$day]['rewardType2Amount']= $amount2;
				$this->character->league_fight_count = 0;
            }
        }
        return $dailyLogin;
    }
    
    public function getUnusedResource($type){
        $data = json_decode($this->character->unused_resources, TRUE);
        return isset_or($data[$type], 0);
    }
    
    public function giveUnusedResource($type, $amount){
        $data = json_decode($this->character->unused_resources, TRUE);
        $data[$type] = max(isset_or($data[$type], 0)+$amount, 0);
        $this->character->unused_resources = json_encode($data);
    }
    
    public function getMoney(){
        return $this->character->game_currency;
    }
    
    public function giveMoney($money){
        $this->character->game_currency += $money;

        if ($money < 0) {
            $spentMoneyToday = $this->getCurrentGoalValue('coins_spent_a_day');
            $spentMoneyToday += abs($money);
            $this->updateCurrentGoalValue('coins_spent_a_day', $spentMoneyToday);
        }
    }
    
    public function setMoney($money){
        $this->character->game_currency = $money;
    }
    
    public function getPremium(){
        return $this->user->premium_currency;
    }
    
    public function givePremium($prem){
        $this->user->premium_currency += $prem;
        if(Core::$PLAYER->user->id == $this->user->id)
            Core::req()->append['user']= $this->user;

        if ($prem < 0) {
            $donutsSpent = $this->getCurrentGoalValue('donuts_spent');
            $donutsSpent += abs($prem);
            $this->updateCurrentGoalValue('donuts_spent', $donutsSpent);
        }
    }
    
    public function setPremium($prem){
        $this->user->premium_currency = $prem;
    }
    
    public function getHonor(){
        return $this->character->honor;
    }
    
    public function getLeaguePoints(){
        return $this->character->league_points;
    }	
	
    public function giveHonor($h){
        $this->character->honor += $h;
        if($this->character->honor < 0)
            $this->character->honor = 0;

        $honorReached = $this->getCurrentGoalValue('honor_reached');
        if ($honorReached < $this->character->honor) {
            $honorReached = $this->character->honor;
            $this->updateCurrentGoalValue('honor_reached', $honorReached);
        }
    }

    public function giveLeaguePoints($h){
        $this->character->league_points += $h;
        if($this->character->league_points < 0)
            $this->character->league_points = 0;
    }
    
    public function setHonor($h){
        $this->character->honor = max($h, 0);
    }
    
    public function getExp(){
        return $this->character->xp;
    }
    
    public function giveExp($exp){
		if($this->character->level < 695){
			$this->character->xp += $exp;
		} else {
			$this->character->xp = 873269273;
			$this->character->level = 695;
		}
        if($this->character->xp < 0)
            $this->character->xp = 0;
        if($this->inventory->sidekick_id){
            $this->sidekicks->xp += round($exp * GameSettings::getConstant('sidekick_xp_factor'));
            if($this->sidekicks->xp < 0)
                $this->sidekicks->xp = 0;
            $this->calculateSidekickLVL();
        } 
        $this->calculateLVL();
    }
    
    public function setExp($exp){
        $this->character->xp = max($exp, 0);
        $this->calculateLVL();
    }
    
    public function getLVL(){
        return $this->character->level;
    }
    
    // Hideout
    public function giveHideoutGlue($glue) {
        $this->hideout->current_resource_glue += $glue;
        if ($this->hideout->current_resource_glue < 0) {
            $this->hideout->current_resource_glue = 0;
        }

        $hideoutGlueCollected = $this->getCurrentGoalValue('hideout_glue_collected');
        if ($hideoutGlueCollected < $this->hideout->current_resource_glue) {
            $hideoutGlueCollected = $this->hideout->current_resource_glue;
            $this->updateCurrentGoalValue('hideout_glue_collected', $hideoutGlueCollected);
        }
    }

    public function giveHideoutStone($stone) {
        $this->hideout->current_resource_stone += $stone;
        if ($this->hideout->current_resource_stone < 0) {
            $this->hideout->current_resource_stone = 0;
        }

        $hideoutStoneCollected = $this->getCurrentGoalValue('hideout_stone_collected');
        if ($hideoutStoneCollected < $this->hideout->current_resource_stone) {
            $hideoutStoneCollected = $this->hideout->current_resource_stone;
            $this->updateCurrentGoalValue('hideout_stone_collected', $hideoutStoneCollected);
        }
    }

    public function regenerateSometime(){
        //Store, refil quest energy
        $this->character->current_energy_storage = min($this->character->current_energy_storage + $this->character->quest_energy, $this->maximumEnergyStorage());
        $this->character->quest_energy = $this->character->max_quest_energy;
        $this->character->quest_energy_refill_amount_today = 0;
        //Store, refil training count
        $this->character->current_training_storage = min($this->character->current_training_storage + $this->character->training_count, $this->maximumTrainingStorage());
        $this->character->training_count = $this->character->max_training_count;
        //Give additional training points from guild booster
        if($this->character->guild_id != 0 && ($booster = $this->guild->getBoosters('quest')) != null)
            $this->character->training_count = Config::get("constants.guild_boosters.$booster.amount") + $this->character->max_training_count;
        //Slotmachine
        //TODO: check if event exists
        $this->giveUnusedResource(ResourceType::FreeSlotMachineSpin, Config::get('constants.resource_free_slotmachine_spin_usage_amount'));
        $this->character->slotmachine_spin_count = 0;
    }
    
    public function refreshDuelStamina(){
        if($this->character->duel_stamina >= $this->character->max_duel_stamina)
            return;
        if($this->character->duel_stamina < $this->character->duel_stamina_cost)
            $totalSecs = round(1 / Config::get('constants.duel_stamina_refresh_amount_per_minute_first_duel') * 60);
        else
            $totalSecs = round(1 / Config::get('constants.duel_stamina_refresh_amount_per_minute') * 60);
        $amount = floor((time() - $this->character->ts_last_duel_stamina_change) / $totalSecs);
        if($amount > 0){
            $this->character->ts_last_duel_stamina_change = time();
            $this->character->duel_stamina += $amount;
        }
    }
    
    public function refreshLeagueStamina(){
        if($this->character->league_stamina >= $this->character->max_league_stamina)
            return;
       
	  /* if($this->character->league_stamina < $this->character->duel_stamina_cost)
            $totalSecs = round(1 / Config::get('constants.league_stamina_refresh_amount_per_minute') * 60);
        else
            $totalSecs = round(1 / Config::get('constants.league_stamina_refresh_amount_per_minute') * 60);*/
		
		if($this->character->active_league_booster_id == 'booster_league1') {
			$totalSecs = round(1 / Config::get('constants.league_stamina_refresh_amount_per_minute_first_fight_booster1') * 60);	
		} elseif($this->character->active_league_booster_id == 'booster_league2') {
			$totalSecs = round(1 / Config::get('constants.league_stamina_refresh_amount_per_minute_first_fight_booster2') * 60);	
		} else {
			$totalSecs = round(1 / Config::get('constants.league_stamina_refresh_amount_per_minute_first_fight_nonbooster') * 60);	
		}
		
        $amount = floor((time() - $this->character->ts_last_league_stamina_change) / $totalSecs);
        if($amount > 0){
            $this->character->ts_last_league_stamina_change = time();
            $this->character->league_stamina += $amount;
        }
    }	
	
    public function calculateSidekickLVL(){
        $levels = GameSettings::getConstant('sidekick_levels');
        $newLVL = -1;
        $maxlevels=count($levels);
        for($lvl=1,$cnt=$maxlevels-1; $lvl<$cnt; $lvl++){
            if($this->sidekicks->xp < $levels[$lvl]['xp'])
                break;
            if($this->sidekicks->xp >= $levels[$lvl]['xp'] && $this->sidekicks->xp < $levels[$lvl+1]['xp'])
                $newLVL = $lvl;
        }
        if($newLVL == -1)
            $newLVL = $maxlevels;
        //
        if($this->sidekicks->level != $newLVL){
            $this->sidekicks->level = $newLVL;

            if ($this->sidekicks->level == $maxlevels) {
                $firstSidekickMaxed = $this->getCurrentGoalValue('first_sidekick_maxed');
                if ($firstSidekickMaxed == 0) {
                    $this->updateCurrentGoalValue('first_sidekick_maxed', 1);
                }

                $sidekickMaxed = $this->getCurrentGoalValue('sidekick_maxed');
                $this->updateCurrentGoalValue('sidekick_maxed', $sidekickMaxed + 1);
            }
        }
    }

    public function calculateLVL(){
        $levels = Config::get('constants.levels');
        $newLVL = -1;
        $maxlevels=count($levels);
        for($lvl=1,$cnt=$maxlevels-1; $lvl<$cnt; $lvl++){
            if($this->getExp() < $levels[$lvl]['xp'])
                break;
            if($this->getExp() >= $levels[$lvl]['xp'] && $this->getExp() < $levels[$lvl+1]['xp'])
                $newLVL = $lvl;
        }
        if($newLVL == -1)
            $newLVL = $maxlevels;
        //
        if($newLVL > $this->character->level)
            $this->character->stat_points_available += ($newLVL - $this->character->level) * Config::get('constants.level_up_stat_points');
		//

        if($this->character->level != $newLVL) {
            $this->character->level = $newLVL;
            
            $max_stages = $this->character->max_quest_stage;
    		$unlock_stage = $this->calculateStages();
    		if($unlock_stage > $max_stages){
    		    $this->givePremium(($unlock_stage - $max_stages) * Config::get('constants.stage_level_up_premium_amount'));
    			for($i=$max_stages + 1; $i <= $unlock_stage; $i++)
    			    $this->generateQuestsAtStage($i, 3);
    		}
    		$this->character->max_quest_stage = $unlock_stage;

			if($this->character->level >= 699){
				$lvl = $player->getLVL()-1;
				$player->setExp(Config::get("constants.levels.$lvl.xp"));
			}

            if($this->character->level == 60 && !$this->character->received_sidekick) {
                $skills = randomSidekickSkills();
                $q = new Sidekicks([
                    'character_id'=>$this->character->id,
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
                $this->character->received_sidekick = 1;

                if(!$this->inventory) {
                    $this->inventory = Inventory::find(function($q){ $q->where('character_id', $this->character->id); });
                }

                $this->inventory->sidekick_data = json_encode(array("orders" => $sidekick_data));
                $this->updateCurrentGoalValue('first_sidekick_collected', 1);
                $this->updateCurrentGoalValue('sidekick_collected', 1);
            }

            // Goals
            $this->updateCurrentGoalValue('level_reached', $this->character->level);
            $this->updateCurrentGoalValue('stage_reached', $this->character->max_quest_stage);
        }
    }
    
    public function calculateStages(){
		$stages = Config::get('constants.stages');
		for($i=1, $c = count($stages)-1; $i <= $c; $i++){
			if($this->character->level >= $stages[$i]["min_level"] && $this->character->level < $stages[$i+1]["min_level"])
				return $i;
		}
		return count($stages);
	}
    
    public function giveRewards($rew){
        if(is_string($rew))
            $rew = json_decode($rew, true);
        if(isset($rew['coins']))
            $this->giveMoney($rew['coins']);
        if(isset($rew['xp']))
			$this->giveExp($rew['xp']);
        if(isset($rew['honor']))
            $this->giveHonor($rew['honor']);
        if(isset($rew['league_points']))
            $this->giveLeaguePoints($rew['league_points']);
        if(isset($rew['premium']))
            $this->givePremium($rew['premium']);
        if(isset($rew['statPoints']))
            $this->character->stat_points_available += $rew['statPoints'];
        //if($rew['item'])
        //    $this->giveItem($rew);
        if(isset($rew['slotmachine_jetons']))
            $this->giveUnusedResource(ResourceType::SlotMachineJetons, $rew['slotmachine_jetons']);
        if(isset($rew['quest_energy']))
            $this->character->quest_energy += $rew['quest_energy'];
        if(isset($rew['training_sessions']))
            $this->character->training_count += $rew['training_sessions'];
    }
    
    public function getBoosters($type=false){
		$b = ["quest"=>null, "stats"=>null, "work"=>null];
		if($this->character->ts_active_quest_boost_expires > time()){
			$b["quest"] = $this->character->active_quest_booster_id;
		}
		if($this->character->ts_active_stats_boost_expires > time()){
			$b["stats"] = $this->character->active_stats_booster_id;
		}
		if($this->character->ts_active_work_boost_expires > time()){
			$b["work"] = $this->character->active_work_booster_id;
		}
		if($this->character->ts_active_league_boost_expires > time()){
			$b["league"] = $this->character->active_league_booster_id;
		}
		return !$type?$b:$b[$type];
	}
	
	public function hasMultitasking(){
        return $this->character->ts_active_multitasking_boost_expires == -1 || $this->character->ts_active_multitasking_boost_expires > time();
    }
    
    public function getItems(){
        $arr = [];
        foreach($this->items as $q)
            $arr[] = $q->toArray();
        return $arr;
    }
    
    public function getQuests(){
        $arr = [];
        foreach($this->quests as $q)
            $arr[] = $q->toArray();
        return $arr;
    }
    
    public function getBattleQuest(){
        foreach($this->quests as $q){
            if($q->fight_battle_id != 0)
                return $q;
        }
        return null;
    }

    public function getBattleDungeons(){
        foreach($this->dungeon_quests as $q){
            if($q->battle_id != 0)
                return $q;
        }
        return null;
    }
    
    public function setItemInInventory($item, $slot){
        if(is_null($item)) $itemid = 0; else $itemid = $item->id;
        $this->inventory->{$slot} = $itemid;
    }
	
	public function changeItemIdentifier($target_id, $item){
		$item->identifier = $target_id;
		return $item;
	}	
    
    public function createItem($data){
        $data['character_id'] = $this->character->id;
        $i = new Items($data);
        $i->save();
        $this->items[] = $i;
        return $i;
    }
    
    public function getItemFromSlot($slotname){
        return $this->getItemById($this->inventory->{$slotname});
    }
    
    public function getItemFromBankSlot($slotname){
        return $this->getItemById($this->bankinv->{$slotname});
    }

    public function getFreeInventorySlot() {
        for ($i=1; $i <= 18; $i++) {
            $slotname = "bag_item{$i}_id";
            if ($this->getItemFromSlot($slotname) == null)
                return $slotname;
        }

        return null;
    }

    public function getFreeBankInventorySlot() {
        $maxBankInv = $this->bankinv->max_bank_index;
        $maxSlot = ($maxBankInv + 1) * 18;
        for ($i=1; $i <= $maxSlot; $i++) {
            $slotname = "bank_item{$i}_id";
            if ($this->getItemFromBankSlot($slotname) == null)
                return $slotname;
        }
        return null;
    }

    public function getFreeInvSlot() {
        $inventorySlot = $this->getFreeInventorySlot();
        if ($inventorySlot) return $inventorySlot;

        $bankInventorySlot = $this->getFreeBankInventorySlot();
        if ($bankInventorySlot) return $bankInventorySlot;

        return null;
    }
    
    public function removeItem($item){
        foreach($this->items as $key=>$it){
            if($it->id != $item->id)
                continue;
            $item->remove();
            unset($this->items[$key]);
            return true;
        }
        return false;
    }
    
    public function getOnlyEquipedItems(){
        $inventory=[];
        $items=[];
        for($i=1; $i<=8; $i++){
			$itemName = Item::$TYPE[$i];
			$item = $this->getItemFromSlot("{$itemName}_item_id");
			$inventory["{$itemName}_item_id"] = $item==null?0:$item->id;
			if($item != null)
				$items[] = $item;
		}
        $inventory["sidekick_id"] = $this->inventory->sidekick_id;
		$inventory["item_set_data"] = $this->getItemFromSlot("item_set_data")==null?0:$this->getItemFromSlot("item_set_data");
        return array('inventory'=>$inventory, 'items'=>$items);
    }
    
    public function findEmptyInventorySlot(){
        $lvl = $this->character->level;
        if($lvl >= Config::get('constants.inventory_bag3_unlock_level'))
            $slots = 18;
        else if($lvl >= Config::get('constants.inventory_bag2_unlock_level') && $lvl < Config::get('constants.inventory_bag3_unlock_level'))
            $slots = 12;
        else
            $slots = 6;
        for($i=1; $i <= $slots; $i++){
            $slotname = "bag_item{$i}_id";
            if($this->getItemFromSlot($slotname) == null)
                return $slotname;
        }
        return null;
    }
    
    public function getItemById($id){
        if($id <= 0) return null;
        foreach($this->items as $item){
            if($item->id == $id)
                return $item;
        }
        return null;
    }
    
    public function createQuest($data=[], $stage=1){
        $data['character_id'] = $this->character->id;
        $data['stage'] = $stage;
        $q = new Quests($data);
        $q->save();
        $this->quests[] = $q;
        return $q;
    }

    public function updateQuestsPool(){
        $qs = [];
        $aqid = 0;
        foreach($this->quests as $q){
            if($q->status < 5)
                $qs[$q->stage][] = $q->id;
            if($q->status > 1 && $q->status < 5)
                $aqid = $q->id;
        }
        $this->character->active_quest_id = $aqid;
        $this->character->quest_pool = json_encode($qs);
    }

    public function getDungeonQuestById($id){
        foreach($this->dungeon_quests as $q)
            if($q->id == $id)
                return $q;
        return null;
    }
    
    public function getDungeonByDungeonId($id){
        foreach($this->dungeons as $q)
            if($q->id == $id)
                return $q;
        return null;
    }

    public function getDungeonById($id){
        foreach($this->dungeons as $q)
            if($q->identifier == "dungeon{$id}")
                return $q;
        return null;
    }
    
    public function getQuestById($id){
        foreach($this->quests as $q)
            if($q->id == $id)
                return $q;
        return null;
    }
    
    public function getQuestsByStage($stage){
        $arr = [];
        foreach($this->quests as $q)
            if($q->stage == $stage)
                $arr[] = $q;
        return $arr;
    }
    
    public function generateQuestAtDungeon($dungeon, $dungeon_id, $stage, $mode){
        /*$stageQuests = $this->getDungeonByStage($stage);
        for($i=0, $c=count($stageQuests)-$count; $i<$c; $i++){
            $stageQuests[$i]->remove();
            unset($stageQuests[$i]);
        }*/
        $data = Utils::randomiseDungeonQuest($this, $dungeon, $dungeon_id, $stage, $mode);
        $data["character_id"] = $this->character->id;
        $q = new DungeonQuests($data);
        $q->save();

        $this->dungeon_quests[] = $q;
        return $q->id;
    }

    public function generateQuestsAtStage($stage, $count, &$isAnyItem=false){
        $qCount = 0;
        $stageQuests = $this->getQuestsByStage($stage);
        for($i=0, $c=count($stageQuests)-$count; $i<$c; $i++){
            $stageQuests[$i]->remove();
            unset($stageQuests[$i]);
        }
        foreach($stageQuests as $q){
            $q->reset(['id','character_id']);
            $q->setData(Utils::randomiseQuest($this, $stage, true, $isAnyItem));
            $qCount++;
        }
        for($i=0; $i < $count - $qCount; $i++)
            $this->createQuest(Utils::randomiseQuest($this, $stage, false, $isAnyItem), $stage);
        $this->updateQuestsPool();
    }
    
    public function setTutorialFlag($flag, $val=true){
        $flags = json_decode($this->character->tutorial_flags, true);
        $flags[$flag] = $val;
        $this->character->tutorial_flags = json_encode($flags);

        if ($flag == 'tutorial_finished') {
            $this->updateCurrentGoalValue('tutorial_completed', 1);
        }
    }
    
    public function getTutorialFlag($flag){
        $tut = json_decode($this->character->tutorial_flags, true);
        if(isset($tut[$flag]) && $tut[$flag] == true)
            return true;
        return false;
    }
    
    public function calculateStats(){
        $boosterVal = 1;
        if(($booster = $this->getBoosters('stats')) != null)
            $boosterVal += (Config::get("constants.boosters.$booster.amount")/100);
        if($this->character->guild_id != 0)
            $boosterVal += ($this->guild->stat_character_base_stats_boost/100);
        $this->character->stat_total_stamina = ceil($this->character->stat_base_stamina * $boosterVal);
        $this->character->stat_total_strength = ceil($this->character->stat_base_strength * $boosterVal);
        $this->character->stat_total_critical_rating = ceil($this->character->stat_base_critical_rating * $boosterVal);
        $this->character->stat_total_dodge_rating = ceil($this->character->stat_base_dodge_rating * $boosterVal);
        
        for($i=1; $i <= 8; $i++) {
            $slot = \Cls\Utils\Item::$TYPE[$i].'_item_id';
            $item = $this->getItemFromSlot($slot);
            if($item == null) continue;
            $this->character->stat_total_stamina += $item->stat_stamina;
            $this->character->stat_total_strength += $item->stat_strength;
            $this->character->stat_total_critical_rating += $item->stat_critical_rating;
            $this->character->stat_total_dodge_rating += $item->stat_dodge_rating;
            $this->character->stat_weapon_damage += $item->stat_weapon_damage;
        }

        //var_dump($this->inventory->sidekick_id);
        if($this->inventory->sidekick_id > 0 && $this->sidekicks){
            //var_dump($this->sidekicks);
            if($this->sidekicks->level >= 20){
                if($this->sidekicks->stage2_skill_id == 6){
                    $this->character->stat_total_strength += ceil($this->character->stat_base_strength * 1.10);
                }

                if($this->sidekicks->stage2_skill_id == 7){
                    $this->character->stat_total_stamina += ceil($this->character->stat_base_stamina * 1.10);
                }
            }

            $this->character->stat_total_stamina += $this->sidekicks->stat_stamina;
            $this->character->stat_total_strength += $this->sidekicks->stat_strength;
            $this->character->stat_total_critical_rating += $this->sidekicks->stat_critical_rating;
            $this->character->stat_total_dodge_rating += $this->sidekicks->stat_dodge_rating;
        }
        $this->character->stat_total = $this->character->stat_total_stamina + $this->character->stat_total_strength + $this->character->stat_total_critical_rating + $this->character->stat_total_dodge_rating;
    }
    
    public static function login($email, $password){
        $user = User::find(function($q) use($email,$password){
            $q->where('email',$email)->where('password_hash',Core::passwordHash($password));
        });
        if(!$user)
            return FALSE;
        $player = new Player();
        $player->user = $user;
        $player->loadPlayer();
        return $player;
    }
    
    public static function findBySSID($uid, $ssid){
        $user = User::find(function($q) use($uid,$ssid){
            $q->where('id',$uid)->where('session_id',$ssid);
        });
        if(!$user)
            return NULL;
        $player = new Player();
        $player->user = $user;
        $player->loadPlayer();
        return $player;
    }
    
    public static function findByUserId($uid){
        $user = User::find(function($q) use($uid){
            $q->where('id',$uid);
        });
        if(!$user)
            return NULL;
        $player = new Player();
        $player->user = $user;
        return $player;
    }
    
    public static function findByCharacterId($chid){
        $character = Character::find(function($q) use($chid){
            $q->where('id',$chid);
        });
        if(!$character)
            return NULL;
        $user = User::find(function($q) use($character){
            $q->where('id',$character->user_id);
        });
        $player = new Player();
        $player->user = $user;
        $player->character = $character;
        return $player;
    }
    
    public static function findAllByGuildId($gid, $bypass = false){
        $characters = Character::findAll(function($q) use($gid,$bypass){
            $q->where('guild_id', $gid);
            if($bypass)
                $q->where('id','<>',$bypass);
        });
        if(!$characters || !count($characters))
            return [];
        $players = [];
        foreach($characters as $char){
            $player = new Player();
            $player->character = $char;
            $players[] = $player;
        }
        return $players;
    }

    public function calculateEquippedItems() {
        $equippedCount = 0;
        for($i=1; $i <= 8; $i++) {
            $slot = \Cls\Utils\Item::$TYPE[$i].'_item_id';
            $item = $this->getItemFromSlot($slot);
            if($item == null) continue;
            if ($i < 8) $equippedCount++;
        }

        $characterFullEquipped = $this->getCurrentGoalValue('character_full_equipped');
        if ($characterFullEquipped == 0) {
            if ($equippedCount == 7) $this->updateCurrentGoalValue('character_full_equipped', 1);
        }
    }

    // Hideout
    public function currentCalculatedResourceAmount() {
        foreach ($this->hideout_rooms as $room) {
            $calculated = HideoutUtils::currentCalculatedResourceAmount($room);
            if ($room->current_resource_amount != $calculated) {
                $room->current_resource_amount = $calculated;
                $room->ts_last_resource_change = time();
            }
        }
    }

    // Goals
    public function checkCurrentGoalValues() {
		$goals = GameSettings::getConstant('goals');

        $checkGoal = false;
        if (isset($value['required_goal']) && !empty($value['required_goal'])) {
            $checkGoal = true;
        }

		foreach ($goals as $key => $value) {
            $goalMeet = true;
            if ($checkGoal) {
                $exists = DB::sql("SELECT * FROM `current_goal_values` WHERE `character_id` = {$this->character->id} AND `identifier` = {$value['required_goal']}")->fetch(PDO::FETCH_ASSOC);
                if ($exists['count']) {
                    $goalMeet = false;
                }
            }

			if ($goalMeet && $value['required_level'] <= $this->character->level) {
				$exists = DB::sql("SELECT COUNT(*) as count FROM `current_goal_values` WHERE `character_id` = {$this->character->id} AND `identifier` = '$key'")->fetch(PDO::FETCH_ASSOC);
				if ($exists['count'] == 0) {
					DB::sql("INSERT INTO `current_goal_values`(`character_id`, `identifier`, `value`, `current_value`) VALUES ({$this->character->id}, '$key', 0, 0)");
				}
			}
		}

		$x = DB::sql("SELECT * FROM `current_goal_values` WHERE `character_id` = {$this->character->id}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($x as $goal) {
            $this->current_goal_values[$goal['identifier']] = [
                    'value' => $goal['value'],
                    'current_value' => $goal['current_value']
            ];
        }
    }

    public function checkCollectedGoals() {
        $x = DB::sql("SELECT * FROM `collected_goals` WHERE `character_id` = {$this->character->id}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($x as $goal) {
            $this->collected_goals[] = [
                $goal['identifier'] => [
                    'value' => $goal['value'],
                    'date' => $goal['date']
                ]
            ];
        }
    }

    public function updateCurrentGoalValue($identifier, $value) {
        DB::sql("UPDATE current_goal_values SET `value` = ?, `current_value` = ? WHERE `identifier` = ? AND `character_id` = ?", 
        [$value, $value, $identifier, $this->character->id]);

        Core::req()->append['current_goal_values'][$identifier] = [
            'value' => $value,
            'current_value' => $value
        ];
    }

    public function getCurrentGoalValue($identifier) {
        if (isset($this->current_goal_values[$identifier])) {
            return $this->current_goal_values[$identifier]['current_value'];
        }
        return 0;
    }
}