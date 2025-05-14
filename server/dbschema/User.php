<?php
namespace Schema;

use Srv\Core;
use Srv\Record;
use JsonSerializable;

class User extends Record implements JsonSerializable{
    protected static $_TABLE = 'user';
    
	public $ref = '';
	public $subid = 0;
	public $accepts_promotion = false;
	public $network_user_id = '';
	public $pmt_player_id = 0;
	public $password_salt = '';
	public $status = 1;
	public $user_device_lookup_id = 0;
	public $ts_first_payment = 0;
	public $ts_last_payment = 0;
	public $payment_count = 0;
	
    public function hasSetting($key){
        $settings = json_decode($this->settings, TRUE);
        if(isset($settings[$key]) && $settings[$key])
            return TRUE;
        return FALSE;
    }
    
    public function setSetting($key, $value=true){
        $settings = json_decode($this->settings, TRUE);
        $settings[$key] = $value;
        $this->settings = json_encode($settings);
    }
    
    public function jsonSerialize(){
        if(in_array(Core::$ACTUAL_ACTION, Core::$REQ_NOLOGIN)){
            $data = $this->getData();
            array_unset($data, ['registration_ip','last_login_ip','password_hash']);
            return $data;
        }else
            return ['id'=>$this->id,'premium_currency'=>$this->premium_currency];
    }
    
    protected static $_FIELDS = [
		"id" => 0,
		"registration_ip" => "",
		"registration_source" => "ref=;subid=;extra=;lp=default_newCharacter_25M;",
		"ts_creation" => 0,
		"network" => "",
		"confirmed" => false,
		"email" => "",
		"email_new" => "",
		'password_hash'=>'',
		"last_login_ip" => "",
		"login_count" => 0,
		"ts_last_login" => 0,
		"session_id" => "",
		"session_id_cache1" => "",
		"session_id_cache2" => "",
		"session_id_cache3" => "",
		"session_id_cache4" => "",
		"session_id_cache5" => "",
		"locale" => "pl_PL",
		"premium_currency" => 0,
		"geo_country_code" => "PL",
		"geo_country_code3" => "",
		"geo_country_name" => "Poland",
		"geo_continent_code" => "EU",
		"settings" => "[\"tos_sep2015\" =>true]",
		"ts_banned" => 0,
		"trusted" => false
    ];
}