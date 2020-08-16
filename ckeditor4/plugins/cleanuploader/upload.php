<?php

session_start();

//  Config file/files
require("config/config.php");
require("functions.php");

$returnData = array();

$loginStatus = login_validation($username, $password);

if ($loginStatus == "Success") {

    $outputDir = "uploads/";
	$fileTypesArray = explode(",", $fileTypes);

    //Thumbnail creation
    function createThumb($fileName) {
        $outputDir = "uploads/";
        $thumbDir = "uploads/thumb/";

        if ((preg_match('/[.](jpg)$/', $fileName)) || (preg_match('/[.](jpeg)$/', $fileName))) {
            $image = imagecreatefromjpeg($outputDir . $fileName);
        } else if (preg_match('/[.](gif)$/', $fileName)) {
            $image = imagecreatefromgif($outputDir . $fileName);
        } else if (preg_match('/[.](png)$/', $fileName)) {
            $image = imagecreatefrompng($outputDir . $fileName);
        }

        $originalX = imagesx($image);
        $originalY = imagesy($image);

        $newX = 150;
        $newY = floor($originalY * (150 / $originalX));

        //$newXY = 100;
        //$newX = floor($originalX * (100 / $originalY));

        $newMeasures = imagecreatetruecolor($newX, $newY);

        imagecopyresized($newMeasures, $image, 0, 0, 0, 0, $newX, $newY, $originalX, $originalY);
        imagejpeg($newMeasures, $thumbDir . $fileName);
    }

    if (isset($_FILES["myfile"])) { //single file
        //Connect to Database
        $dbc = mysqli_connect($db_host, $db_username, $db_password, $db_database)
                or die('Connection failed: ' . mysqli_connect_error());
        $dbc->set_charset('utf8');

        $error = $_FILES["myfile"]["error"];

        if (!is_array($_FILES["myfile"]["name"])) { //single file
			if ($_FILES["myfile"]["size"] < $fileSize){
				$fileName = $_FILES["myfile"]["name"];

				//Get file Extension
				$extension = strtolower(end((explode(".", $fileName))));
				if (in_array($extension,$fileTypesArray)){
					//Give file a unique name
					$newImageNameNoExt = date('Y-m-d-H-i-s') . "_" . uniqid();
					$newImageName = $newImageNameNoExt . '.' . $extension;

					move_uploaded_file($_FILES["myfile"]["tmp_name"], $outputDir . $newImageName);
					$filePath = $outputDir . $newImageName;
					$fileSizeUpload = round(filesize($filePath) / 1024);
					//See if filesize is smaller then the config

					$details = array();
					$details["name"] = $newImageNameNoExt;
					$details["extension"] = $extension;
					$details["size"] = $fileSizeUpload;

					$returnData[] = $details;

					//Create Thumbnail
					createThumb($newImageName);

					//Insert Into Database
					$query = "INSERT INTO " . $db_table . " (name,size) VALUES ('$newImageName',$fileSizeUpload)";
					$dbc->query($query)
							or die('Database Error: ' . $dbc->error);
				} else {
					$returnData[] = "File type is not accepted.";
				}
			} else {
				$returnData[] = "File is too large.";
			}

        } else {  //Multiple files, file[]
            $fileCount = count($_FILES["myfile"]["name"]);

            for ($i = 0; $i < $fileCount; $i++) {
				if ($_FILES["myfile"]["size"][$i] < $fileSize){
					$fileName = $_FILES["myfile"]["name"][$i];

					//Get file Extension
					$extension = strtolower(end((explode(".", $fileName))));
					if (in_array($extension,$fileTypesArray)){

					//Give file a unique name
					$newImageNameNoExt = date('Y-m-d-H-i-s') . "_" . uniqid();
					$newImageName = $newImageNameNoExt . '.' . $extension;

					move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $outputDir . $newImageName);
					$filePath = $outputDir . $newImageName;
					$fileSizeUpload = round(filesize($filePath) / 1000);
					$details = array();
					$details["name"] = $newImageNameNoExt;
					$details["extension"] = $extension;
					$details["size"] = $fileSizeUpload;

					$returnData[] = $details;

					//Insert image info into database
					$query = "INSERT INTO " . $db_table . " (name,size) VALUES ('$newImageName',$fileSizeUpload";
					$dbc->query($query)
							or die('Database Error: ' . $dbc->error);
					}
				}
            }
        }
		$dbc->close();
        echo json_encode($returnData);
    }
} else {
    $returnData[] = "Login validation Failed";
    echo json_encode($returnData);
}
?>
