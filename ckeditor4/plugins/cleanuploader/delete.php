<?php

session_start();

//  Config file/files
require("config/config.php");
require("functions.php");

$loginStatus = login_validation($username, $password);

$returnData = array();

if ($loginStatus == "Success") {

    if (isset($_POST['name'])) {
        $outputDir = "uploads/";
        $outputDirThumb = "uploads/thumb/";

        //Connect to Database
        $dbc = mysqli_connect($db_host, $db_username, $db_password, $db_database)
                or die('Connection failed: ' . mysqli_connect_error());
        $dbc->set_charset('utf8');

        $query = "DELETE FROM " . $db_table . " WHERE name='" . $_POST['name'] . "'";
        $dbc->query($query);
        $dbc->close();

        $filePath = $outputDir . $_POST['name'];
        $filePathThumb = $outputDirThumb . $_POST['name'];

        if (file_exists($filePath)) {
            unlink($filePath);
            if (file_exists($filePathThumb)) {
                unlink($filePathThumb);
            }
        }

        $returnData = true;
    } else {
        $returnData = false;
    }
} else {
    $returnData = false;
}
echo json_encode($returnData);
?>
