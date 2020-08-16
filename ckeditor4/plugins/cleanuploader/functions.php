<?php

//Function to get current status of loggin
function checkLoginStatus($config_username, $config_password, $post_username, $post_password) {

    if (!empty($post_username) && !empty($post_password)) {
        if ($config_username == $post_username && (password_verify($post_password, $config_password))) {
            $_SESSION["activeLogin"] = true;
            $_SESSION["username"] = $post_username;
            $_SESSION["loginID"] = hash("sha256", $_SERVER["HTTP_USER_AGENT"] . $_SERVER['REMOTE_ADDR'], false);
            $_SESSION["timeout"] = time();
            return "Success";
        } else {
            return "Wrong login information";
        }
    } else {
        if (($_SESSION["activeLogin"] != true) || ($_SESSION["username"] == "") || ($_SESSION["loginID"] == "")) {
            $_SESSION = array();
            return "";
        } else {
            $status = login_validation($config_username, $config_password);
            return $status;
        }
    }
}

//Is the current session still valid
function login_validation($config_username, $config_password) {
    $activeLogin = $_SESSION["activeLogin"];
    $username = $_SESSION["username"];
    $loginID = $_SESSION["loginID"];
    $timeout = $_SESSION["timeout"] + 20 * 60;

    $loginIDControll = hash("sha256", $_SERVER["HTTP_USER_AGENT"] . $_SERVER['REMOTE_ADDR'], false);

    if (($activeLogin == true) && ($username == $config_username) && ($loginID == $loginIDControll) && ($timeout > time())) {
        //Active Login Status
        $_SESSION["timeout"] = time();
        session_regenerate_id();
        return "Success";
    } else {
        //Not logged in
        $_SESSION = array();
        session_destroy();
    }
}

?>
