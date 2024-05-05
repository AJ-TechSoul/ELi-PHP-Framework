<?php
// DATABASE CONFIG
//------------------------------------
//------------------------------------

# USER DATABASE
// $GLOBALS['USER']['config']['dbtype'] = "mysql"; // mysql,sqlite
// $GLOBALS['USER']['config']['dbhost'] = "localhost"; // FOR SQLITE, Please mention SQLlite File Path
// $GLOBALS['USER']['config']['dbuser'] = "root";
// $GLOBALS['USER']['config']['dbpass'] = "";
// $GLOBALS['USER']['config']['dbname'] = "";// FOR SQLITE, Please mention SQLlite File Path
$GLOBALS['USER']['config']['dbtype'] = "sqlite"; // mysql,sqlite
$GLOBALS['USER']['config']['dbhost'] = "localhost"; // FOR SQLITE, Please mention SQLlite File Path
$GLOBALS['USER']['config']['dbuser'] = "admin";
$GLOBALS['USER']['config']['dbpass'] = "admin";
$GLOBALS['USER']['config']['dbname'] = "db/cmsv3";


# CMS Database
$GLOBALS['CMS']['config']['dbtype'] = "sqlite"; // mysql,sqlite
$GLOBALS['CMS']['config']['dbhost'] = "localhost"; // FOR SQLITE, Please mention SQLlite File Path
$GLOBALS['CMS']['config']['dbuser'] = "admin";
$GLOBALS['CMS']['config']['dbpass'] = "admin";
$GLOBALS['CMS']['config']['dbname'] = "db/cmsv3";


# EMAIL INFORMATION
$GLOBALS['config']['SmtpServer']="mail.techsoul.in";
$GLOBALS['config']['SmtpPort']=587;
$GLOBALS['config']['SmtpUser']="dummy@app.techsoul.in";
$GLOBALS['config']['SmtpPass']="dummy@777";
$GLOBALS['config']['SMTPSecure']="tls";
$GLOBALS['config']['mailFROM']="dummy@app.techsoul.in";


# CMS
$GLOBALS['cmspass'] = "superadmin777";


# SMS INFORMATION
$GLOBALS['sms']['url'] = "***";
$GLOBALS['sms']['user'] = "***";
$GLOBALS['sms']['pass'] = "***";
$GLOBALS['sms']['sid'] = "******";

//------------------------------------
//   SITE DETAILS
//------------------------------------
$GLOBALS['impMail'] = "dummy@app.techsoul.in";
$GLOBALS['site']['name'] = "Site-Name";
$GLOBALS['site']['author'] = "Your Name";
$GLOBALS['shorturl'] = ""; //optional
//------------------------------------

$GLOBALS['config']['mailFROM'] = "dummy@app.techsoul.in"; // mail username from where to send mail
$GLOBALS['officemail'] = "dummy@app.techsoul.in"; // official email

//------------------------------------
// Facebook Login

define('PROFILE_PIC', _ABSPATH_.'/files/dp/');

//------------------------------------
// Custom User Sets
//------------------------------------

// $_SESSION['user']['name'] = "Ajay";
// $_SESSION['user']['id'] = "1";
// $_SESSION['user']['role'] = "admin";

//////////////////////////////////////////

date_default_timezone_set("Asia/Kolkata");
//echo date('d-m-Y h:i a'); //Returns IST
//------------------------------------

$freepages = "REGISTER,LOGIN,SIGNUP,contact.php,partner.php,test.php,tool.php";

// IMPORTANT TO SET
$websitepath = "rad/eli-php-framework-main/";
 ?>
