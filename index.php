<?php
/**
 *  ELI FRAMEWORK
 *  FRAMEWORK CREATED BY AJAY KUMAR (TECHSOUL.IN) // EAGLE LITE - ELI FRAMEWORK
 *  +91 9862542983
 *  techsoul4@gmail.com
 */

if( !function_exists('apache_request_headers') ) {
///
function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}
///
}
///

// SET DEFAULT TIME
date_default_timezone_set('Asia/Kolkata');

// DISPLAY ERROR CODE

$display_error = true;
if($display_error)
{
ini_set('display_errors', '1');
error_reporting(E_ALL);
}
else
{
ini_set('display_errors', '0');
error_reporting(0);
}

//////////////////////// HACK PROOF SCRIPT - AJ ///////////////////////
//ini_set("session.use_cookies", 0);
ini_set("session.use_only_cookies", 0);
ini_set("session.use_trans_sid", 0);
ini_set("session.cache_limiter", "nocache");
//session_cache_expire(10080);
ini_set('session.cookie_httponly', 1);
ini_set('expose_php', 'off');

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600);
// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);
session_set_cookie_params([
    'samesite' => 'None',
    'secure' => true, // You should also set the secure flag for cookies sent over HTTPS
    'httponly' => true
]);
session_start();
session_regenerate_id();
/////////////////////////////////////////////////
//header("Cache-Control", "no-store, no-cache, must-revalidate");
$headers = apache_request_headers();
$headers['Cookie'] = "";
///////////////////////////////////////////////////////////////////////
// DEFINE CORE ELI
include('ELI/functions.php');
csrf();
$tmp = new TEMPLATE;
include(PROCESS.'url.php');
/////////////////////////////////

?>
