<?php
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
    if(!isset($_POST["images"]) || empty($_POST["images"])){
      echo "No image selected";
      exit;
    }
  
    if(!isset($_POST["file"]) || empty($_POST["file"])){
      echo "No file selected";
      exit;
    }
  
    $images = is_array($_POST["images"]) ? array_map(function($item){ return json_decode($item, true); }, $_POST["images"]) : json_decode($_POST["images"], true);
    $file = is_array($_POST["file"]) ? array_map(function($item){ return json_decode($item, true); }, $_POST["file"]) : json_decode($_POST["file"], true);
  
    if(isset($images[0]) && array_reduce($images, function($carry, $item){ return $carry && $item["status"] == "successful"; }, true) === false){
      echo "Error: " . $images[0]["message"];
      exit;
    }
  
    if(isset($file[0]) && array_reduce($file, function($carry, $item){ return $carry && $item["status"] == "successful"; }, true) === false){
      echo "Error: " . $file[0]["message"];
      exit;
    }
  
    $imagePaths = array_map(function($item) {
      return $item["data"];
    }, $images);
  
    $filePath = $file["data"];
  
    print_r($imagePaths);
    print_r($filePath);
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
      .form {
        margin-top: 50px;
      }

      .form-group {
        width: 70%;
        margin: auto;
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
      <div class="form-group">
        <label for="images">Images:</label>
        <input type="file" class="filepond" name="images[]" data-max-file-size="10MB" accept="image/jpeg, image/png, image/gif, image/bmp, image/webp, image/tiff, image/svg+xml, image/x-icon" multiple>
      </div>
      <div class="form-group">
        <label for="file">File:</label>
        <input type="file" class="filepond" name="file">
      </div>
      <div class="form-group">
        <input type="submit" name="upload" value="Upload">
      </div>
    </form>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src='https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js'></script>
    <script src='https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js'></script>
    <script src='https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js'></script>
    <script src='https://unpkg.com/filepond/dist/filepond.min.js'></script>
    <script src='filepond-init.js'></script>
  </body>
</html>