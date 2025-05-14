<?php
date_default_timezone_set('Europe/Warsaw');

define('IN_INDEX',TRUE);
//
$start = '2019-08-23 18:00';

if(time() < strtotime($start)){
	include("countdown.php");
} else {
	include("game.php");
}