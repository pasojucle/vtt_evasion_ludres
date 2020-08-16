<?php
session_start();
//url data
$CKEditor = filter_input(INPUT_GET, "CKEditor", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$CKEditorFuncNum = filter_input(INPUT_GET, "CKEditorFuncNum", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$langCode = filter_input(INPUT_GET, "langCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$endURL = "?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode";
// Require the config file to exist for the plugin to activate.
if (!file_exists("config/config.php")) {
    // Config file can not be found. Check for install file.
    if (file_exists("install.php")) {
        // Send user to Install
        header("Location: install.php$endURL");
        // Makes sure that other code dont get executed
        exit;
    }
} else {

    //  Config file/files
    require("config/config.php");
    require("functions.php");

    $loginStatus = "";
    // Get Login Status
    if (isset($_POST) && !empty($_POST)) {

        if (isset($_POST["logout"]) && $_POST["logout"] == "true") {
            $_SESSION = array();
            session_destroy();
            $loginStatus = "You have been logged out.";
        } else {
            $post_username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $post_password = $_POST["password"];
            $loginStatus = checkLoginStatus($username, $password, $post_username, $post_password);
        }
    } else {
        $loginStatus = login_validation($username, $password);
    }
    ?>
    <!doctype html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,100,400italic,500,700' rel='stylesheet' type='text/css'>
            <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
            <link href="assets/css/uploadfile.css" rel="stylesheet">
            <title>CleanUpload</title>
        </head>
        <body>
            <nav class="navbar navbar-default navbar-fixed-top navbar">
                <div class="container">
                    <div class="navbar-brand text-left"> CleanUploader </div>
                    <?php if ($loginStatus == "Success") { ?>
                    <form method="post" action="start.php<?php echo $endURL; ?>">
                    <div class="navbar-right">
                        <button type="submit" name="logout" value="true" class="btn navbar-btn navbar-right">Logout</button>
                        <p class="navbar-text hidden-xs">Welcome <?php echo $_SESSION["username"] ?>,</p>
                    </div>
                    </form>
					<?php } ?>

                </div>
            </nav>
            <?php
            if ($loginStatus == "Success") {
                $dbc = mysqli_connect($db_host, $db_username, $db_password, $db_database)
                        or die('Connection to the database is lost');
                $dbc->set_charset('utf8');

                $query = "SELECT * FROM " . $db_table;
                $result = mysqli_query($dbc, $query)
                        or die('Could not fetch data');
                ?>
                <div class="container-fluid text-center">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="upload" id="multipleupload"></div>
                        </div>
                    </div>
                    <div class="row" id="filelist"> </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <div class="uploadButtonContainer" id="uploadButtonContainer">
                                <button type="button" class="btn btn-primary" onClick="startUpload();"><i class="glyphicon glyphicon-upload"></i> Upload</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container currentFiles">
                    <table class="table table-striped" id="list">
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_array($result)) {
                                $temp = explode('.', $row['name']);
                                $ext = array_pop($temp);
                                $name = implode('.', $temp);
                                ?>
                                <tr id="<?php echo $name ?>">
                                    <td><img src="uploads/thumb/<?php echo $row["name"] ?>" onClick="useImage(this.src);" id="addImg" data-toggle="tooltip" data-placement="right" title="Add image"/></td>
                                    <td><p><?php echo $row["name"] ?></p></td>
                                    <td><p><?php echo $row["size"] ?> kb</p></td>
                                    <td><button type="button" class="btn btn-danger pull-right" onClick="deleteImage('<?php echo $name ?>', '<?php echo $ext ?>');"><i class="glyphicon glyphicon-trash"> </i> Delete</button></td>
                                </tr>
                                <?php
                            }
                            mysqli_close($dbc);
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } else {
                ?>
                <!-- Modal -->
                <form class="form-horizontal" method="post" action="start.php<?php echo $endURL; ?>" id="loginForm">
                    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" data-backdrop="static">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Login<?php echo $test ?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php if ($loginStatus != "") { ?>
                                        <legend class="text-left bg-danger paddingLeft5">Errors</legend>
                                        <div class="col-sm-12 text-left">
                                            <p class="text-warning"><?php echo $loginStatus; ?></p>
                                        </div>
                                    <?php } ?>

                                    <div class="form-group">
                                        <label for="username" class="col-sm-3 control-label ">Username</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="username" placeholder="Username">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="col-sm-3 control-label">Password</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="password" placeholder="Password">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" form="loginForm">Login</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } ?>
            <!--  Scripts   -->
            <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
            <script src="assets/js/jquery.uploadfile.js"></script>
            <script src="assets/js/functions.js"></script>
            <script>
                                var downloadObj = $("#multipleupload").uploadFile({
                                    url: "upload.php",
                                    multiple: true,
                                    dragDrop: true,
                                    fileName: "myfile",
                                    allowedTypes: "<?php echo $fileTypes ?>",
                                    maxFileSize: <?php echo $fileSize ?>,
                                    autoSubmit: false,
                                    showPreview: true,
                                    previewHeight: 120,
                                    previewWidth: 120,
                                    onLoad: function (obj) {
                                        $("#filelist").hide()
                                    },
                                    afterUploadAll: function (obj)
                                    {

                                    },
                                    onSuccess: function (files, data, xhr, pd)
                                    {
                                        $("#filelist").fadeOut(750, "linear"),
                                                jObj = JSON.parse(data),
                                                addNewImage(jObj[0].name, jObj[0].size, jObj[0].extension),
                                                $("#uploadButtonContainer").hide()

                                    },
                                    onSelect: function (files)
                                    {
                                        $("#filelist").fadeIn(250, "linear"),
                                                controlOfFileExtension(files)


                                    },
                                    onCancel: function (files, pd)
                                    {
                                        numberOfFilesSelected--,
                                                checkNumberOfFiles()
                                    },
                                    onError: function (files, status, errMsg, pd)
                                    {

                                    }
                                });
            </script>

        </body>
    </html>
<?php } ?>
