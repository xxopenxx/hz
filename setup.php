<?php
set_time_limit(300);
ini_set('memory_limit', '-1');


$error = '';
$success = '';

if(file_exists('lock')) {
	die('you can use setup file one time');
}

if (isset($_POST['submit'])) {
    if (!($pdo = checkMySQLConnection($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database']))) {
        $error = 'Failed to connect to the MySQL database. Please check your credentials.';
    } else {
        $downloadResult = downloadZIP();
        if (!$downloadResult['status']) {
            $error = $downloadResult['message'];
        } else {
			$extractResult = extractZIP();
			if (!$extractResult['status']) {
				$error = $extractResult['message'];
			} else {
				$x = importDatabase($pdo);
				if(!$x) {
					$error = 'Can\'t upload SQL file to DB';
				} else {
					editConfigFile($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database'], $_POST['defaultLocale'], $_POST['serverName'], $_POST['domain']);
					
					$success = 'Installation completed successfully! (do not remove lock file!)';
					file_put_contents('lock', 'do not remove it!');
				}
			}
		}
    }
}

function checkMySQLConnection($hostname, $username, $password, $database) {
    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return false;
    }
}

function downloadZIP() {
    $remoteFile = 'https://hz-server.7m.pl/hz_dl.zip';
    $localFile = 'hz_dl.zip';
    $bufferSize = 4096;

    try {
        $input = fopen($remoteFile, 'rb');
        if ($input === false) {
            throw new Exception('Failed to open remote file for reading.');
        }

        $output = fopen($localFile, 'wb');
        if ($output === false) {
            fclose($input);
            throw new Exception('Failed to open local file for writing.');
        }

        while (!feof($input)) {
            $chunk = fread($input, $bufferSize);
            fwrite($output, $chunk);
        }

        fclose($input);
        fclose($output);

        return ['status' => true, 'message' => 'File downloaded successfully'];

    } catch (Exception $e) {
        return ['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
    }
}

function extractZIP() {
    try {
        $zip = new ZipArchive;
        $res = $zip->open('hz_dl.zip');
        
        if ($res === TRUE) {
            $zip->extractTo(dirname(__FILE__) . '/');
            $zip->close();
            return ['status' => true, 'message' => 'Files extracted successfully'];
        } else {
            throw new Exception('Failed to extract ZIP file');
        }

    } catch (Exception $e) {
        return ['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
    }
}

function editConfigFile($hostname, $username, $password, $database, $defaultLocale, $serverName, $domain) {
	define('IN_ENGINE', true);
	$x = include_once('server/config.php');

	$x['database']['hostname'] = $hostname;
	$x['database']['username'] = $username;
	$x['database']['password'] = $password;
	$x['database']['database'] = $database;
	$x['site']['default_locale'] = $defaultLocale;
	$x['site']['server_domain'] = $domain;
	$x['site']['title'] = $serverName;
	$x['site']['server_name'] = $serverName;

	$jsonString = json_encode($x, JSON_PRETTY_PRINT);
	$jsonString = str_replace(['{', '}', '":', '\/'], ['[', ']', '"=>', '/'], $jsonString);
	
	$config = "<?php".PHP_EOL;
	$config .= "if(!defined('IN_ENGINE')) exit(http_response_code(404));".PHP_EOL;
	$config .= "return ".$jsonString.PHP_EOL;
	$config .= "?>";
	
    file_put_contents('server/config.php', $config);
}

function importDatabase($pdo) {
    try {
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $pdo->beginTransaction();

        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
        }
        
        $sql = file_get_contents('DATABASE.sql');
        $pdo->exec($sql);
        
        $pdo->commit();

        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 50px;
    }
    input[type="text"], input[type="password"], select {
        padding: 10px;
        width: 100%;
        margin-bottom: 10px;
        border: 1px solid #ddd;
    }
    input[type="submit"] {
        padding: 10px 15px;
        background-color: #007BFF;
        border: none;
        color: white;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: #0056b3;
    }
    .message {
        padding: 10px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    .error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    .success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
</style>

<div class="container mt-5">
    <?php
    if (!empty($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }
    if (!empty($success)) {
        echo '<div class="alert alert-success">' . $success . '</div>';
    }
    ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="hostname">Hostname:</label>
            <input type="text" class="form-control" id="hostname" name="hostname" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="database">Database:</label>
            <input type="text" class="form-control" id="database" name="database" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="serverName">Server Name:</label>
            <input type="text" class="form-control" id="serverName" name="serverName" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="domain">Domain:</label>
            <input type="text" class="form-control" id="domain" name="domain" value="<?=$_SERVER['HTTP_HOST']?>" required autocomplete="off">
        </div>
		<div class="form-group">
			<label for="defaultLocale">Default game language:</label>
			<select class="form-control" id="defaultLocale" name="defaultLocale" required>
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
        <div class="form-group">
            <input type="submit" class="btn btn-primary" name="submit" value="Install">
        </div>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>