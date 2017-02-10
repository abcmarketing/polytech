<?php

    $this_end = 'front_end';

    // Connect to database.
    include_once ('./assets/include/db-connect.php');

    // Begin the switch statements to show page content.
    switch($_GET['mode']) {
        case 'forgot':
            include_once ('./login/forgot_password.php');
        break;
        case 'reset':
            include_once ('./login/reset_password.php');
        break;
        case 'logout':
            session_start();
            session_destroy();
            header ('Location: ./login.php');
        break;
        default:
            include_once ('./login/login.php');
        break;
    }

?>