<?php

    // Connect to database.
    include_once ('./assets/include/db-connect.php');
    include_once ('./assets/include/db-user-check.php');
    

    // Begin the switch statements to show page content.
    switch($_GET['page']) {
        case 'home':
            include_once ('./pages/home/index.php');
        break;
        case 'paratech-strut-mounts':
            include_once ('./pages/products/strut_mounts.php');
        break;
        case 'fluid-container-caddy':
            include_once ('./pages/products/fluid-container-caddy.php');
        break;
        case 'poly-boxes':
            include_once ('./pages/products/poly-boxes.php');
        break;
        case 'tool-mounts':
            include_once ('./pages/products/tool-mounts.php');
        break;
        case 'container-mounts':
            include_once ('./pages/products/container-mounts.php');
        break;
        case 'cart':
            include_once ('./pages/cart/index.php');
        break;
        case 'my-account':
            include_once ('./pages/account/index.php');
        break;
        case 'why-polytech':
            include_once ('./pages/why-poly-tech/index.php');
        break;
        case 'about-us':
            include_once ('./pages/about-us/index.php');
        break;
        case 'contact-us':
            include_once ('./pages/contact/index.php');
        break;
        default:
            include_once ('./pages/home/index.php');
        break;
    }

    

?>

