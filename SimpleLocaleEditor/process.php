<?php
require_once 'inc/vendor/autoload.php';

// Compress to locale from json
function compressAMF3($outputFile, $data, $inputFile = null) {
    if ($inputFile) {
        $data = json_decode(file_get_contents($inputFile), true);
    }
    $amfOutputStream = new SabreAMF_OutputStream();
    $amfSerializer = new SabreAMF_AMF3_Serializer($amfOutputStream);
    $amfSerializer->writeAMFData($data);
    $compressedData = gzcompress($amfOutputStream->getRawData());
    file_put_contents($outputFile, $compressedData);
}

// Decompress locale to json
function decompressAMF3($inputFile, $outputFile = null, $ret = false) {
    $compressedData = file_get_contents($inputFile);
    $decompressedData = gzuncompress($compressedData);
    $amfInputStream = new SabreAMF_InputStream($decompressedData);
    $amfDeserializer = new SabreAMF_AMF3_Deserializer($amfInputStream);
    $jsonData = json_encode($amfDeserializer->readAMFData(), JSON_PRETTY_PRINT);
    if ($ret) {
        return $jsonData;
    }
    file_put_contents($outputFile, $jsonData);
}

// Decompress old version hz (or html5 ver) to json
function decompressOldLocale($inputFile, $outputFile = null, $ret = false) {
    $compressedData = file_get_contents($inputFile);
    $decompressedData = gzuncompress($compressedData);
    $jsonData = json_encode(json_decode($decompressedData, true), JSON_PRETTY_PRINT);
    if ($ret) {
        return $jsonData;
    }
    file_put_contents($outputFile, $jsonData);
}

// Compress old version hz (or html5 ver) to json
function compressOldLocale($inputFile, $outputFile = null, $ret = false) {
    $jsonData = file_get_contents($inputFile);
    $compressedData = gzcompress($jsonData);
    if ($ret) {
        return $compressedData;
    }
    file_put_contents($outputFile, $compressedData);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        $outputFile = $uploadDir . 'processed.data';
        
        if ($_POST['type'] === 'gzip' && $_POST['action'] === 'compress') {
            compressOldLocale($uploadFile, $outputFile);
        } elseif ($_POST['type'] === 'gzip' && $_POST['action'] === 'decompress') {
            decompressOldLocale($uploadFile, $outputFile);
        } elseif ($_POST['type'] === 'gzip_amf3' && $_POST['action'] === 'compress') {
            compressAMF3($outputFile, null, $uploadFile);
        } elseif ($_POST['type'] === 'gzip_amf3' && $_POST['action'] === 'decompress') {
            decompressAMF3($uploadFile, $outputFile);
        }

        // Provide link to download
        echo "<a href='" . $outputFile . "' download>Click here to download processed file</a>";
    } else {
        echo "File upload failed!";
    }
}

?>
