<?php
namespace Cls\Utils;
use Cls\GameSettings;

class HideoutUtils
{
    const HideoutBuildStatus = [
        "Unknown" => 0,
        "Idle" => 1,
        "Building" => 2,
        "Upgrading" => 3,
        "Placing" => 4,
        "Storing" => 5,
        "Producing" => 6
    ];

    const MAX_LEVELS = 4;
    const MAX_SLOTS = 5;

    public static function getInstantFinishPremiumAmount($param1) {
        $gameSettings = GameSettings::returnConstants();
        $_loc8_ = 0;
        $_loc3_ = 0;
        $_loc7_ = 0;
        $_loc5_ = 0;
        $_loc6_ = 0;
        $_loc10_ = 0;
        $_loc4_ = 0;
        $_loc9_ = 0;
        $_loc2_ = 0;

        if ($param1 > 0) {
            $_loc8_ = $gameSettings["hideout_skip_min_time"];
            $_loc3_ = $gameSettings["hideout_skip_min_premium_amount"];
            $_loc7_ = $gameSettings["hideout_skip_medium_time"];
            $_loc5_ = $gameSettings["hideout_skip_medium_premium_amount"];
            $_loc6_ = $gameSettings["hideout_skip_long_time"];
            $_loc10_ = $gameSettings["hideout_skip_long_premium_amount"];
            $_loc4_ = $gameSettings["hideout_skip_max_time"];
            $_loc9_ = $gameSettings["hideout_skip_max_premium_amount"];

            if ($param1 <= $_loc8_) {
                $_loc2_ = $_loc3_;
            } elseif ($param1 <= $_loc7_) {
                $_loc2_ = ($_loc5_ - $_loc3_) / ($_loc7_ - $_loc8_) * ($param1 - $_loc8_) + $_loc3_;
            } elseif ($param1 <= $_loc6_) {
                $_loc2_ = ($_loc10_ - $_loc5_) / ($_loc6_ - $_loc7_) * ($param1 - $_loc7_) + $_loc5_;
            } else {
                $_loc2_ = ($_loc9_ - $_loc10_) / ($_loc4_ - $_loc6_) * ($param1 - $_loc6_) + $_loc10_;
            }
        }

        return ceil($_loc2_);
    }

    public static function isManuallyProductionRoom($identifier)
    {
        return $identifier == "attacker_production" || $identifier == "defender_production" || $identifier == "gem_production";
    }
      
    public static function isAutoProductionRoom($identifier)
    {
        return $identifier == "stone_production" || $identifier == "glue_production" || $identifier == "attacker_production" || $identifier == "main_building";
    }
    
    public static function resourceAmountPerMinute($hideoutRoom) {
        $_loc3_ = 0;
        $_loc2_ = 0;
        $_loc1_ = 0;

        if (HideoutUtils::isManuallyProductionRoom($hideoutRoom->identifier)) {
            $_loc3_ = ($hideoutRoom->ts_activity_end - $hideoutRoom->ts_last_resource_change) / ($hideoutRoom->max_resource_amount - $hideoutRoom->current_resource_amount);
            $_loc1_ = 60 / $_loc3_;
        } elseif (HideoutUtils::isAutoProductionRoom($hideoutRoom->identifier)) {
            $_loc2_ = GameSettings::getConstant("hideout_rooms.{$hideoutRoom->identifier}.levels.{$hideoutRoom->level}.min_till_max_resource");
            $_loc1_ = $hideoutRoom->max_resource_amount / $_loc2_;
        }
        
        return $_loc1_;
    }

    public static function currentCalculatedResourceAmount($hideoutRoom) {
        $_loc3_ = $hideoutRoom->current_resource_amount;
        $_loc1_ = $hideoutRoom->max_resource_amount;

        if ($hideoutRoom->status != HideoutUtils::HideoutBuildStatus['Producing'] || $_loc3_ >= $_loc1_) {
            return $_loc3_;
        }

        if (HideoutUtils::isManuallyProductionRoom($hideoutRoom->identifier) && $hideoutRoom->ts_activity_end <= time()) {
            return $_loc1_;
        }

        $_loc4_ = $hideoutRoom->ts_last_resource_change;
        
        $_loc5_ = time() - $_loc4_;
        $_loc6_ = floor($_loc5_ / 60);

        if ($_loc6_ <= 0) {
            return $_loc3_;
        }

        $_loc2_ = floor(HideoutUtils::resourceAmountPerMinute($hideoutRoom) * $_loc6_);

        return min($_loc2_ + $_loc3_, $_loc1_);
    }
}