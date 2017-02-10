<?php

    // Connect to database.
    require(dirname(__FILE__).'/assets/include/db-connect.php');

    // Prepare and execute the SELECT statement for SETTINGS.
    if ($select = $db -> prepare("SELECT setting_email, setting_latefee, setting_latefeetype, setting_graceperiod FROM settings")) {
        $select -> execute();
        $select -> bind_result($setting_email, $setting_latefee, $setting_latefeetype, $setting_graceperiod);
        $select -> fetch();
        $select -> close();
    }

    // Prepare and execute the SELECT statement for RENTALS.
    if ($select = $db->query("SELECT rental_id, user_id, unit_id, rental_renewdate, rental_balance FROM rentals WHERE rental_status='A'")) {

        // Search each result to send email
        while ($rental = $select->fetch_assoc()) {
            
            $rental_id        = $rental['rental_id'];
            $user_id          = $rental['user_id'];
            $unit_id          = $rental['unit_id'];
            $rental_renewdate = strtotime($rental['rental_renewdate']);
            $rental_balance   = $rental['rental_balance'];

            // Prepare and execute the SELECT statement for USERS.
            if ($getTenant = $db->query("SELECT user_fname, user_lname, user_email, user_phone FROM users WHERE user_id={$user_id}")) {
                $tenant     = $getTenant->fetch_array();
                $user_fname = $tenant['user_fname'];
                $user_lname = $tenant['user_lname'];
                $user_email = $tenant['user_email'];
                $user_phone = $tenant['user_phone'];
                $getTenant->close();
            }

            // Prepare and execute the SELECT statement for UNITS. 
            if ($getUnit = $db->query("SELECT unit_number FROM units WHERE unit_id={$unit_id}")) {
                $unit = $getUnit->fetch_array();
                $unit_number = $unit['unit_number'];
                $getUnit->close();
            }

            $time         = strtotime('-'. $setting_graceperiod .' Days',$rental_renewdate);
            $notify_prior = date('Y-m-d',$time);
            $notify_today = date('Y-m-d',$rental_renewdate);
            $time2        = strtotime('+'. $setting_graceperiod .' Days',$rental_renewdate);
            $notify_post  = date('Y-m-d',$time2);    $locked = ($setting_graceperiod + 1);
            $time3        = strtotime('+'. $locked .' Days',$rental_renewdate);
            $notify_lock  = date('Y-m-d',$time3);
            $today        = date('Y-m-d');

            /* -------------------------------------------------------- 
            
                PRIOR NOTIFICATION CUSTOMER
                
            -------------------------------------------------------- */

            if ($today == $notify_prior) {
                
                // SELECT statement for EMAILS for PRIOR.
                if ($getPrior = $db -> query("SELECT email_subject, email_h1, email_body, email_fromemail FROM emails WHERE email_id=1")) {
                    $prior           = $getPrior -> fetch_array();
                    $email_subject   = $prior['email_subject'];
                    $email_h1        = $prior['email_h1']; 
                    $email_body      = $prior['email_body'];
                    $email_fromemail = $prior['email_fromemail'];
                    $getPrior -> close();
                }

                /* PRIOR NOTIFICATION CUSTOMER EMAIL*/        
                // Send the email to the customer.
                $to       = $user_email;
                $subject  = $email_subject;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $headers .= 'Reply-To: '.$user_fname.' '.$user_lname.' <'.$user_email.'>'."\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(assets/img/tile-pattern.png) #222D3A !important;">
                                <tr><td style="height: 60px;">&nbsp;</td></tr>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <img id="logo" src="http://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                                </td>
                                            </tr>  
                                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 10px 40px; background: #eb9a00;">
                                                <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h1 .'</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;">
                    <p>'. $user_fname .' '. $user_lname .',</p>
                              <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body)) .'</p>
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
            </table>';
            /* END HTML CONTENT */

                mail($to, $subject, $message, $headers); 

                /* PRIOR NOTIFICATION ADMIN EMAIL */
//                $to       = $setting_email;
//                $subject  = $email_subject;
//                $headers  = 'MIME-Version: 1.0' . "\r\n";
//                $headers .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
//                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
//                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(assets/img/tile-pattern.png) #222D3A !important;">
//                                <tr><td style="height: 60px;">&nbsp;</td></tr>
//                                <tr>
//                                    <td>
//                                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
//                                            <tr>
//                                                <td style="text-align: center;">
//                                                    <img id="logo" src="https://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
//                                                </td>
//                                            </tr>  
//                                            <tr><td style="height: 20px;">&nbsp;</td></tr>
//                                            <tr>
//                                                <td style="padding: 10px 40px; background: #eb9a00;">
//                                                <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h1 .'</h1>
//                                                </td>
//                                            </tr>
//                                            <tr>
//                                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;">
//                              <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body)) .'</p>
//                    <p>
//                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
//                                        </p>                              
//                                        <p>Thank you,<br />
//                                        <strong>Self Storage East Haven</strong><br />
//                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
//                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
//                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
//                                    </p>
//                                </td>
//                            </tr>
//                        </table>
//
//                    </td>
//                </tr>
//                <tr><td style="height: 60px;">&nbsp;</td></tr>
//            </table>';
//                /* END HTML CONTENT */
//
//                mail($to, $subject, $message, $headers);
                echo 'Tenant Name: ',$user_fname, '', $user_lname, ' Email:', $user_email, ' Renew Date:  ', $rental_renewdate, ' - 5 Days Due Warning<br/>';
            }

            /* --------------------------------------------------------
            
                DUE TODAY NOTIFICATION
                
            -------------------------------------------------------- */

            if ($today == $notify_today) {
                
                // SELECT statement for EMAILS for PRIOR.
                if ($getToday = $db->query("SELECT email_subject, email_h1, email_body, email_fromemail FROM emails WHERE email_id=9")) {
                    $prior            = $getToday->fetch_array();
                    $email_subject2   = $prior['email_subject'];
                    $email_h12        = $prior['email_h1'];
                    $email_body2      = $prior['email_body'];
                    $email_fromemail2 = $prior['email_fromemail'];
                    $getToday->close();
                }

                /* DUE TODAY CUSTOMER EMAIL*/
                // Send the email to the customer.
                $to       = $user_email;
                $subject  = $email_subject2;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $headers .= 'Reply-To: '.$user_fname.' '.$user_lname.' <'.$user_email.'>'."\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(http://selfstorageeasthaven.com/assets/img/tile-pattern.png) #222D3A !important;">
                <tr><td style="height: 60px;">&nbsp;</td></tr>
                <tr>
                    <td>


                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                            <tr>
                                <td style="text-align: center;">
                                    <img id="logo" src="https://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                </td>
                            </tr>  
                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                            <tr>
                                <td style="padding: 10px 40px; background: #eb9a00;">
                                    <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h12 .'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;"><p>'. $user_fname .' '. $user_lname .',</p>             
                                <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body2)) .'</p>
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
            </table>';
                mail($to, $subject, $message, $headers);
                $unitListAdmin .= $unit['unit_number'] . '<br/>';

                /* DUE TODAY ADMIN EMAIL */        
                $to       = $setting_email;
                $subject  = $email_subject2;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(http://selfstorageeasthaven.com/assets/img/tile-pattern.png) #222D3A !important;">
                <tr><td style="height: 60px;">&nbsp;</td></tr>
                <tr>
                    <td>


                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                            <tr>
                                <td style="text-align: center;">
                                    <img id="logo" src="https://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                </td>
                            </tr>  
                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                            <tr>
                                <td style="padding: 10px 40px; background: #eb9a00;">
                                    <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h12 .'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;"><p>'. $user_fname .' '. $user_lname .',</p>             
                                <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body2)) .'</p>
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
            </table>';
                mail($to, $subject, $message, $headers);

                echo 'Tenant Name: ',$user_fname, '', $user_lname, ' Email:', $user_email, ' Renew Date:  ', $rental_renewdate, ' - Due Today Warning<br/>';
            }

            /* -------------------------------------------------------- 
            
                POST NOTIFICATION
                
            -------------------------------------------------------- */
            
            if ($today == $notify_post) {
                
                /* POST NOTIFICATION CUSTOMER EMAIL */
                if ($getPost = $db->query("SELECT email_subject, email_h1, email_body, email_fromemail FROM emails WHERE email_id=7")) {
                    $prior            = $getPost->fetch_array();
                    $email_subject3   = $prior['email_subject'];
                    $email_h13        = $prior['email_h1'];
                    $email_body3      = $prior['email_body'];
                    $email_fromemail3 = $prior['email_fromemail'];
                    $getPost->close();
                }

                /* POST CUSTOMER EMAIL*/
                // Send the email to the customer.
                $to       = $user_email;
                $subject  = $email_subject3;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $headers .= 'Reply-To: '.$user_fname.' '.$user_lname.' <'.$user_email.'>'."\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(http://selfstorageeasthaven.com/assets/img/tile-pattern.png) #222D3A !important;">
                                <tr><td style="height: 60px;">&nbsp;</td></tr>
                                <tr>
                                    <td>


                                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <img id="logo" src="http://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                                </td>
                                            </tr>  
                                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 10px 40px; background: #eb9a00;">
                                                    <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h13 .'</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;">
                                                <p>'. $user_fname .' '. $user_lname .',</p>
                                                <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body3)) .'</p>
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
            </table>';
                mail($to, $subject, $message, $headers);
                $unitListAdmin .= $unit['unit_number'] . '<br/>';

                /* POST ADMIN EMAIL */        
                $to       = $setting_email;
                $subject  = $email_subject3;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(http://selfstorageeasthaven.com/assets/img/tile-pattern.png) #222D3A !important;">
                                <tr><td style="height: 60px;">&nbsp;</td></tr>
                                <tr>
                                    <td>


                                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <img id="logo" src="http://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                                </td>
                                            </tr>  
                                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 10px 40px; background: #eb9a00;">
                                                    <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">'. $email_h13 .'</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;">
                                                <p>'. $user_fname .' '. $user_lname .',</p>
                                                <p>'. str_replace(array("{UNIT_LIST}","{DUE_DATE}","{BALANCE}"), array($unit_number,date('m-d-Y',$rental_renewdate),$rental_balance), stripslashes($email_body3)) .'</p>
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
            </table>';
                mail($to, $subject, $message, $headers);
                echo 'Tenant Name: ',$user_fname, '', $user_lname, ' Email:', $user_email, ' Renew Date:  ', $rental_renewdate, ' - 5 Days Past Due Warning<br/>';
            }        

            /* -------------------------------------------------------- 
            
                LOCK NOTIFICATION
                
            -------------------------------------------------------- */  
            
            if ($today == $notify_lock) {
                
                foreach($rental as $unitListLock){
                    $unitListLock= array($unit['unit_number']);
                }                 

                /* LOCK ADMIN EMAIL */             
                if ($getLock = $db->query("SELECT email_subject, email_h1, email_body, email_fromemail FROM emails WHERE email_id=11")) {
                    $lock             = $getLock->fetch_array();
                    $email_subject4   = $lock['email_subject'];
                    $email_h14        = $lock['email_h1'];
                    $email_body4      = $lock['email_body'];
                    $email_fromemail4 = $lock['email_fromemail'];
                    $getLock->close();
                }
                
                $to       = $setting_email;
                $subject  = $email_subject4;
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html;
                charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Self Storage East Haven <info@selfstorageeasthaven.com>' . "\r\n";
                $message  = '<table style="width: 100%; height: 100%; border: 0; background: url(http://selfstorageeasthaven.com/assets/img/tile-pattern.png) #222D3A !important;">
                                <tr><td style="height: 60px;">&nbsp;</td></tr>
                                <tr>
                                    <td>


                                        <table cellpadding="0" cellspacing="0" style="margin: auto; width: 600px; border: 0; font-family: Open Sans, Segoe UI, Arial, Helvetica; line-height: 1.75;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <img id="logo" src="https://selfstorageeasthaven.com/assets/img/logo.png" alt="Self Storage East Haven, Connecticut">
                                                </td>
                                            </tr>  
                                            <tr><td style="height: 20px;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 10px 40px; background: #eb0003;">
                                                    <h1 style="color: #fff; font-size: 30px; margin:0; padding:10px 0; text-transform: uppercase;">' . $email_h14 .'</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="background: #fff; padding: 30px 40px 40px; color: #666 !important; font-size: 16px;">        <p>'. str_replace(array("{UNIT_LIST}"), array($unit['unit_number']), stripslashes($email_body4)) .'</p>
                                                
                                                <p><strong>User Info:</strong><br/>
                                                Name: '. $user_fname .' '. $user_lname .'<br/>
                                                Email: '. $user_email .'<br/>
                                                Phone: '. $user_phone .'</p>             
                    <p>
                                        <a href="https://selfstorageeasthaven.com/pages/account/" style="position:relative; display:inline-block; padding:12px 30px; color:#fff!important; font-size:16px; font-weight:600; text-transform:uppercase; text-decoration:none; letter-spacing:1px; background:#76bd1d; border:0; outline:0; cursor:pointer; -webkit-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -moz-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -ms-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); -o-box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34); box-shadow:inset 0 100px 0 rgba(255,255,255,0),inset 0 -3px rgba(0,0,0,.34)">Access Your Account</a>
                                        </p>                              
                                        <p>Thank you,<br />
                                        <strong>Self Storage East Haven</strong><br />
                                        <a style="color: #5c9f08;" href="tel:+12034681245">(203) 468-1245</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="mailto:info@selfstorageeasthaven.com">info@selfstorageeasthaven.com</a> &nbsp; | &nbsp; 
                                        <a style="color: #5c9f08;" href="https://selfstorageeasthaven.com/login.php">Make a Payment</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr><td style="height: 60px;">&nbsp;</td></tr>
                </table>';
                mail($to, $subject, $message, $headers);

                echo 'Tenant Name: ',$user_fname, '', $user_lname, ' Email:', $user_email, ' Renew Date:  ', $rental_renewdate, ' - Lock Up Unit Warning<br/>';
            }
        }
        
    }
    $select->close();
?>