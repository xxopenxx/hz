<?php
namespace Request;

use Srv\Core;
use Srv\Config;
// Ensure Schema\Hideout, Schema\Character, Cls\Player are available
// either via autoloader or explicit require_once if not already handled.

class gethideoutopponent {
    public function __request($player) {
        // Step 1: Load player's hideout
        $player_hideout = \Schema\Hideout::find(function($q) use ($player) {
            $q->where('character_id', $player->character->id);
        });

        if (!$player_hideout) {
            return Core::setError('errPlayerHideoutNotFound');
        }

        // Step 2: Check Refresh Cooldown
        $cooldown_seconds = Config::get('constants.hideout_opponent_refresh_cooldown_seconds', 3600);

        $opponent_hideout_to_return = null;
        $refresh_time_remaining = 0;

        if ($player_hideout->current_opponent_id != 0) {
            if (time() < ($player_hideout->ts_last_opponent_refresh + $cooldown_seconds)) {
                $temp_opponent = \Schema\Hideout::find($player_hideout->current_opponent_id);
                if ($temp_opponent && $temp_opponent->id) { // Check if a valid object was returned
                    $opponent_hideout_to_return = $temp_opponent;
                    $refresh_time_remaining = ($player_hideout->ts_last_opponent_refresh + $cooldown_seconds) - time();
                }
            }
        }
        
        // Step 3: Find New Opponent (if not returning existing)
        if ($opponent_hideout_to_return === null) {
            $last_ids_str = $player_hideout->last_opponent_ids ?? '';
            $last_opponent_ids_array = array_filter(explode(',', $last_ids_str));

            $new_opponent_hideout_schema = \Schema\Hideout::find(function($q) use ($player, $last_opponent_ids_array) {
                $q->where('character_id', '!=', $player->character->id);
                $q->where('active_battle_id', 0);
                if (!empty($last_opponent_ids_array)) {
                    $q->whereNotIn('id', $last_opponent_ids_array);
                }
                $q->orderByRaw('RAND()'); 
                $q->limit(1);
            });

            if (!$new_opponent_hideout_schema || !$new_opponent_hideout_schema->id) {
                return Core::setError('errNoHideoutOpponentFound');
            }
            $opponent_hideout_to_return = $new_opponent_hideout_schema;

            // Step 4: Update Player's Hideout Data for the new opponent
            // Fetch the player's hideout record for update:
            $player_hideout_for_update = \Schema\Hideout::find($player_hideout->id); // Re-fetch for save
            if (!$player_hideout_for_update) {
                 return Core::setError('errPlayerHideoutNotFoundCritical');
            }

            $previous_opponent_id = $player_hideout_for_update->current_opponent_id;
            
            $player_hideout_for_update->current_opponent_id = $opponent_hideout_to_return->id;
            $player_hideout_for_update->ts_last_opponent_refresh = time();

            $current_last_ids_array = array_filter(explode(',', $player_hideout_for_update->last_opponent_ids ?? ''));
            if ($previous_opponent_id != 0 && $previous_opponent_id != $opponent_hideout_to_return->id && !in_array($previous_opponent_id, $current_last_ids_array)) {
                array_unshift($current_last_ids_array, $previous_opponent_id);
            }
            
            $max_last_opponents = (int)Config::get('constants.max_hideout_opponent_history', 10);
            
            $final_last_ids_array = array_slice($current_last_ids_array, 0, $max_last_opponents);
            $player_hideout_for_update->last_opponent_ids = implode(',', $final_last_ids_array);
            
            $player_hideout_for_update->save();
        }
        
        // Step 5: Fetch Opponent's Details
        if (!$opponent_hideout_to_return || !$opponent_hideout_to_return->id) {
             // This case should ideally be caught by errNoHideoutOpponentFound earlier
             return Core::setError('errOpponentDataInvalid');
        }

        $opponent_character_name = "Unknown";
        if ($opponent_hideout_to_return->character_id) {
            $opponent_player = \Cls\Player::findByCharacterId($opponent_hideout_to_return->character_id);
            if ($opponent_player && $opponent_player->character && isset($opponent_player->character->name)) {
                $opponent_character_name = $opponent_player->character->name;
            } else {
                $opponent_char_schema = \Schema\Character::find($opponent_hideout_to_return->character_id);
                if ($opponent_char_schema && isset($opponent_char_schema->name)) {
                    $opponent_character_name = $opponent_char_schema->name;
                }
            }
        }

        // Step 6: Return Response
        Core::req()->data = [
            "opponent" => [
                "hideout_id" => $opponent_hideout_to_return->id,
                "character_id" => $opponent_hideout_to_return->character_id,
                "character_name" => $opponent_character_name,
                "level" => $opponent_hideout_to_return->current_level,
                "points" => $opponent_hideout_to_return->hideout_points,
                "wall_level" => $opponent_hideout_to_return->current_wall_level,
                "defender_units" => $opponent_hideout_to_return->current_defender_units
            ],
            "refresh_time_remaining" => intval($refresh_time_remaining) // Ensure it's an integer
        ];
    }
}
?>
