<?php
$dirs = array("uploads/", "config/","uploads/thumb/");
$allErrors = array();

//url data
$CKEditor = filter_input(INPUT_GET, "CKEditor", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$CKEditorFuncNum = filter_input(INPUT_GET, "CKEditorFuncNum", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$langCode = filter_input(INPUT_GET, "langCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$endURL = "?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode";

//Get info from php.ini on allowed file size.
$maxPostUpload = preg_replace("/[^0-9]/", "", ini_get("post_max_size"));
$maxUpload = preg_replace("/[^0-9]/", "", ini_get("upload_max_filesize"));
if (empty($fileSize)) {
	//Set filesize textbox to maxupload number
    $fileSize = $maxUpload;
}

//Check and change persmission on folders needed for CleanUpload
foreach ($dirs as $dir) {
    if (!is_writable($dir)) {
        if (!chmod($dir, 0755)) {
			// Dirs are not writeable
            $allErrors[] = $dir . " is not writeable or is missing. This is needed for the install to work.";
        }
    }
}

//See if user has sent form and if errors is empty
if (isset($_POST) && !empty($_POST)) {

	//Check checkboxes
    foreach ($_POST["filetype"] as $type) {
        if ($type == "jpg,jpeg") {
            $checkJPG = "checked";
        } elseif ($type == "gif") {
            $checkGIF = "checked";
        } elseif ($type == "png") {
            $checkPNG = "checked";
        }
    }

    $host = filter_input(INPUT_POST, "host", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $database = filter_input(INPUT_POST, "database", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST["password"];
    $tablePrefix = filter_input(INPUT_POST, "tablePrefix", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $admin_username = filter_input(INPUT_POST, "admin_username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $admin_password = $_POST["admin_password"];
    $fileSize = filter_input(INPUT_POST, "fileSize", FILTER_SANITIZE_NUMBER_INT);

    if ((!empty($_POST["host"]) && !empty($_POST["database"]) && !empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["tablePrefix"]) && !empty($_POST["admin_username"]) && !empty($_POST["admin_password"]) && !empty($_POST["admin_password_again"]) && !empty($_POST["fileSize"]) && !empty($_POST["filetype"])) && ($_POST["admin_password"] == $_POST["admin_password_again"]) && (empty($allErrors))) {

        $dbc = mysqli_connect($host, $username, $password, $database);

        //Open a databse connection
        if (!$dbc) {
            $allErrors[] = "Connection Failed: " . mysqli_connect_error();
        } else {
            $dbc->set_charset("utf8");
            preg_replace("/[^a-zA-Z0-9_]+/", "", $tablePrefix);
            $tablePrefix = mysqli_real_escape_string($dbc, trim($tablePrefix));
            $tablePrefix .= "images";
            $query = "CREATE TABLE " . $tablePrefix . " (
			id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			size INT NOT NULL
			)";

            //Creation of config file
            if ($configFile = fopen("config/config.php", "w")) {

                //Create Table
                if ($dbc->query($query) === TRUE) {

                    $filetype = "";
                    foreach ($_POST["filetype"] as $type) {
                        $type = filter_var($type, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        $filetype .= $type . ",";
                    }
                    $fileSize = 1048576 * $fileSize;

                    $admin_password = password_hash($admin_password, PASSWORD_DEFAULT);

                    //Content for config.php
                    $content = "<?php \n";
                    $content .= "// CleanUpload info \n";
                    $content .= "\$username = \"$admin_username\"; \n";
                    $content .= "\$password = '$admin_password'; \n";
                    $content .= "\$fileTypes = \"$filetype\"; \n";
                    $content .= "\$fileSize = $fileSize; \n";
                    $content .= "// CleanUpload info \n";
                    $content .= "\$db_host = \"$host\"; \n";
                    $content .= "\$db_database = \"$database\"; \n";
                    $content .= "\$db_username = \"$username\"; \n";
                    $content .= "\$db_password = \"$password\"; \n";
                    $content .= "\$db_table = \"$tablePrefix\"; \n";
                    $content .= "?>";

                    //Write file
                    fwrite($configFile, $content);
                    fclose($configFile);

                    if ($_POST["remove"] == "yes") {
						if (file_exists("install.php")){
						  unlink("install.php");
						  }
					}
                } else {
                    $allErrors[] = "Error creating table: " . $dbc->error;
                }
            } else {
                $allErrors[] .= "Couldnt create config file.";
            }
            //Close database connection
            $dbc->close();
        }
    } else {
        // Error Message
		if (!($_POST["admin_password"] == $_POST["admin_password_again"])) {
            $allErrors[] = "Admin password is not the same in both fields";
		}
		if (empty($allErrors)) {
        	$allErrors[] = "All fields need a value";
        }
    }
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,100,400italic,500,700' rel='stylesheet' type='text/css'>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/uploadfile.css" rel="stylesheet">
        <title>Install CleanUpload</title>
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-brand text-left"> CleanUpload Installer </div>
            </div>
        </nav>
        <?php if (!file_exists("config/config.php")) { ?>
            <!-- SETUP -->
            <form class="form-horizontal" method="post" action="<?php echo htmlentities($_SERVER["PHP_SELF"], ENT_QUOTES); ?><?php echo $endURL; ?>" id="mainForm">
                <div class="container">
                    <div class="row">
                        <div class="installHeader col-lg-12 text-center">
                            <fieldset>
                                <?php if (count($allErrors) > 0) { ?>
                                    <legend class="text-left bg-danger paddingLeft5">Errors</legend>
                                    <div class="col-sm-12 text-left">
                                        <ul>
                                            <?php foreach ($allErrors as $error) { ?>
                                                <li><?php echo $error ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <legend class="text-left paddingLeft5">MySQL Information</legend>
                                <div class="form-group">
                                    <label for="host" class="col-sm-3 control-label">Database Host</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="host" placeholder="Hostname" data-toggle="tooltip" data-placement="top" title="Database host e.g. localhost" value="<?php echo $host; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="database" class="col-sm-3 control-label">Database Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="database" placeholder="Database" data-toggle="tooltip" data-placement="top" title="In that database you want to use cleanupload" value="<?php echo $database ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="username" class="col-sm-3 control-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="username" placeholder="Username" data-toggle="tooltip" data-placement="top" title="MySQL username" value="<?php echo $username ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="col-sm-3 control-label">Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" name="password" placeholder="Password" data-toggle="tooltip" data-placement="top" title="MySQL password">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="tablePrefix" class="col-sm-3 control-label">Table Prefix</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="tablePrefix" value="cleanupload_" data-toggle="tooltip" data-placement="top" title="What table prefix you want to use. Allowed characters[a-ö,A-Ö,0-9,_]" value="<?php echo $tablePrefix ?>">
                                            <span class="input-group-addon">images</span> </div>
                                    </div>
                                </div>
                                <legend class="text-left paddingLeft5">CleanUpload Administration Information</legend>
                                <div class="form-group">
                                    <label for="admin_username" class="col-sm-3 control-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="admin_username" placeholder="Username" data-toggle="tooltip" data-placement="top" title="Username for CleanUpload access" value="<?php echo $admin_username ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="admin_password" class="col-sm-3 text-left control-label">Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control equalTextField" id="admin_password" name="admin_password" placeholder="Password" data-toggle="tooltip" data-placement="top" title="Password for CleanUpload access" onKeyUp="checkPassword();">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="admin_password_again" class="col-sm-3 text-left control-label">Password again</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control equalTextField" id="admin_password_again" name="admin_password_again" placeholder="Password" data-toggle="tooltip" data-placement="top" title="Same password as in the first textfield." onKeyUp="checkPassword();" style="">
                                    </div>
                                </div>
                                <legend class="text-left paddingLeft5">CleanUpload Configuration</legend>
                                <div class="form-group">
                                    <label for="fileSize" class="col-sm-3 control-label">Max file size</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="fileSize" min="0" max="<?php echo $maxUpload ?>" value="<?php echo $fileSize ?>" data-toggle="tooltip" data-placement="top" title="This is the maximum allowed file size on you server. Can not have a higher value than <?php echo $maxUpload ?> megabytes" >
                                            <span class="input-group-addon">Mbyte</span> </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Allowed Files</label>
                                    <div class="col-sm-9 text-left">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" value="jpg,jpeg" name="filetype[]" <?php echo $checkJPG; ?>>
                                                jpg/jpeg&nbsp; </label>
                                            <label>
                                                <input type="checkbox" value="gif" name="filetype[]" <?php echo $checkGIF; ?>>
                                                gif&nbsp; </label>
                                            <label>
                                                <input type="checkbox" value="png" name="filetype[]" <?php echo $checkPNG; ?>>
                                                png </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-danger">Remove install file?</label>
                                    <div class="col-sm-9 text-left">
                                        <div class="checkbox">
                                            <label>
                                                <input type="radio" value="yes" name="remove">
                                                Yes&nbsp; </label>
                                            <label>
                                                <input type="radio" value="no" name="remove" checked>
                                                No&nbsp; </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group text-left">
                                    <button type="submit" form="mainForm" class="btn btn-primary">Install</button>
                                    <div>
                                        </fieldset>
                                    </div>
                                </div>
                        </div>
                        </form>
                        <!-- END OF SETUP -->
                        <?php
                    } else {
                        ?>
                        <!-- SETUP COMPLETE-->
                        <div class="container">
                            <div class="installHeader col-lg-12 text-center">
                                <legend class="text-left bg-success paddingLeft5">Installation Complete</legend>
                                <h3 class="text-left paddingLeft5"><small>Remove the installation file and start uploading your images. <a href="start.php<?php echo $endURL; ?>" class="alert-link">CleanUploader</a></small></h3>
                            </div>
                        </div>
                        <!-- END OF COMPLETE SETUP -->
                    <?php } ?>
                    <!-- Scripts -->
                    <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
                    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
                    <script src="assets/js/jquery.uploadfile.js"></script>
                    <script src="assets/js/functions.js"></script>
                    </body>
                    </html>
