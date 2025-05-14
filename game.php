<?php
if(!defined('IN_INDEX')) exit();
//
define('IN_ENGINE',TRUE);
$cfg = include(__DIR__.'/server/config.php');
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $cfg['site']['title']; ?></title>
<link href="https://hz-static-2.akamaized.net/favicon.ico" rel="shortcut icon"/>
<link href="https://hz-static-2.akamaized.net/favicon.ico" rel="icon"/>
<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style/game.css" type="text/css">
<link rel="stylesheet" href="style/heroz.css" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>
<script src="js/swfobject.js"></script>
<script src="js/js.cookie.js"></script>
</head>
<style>
	.lang-selector {
		margin: 20px;
		position: absolute;
	}

	label {
		display: block;
		margin-bottom: 10px;
	}

	select {
		width: 200px;
		padding: 5px;
		border: 1px solid #ccc;
		border-radius: 5px;
	}
	
	.text-lang {
		font-weight: 300;
		color: black;
		font-size: 15px;
	}
</style>
<body>
<div class="main-container">
    <div class="lang-selector">
        <label for="sel-lang" class="text-lang">Select game language:</label>
        <select id="sel-lang" onchange="setLanguage(this.value);">
			<option disabled selected>Choose one</option>
			<option value="en_GB">English</option>
            <option value="cs_CZ">Čeština</option>
            <option value="de_DE">Deutsch</option>
            <option value="el_GR">Ελληνικά</option>
            <option value="es_ES">Español</option>
            <option value="fr_FR">Français</option>
            <option value="it_IT">Italiano</option>
            <option value="lt_LT">Lietuvių</option>
            <option value="pl_PL">Polski</option>
            <option value="pt_BR">Português (Brasil)</option>
            <option value="ro_RO">Română</option>
            <option value="ru_RU">Русский</option>
            <option value="tr_TR">Türkçe</option>
        </select>
    </div>
<div class="logo"></div>
<div class="main-content">
<div style="position:absolute;width:100px;height:40px;top:-120px;"></div>
<div class="container-header"></div>
<div class="main-content-wrapper">
<div id="flashContainer" style="width:1120px; height:755px; position:relative; left:0px; top:0px;">
<script type="text/javascript">
appCDNUrl = "<?php echo $cfg['site']['resource_cdn']; ?>";
appConfigPlatform = "standalone";
appConfigLocale = "en_GB";
appConfigServerId = "heroz";

var flashVars = {
applicationTitle: "<?php echo $cfg['site']['title'];?>",
urlPublic: "<?php echo $cfg['site']['public_url']; ?>",
urlRequestServer: "<?php echo $cfg['site']['request_url'].(isset($_GET['d'])?'?d':''); ?>",
urlSocketServer: "<?php echo $cfg['site']['socket_url'] ?>",
urlSwfMain: "<?php echo $cfg['site']['swf_main'] ?>",
urlSwfCharacter: "<?php echo $cfg['site']['swf_character'] ?>",
urlSwfUi: "<?php echo $cfg['site']['swf_ui'] ?>",
urlCDN: "<?php echo $cfg['site']['resource_cdn'] ?>",
userId: "0",
userSessionId: "0",
testMode: "<?php echo isset($_GET['d'])?'true':'false'; ?>",
debugRunTests: "<?php echo isset($_GET['d'])?'true':'false'; ?>",
registrationSource: "",
startupParams: "",
platform: "standalone",
ssoInfo: "",
uniqueId: "",
server_id: "<?php echo $cfg['site']['server_id'] ?>", //Original pl18
default_locale: "en_GB",
localeVersion: "",
blockRegistration: "false",
isFriendbarSupported: "false"
};

var params = {
menu: "true",
allowFullscreen: "false",
allowScriptAccess: "always",
bgcolor: "#6c5bb7"
};

var isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') != -1;
var isOpera = (navigator.userAgent.match(/Opera|OPR\//) ? true : false);
var isWin = navigator.appVersion.indexOf("Win") != -1;
var isMac = navigator.appVersion.indexOf("Mac") !=-1;
var isLinux = navigator.appVersion.indexOf("Linux") !=-1;

if (isChrome && (isWin || isMac)) {
params.wmode = "opaque";
flashVars["browser"] = "chrome";
}

var attributes = {
id:"swfClient"
};

window.setSessionCookie = function(){
console.log("DD setSessionCookie:", arguments);
Cookies.set('ssid', arguments[0]);
};

/*window.deleteSessionCookie = function(){
console.log("DD deleteSessionCookie:", arguments);
};*/

swfobject.embedSWF("<?php echo $cfg['site']['swf_main'] ?>", "altContent", "1120", "755", "19.0.0", "<?php echo $cfg['site']['swf_install'] ?>", flashVars, params, attributes);

setLanguage = (locale) => {
	Cookies.set('default_locale', locale);
	location.reload();
}
</script>
<div id="altContent">
<div id="content">
Wszystko prawie gotowe, jeszcze potrzeba zezwolić na załadowanie gry.</br>
Kliknij poniższe "Graj teraz !", a następnie w nowym oknie "Zezwalaj".
<a href="http://www.adobe.com/go/getflashplayer" style="text-decoration: none; ">Graj teraz !</a>
</div>
</div>
</div>
</div>
<div class="container-footer"></div>
</div>
</body>
</html>