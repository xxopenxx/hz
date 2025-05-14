<?php
namespace Cls;

use Srv\Config;
use Cls\Player;
use Cls\NPC;
use Cls\Utils;

class Fight{
    public static $ATTACK_TYPE = [
        0=>'unknown',
        1=>'dodged',
        2=>'normalhit',
        3=>'criticalhit',
        4=>'guildmissilenormalhit',
        5=>'guildmissilecriticalhit'
    ];
    public static $ATTACK_TYPE_ID = [
        'dodged'=>1,
        'normalhit'=>2,
        'criticalhit'=>3,
        'guildmissilenormalhit'=>4,
        'guildmissilecriticalhit'=>5
    ];
    
    private $op1 = null;
    private $op2 = null;
    private $rounds = [];
    private $winner = 0;
    private $critFact = 0;
    private $isGuildFight = false;
    private $missileFactor = 1;
    private $missilesUsed = false;
    private $skills_used = [];
    
    public function __construct($op1, $op2, $guildFight=false){
        $this->op1 = $op1;
        $this->op2 = $op2;
        $this->isGuildFight = $guildFight;
        //
        $this->op1->chance_critical = Utils::getCriticalHitPercentage($this->op1->criticalrating, $this->op2->criticalrating);
        $this->op2->chance_critical = Utils::getCriticalHitPercentage($this->op2->criticalrating, $this->op1->criticalrating);
        $this->op1->chance_dodge = Utils::getDodgePercentage($this->op1->dodgerating, $this->op2->dodgerating);
        $this->op2->chance_dodge = Utils::getDodgePercentage($this->op2->dodgerating, $this->op1->dodgerating);
        //
        $this->critFact = Config::get('constants.battle_critical_factor');
        $this->missileFactor = Config::get('constants.guild_battle_missile_damage_factor');
    }
    
    public function fight(){
        $attacker = $this->op1;
        $attacker_canmissile = $this->isGuildFight && $attacker->useGuildMissile() && $attacker->guild->missiles > 0 && $attacker->guild->use_missiles_attack;
        if($attacker_canmissile){
            $attacker->guild->missiles--;
            $attacker->guild->missiles_fired++;
        }

        $opponent = $this->op2;
        $opponent_canmissile = $this->isGuildFight && $opponent->useGuildMissile() && $opponent->guild->missiles > 0 && $opponent->guild->use_missiles_defense;
        if($opponent_canmissile){
            $opponent->guild->missiles--;
            $opponent->guild->missiles_fired++;
        }

        $rand = random() < 0.5;
        $skill_attacker = 0;
        $skill_opponent = 0; 
        
        if($attacker->sidekicks && $attacker->sidekicks->level >= 10 && $attacker->sidekicks->stage1_skill_id == 3){
            $rand = random() < 0.7;
            $skill_attacker = 3;            
        }

        if($opponent->sidekicks && $opponent->sidekicks->level >= 10 && $opponent->sidekicks->stage1_skill_id == 3){
            $rand = random() < 0.3;
            $skill_opponent = 3;
        }

        if($skill_attacker == 3 && $skill_opponent == 3){
            $rand = random() < 0.5;
        }
        //Walka
        do{
            if($rand){
                if($attacker->hitpoints > 0)
                    $this->hit($attacker, $opponent, $attacker_canmissile);
                if($opponent->hitpoints > 0)
                    $this->hit($opponent, $attacker, $opponent_canmissile);
            }else{
                if($opponent->hitpoints > 0)
                    $this->hit($opponent, $attacker, $opponent_canmissile);
                if($attacker->hitpoints > 0)
                    $this->hit($attacker, $opponent, $attacker_canmissile);
            }
        }while($attacker->hitpoints > 0 && $opponent->hitpoints > 0);
        if($this->op1->hitpoints > 0)
            $this->winner = 1;
        else
            $this->winner = 2;
    }
    
    private function hit($attacker, $issuer, $use_missile){
        if($this->isGuildFight)
            $roundData = ['a'=> (string)$attacker->profile, 'd'=>(string)$issuer->profile];
        else
            $roundData = ['a'=> $attacker->profile];

        if(!$use_missile && round(random(), 3) < $issuer->chance_dodge)
            $roundData['r'] = static::$ATTACK_TYPE_ID['dodged'];
        else{
            if($use_missile)
                $roundData['r'] = static::$ATTACK_TYPE_ID['guildmissilenormalhit'];
            else{
                $roundData['r'] = static::$ATTACK_TYPE_ID['normalhit'];
                if($attacker->getMissile() && $attacker->getMissile()->charges > 0){
                    $roundData['m'] = 1;
                    $attacker->getMissile()->charges--;
                    $this->missilesUsed = true;
                }
            }
            $hitpoints = random(0.8,1.05)*$attacker->damage_normal*($use_missile?$this->missileFactor:1);


            if(round(random(), 3) < $attacker->chance_critical){
                $hitpoints = round($hitpoints * $this->critFact);
                $roundData['r'] = $use_missile?static::$ATTACK_TYPE_ID['guildmissilecriticalhit']:static::$ATTACK_TYPE_ID['criticalhit'];
                //var_dump($issuer->sidekicks);

                if($issuer->sidekicks && $issuer->sidekicks->level >= 10){
                    if(rand() <= 0.2 && $issuer->sidekicks->stage1_skill_id == 2 && !$skills_used[1][2][$issuer->id] && !$roundData['r']){ //if critical hit and has the skill                  
                        $hitpoints = $hitpoints - ($hitpoints * 0.4);
                        $skills_used[1][2][$issuer->id] = true;
                    }

                    if(rand() <= 0.15 && $issuer->sidekicks->stage1_skill_id == 24 && !$skills_used[1][24][$issuer->id] && $roundData['r']){ //if missile critical hit and has le skill                
                        $hitpoints = $hitpoints - ($hitpoints * 0.75);
                        $skills_used[1][24][$issuer->id] = true;
                    }
                }
            }

            if($issuer->sidekicks && $issuer->sidekicks->level >= 30){
                if(rand() <= 0.10 && $issuer->sidekicks->stage3_skill_id == 26) $hitpoints = $hitpoints - ($hitpoints * 0.25);
            }

            if($attacker->sidekicks){
                if($attacker->sidekicks->level >= 20) {
                    if($attacker->sidekicks->stage2_skill_id == 10 && $use_missile) $hitpoints*= 1.10;
                }

                if($attacker->sidekicks->level >= 30) {
                    if(rand() <= 0.30 && $attacker->sidekicks->stage3_skill_id == 21) $hitpoints*= 1.50;
                    if(rand() <= 0.40 && $attacker->sidekicks->stage3_skill_id == 30) $hitpoints*= 1.50;
                }
            }

            //$roundData['m'] - Użycie pocisku/broni rzucanej
            $hitpoints = round($hitpoints);
            $roundData['v'] = $hitpoints;
            $issuer->hitpoints -= $hitpoints;
        }
        
        $this->rounds[] = $roundData;
    }
    
    public function getWinner(){
        return $this->winner;
    }
    
    public function getRounds(){
        return $this->rounds;
    }
    
    public function isMissileUsed(){
        return $this->missilesUsed;
    }
}