<?php
header("Access-Control-Allow-Origin: *");
header('Cache-Control: no-cache');
header('Content-Type: application/json');

$aid = base64_encode("aj818");
$csrf_got = @$_POST['csrf'];
$validateEmail = false; // VALIDATE EMAIL (true/false)
$url = page(PAGE);  
$v = new VALIDATOR;
$db = new DATABASE;
$enablelog = TRUE;
$api = new API;
$s = new SECURITY;

$validkey = false;
// working on API Key
$apikey_default = (isset($_REQUEST['csrf']) && strlen($_REQUEST['csrf']) >= 32) ? $_REQUEST['csrf'] : $api->getDefaultAPIKey();

// var_dump($_REQUEST['apikey'] == $apikey_default || $_SESSION['token'] == $apikey_default);

if(isset($_REQUEST['apikey']) && strlen(@$_REQUEST['apikey']) == 32){
  // check for same server use
if($_REQUEST['apikey'] == $apikey_default){
  $validkey = true;
}
else
{
   // Check API Key
 $apikey = @$_REQUEST['apikey'];
 $chkapi = $db->query("SELECT * FROM apilicense WHERE license='$apikey' AND  expirydate >= date('now')
 ","CMS");
    if(count($chkapi) > 0){
        $validkey = true;
    }
    else
    {
      $validkey = false;
    }
  }
}

if(check_csrf($apikey_default) || $_SESSION['token'] == $apikey_default){
  // same origin // csrf check
  $validkey = true;
}

// FREE CASES 
$casefree = explode(",","currentcsrf");
if(isset($url[1]) && in_array($url[1],$casefree)){
  $validkey = true;
}
// =========

if(!$validkey){
  $err['error'] = "API key is not valid";
  $err['success'] = false;
  die(json_encode($err));
}
//------------------


/*  API CALL Example : http://domain.com/api/app/area/insert */

switch (@$url[1]){

  case "currentcsrf":
    echo $s->csrf();
  break;

  case "getprofilext":
    if(isset($_SESSION['user']['id']) && $url[1]!=""){
          
          $uid = @$_SESSION['user']['id'];
          $dir = "profilext/".$uid.".pxt";
          if(file_exists($dir)){
             $odata =  file_get_contents($dir);
             $orr = json_decode($odata,true);
             $array = $orr[$url[1]];            
          }
          else
          { 
              $array = array();
          }

          echo json_encode(@$array);
    }
  break;

  case "getdatajson":
  //print_r($_POST);
  if(isset($_SESSION['user']['id']) || isset($_SESSION['cms']) && isset($_POST['tbl']) && check_csrf($csrf_got))
  {
    //echo "Inside";
      if(isset($_SESSION['user']['id']) && $_SESSION['user']['id']>0  )
      {
          $uid = @$_SESSION['user']['id'];
          $tbl = $_POST['tbl'];
          $db = new DATABASE;

          if(isset($_POST['id']) && is_numeric($_POST['id']) )
          {
              $id = addslashes($_POST['id']);
              $where = " id='$id' ";
              $where .= " limit 1";
          }
          else {
            $where = "status >= '0'";
          }

          $res = array();

//print_r($_POST);

          if(is_numeric($_POST['id']) &&  $_POST['id']!='0'){
              $sql = "SELECT * FROM $tbl WHERE $where ";
            //  echo $sql;
              $res = $db->query($sql);
            }

            if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0 ){
                $res[0]['uid'] = $_POST['uid'];
            }
            echo  json_encode(@$res[0]);

      }



  }
  else
  {
      echo "Login Expired! Please login again";
  }

  break;

case "records":
    if(isset($url[2])){
      switch($url[2]){
        case "customcase":
        $res  =  $db->query("SELECT COUNT(id) total FROM tablename ");
          echo (isset($res[0]['total'])) ? $res[0]['total'] : $res[0]['total'];
        break;

        case "default":
        $tbl = $url[2];
        $res  =  $db->query("SELECT COUNT(id) total FROM $tbl ");
          echo (isset($res[0]['total'])) ? $res[0]['total'] : $res[0]['total'];
        break;
      }
    }
    else{
      $err['error'] = "Invalid API CALL LEVEL 2";
      die(json_encode($err));
    }
break;

case "cmsrecords":
    if(isset($url[2])){
      switch($url[2]){
        case "customcase":
        $res  =  $db->query("SELECT COUNT(id) total FROM tablename ","CMS");
           if(count($res) > 0){
        echo (isset($res[0]['total'])) ? $res[0]['total'] : $res[0]['total'];
        }
        else
        {
          echo 0;
        }

        break;

        default:
        $tbl = $url[2];
        $res  =  $db->query("SELECT COUNT(id) total FROM $tbl ","CMS");
        if(count($res) > 0){
        echo (isset($res[0]['total'])) ? $res[0]['total'] : $res[0]['total'];
        }
        else
        {
          echo 0;
        }
        break;
      }
    }
    else{
      $err['error'] = "Invalid API CALL LEVEL 2";
      die(json_encode($err));
    }
break;
  
  case "test":
    $tbl = "users";
    $res = $db->query("SELECT * FROM $tbl WHERE 1");
    echo json_encode($res);
  break;


        default:
            if(isset($url[1]) && @$url[1]!=""){
              // Real API
              $tbl = $url[1];
              $tblexists = $db->tableExists($tbl); 
              if(!$tblexists){
                $err['message'] = "Table not Found!";
                $err['success'] = false;
                die(json_encode($err));
              }

              if(isset($url[2]) && is_numeric($url[2])){
                $id = $url[2];
                $res = $db->query("SELECT * FROM $tbl WHERE id='$id' LIMIT 1");
                echo $api->formatResult(@$res[0]);
              }
              else
              {
                $limit = (isset($_GET['limit'])) ? $_GET['limit'] : 10;
                $res = $db->query("SELECT * FROM $tbl limit $limit");
                echo $api->formatResult($res);
              }


              //---
            }
            else
            {
              $err['success'] = false;
              $err['error'] = "Invalid API CALL LEVEL 1";
              die(json_encode($err));
            }
        break;
}
?>
