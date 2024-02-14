<?php
$uploadFolder = "uploads";
$maxFileSize = 10 * 1024 * 1024 * 100; //1GB
$allowedExtensions = array(
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/gif" => "gif",
    "image/bmp" => "bmp",
    "image/webp" => "webp",
    "image/tiff" => "tiff",
    "image/svg+xml" => "svg",
    "image/x-icon" => "ico",
    "application/zip" => "zip",
    "application/x-rar-compressed" => "rar",
    "application/x-msdownload" => "exe",
    "application/octet-stream" => "bin or dll",
);

$response = ["status" => "", "message" => ""];

function returnStatus($code) {
    global $response;
    http_response_code($code);
    echo json_encode($response);
    exit();
}

function error($message, $code) {
    global $response;
    $response["status"] = "error";
    $response["message"] = $message;
    returnStatus($code);
}

function successful($message, $data, $code) {
    global $response;
    $response["status"] = "successful";
    $response["message"] = $message;
    $response["data"] = $data;
    returnStatus($code);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES) && !empty($_FILES)) {
        $fieldName = array_keys($_FILES)[0];
        $file = $_FILES[$fieldName];

        if(!is_array($file["name"])){
            foreach ($file as $key => $value) {
                $file[$key] = [$value];
            }   
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMimeType = finfo_file($finfo, $file["tmp_name"][0]);
        finfo_close($finfo);

        if (!array_key_exists($fileMimeType, $allowedExtensions)) {
            error("You cannot upload this file extension", 403);
        }
        
        $fileExtension = $allowedExtensions[$fileMimeType];
        $fileName = base64_encode($file["name"][0]);
        $uniqueFileName = bin2hex(random_bytes(8));
        $filePath = $uploadFolder . "/" . $fileName . "." .$uniqueFileName;

        $fileSize = filesize($file["tmp_name"][0]);
        if ($fileSize > $maxFileSize) {
            error("File size exceeds maximum limit", 400);
        }

        if ($file["size"][0] === 0 || $file["error"][0] !== UPLOAD_ERR_OK) {
            error("File upload failed or empty file uploaded", 400);
        }
        
        while (file_exists($filePath)) {
            $uniqueFileName = bin2hex(random_bytes(8));
            $filePath = $uploadFolder . "/" . $fileName . "." .$uniqueFileName;
        }

        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0755, true);
        }

        if (!is_writable($uploadFolder)) {
            error("Upload directory is not writable", 500);
        }

        if (move_uploaded_file($file['tmp_name'][0], $filePath)) {
            chmod($filePath, 0444);
            successful("File uploaded", $filePath, 200);
        } else {
            error("Failed to upload file", 500);
        }
    }
    else {
        $fileData = json_decode(file_get_contents('php://input'), true);
        if(isset($fileData["method"]) && isset($fileData["fileName"]) && isset($fileData["fileSize"]) && isset($fileData["fileType"])){
            if ($fileData["method"] == "delete" && is_dir($uploadFolder)) {
                $files = scandir($uploadFolder);
                if(isset($files) && is_array($files) && count($files) > 0){
                    $fileNames = array();
                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..') continue;
                        $fileNames[] = array(base64_decode(pathinfo($file, PATHINFO_FILENAME)), $file);
                    }
                    
                    if (empty($fileNames)) {
                        error("The file to be deleted was not found", 404);
                    }
    
                    $file = null;
                    for($i = 0; $i < count((array)$fileNames); $i++){
                        if($fileNames[$i][0] == $fileData["fileName"] && filesize($uploadFolder . "/" . $fileNames[$i][1]) == $fileData["fileSize"]){
                            $file = $fileNames[$i][1];
                            break;
                        }
                    }
    
                    if ($file === null) {
                        error("The file to be deleted was not found", 404);
                    }
    
                    $filePath = $uploadFolder . "/" . $file;
                    if(file_exists($filePath)){
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $fileMimeType = finfo_file($finfo, $filePath);
                        finfo_close($finfo);
    
                        if($fileMimeType == $fileData["fileType"]){
                            chmod($uploadFolder . "/" . $file, 0777);
                            if (unlink($uploadFolder . "/" . $file)) {
                                successful("File deleted", null, 200);
                            } else {
                                error("Failed to delete the file", 500);
                            }
                        } else {
                            error("File type mismatch", 400);
                        }
                    } else {
                        error("File not found", 404);
                    }
                } else {
                    error("Failed to get the file list", 500);
                }
            } else {
                error("Invalid request method or upload directory not found", 400);
            }
        }
    }
}