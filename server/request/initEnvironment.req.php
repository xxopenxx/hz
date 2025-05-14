<?php
namespace Request;

use Srv\Req;
use Srv\Cache;
use Srv\Config;
use Cls\GameSettings;

class initEnvironment{
    
    public function __request(){
        $configFile = SERVER_DIR.'/config.php';
        if(Cache::exists('initEnvironmentData') && Cache::exists('initEnvironmentHash') && Cache::getData('initEnvironmentHash') == sha1_file($configFile)) {
            $data = Cache::getFile('initEnvironmentData');
		} else{
            Cache::storeData('initEnvironmentHash', sha1_file($configFile));
            #$data = '"extendedConfig":'.json_encode(GameSettings::returnExtendedConfig(), JSON_NUMERIC_CHECK);
			$data = '"extendedConfig":'.json_encode(GameSettings::returnExtendedConfig(), JSON_NUMERIC_CHECK).','.'"textures":'.json_encode(GameSettings::returnTextures(), JSON_NUMERIC_CHECK);
            Cache::storeToFile('initEnvironmentData', $data);
        }
		//$data = '"extendedConfig":'.json_encode(GameSettings::returnExtendedConfig(), JSON_NUMERIC_CHECK).','.'"textures":'.json_encode(GameSettings::returnTextures(), JSON_NUMERIC_CHECK);
       
			$data_array = json_decode('{'.$data.'}', true);
			$data_array['extendedConfig']['default_locale'] = GameSettings::getLocaleFromCookie();
			$data = substr(json_encode($data_array), 1, -1);
	   Req::rawData($data);
    }
    
}