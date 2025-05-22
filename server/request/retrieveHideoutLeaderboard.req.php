<?php
namespace Request;

use Srv\Core;
use Srv\DB;
use PDO;

class retrieveHideoutLeaderboard {
    
    public function __request($player) {
        $sortByLVL = getField('points_sort', FIELD_BOOL) == 'true';
        $sort_rank = intval(getField('rank', FIELD_NUM, FALSE));
        
        $max_ch = DB::table('character')->select()->count();
        if ($sort_rank < 0 || $sort_rank > $max_ch) {
            return Core::setError('errRetrieveLeaderboardInvalidRank');
        }
        
        $sortBy = $sortByLVL ? 'hideout_points' : 'current_level';
        if ($sort_rank) {
            $centerRank = $sort_rank;
        } else {
            $where = "`hi`.`character_id`={$player->character->id}";
            DB::sql("set @rank = 0");
            $centerRank = DB::sql("
                SELECT `hi`.`rank` FROM
                    (SELECT @rank := @rank+1 as 'rank', `id`, `character_id` FROM `hideout` ORDER BY `{$sortBy}` DESC) as hi
                WHERE {$where};
            ")->fetchColumn();
            $centerRank = intval($centerRank);
        }
        

        $time = time();
        $columns = "(@rank := @rank+1) as 'rank', hi.`id`, ch.`id` as 'character_id', ch.`name`, ch.`guild_id`, ch.`gender`, hi.`current_level` as 'hideout_level', hi.`hideout_points` as 'hideout_points', null as 'level', null as 'league_points', IF(({$time}-(`ch`.`ts_last_action`))<60,1,2) as `online_status`, null as 'league_group_id', null as 'honor', null as 'value', null as 'attacked_count',null as 'max_attack_count',
        COALESCE(g.`name`,'') as 'guild_name', g.`emblem_background_shape`, g.`emblem_background_color`, g.`emblem_background_border_color`, g.`emblem_icon_shape`, g.`emblem_icon_color`, g.`emblem_icon_size`,COALESCE(u.`locale`,'') as 'locale'";
        
        DB::sql("set @rank = 0");
        $lb = DB::sql("
            SELECT `h`.* FROM
                (SELECT {$columns} FROM `hideout` hi LEFT JOIN `character` ch ON `ch`.`id`=`hi`.`character_id` LEFT JOIN `user` u ON `u`.`id`=`ch`.`user_id` LEFT JOIN `guild` g ON `g`.`id`=`ch`.`guild_id` ORDER BY `{$sortBy}` DESC) as h
            WHERE `h`.`rank` > {$centerRank}-49 LIMIT 50;
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($lb as &$l) {
            foreach ($l as $k => &$c) {
                if (is_numeric($c)) $c = intval($c);
            }
        }
        
        Core::req()->data = array(
            'character' => [],
            'leaderboard_hideouts' => $lb,
            'max_hideouts' => $max_ch,
            'centered_rank' => $centerRank
        );
    }
}