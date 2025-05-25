<?php
namespace Request;

use Srv\Core;
use Srv\Config;
// Ensure Schema\Hideout, Schema\HideoutBattle, Schema\Character, Cls\Player are available

class startHideoutBattle {
    public function __request($player) {
        // Step 1: Load Player's Data & Initial Checks
        $player_hideout = \Schema\Hideout::find(function($q) use ($player) {
            $q->where('character_id', $player->character->id);
        });

        if (!$player_hideout) {
            return Core::setError('errPlayerHideoutNotFound');
        }
        if ($player_hideout->active_battle_id != 0) {
            return Core::setError('errHideoutBattleAlreadyInProgress');
        }

        // Step 2: Validate Opponent
        $opponent_hideout_id = intval(getField('opponent_hideout_id', FIELD_NUM));
        if (!$opponent_hideout_id) {
            return Core::setError('errOpponentHideoutIdMissing');
        }
        if ($opponent_hideout_id != $player_hideout->current_opponent_id) {
            return Core::setError('errInvalidHideoutOpponent');
        }

        $opponent_hideout = \Schema\Hideout::find($opponent_hideout_id);
        if (!$opponent_hideout || !$opponent_hideout->id) {
            return Core::setError('errOpponentHideoutNotFound');
        }
        if ($opponent_hideout->active_battle_id != 0) {
            return Core::setError('errOpponentAlreadyInBattle');
        }

        // Step 3: Check Battle Preconditions (Player)
        $min_attacker_units = (int)Config::get('constants.min_hideout_attacker_units', 1);

        if ($player_hideout->current_attacker_units < $min_attacker_units) {
            return Core::setError('errNotEnoughAttackerUnits');
        }

        // Step 4: Create Battle Record
        $attacker_character_name = $player->character->name;
        $defender_character_name = "Unknown";
        if ($opponent_hideout->character_id) {
            $defender_player = \Cls\Player::findByCharacterId($opponent_hideout->character_id);
            if ($defender_player && $defender_player->character && isset($defender_player->character->name)) {
                $defender_character_name = $defender_player->character->name;
            } else {
                $defender_char_schema = \Schema\Character::find($opponent_hideout->character_id);
                if ($defender_char_schema && isset($defender_char_schema->name)) {
                    $defender_character_name = $defender_char_schema->name;
                }
            }
        }

        $attacker_profile_data = json_encode(['units' => $player_hideout->current_attacker_units]);
        $defender_profile_data = json_encode(['units' => $opponent_hideout->current_defender_units]);

        $new_battle_data = [
            'attacker_hideout_id' => $player_hideout->id,
            'defender_hideout_id' => $opponent_hideout->id,
            'attacker_status' => 1, 
            'defender_status' => 0, 
            'attacker_count' => $player_hideout->current_attacker_units,
            'defender_count' => $opponent_hideout->current_defender_units,
            'attacker_profiles' => $attacker_profile_data,
            'defender_profiles' => $defender_profile_data,
            'attacker_character_name' => $attacker_character_name,
            'defender_character_name' => $defender_character_name
            // 'ts_battle_start' => time() // Add this if schema supports, or use existing field
        ];
        // Add ts_battle_start if the field exists in Schema/HideoutBattle.php
        if (array_key_exists('ts_battle_start', \Schema\HideoutBattle::getFields())) {
            $new_battle_data['ts_battle_start'] = time();
        }


        $new_battle = new \Schema\HideoutBattle($new_battle_data);
        $new_battle->save();

        if (!$new_battle->id) {
            return Core::setError('errBattleCreationFailure');
        }

        // Step 5: Update Hideout Records
        $player_hideout_update = \Schema\Hideout::find($player_hideout->id);
        if (!$player_hideout_update) return Core::setError('errPlayerHideoutNotFoundCritical');
        $player_hideout_update->active_battle_id = $new_battle->id;
        $player_hideout_update->save();

        $opponent_hideout_update = \Schema\Hideout::find($opponent_hideout->id);
        if (!$opponent_hideout_update) return Core::setError('errOpponentHideoutNotFoundCritical');
        $opponent_hideout_update->active_battle_id = $new_battle->id;
        $opponent_hideout_update->save();
        
        // Step 6: Return Response
        Core::req()->data = [
            "battle_id" => $new_battle->id,
            "attacker_hideout_id" => $player_hideout->id,
            "defender_hideout_id" => $opponent_hideout->id,
            "battle_status_code" => 1, 
            "battle_status_message" => "initiated",
            "player_hideout_updated" => [
                "active_battle_id" => $new_battle->id
            ],
            "opponent_details" => [
                "character_name" => $defender_character_name,
                "hideout_level" => $opponent_hideout->current_level
            ]
        ];
    }
}
?>
