<?php
$uploadFolder = "uploads";
$uploadFolderAccess = true;

function checkFile($filePath, $fileHex) {
    if (!file_exists($filePath)) {
        return false;
    }
    $fileContent = bin2hex(file_get_contents($filePath));
    $fileMD5 = md5($fileContent);
    $hexMD5 = md5($fileHex);
    if ($fileMD5 == $hexMD5) {
        return true;
    }
    return false;
}

function getFilePath($file, $fullPath = true) {
    global $uploadFolder;
    if (!isset($file)) {
        return;
    }
    if (!isset($file->folderID) || !isset($file->fileId)) {
        return;
    }
    
    if($fullPath = true){
        return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . $uploadFolder . "/" . $file->folderID . "/" . $file->fileId;
    }
    return $uploadFolder . "/" . $file->folderID . "/" . $file->fileId;
}

function getFileHex($file) {
    global $uploadFolder;
    return bin2hex(file_get_contents(getFilePath($file)));
}

function deleteFile($file){
    global $uploadFolder;
    if (!isset($file)) {
        return;
    }
    if (!isset($file->folderID) || !isset($file->fileId)) {
        return;
    }

    $folderPath = $uploadFolder . "/" . $file->folderID;
    unlink($folderPath . "/" . $file->fileId);
    $checkFolder = count(array_diff(scandir($folderPath), array('.', '..', '.htaccess'))) == 0;
    if ($checkFolder) {
        unlink($folderPath . "/.htaccess");
        rmdir($folderPath);
        exit;
    }
}

function deleteAllFiles($file) {
    global $uploadFolder;
    if (!isset($file)) {
        return;
    }
    if (!isset($file->folderID) || !isset($file->fileId)) {
        return;
    }

    $folderPath = $uploadFolder . "/" . $file->folderID;
    if (!is_dir($folderPath)) return;

    $dirHandle = opendir($folderPath);
    while (($file = readdir($dirHandle)) !== false) {
        if ($file != '.' && $file != '..') {
            $filePath = $folderPath . '/' . $file;
            if (is_dir($filePath)) {
                deleteAllFiles($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    closedir($dirHandle);
    rmdir($folderPath);
}

function getSize($val) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = intval($val);
    switch ($last) {
        case 't':
            $val*= 1024;
        case 'g':
            $val*= 1024;
        case 'm':
            $val*= 1024;
        case 'k':
            $val*= 1024;
    }
    $bytes = max($val, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes/= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileData = json_decode(file_get_contents('php://input'), true);
    if (!isset($fileData) || empty($fileData) || is_null($fileData)) {
        if (!empty($_FILES)) {
            $file = current($_FILES);
            echo bin2hex(file_get_contents($file['tmp_name']));
            unlink($file['tmp_name']);
            exit;
        }
    } else {
        if (!file_exists($uploadFolder)) {
            mkdir($uploadFolder, 0755, true);
        }
        $folderPath = $uploadFolder . "/" . $fileData["folderID"];
        $fileName = $fileData["fileId"];
        if ($fileData["method"] == "add") {
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            if($uploadFolderAccess == false){
                file_put_contents($folderPath . "/.htaccess", "Deny from all");
            }
            
            file_put_contents($folderPath . "/" . $fileName, hex2bin($fileData["fileHex"]));
            chmod($folderPath . "/" . $fileName, 0755);
            echo (checkFile($folderPath . "/" . $fileName, $fileData["fileHex"])) ? "true" : "false";
            exit;
        }
        if ($fileData["method"] == "delete") {
            if (!file_exists($folderPath)) {
                exit;
            }
            unlink($folderPath . "/" . $fileName);
            $checkFolder = count(array_diff(scandir($folderPath), array('.', '..', '.htaccess'))) == 0;
            if ($checkFolder) {
                unlink($folderPath . "/.htaccess");
                rmdir($folderPath);
                exit;
            }
        }
    }
}