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
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
        max-width: 300px;
	}

	label {
		display: block;
		margin-bottom: 10px;
	}

	select {
		width: 100%;
		padding: 10px 12px;
		border: 1px solid #ced4da;
		border-radius: 5px;
        background-color: white;
        font-size: 14px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c5bb7' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: calc(100% - 12px) center;
        transition: border-color 0.2s, box-shadow 0.2s;
	}
    
    select:focus {
        border-color: #6c5bb7;
        box-shadow: 0 0 0 0.2rem rgba(108, 91, 183, 0.25);
        outline: none;
    }
    
    select:hover {
        border-color: #6c5bb7;
    }
	
	.text-lang {
		font-weight: 500;
		color: #333;
		font-size: 16px;
        margin-bottom: 8px;
        display: block;
        font-family: 'Lato', sans-serif;
	}

	/* Shoutbox styles */
.shoutbox-container {
    position: fixed;
    top: 100px;
    right: 0;
    height: 500px;
    width: 350px;
    transition: transform 0.3s ease-in-out;
    z-index: 1000;
    font-family: 'Lato', sans-serif;
    display: flex;
}

.shoutbox-container.open {
    transform: translateX(0);
}

.shoutbox-container.closed {
    transform: translateX(calc(100% - 40px));
}

.shoutbox-toggle {
    width: 40px;
    height: 100px;
    background: linear-gradient(135deg, #6c5bb7 0%, #8a7ad1 100%);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px 0 0 8px;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
    position: absolute;
    left: 0;
    top: 50px;
}

.shoutbox-badge {
    background-color: #ff4757;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 10px;
    left: 8px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    display: none;
    border: 2px solid white;
    line-height: 1;
    padding: 0;
}

.shoutbox-panel {
    width: 100%;
    height: 100%;
    background-color: #f8f9fa;
    border-radius: 8px 0 0 8px;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-left: 1px solid #e0e0e0;
    margin-left: 40px;
}

.shoutbox-header {
    background: linear-gradient(135deg, #6c5bb7 0%, #8a7ad1 100%);
    color: white;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shoutbox-title {
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
}

.shoutbox-messages {
    flex: 1;
    overflow-y: auto;
    padding: 0;
    background-color: #ffffff;
}

.shoutbox-message {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f1f1;
    transition: background-color 0.2s;
    display: flex;
    flex-direction: column;
}

.shoutbox-message:hover {
    background-color: #f9f9f9;
}

.shoutbox-message.self {
    background-color: #f5f7ff;
}

.shoutbox-message-system {
    background-color: #fff8e6;
    text-align: center;
    font-size: 13px;
    padding: 8px 15px;
    color: #856404;
}

.shoutbox-message-error {
    background-color: #fff5f5;
    color: #dc3545;
}

.shoutbox-message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    align-items: center;
}

.shoutbox-message-username {
    font-weight: 600;
    color: #6c5bb7;
    font-size: 14px;
}

.shoutbox-message.self .shoutbox-message-username {
    color: #4a3d8f;
}

.shoutbox-message-time {
    font-size: 11px;
    color: #999;
}

.shoutbox-message-text {
    font-size: 14px;
    line-height: 1.4;
    color: #333;
    word-break: break-word;
}

.shoutbox-input-container {
    padding: 12px;
    background-color: #fff;
    border-top: 1px solid #e0e0e0;
    display: flex;
}

.shoutbox-input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
    font-family: 'Lato', sans-serif;
}

.shoutbox-input:focus {
    border-color: #6c5bb7;
}

.shoutbox-send {
    background: linear-gradient(135deg, #6c5bb7 0%, #8a7ad1 100%);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0 15px;
    margin-left: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
}

.shoutbox-send:hover {
    opacity: 0.9;
}

.shoutbox-send:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.shoutbox-messages::-webkit-scrollbar {
    width: 6px;
}

.shoutbox-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.shoutbox-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.shoutbox-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
<body>
<div class="main-container">
    <div class="lang-selector">
        <label for="sel-lang" class="text-lang">
            <i class="fa fa-globe" style="margin-right: 8px;"></i>Game Language
        </label>
        <select id="sel-lang" onchange="setLanguage(this.value);">
			<option disabled selected>Choose language...</option>
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
var isLinux = navigator.appVersion.indexOf("Linux") != -1;

if (isChrome && (isWin || isMac)) {
params.wmode = "opaque";
flashVars["browser"] = "chrome";
}

var attributes = {
id:"swfClient"
};

let sessionId = null;
window.setSessionCookie = function() {
	console.log("DD setSessionCookie:", arguments);
	Cookies.set('ssid', arguments[0]);
	sessionId = arguments[0];
};

window.deleteSessionCookie = function() {
	console.log("DD deleteSessionCookie:", arguments);
	Cookies.delete('ssid');
	sessionId = null;
};

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

<div class="shoutbox-container closed">
    <div class="shoutbox-toggle">
        <div class="shoutbox-badge">0</div>
        <i class="fa fa-comments"></i>
    </div>
    
    <div class="shoutbox-panel">
        <div class="shoutbox-header">
            <div class="shoutbox-title">Global Chat</div>
        </div>
        
        <div class="shoutbox-messages">
            <div class="shoutbox-message shoutbox-message-system">
                Welcome to the global chat! Be nice to each other.
            </div>
        </div>
        
        <div class="shoutbox-input-container">
            <input type="text" class="shoutbox-input" placeholder="Please log in to chat..." maxlength="200">
            <button class="shoutbox-send">Send</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let lastMessageId = 0;
    let messagePollingInterval = null;
    const MESSAGE_POLLING_INTERVAL = 3000;
    let isLoggedIn = false;

    function scrollToBottom() {
        const messagesContainer = $('.shoutbox-messages');
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    function isScrolledToBottom() {
        const messagesContainer = $('.shoutbox-messages')[0];
        return messagesContainer.scrollHeight - messagesContainer.clientHeight <= messagesContainer.scrollTop + 50;
    }

    function updateMessageBadge(newCount) {
        const badge = $('.shoutbox-badge');
        const currentCount = parseInt(badge.text() || 0);
        badge.text(currentCount + newCount).show();
        badge.css('transform', 'scale(1.2)');
        setTimeout(() => badge.css('transform', 'scale(1)'), 300);
    }

    function resetUI() {
        if (isLoggedIn) {
            $('.shoutbox-input')
                .prop('disabled', false)
                .attr('placeholder', 'Type your message...')
                .val('');
            $('.shoutbox-send').prop('disabled', false);
        } else {
            $('.shoutbox-input')
                .prop('disabled', true)
                .attr('placeholder', 'Please log in to chat...');
            $('.shoutbox-send').prop('disabled', true);
        }
    }

    $('.shoutbox-toggle').on('click', function() {
        const shoutboxContainer = $('.shoutbox-container');
        shoutboxContainer.toggleClass('open closed');
        
        if(shoutboxContainer.hasClass('open')) {
            $('.shoutbox-badge').hide().text('0');
            setTimeout(scrollToBottom, 300);
        }
    });

    function addShoutboxMessage(msgData) {
        if ($(`[data-message-id="${msgData.id}"]`).length) return;

        let messageClass = msgData.isSelf ? 'self' : '';
        let messageType = msgData.system ? 'shoutbox-message-system' : 
                        msgData.error ? 'shoutbox-message-error' : '';
        
        const levelDisplay = msgData.level ? ` <span style="color: #ffa500; font-size: 11px;">[Lvl ${msgData.level}]</span>` : '';
        
        const messageHtml = `
            <div class="shoutbox-message ${messageClass} ${messageType}" data-message-id="${msgData.id}">
                <div class="shoutbox-message-header">
                    <span class="shoutbox-message-username">${msgData.username}${levelDisplay}</span>
                    <span class="shoutbox-message-time">${msgData.time}</span>
                </div>
                <div class="shoutbox-message-text">${msgData.message}</div>
            </div>
        `;

        $('.shoutbox-messages').append(messageHtml);
        
        const wasAtBottom = isScrolledToBottom();
        if (wasAtBottom || $('.shoutbox-container').hasClass('open')) {
            scrollToBottom();
        }
    }

    function showShoutboxError(errorMsg) {
        const errorHtml = `
            <div class="shoutbox-message shoutbox-message-error">
                <div class="shoutbox-message-text">${errorMsg}</div>
            </div>
        `;

        $('.shoutbox-messages').append(errorHtml);
        scrollToBottom();

        setTimeout(function() {
            $('.shoutbox-message-error').first().fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    function sendMessage() {
        if (!isLoggedIn) {
            showShoutboxError("You need to be logged in to send messages");
            return;
        }

        const messageInput = $('.shoutbox-input');
        const message = messageInput.val().trim();

        if (!message || message.length > 200) return;

        messageInput.prop('disabled', true);
        $('.shoutbox-send').prop('disabled', true);

        const tempId = 'temp-' + Date.now();
        const tempMessage = {
            id: tempId,
            user_id: 0,
            username: character ? character.name : 'You',
            level: character ? character.level : null,
            message: message,
            time: new Date().toTimeString().slice(0, 5),
            isSelf: true,
            system: false,
            timestamp: new Date().toISOString()
        };
        addShoutboxMessage(tempMessage);
        
        $.ajax({
            url: 'shoutbox.php',
            type: 'POST',
            data: {
                session_id: sessionId,
                action: 'post',
                message: message
            },
            dataType: 'json',
            success: function(response) {
                $(`[data-message-id="${tempId}"]`).remove();
                
                if(response.error) {
                    showShoutboxError(response.error);
                } else if(response.success && response.message) {
                    messageInput.val('');
                    addShoutboxMessage(response.message);
                    lastMessageId = Math.max(lastMessageId, response.message.id);
                    scrollToBottom();
                }
            },
            error: function() {
                $(`[data-message-id="${tempId}"]`).remove();
                showShoutboxError("Failed to send message. Please try again.");
            },
            complete: function() {
                resetUI();
                $('.shoutbox-input').focus();
            }
        });
    }

    function fetchMessages() {
        if (!isLoggedIn) return;

        $.ajax({
            url: 'shoutbox.php',
            type: 'POST',
            data: {
                session_id: sessionId,
                action: 'get',
                last_id: lastMessageId
            },
            dataType: 'json',
            success: function(response) {
                if(response.error) {
                    showShoutboxError(response.error);
                } else if(response.messages && response.messages.length) {
                    const newMessages = response.messages.filter(msg => msg.id > lastMessageId);
                    if (newMessages.length) {
                        newMessages.forEach(msg => {
                            addShoutboxMessage(msg);
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        
                        if($('.shoutbox-container').hasClass('closed') && newMessages.length) {
                            updateMessageBadge(newMessages.length);
                        }
                    }
                }
            }
        });
    }

    let character = null;

    function startPolling() {
        if(!messagePollingInterval) {
            $.ajax({
                url: 'shoutbox.php',
                type: 'POST',
                data: {
                    session_id: sessionId,
                    action: 'get_character'
                },
                dataType: 'json',
                success: function(response) {
                    if(response.character) {
                        character = response.character;
                    }
                    
                    fetchMessages();
                    messagePollingInterval = setInterval(fetchMessages, MESSAGE_POLLING_INTERVAL);
                }
            });
        }
    }

    function checkSession() {
        if(typeof sessionId !== 'undefined' && sessionId) {
            isLoggedIn = true;
            startPolling();
        } else {
            isLoggedIn = false;
            setTimeout(checkSession, 1000);
        }
        resetUI();
    }

    $('.shoutbox-send').on('click', sendMessage);
    
    $('.shoutbox-input').on('keypress', function(e) {
        if(e.which === 13) sendMessage();
    });
    
    checkSession();
    setTimeout(scrollToBottom, 300);
});
</script>

</body>
</html>