<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');


$db = new DATABASE;
$sse = new SSE;

$csrf_got = @$_REQUEST['csrf'];
$validateEmail = false; // VALIDATE EMAIL (true/false)
$url = page(PAGE);
$validkey = false;


if(check_csrf($csrf_got)){
  $validkey = true;
}

// FREE CASES 
$casefree = explode(",","test");
if(isset($url[1]) && in_array($url[1],$casefree)){
  $validkey = true;
}
// =========

if(!$validkey){
  $err['error'] = "Token is not valid";
  $err['success'] = false;
  die(json_encode($err));
}

//print_r($url);
switch (@$url[1]){

    // dummy1
    case "test":
      $msx = '<select name="" label="All Users" multiple>';
      $msx .= $tmp->viewer($db->query("SELECT * FROM users"),'<option value="{{id}}">{{name}}</option>',false,true);
      $msx .= '</select>';

      $sse->seepreformat($msx);
    break;

    // dummy2
    case "tbldata":
      $sql = @$url[2];
      $tbtn = @$url[3];
      $sqlquery = base64_decode($sql);
      $token = $_SESSION["token"];
      $template = '<tr template="" id="row{{id}}">
              <td class="status-field"><span onclick="statusupdate(this)" data-value="{{status}}" data-id="{{id}}" data-action="users" class="status {{statustxt}}">{{statustxt}}</span> {{name}}</td><td>{{email}}</td><td>{{phone}}</td>
            <td class="elitable-actions">
              <span class="mdi mdi-pencil btn blue white-text small" onclick="editData(this,\'#editusersmod\',\'getdatajson\')" data-t="users" csrf="'.$token.'" data-id="{{id}}"></span>
              <span class="mdi mdi-close btn red white-text small" onclick="delrow(this,\'#row\')" csrf="'.$token.'"  data-id="{{id}}" data-action="users"></span>
            </td>
            </tr>';

         $templatex = str_replace('template=""',"",$template);   
      $tbldata = $template;  
      $tbldata .= $tmp->viewer($db->query($sqlquery),$templatex,false,true);

      $sse->seepreformat($tbldata);
    break;

    // Option Field
    case "optionfield":
      // Example: "v/optionfield/tblname/columns|id|true";
      $tbl = @$url[2];
      $col = explode("|",$url[3]);

      $val = (isset($col[1]))?$col[1] : 'id';
      $txt = (isset($col[0]))?$col[0] : 'id';

      $where = " ";
      if(isset($col[2]) AND $col[2] == "true"){
          $where .= " status='1' ";
      }
      else
      {
        $where .=" 1 ";
      }

      $xmsg = '<option value=""></option>';      
      $res =  $db->query("SELECT $val,$txt FROM $tbl
                          WHERE $where ");
      $template='<option value="{{'.$val.'}}">{{'.$txt.'}}</option>';
      $xmsg .= $tmp->viewer($res,$template,false,true);

      $data = array(
        'message' => base64_encode($xmsg),
        'timestamp' => time()
      );
      $sse->sendSSE($data);
      // Simulate a delay between updates
      // sleep(5);

    break;

    default:
      $pageurl = VIEW.end($url);
      if(file_exists($pageurl))
      {
        include($pageurl);
      }
      else
      {
        if(file_exists(VIEW.'404.php')){
          include(VIEW.'404.php');
        }
        else
        {
          echo "404 Error. Page not Found";
        }
      }
    break;
}
?>
