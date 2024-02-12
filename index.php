<?php
  require_once("filepond.php");
  $maxUploadSize = getSize(ini_get('upload_max_filesize'));
  $maxPostSize = getSize(ini_get('post_max_size'));
  
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
    if(!isset($_POST["filepond"])) {
      echo "Error: No data found!";
      exit;
    }
  
    $filepondData = json_decode($_POST["filepond"]);
    if(!isset($filepondData) || empty($filepondData) || is_null($filepondData) || !is_array($filepondData)){
      echo "Error: File not uploaded";
      exit;
    }
  
    foreach ($filepondData as $file) {
      echo "File Name: " . $file->fileName . "<br>";
      echo "File Size: " . $file->fileSize . "<br>";
      echo "File Type: " . $file->fileType . "<br>";
      echo "File Extension: " . $file->fileExtension . "<br>";
      echo "File Id: " . $file->fileId . "<br>";
      echo "Folder Id: " . $file->folderID . "<br>";
      echo "Field Name: " . $file->fieldName . "<br>";
      echo "File Path: " . getFilePath($file) . "<br>";
      //echo "File Hex: " . getFileHex($file) . "<br>";
      echo "<hr>";
      //deleteAllFiles($file);
      //deleteFile($file);
    }
    exit;
  } 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <style>
      .form{
        margin-top: 50px;
      }

      .form-group {
        width: 70%;
        margin: auto;
      }

      .info{
        margin: auto;
        text-align: center;
        font-size: 25px;
      }

      label {
        display: block;
        font-weight: bold;
      }

      input[type="submit"] {
        width: 100%;
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
      }

      input[type="submit"]:hover {
        background-color: #45a049;
      }
    </style>
  </head>
  <body>
    <form id="myform" method="post" class="form">
    <label class="info">PHP Max Upload Size: <?php echo $maxUploadSize?></label>
    <label class="info">PHP Max Post Size: <?php echo $maxPostSize?></label>
      <div class="form-group">
        <label for="images">Images:</label>
        <input type="file" class="filepond" name="images" data-max-file-size="10MB" accept="image/png, image/jpeg" multiple>
      </div>
      <div class="form-group">
        <label for="file">File:</label>
        <input type="file" class="filepond" name="file">
      </div>
      <div class="form-group">
        <input type="submit" name="upload" value="Upload">
      </div>
    </form>
    <script>
      var formId = "myform";
      document.addEventListener('DOMContentLoaded', function() {
          document.getElementById(formId).addEventListener('submit', function(event) {
              const newInput = Object.assign(document.createElement("input"), {
                  name: "filepond",
                  type: "text",
                  value: JSON.stringify(files)
              });
              newInput.style.display = "none";
              document.getElementById(formId).appendChild(newInput);
          });
      });
    </script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src='https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js'></script>
    <script src='https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js'></script>
    <script src='https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js'></script>
    <script src='https://unpkg.com/filepond/dist/filepond.min.js'></script>
    <script src='filepond-init.js'></script>
  </body>
</html>