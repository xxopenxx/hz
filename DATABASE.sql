-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Czas generowania: 07 Gru 2019, 19:09
-- Wersja serwera: 10.1.41-MariaDB
-- Wersja PHP: 7.0.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `DATABASE`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bank_inventory`
--

CREATE TABLE `bank_inventory` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `max_bank_index` int(11) NOT NULL,
  `bank_item1_id` int(11) NOT NULL,
  `bank_item2_id` int(11) NOT NULL,
  `bank_item3_id` int(11) NOT NULL,
  `bank_item4_id` int(11) NOT NULL,
  `bank_item5_id` int(11) NOT NULL,
  `bank_item6_id` int(11) NOT NULL,
  `bank_item7_id` int(11) NOT NULL,
  `bank_item8_id` int(11) NOT NULL,
  `bank_item9_id` int(11) NOT NULL,
  `bank_item10_id` int(11) NOT NULL,
  `bank_item11_id` int(11) NOT NULL,
  `bank_item12_id` int(11) NOT NULL,
  `bank_item13_id` int(11) NOT NULL,
  `bank_item14_id` int(11) NOT NULL,
  `bank_item15_id` int(11) NOT NULL,
  `bank_item16_id` int(11) NOT NULL,
  `bank_item17_id` int(11) NOT NULL,
  `bank_item18_id` int(11) NOT NULL,
  `bank_item19_id` int(11) NOT NULL,
  `bank_item20_id` int(11) NOT NULL,
  `bank_item21_id` int(11) NOT NULL,
  `bank_item22_id` int(11) NOT NULL,
  `bank_item23_id` int(11) NOT NULL,
  `bank_item24_id` int(11) NOT NULL,
  `bank_item25_id` int(11) NOT NULL,
  `bank_item26_id` int(11) NOT NULL,
  `bank_item27_id` int(11) NOT NULL,
  `bank_item28_id` int(11) NOT NULL,
  `bank_item29_id` int(11) NOT NULL,
  `bank_item30_id` int(11) NOT NULL,
  `bank_item31_id` int(11) NOT NULL,
  `bank_item32_id` int(11) NOT NULL,
  `bank_item33_id` int(11) NOT NULL,
  `bank_item34_id` int(11) NOT NULL,
  `bank_item35_id` int(11) NOT NULL,
  `bank_item36_id` int(11) NOT NULL,
  `bank_item37_id` int(11) NOT NULL,
  `bank_item38_id` int(11) NOT NULL,
  `bank_item39_id` int(11) NOT NULL,
  `bank_item40_id` int(11) NOT NULL,
  `bank_item41_id` int(11) NOT NULL,
  `bank_item42_id` int(11) NOT NULL,
  `bank_item43_id` int(11) NOT NULL,
  `bank_item44_id` int(11) NOT NULL,
  `bank_item45_id` int(11) NOT NULL,
  `bank_item46_id` int(11) NOT NULL,
  `bank_item47_id` int(11) NOT NULL,
  `bank_item48_id` int(11) NOT NULL,
  `bank_item49_id` int(11) NOT NULL,
  `bank_item50_id` int(11) NOT NULL,
  `bank_item51_id` int(11) NOT NULL,
  `bank_item52_id` int(11) NOT NULL,
  `bank_item53_id` int(11) NOT NULL,
  `bank_item54_id` int(11) NOT NULL,
  `bank_item55_id` int(11) NOT NULL,
  `bank_item56_id` int(11) NOT NULL,
  `bank_item57_id` int(11) NOT NULL,
  `bank_item58_id` int(11) NOT NULL,
  `bank_item59_id` int(11) NOT NULL,
  `bank_item60_id` int(11) NOT NULL,
  `bank_item61_id` int(11) NOT NULL,
  `bank_item62_id` int(11) NOT NULL,
  `bank_item63_id` int(11) NOT NULL,
  `bank_item64_id` int(11) NOT NULL,
  `bank_item65_id` int(11) NOT NULL,
  `bank_item66_id` int(11) NOT NULL,
  `bank_item67_id` int(11) NOT NULL,
  `bank_item68_id` int(11) NOT NULL,
  `bank_item69_id` int(11) NOT NULL,
  `bank_item70_id` int(11) NOT NULL,
  `bank_item71_id` int(11) NOT NULL,
  `bank_item72_id` int(11) NOT NULL,
  `bank_item73_id` int(11) NOT NULL,
  `bank_item74_id` int(11) NOT NULL,
  `bank_item75_id` int(11) NOT NULL,
  `bank_item76_id` int(11) NOT NULL,
  `bank_item77_id` int(11) NOT NULL,
  `bank_item78_id` int(11) NOT NULL,
  `bank_item79_id` int(11) NOT NULL,
  `bank_item80_id` int(11) NOT NULL,
  `bank_item81_id` int(11) NOT NULL,
  `bank_item82_id` int(11) NOT NULL,
  `bank_item83_id` int(11) NOT NULL,
  `bank_item84_id` int(11) NOT NULL,
  `bank_item85_id` int(11) NOT NULL,
  `bank_item86_id` int(11) NOT NULL,
  `bank_item87_id` int(11) NOT NULL,
  `bank_item88_id` int(11) NOT NULL,
  `bank_item89_id` int(11) NOT NULL,
  `bank_item90_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `battle`
--

CREATE TABLE `battle` (
  `id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `profile_a_stats` text NOT NULL,
  `profile_b_stats` text NOT NULL,
  `winner` varchar(1) NOT NULL,
  `rounds` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `character`
--

CREATE TABLE `character` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `gender` enum('m','f') NOT NULL,
  `game_currency` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `xp` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `level` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `description` varchar(255) NOT NULL,
  `note` varchar(512) NOT NULL,
  `ts_last_action` int(11) NOT NULL,
  `score_honor` int(11) NOT NULL DEFAULT '10',
  `score_level` int(11) NOT NULL DEFAULT '10',
  `stat_points_available` mediumint(9) NOT NULL,
  `stat_base_stamina` smallint(6) NOT NULL DEFAULT '10',
  `stat_base_strength` smallint(6) NOT NULL DEFAULT '10',
  `stat_base_critical_rating` smallint(6) NOT NULL DEFAULT '10',
  `stat_base_dodge_rating` smallint(6) NOT NULL DEFAULT '10',
  `stat_bought_stamina` mediumint(8) NOT NULL,
  `stat_bought_strength` mediumint(8) NOT NULL,
  `stat_bought_critical_rating` mediumint(8) NOT NULL,
  `stat_bought_dodge_rating` mediumint(8) NOT NULL,
  `active_quest_booster_id` varchar(25) NOT NULL,
  `ts_active_quest_boost_expires` int(11) NOT NULL,
  `active_stats_booster_id` varchar(25) NOT NULL,
  `ts_active_stats_boost_expires` int(11) NOT NULL,
  `active_work_booster_id` varchar(25) NOT NULL,
  `ts_active_work_boost_expires` int(11) NOT NULL,
  `ts_active_sense_boost_expires` int(11) NOT NULL,
  `active_league_booster_id` varchar(16) NOT NULL,
  `ts_active_league_boost_expires` int(11) NOT NULL,
  `ts_active_multitasking_boost_expires` int(11) NOT NULL,
  `max_quest_stage` smallint(6) NOT NULL DEFAULT '1',
  `current_quest_stage` smallint(6) NOT NULL DEFAULT '1',
  `quest_energy` smallint(6) NOT NULL DEFAULT '100',
  `max_quest_energy` smallint(6) NOT NULL DEFAULT '1000',
  `ts_last_quest_energy_refill` int(11) NOT NULL,
  `quest_energy_refill_amount_today` smallint(6) NOT NULL,
  `quest_reward_training_sessions_rewarded_today` smallint(6) NOT NULL,
  `honor` mediumint(8) UNSIGNED NOT NULL DEFAULT '100',
  `ts_last_duel` int(11) NOT NULL,
  `duel_stamina` smallint(6) NOT NULL DEFAULT '100',
  `max_duel_stamina` smallint(6) NOT NULL DEFAULT '100',
  `ts_last_duel_stamina_change` int(11) NOT NULL,
  `ts_last_duel_enemies_refresh` int(11) NOT NULL,
  `current_work_offer_id` varchar(32) NOT NULL DEFAULT 'work1',
  `stat_trained_stamina` mediumint(8) NOT NULL,
  `stat_trained_strength` mediumint(8) NOT NULL,
  `stat_trained_critical_rating` mediumint(8) NOT NULL,
  `stat_trained_dodge_rating` mediumint(8) NOT NULL,
  `training_progress_value_stamina` smallint(8) NOT NULL,
  `training_progress_value_strength` mediumint(8) NOT NULL,
  `training_progress_value_critical_rating` mediumint(8) NOT NULL,
  `training_progress_value_dodge_rating` mediumint(8) NOT NULL,
  `training_progress_end_stamina` smallint(6) NOT NULL DEFAULT '3',
  `training_progress_end_strength` smallint(6) NOT NULL DEFAULT '3',
  `training_progress_end_critical_rating` smallint(6) NOT NULL DEFAULT '3',
  `training_progress_end_dodge_rating` smallint(6) NOT NULL DEFAULT '3',
  `ts_last_training` int(11) NOT NULL,
  `training_count` smallint(6) NOT NULL DEFAULT '10',
  `max_training_count` smallint(6) NOT NULL DEFAULT '10',
  `active_worldboss_attack_id` int(11) NOT NULL,
  `active_dungeon_quest_id` int(11) NOT NULL,
  `ts_last_dungeon_quest_fail` int(11) NOT NULL,
  `max_dungeon_index` int(11) NOT NULL,
  `appearance_skin_color` tinyint(3) NOT NULL,
  `appearance_hair_color` tinyint(3) NOT NULL,
  `appearance_hair_type` tinyint(3) NOT NULL,
  `appearance_head_type` tinyint(3) NOT NULL,
  `appearance_eyes_type` tinyint(3) NOT NULL,
  `appearance_eyebrows_type` tinyint(3) NOT NULL,
  `appearance_nose_type` tinyint(3) NOT NULL,
  `appearance_mouth_type` tinyint(3) NOT NULL,
  `appearance_facial_hair_type` tinyint(3) NOT NULL,
  `appearance_decoration_type` tinyint(3) NOT NULL DEFAULT '1',
  `show_mask` tinyint(1) NOT NULL DEFAULT '1',
  `tutorial_flags` text NOT NULL,
  `guild_id` int(11) NOT NULL,
  `guild_rank` tinyint(2) NOT NULL,
  `ts_guild_joined` int(11) NOT NULL,
  `finished_guild_battle_attack_id` int(11) NOT NULL,
  `finished_guild_battle_defense_id` int(11) NOT NULL,
  `finished_guild_dungeon_battle_id` int(11) NOT NULL,
  `guild_donated_game_currency` int(11) NOT NULL,
  `guild_donated_premium_currency` int(11) NOT NULL,
  `worldboss_event_id` int(11) NOT NULL,
  `worldboss_event_attack_count` smallint(6) NOT NULL,
  `ts_last_wash_item` int(11) NOT NULL,
  `ts_last_daily_login_bonus` int(11) NOT NULL,
  `daily_login_bonus_day` tinyint(3) NOT NULL DEFAULT '1',
  `pending_tournament_rewards` int(11) NOT NULL,
  `ts_last_shop_refresh` int(11) NOT NULL,
  `shop_refreshes` smallint(6) NOT NULL,
  `max_free_shop_refreshes` int(11) NOT NULL,
  `event_quest_id` int(11) NOT NULL,
  `friend_data` varchar(32) NOT NULL,
  `pending_resource_requests` smallint(6) NOT NULL,
  `unused_resources` varchar(32) NOT NULL DEFAULT '{"1":4,"2":1}',
  `used_resources` int(11) NOT NULL,
  `league_points` int(11) NOT NULL,
  `league_group_id` int(11) NOT NULL,
  `active_league_fight_id` int(11) NOT NULL,
  `active_duel_id` int(10) NOT NULL,
  `active_quest_id` int(11) NOT NULL,
  `active_training_id` int(11) NOT NULL,
  `active_work_id` int(11) NOT NULL,
  `ts_last_league_fight` int(11) NOT NULL,
  `league_fight_count` int(11) NOT NULL,
  `league_opponents` varchar(32) NOT NULL,
  `ts_last_league_opponents_refresh` int(11) NOT NULL,
  `league_stamina` smallint(6) NOT NULL DEFAULT '20',
  `max_league_stamina` smallint(6) NOT NULL DEFAULT '20',
  `ts_last_league_stamina_change` int(11) NOT NULL,
  `duel_stamina_cost` int(11) NOT NULL,
  `league_stamina_cost` int(11) NOT NULL DEFAULT '20',
  `herobook_objectives_renewed_today` int(11) NOT NULL,
  `slotmachine_spin_count` int(11) NOT NULL,
  `ts_last_slotmachine_refill` int(11) NOT NULL,
  `new_user_voucher_ids` varchar(32) NOT NULL,
  `current_energy_storage` int(11) NOT NULL,
  `current_training_storage` int(11) NOT NULL,
  `received_sidekick` tinyint(1) NOT NULL DEFAULT '0',
  `role` tinyint(1) NOT NULL DEFAULT '0',
  `current_slotmachine_spin` int(11) NOT NULL,
  `online_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `duel`
--

CREATE TABLE `duel` (
  `id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `battle_id` int(11) NOT NULL,
  `character_a_id` int(11) NOT NULL,
  `character_b_id` int(11) NOT NULL,
  `character_a_status` tinyint(1) NOT NULL DEFAULT '1',
  `character_b_status` tinyint(1) NOT NULL DEFAULT '1',
  `character_a_rewards` text NOT NULL,
  `character_b_rewards` text NOT NULL,
  `unread` enum('true','false','','') NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dungeons`
--

CREATE TABLE `dungeons` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `identifier` varchar(32) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `current_dungeon_quest_id` int(11) NOT NULL,
  `progress_index` tinyint(3) NOT NULL DEFAULT '1',
  `mode` tinyint(3) NOT NULL,
  `ts_last_complete` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dungeon_quests`
--

CREATE TABLE `dungeon_quests` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `character_level` tinyint(3) NOT NULL DEFAULT '0',
  `identifier` varchar(32) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `battle_id` int(11) NOT NULL,
  `rewards` varchar(200) NOT NULL,
  `mode` tinyint(3) NOT NULL,
  `dungeon_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild`
--

CREATE TABLE `guild` (
  `id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `initiator_character_id` int(11) NOT NULL,
  `leader_character_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `note` text NOT NULL,
  `forum_page` varchar(128) NOT NULL,
  `premium_currency` int(11) NOT NULL,
  `game_currency` int(11) NOT NULL DEFAULT '500',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `accept_members` tinyint(1) NOT NULL,
  `honor` int(11) NOT NULL DEFAULT '1000',
  `artifact_ids` text NOT NULL,
  `missiles` int(11) NOT NULL DEFAULT '15',
  `auto_joins` tinyint(1) NOT NULL,
  `battles_attacked` int(11) NOT NULL,
  `battles_defended` int(11) NOT NULL,
  `battles_won` int(11) NOT NULL,
  `battles_lost` int(11) NOT NULL,
  `artifacts_won` int(11) NOT NULL,
  `artifacts_lost` int(11) NOT NULL,
  `artifacts_owned_max` int(11) NOT NULL DEFAULT '2',
  `artifacts_owned_current` int(11) NOT NULL,
  `ts_last_artifact_released` int(11) NOT NULL,
  `missiles_fired` int(11) NOT NULL,
  `auto_joins_used` tinyint(1) NOT NULL,
  `dungeon_battles_fought` int(11) NOT NULL,
  `dungeon_battles_won` int(11) NOT NULL,
  `stat_points_available` int(11) NOT NULL,
  `stat_guild_capacity` int(11) NOT NULL DEFAULT '10',
  `stat_character_base_stats_boost` int(11) NOT NULL DEFAULT '1',
  `stat_quest_xp_reward_boost` int(11) NOT NULL DEFAULT '1',
  `stat_quest_game_currency_reward_boost` int(11) NOT NULL DEFAULT '1',
  `arena_background` smallint(3) NOT NULL DEFAULT '1',
  `emblem_background_shape` tinyint(3) NOT NULL DEFAULT '1',
  `emblem_background_color` tinyint(3) NOT NULL DEFAULT '2',
  `emblem_background_border_color` tinyint(3) NOT NULL,
  `emblem_icon_shape` tinyint(3) NOT NULL DEFAULT '1',
  `emblem_icon_color` tinyint(3) NOT NULL DEFAULT '4',
  `emblem_icon_size` smallint(3) NOT NULL DEFAULT '100',
  `use_missiles_attack` tinyint(1) NOT NULL DEFAULT '1',
  `use_missiles_defense` tinyint(1) NOT NULL DEFAULT '1',
  `use_missiles_dungeon` tinyint(1) NOT NULL DEFAULT '1',
  `use_auto_joins_attack` tinyint(1) NOT NULL DEFAULT '1',
  `use_auto_joins_defense` tinyint(1) NOT NULL DEFAULT '1',
  `use_auto_joins_dungeon` tinyint(1) NOT NULL DEFAULT '1',
  `pending_leader_vote_id` int(11) NOT NULL,
  `min_apply_level` int(11) NOT NULL,
  `min_apply_honor` int(11) NOT NULL,
  `guild_battle_tactics_attack_order` int(11) NOT NULL DEFAULT '1',
  `guild_battle_tactics_attack_tactic` int(11) NOT NULL DEFAULT '10',
  `guild_battle_tactics_defense_order` int(11) NOT NULL DEFAULT '1',
  `guild_battle_tactics_defense_tactic` int(11) NOT NULL DEFAULT '10',
  `active_training_booster_id` varchar(40) NOT NULL,
  `ts_active_training_boost_expires` int(11) NOT NULL,
  `active_quest_booster_id` varchar(40) NOT NULL,
  `ts_active_quest_boost_expires` int(11) NOT NULL,
  `active_duel_booster_id` varchar(40) NOT NULL,
  `ts_active_duel_boost_expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_battle`
--

CREATE TABLE `guild_battle` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `battle_time` tinyint(1) NOT NULL,
  `ts_attack` int(11) NOT NULL,
  `guild_attacker_id` int(11) NOT NULL,
  `guild_defender_id` int(11) NOT NULL,
  `attacker_character_ids` text NOT NULL,
  `defender_character_ids` text NOT NULL,
  `guild_winner_id` int(11) NOT NULL,
  `attacker_character_profiles` text NOT NULL,
  `defender_character_profiles` text NOT NULL,
  `rounds` text NOT NULL,
  `attacker_rewards` text NOT NULL,
  `defender_rewards` text NOT NULL,
  `initiator_character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_battle_rewards`
--

CREATE TABLE `guild_battle_rewards` (
  `id` int(11) NOT NULL,
  `guild_battle_id` int(11) NOT NULL,
  `character_id` int(111) NOT NULL,
  `game_currency` int(11) NOT NULL,
  `item_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_dungeon`
--

CREATE TABLE `guild_dungeon` (
  `id` int(11) NOT NULL,
  `guild_id` int(11) NOT NULL,
  `npc_team_identifier` varchar(10) NOT NULL,
  `npc_team_character_profiles` text NOT NULL,
  `settings` text NOT NULL,
  `ts_unlock` int(11) NOT NULL,
  `locking_character_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_dungeon_battle`
--

CREATE TABLE `guild_dungeon_battle` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `battle_time` int(11) NOT NULL,
  `ts_attack` int(11) NOT NULL,
  `guild_id` int(11) NOT NULL,
  `ts_unlock` int(11) NOT NULL,
  `npc_team_identifier` varchar(64) NOT NULL,
  `settings` varchar(128) NOT NULL,
  `character_ids` varchar(32) NOT NULL,
  `joined_character_profiles` varchar(32) NOT NULL,
  `npc_team_character_profiles` varchar(128) NOT NULL,
  `rounds` varchar(128) NOT NULL,
  `rewards` varchar(128) NOT NULL,
  `initiator_character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_invites`
--

CREATE TABLE `guild_invites` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `guild_id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_logs`
--

CREATE TABLE `guild_logs` (
  `id` int(11) NOT NULL,
  `guild_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `character_name` varchar(32) NOT NULL,
  `type` int(11) NOT NULL,
  `value1` varchar(64) NOT NULL,
  `value2` varchar(64) NOT NULL,
  `value3` varchar(64) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guild_messages`
--

CREATE TABLE `guild_messages` (
  `id` int(11) NOT NULL,
  `guild_id` int(11) NOT NULL,
  `character_from_id` int(11) NOT NULL,
  `character_from_name` varchar(32) NOT NULL,
  `character_to_id` int(11) NOT NULL,
  `is_officer` tinyint(1) NOT NULL,
  `is_private` tinyint(1) NOT NULL,
  `message` text NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `hideout`
--

CREATE TABLE `hideout` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `hideout_points` int(11) NOT NULL,
  `current_level` int(11) NOT NULL,
  `idle_worker_count` int(11) NOT NULL,
  `max_worker_count` int(11) NOT NULL,
  `ts_time_worker_expires` int(11) NOT NULL,
  `current_resource_glue` int(11) NOT NULL,
  `max_resource_glue` int(11) NOT NULL,
  `current_resource_stone` int(11) NOT NULL,
  `max_resource_stone` int(11) NOT NULL,
  `current_attacker_units` int(11) NOT NULL,
  `max_attacker_units` int(11) NOT NULL,
  `current_defender_units` int(11) NOT NULL,
  `max_defender_units` int(11) NOT NULL,
  `current_opponent_id` int(11) NOT NULL,
  `current_opponent_chest_reward` int(11) NOT NULL,
  `ts_last_opponent_refresh` int(11) NOT NULL,
  `last_opponent_ids` int(11) NOT NULL,
  `active_battle_id` int(11) NOT NULL,
  `ts_last_lost_attack` int(11) NOT NULL,
  `current_worker_level` int(11) NOT NULL,
  `current_wall_level` int(11) NOT NULL,
  `current_barracks_level` int(11) NOT NULL,
  `max_barracks_level` int(11) NOT NULL,
  `current_gem_production_level` int(11) NOT NULL,
  `current_broker_level` int(11) NOT NULL,
  `current_robot_storage_level` int(11) NOT NULL,
  `room_slot_0_0` int(11) NOT NULL,
  `room_slot_0_1` int(11) NOT NULL,
  `room_slot_0_2` int(11) NOT NULL,
  `room_slot_0_3` int(11) NOT NULL,
  `room_slot_0_4` int(11) NOT NULL,
  `room_slot_1_0` int(11) NOT NULL,
  `room_slot_1_1` int(11) NOT NULL,
  `room_slot_1_2` int(11) NOT NULL,
  `room_slot_1_3` int(11) NOT NULL,
  `room_slot_1_4` int(11) NOT NULL,
  `room_slot_2_0` int(11) NOT NULL,
  `room_slot_2_1` int(11) NOT NULL,
  `room_slot_2_2` int(11) NOT NULL,
  `room_slot_2_3` int(11) NOT NULL,
  `room_slot_2_4` int(11) NOT NULL,
  `room_slot_3_0` int(11) NOT NULL,
  `room_slot_3_1` int(11) NOT NULL,
  `room_slot_3_2` int(11) NOT NULL,
  `room_slot_3_3` int(11) NOT NULL,
  `room_slot_3_4` int(11) NOT NULL,
  `room_slot_4_0` int(11) NOT NULL,
  `room_slot_4_1` int(11) NOT NULL,
  `room_slot_4_2` int(11) NOT NULL,
  `room_slot_4_3` int(11) NOT NULL,
  `room_slot_4_4` int(11) NOT NULL,
  `room_slot_5_0` int(11) NOT NULL,
  `room_slot_5_1` int(11) NOT NULL,
  `room_slot_5_2` int(11) NOT NULL,
  `room_slot_5_3` int(11) NOT NULL,
  `room_slot_5_4` int(11) NOT NULL,
  `room_slot_6_0` int(11) NOT NULL,
  `room_slot_6_1` int(11) NOT NULL,
  `room_slot_6_2` int(11) NOT NULL,
  `room_slot_6_3` int(11) NOT NULL,
  `room_slot_6_4` int(11) NOT NULL,
  `room_slot_7_0` int(11) NOT NULL,
  `room_slot_7_1` int(11) NOT NULL,
  `room_slot_7_2` int(11) NOT NULL,
  `room_slot_7_3` int(11) NOT NULL,
  `room_slot_7_4` int(11) NOT NULL,
  `room_slot_8_0` int(11) NOT NULL,
  `room_slot_8_1` int(11) NOT NULL,
  `room_slot_8_2` int(11) NOT NULL,
  `room_slot_8_3` int(11) NOT NULL,
  `room_slot_8_4` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `hideout_room`
--

CREATE TABLE `hideout_room` (
  `id` int(11) NOT NULL,
  `hideout_id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `identifier` varchar(64) NOT NULL,
  `status` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `current_resource_amount` int(11) NOT NULL,
  `max_resource_amount` int(11) NOT NULL,
  `ts_last_resource_change` int(11) NOT NULL,
  `ts_activity_end` int(11) NOT NULL,
  `current_generator_level` int(11) NOT NULL,
  `additional_value_1` int(11) NOT NULL,
  `additional_value_2` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `mask_item_id` int(11) NOT NULL,
  `cape_item_id` int(11) NOT NULL,
  `suit_item_id` int(11) NOT NULL,
  `belt_item_id` int(11) NOT NULL,
  `boots_item_id` int(11) NOT NULL,
  `weapon_item_id` int(11) NOT NULL,
  `gadget_item_id` int(11) NOT NULL,
  `missiles_item_id` int(11) NOT NULL,
  `missiles1_item_id` int(11) NOT NULL DEFAULT '-1',
  `missiles2_item_id` int(11) NOT NULL DEFAULT '-1',
  `missiles3_item_id` int(11) NOT NULL DEFAULT '-1',
  `missiles4_item_id` int(11) NOT NULL DEFAULT '-1',
  `sidekick_id` int(11) NOT NULL,
  `bag_item1_id` int(11) NOT NULL,
  `bag_item2_id` int(11) NOT NULL,
  `bag_item3_id` int(11) NOT NULL,
  `bag_item4_id` int(11) NOT NULL,
  `bag_item5_id` int(11) NOT NULL,
  `bag_item6_id` int(11) NOT NULL,
  `bag_item7_id` int(11) NOT NULL,
  `bag_item8_id` int(11) NOT NULL,
  `bag_item9_id` int(11) NOT NULL,
  `bag_item10_id` int(11) NOT NULL,
  `bag_item11_id` int(11) NOT NULL,
  `bag_item12_id` int(11) NOT NULL,
  `bag_item13_id` int(11) NOT NULL,
  `bag_item14_id` int(11) NOT NULL,
  `bag_item15_id` int(11) NOT NULL,
  `bag_item16_id` int(11) NOT NULL,
  `bag_item17_id` int(11) NOT NULL,
  `bag_item18_id` int(11) NOT NULL,
  `shop_item1_id` int(11) NOT NULL,
  `shop_item2_id` int(11) NOT NULL,
  `shop_item3_id` int(11) NOT NULL,
  `shop_item4_id` int(11) NOT NULL,
  `shop_item5_id` int(11) NOT NULL,
  `shop_item6_id` int(11) NOT NULL,
  `shop_item7_id` int(11) NOT NULL,
  `shop_item8_id` int(11) NOT NULL,
  `shop_item9_id` int(11) NOT NULL,
  `shop2_item1_id` int(11) NOT NULL,
  `shop2_item2_id` int(11) NOT NULL,
  `shop2_item3_id` int(11) NOT NULL,
  `shop2_item4_id` int(11) NOT NULL,
  `shop2_item5_id` int(11) NOT NULL,
  `shop2_item6_id` int(11) NOT NULL,
  `shop2_item7_id` int(11) NOT NULL,
  `shop2_item8_id` int(11) NOT NULL,
  `shop2_item9_id` int(11) NOT NULL,
  `item_set_data` varchar(64) NOT NULL,
  `sidekick_data` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `quality` tinyint(3) NOT NULL,
  `required_level` smallint(6) NOT NULL,
  `charges` tinyint(4) NOT NULL,
  `item_level` smallint(6) NOT NULL,
  `ts_availability_start` int(11) NOT NULL,
  `ts_availability_end` int(11) NOT NULL,
  `premium_item` tinyint(1) NOT NULL DEFAULT '0',
  `buy_price` mediumint(8) NOT NULL,
  `sell_price` mediumint(8) NOT NULL,
  `stat_stamina` mediumint(8) NOT NULL,
  `stat_strength` mediumint(8) NOT NULL,
  `stat_critical_rating` mediumint(8) NOT NULL,
  `stat_dodge_rating` mediumint(8) NOT NULL,
  `stat_weapon_damage` mediumint(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `league_fight`
--

CREATE TABLE `league_fight` (
  `id` int(11) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `battle_id` int(11) NOT NULL,
  `character_a_id` int(11) NOT NULL,
  `character_b_id` int(11) NOT NULL,
  `character_a_status` tinyint(1) NOT NULL DEFAULT '1',
  `character_b_status` tinyint(1) NOT NULL DEFAULT '1',
  `character_a_rewards` text NOT NULL,
  `character_b_rewards` text NOT NULL,
  `unread` enum('true','false','','') NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `character_from_id` int(11) NOT NULL,
  `character_to_ids` mediumtext NOT NULL,
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  `flag` varchar(64) NOT NULL,
  `flag_value` varchar(64) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `readed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `quests`
--

CREATE TABLE `quests` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `identifier` varchar(32) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `stage` tinyint(3) NOT NULL,
  `level` mediumint(8) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `duration_type` tinyint(3) NOT NULL DEFAULT '1',
  `duration_raw` smallint(6) NOT NULL,
  `duration` smallint(6) NOT NULL,
  `ts_complete` int(11) NOT NULL DEFAULT '0',
  `energy_cost` smallint(6) NOT NULL,
  `fight_difficulty` tinyint(3) NOT NULL DEFAULT '0',
  `fight_npc_identifier` varchar(60) NOT NULL,
  `fight_battle_id` int(11) NOT NULL DEFAULT '0',
  `used_resources` tinyint(3) NOT NULL DEFAULT '0',
  `rewards` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `sidekicks`
--

CREATE TABLE `sidekicks` (
  `id` int(11) NOT NULL,
  `identifier` varchar(32) NOT NULL,
  `character_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `quality` tinyint(1) NOT NULL DEFAULT '1',
  `level` int(11) NOT NULL DEFAULT '1',
  `xp` int(11) NOT NULL DEFAULT '0',
  `stat_base_stamina` mediumint(9) NOT NULL,
  `stat_base_strength` mediumint(9) NOT NULL,
  `stat_base_critical_rating` mediumint(9) NOT NULL,
  `stat_base_dodge_rating` mediumint(9) NOT NULL,
  `stat_stamina` mediumint(9) NOT NULL,
  `stat_strength` mediumint(9) NOT NULL,
  `stat_critical_rating` mediumint(9) NOT NULL,
  `stat_dodge_rating` mediumint(9) NOT NULL,
  `stage1_skill_id` mediumint(5) NOT NULL,
  `stage2_skill_id` mediumint(5) NOT NULL,
  `stage3_skill_id` mediumint(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `slotmachines`
--

CREATE TABLE `slotmachines` (
  `id` int(11) NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `slotmachine_reward_quality` tinyint(3) UNSIGNED NOT NULL,
  `slotmachine_slot1` tinyint(3) UNSIGNED NOT NULL,
  `slotmachine_slot2` tinyint(3) UNSIGNED NOT NULL,
  `slotmachine_slot3` tinyint(3) UNSIGNED NOT NULL,
  `slot` tinyint(1) NOT NULL DEFAULT '0',
  `won` tinyint(1) NOT NULL DEFAULT '0',
  `reward` text NOT NULL,
  `history` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `training`
--

CREATE TABLE `training` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `stat_type` tinyint(1) NOT NULL,
  `ts_creation` int(11) NOT NULL,
  `ts_complete` int(11) NOT NULL,
  `iterations` tinyint(1) NOT NULL,
  `used_resources` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `registration_source` varchar(64) NOT NULL DEFAULT 'ref=;subid=;lp=default_newCharacter_25M;',
  `registration_ip` varchar(45) DEFAULT NULL,
  `ts_creation` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_new` varchar(100) NOT NULL,
  `password_hash` varchar(40) NOT NULL,
  `last_login_ip` varchar(45) NOT NULL,
  `login_count` int(11) NOT NULL,
  `ts_last_login` int(11) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `session_id_cache1` varchar(32) NOT NULL,
  `session_id_cache2` varchar(32) NOT NULL,
  `session_id_cache3` varchar(32) NOT NULL,
  `session_id_cache4` varchar(32) NOT NULL,
  `session_id_cache5` varchar(32) NOT NULL,
  `premium_currency` int(11) NOT NULL DEFAULT '0',
  `locale` varchar(6) NOT NULL DEFAULT 'pl_PL',
  `network` varchar(10) NOT NULL,
  `geo_country_code` varchar(3) NOT NULL DEFAULT 'PL',
  `geo_country_code3` varchar(3) NOT NULL,
  `geo_country_name` varchar(16) NOT NULL DEFAULT 'Poland',
  `geo_continent_code` varchar(3) NOT NULL DEFAULT 'EU',
  `settings` varchar(250) NOT NULL DEFAULT '{"tos_sep2015":true}',
  `ts_banned` int(11) NOT NULL,
  `trusted` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `work`
--

CREATE TABLE `work` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `work_offer_id` varchar(64) NOT NULL,
  `status` smallint(3) NOT NULL,
  `duration` int(11) NOT NULL,
  `ts_complete` int(11) NOT NULL,
  `rewards` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `bank_inventory`
--
ALTER TABLE `bank_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indeksy dla tabeli `battle`
--
ALTER TABLE `battle`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `character`
--
ALTER TABLE `character`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild_id` (`guild_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `honor` (`honor`),
  ADD KEY `level` (`level`);

--
-- Indeksy dla tabeli `duel`
--
ALTER TABLE `duel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attackerduel` (`character_a_id`,`character_a_status`),
  ADD KEY `defenderduel` (`character_b_id`,`character_b_status`);

--
-- Indeksy dla tabeli `dungeons`
--
ALTER TABLE `dungeons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dungeons` (`character_id`,`status`);

--
-- Indeksy dla tabeli `dungeon_quests`
--
ALTER TABLE `dungeon_quests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dungeon_quests` (`character_id`,`status`);

--
-- Indeksy dla tabeli `guild`
--
ALTER TABLE `guild`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `honor` (`honor`);

--
-- Indeksy dla tabeli `guild_battle`
--
ALTER TABLE `guild_battle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attackerbattle` (`guild_attacker_id`,`status`),
  ADD KEY `defenderbattle` (`guild_defender_id`,`status`);

--
-- Indeksy dla tabeli `guild_battle_rewards`
--
ALTER TABLE `guild_battle_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attackerreward` (`character_id`,`type`),
  ADD KEY `battlereward` (`guild_battle_id`,`character_id`);

--
-- Indeksy dla tabeli `guild_dungeon`
--
ALTER TABLE `guild_dungeon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild_id` (`guild_id`);

--
-- Indeksy dla tabeli `guild_dungeon_battle`
--
ALTER TABLE `guild_dungeon_battle`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `guild_invites`
--
ALTER TABLE `guild_invites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `guild_id` (`guild_id`);

--
-- Indeksy dla tabeli `guild_logs`
--
ALTER TABLE `guild_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `log` (`guild_id`,`timestamp`,`character_id`);

--
-- Indeksy dla tabeli `guild_messages`
--
ALTER TABLE `guild_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tsmessage` (`guild_id`,`timestamp`,`character_from_id`,`character_to_id`,`is_officer`,`is_private`);

--
-- Indeksy dla tabeli `hideout`
--
ALTER TABLE `hideout`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `hideout_room`
--
ALTER TABLE `hideout_room`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indeksy dla tabeli `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indeksy dla tabeli `league_fight`
--
ALTER TABLE `league_fight`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attackerduel` (`character_a_id`,`character_a_status`),
  ADD KEY `defenderduel` (`character_b_id`,`character_b_status`);

--
-- Indeksy dla tabeli `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_from_id`);
ALTER TABLE `messages` ADD FULLTEXT KEY `character_to_id` (`character_to_ids`);

--
-- Indeksy dla tabeli `quests`
--
ALTER TABLE `quests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quests` (`character_id`,`status`);

--
-- Indeksy dla tabeli `sidekicks`
--
ALTER TABLE `sidekicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sidekicks` (`character_id`,`status`);

--
-- Indeksy dla tabeli `slotmachines`
--
ALTER TABLE `slotmachines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indeksy dla tabeli `training`
--
ALTER TABLE `training`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training` (`character_id`,`status`);

--
-- Indeksy dla tabeli `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login` (`email`,`password_hash`),
  ADD KEY `autologin` (`id`,`session_id`);

--
-- Indeksy dla tabeli `work`
--
ALTER TABLE `work`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work` (`character_id`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `bank_inventory`
--
ALTER TABLE `bank_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `battle`
--
ALTER TABLE `battle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `character`
--
ALTER TABLE `character`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `duel`
--
ALTER TABLE `duel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `dungeons`
--
ALTER TABLE `dungeons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `dungeon_quests`
--
ALTER TABLE `dungeon_quests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild`
--
ALTER TABLE `guild`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_battle`
--
ALTER TABLE `guild_battle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_battle_rewards`
--
ALTER TABLE `guild_battle_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_dungeon`
--
ALTER TABLE `guild_dungeon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_dungeon_battle`
--
ALTER TABLE `guild_dungeon_battle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_invites`
--
ALTER TABLE `guild_invites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_logs`
--
ALTER TABLE `guild_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `guild_messages`
--
ALTER TABLE `guild_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `hideout`
--
ALTER TABLE `hideout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `hideout_room`
--
ALTER TABLE `hideout_room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `league_fight`
--
ALTER TABLE `league_fight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `quests`
--
ALTER TABLE `quests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `sidekicks`
--
ALTER TABLE `sidekicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `slotmachines`
--
ALTER TABLE `slotmachines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `training`
--
ALTER TABLE `training`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `work`
--
ALTER TABLE `work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
