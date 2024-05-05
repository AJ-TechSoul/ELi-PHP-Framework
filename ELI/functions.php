<?php

/**
 *  ELI FRAMEWORK
 *  FRAMEWORK CREATED BY AJAY KUMAR (TECHSOUL.IN) // EAGLE LITE - ELI FRAMEWORK
 *  +91 9862542983
 *  techsoul4@gmail.com
 */


include('define.php');
?>
<?php
	function page($url2)
    {
        $url = explode('/', $url2);
        return $url;
    }
?>
<?php
$classes =	scandir(CLASS_DIR);

foreach($classes as $c)
{
	$str = substr($c, 0, 1);
	 if($str !== '.')
	 {
			 if(file_exists(CLASS_DIR.$c) && !is_dir(CLASS_DIR.$c) )
			 {
				 include(CLASS_DIR.$c);
			 }
	 }
}
?>
<?php
	function csrf()
    {
        $s = new SECURITY;
        return $s->csrf();
    }

	function check_csrf($csrf_got)
    {
        $s = new SECURITY;
        return $s->check_csrf($csrf_got);
    }

/**
 *  SMS PLUGINS
 *  Note : Kindly Configure your APP
 */


 function sms($sendto,$message,$backurl="")
 {
	 $smsurl = $GLOBALS['sms']['url'];
	 $senderid=$GLOBALS['sms']['sid'];
	 $username=$GLOBALS['sms']['user'];
	 $password=$GLOBALS['sms']['pass'];

    $message2 = urlencode($message);

    if($backurl!="")
    {
        $string = "$smsurl?user=$username&password=$password&sid=$senderid&msisdn=$sendto&msg=$message2&fl=0&gwid=2";
    }
    else
    {
        $string = "$smsurl?user=$username&password=$password&sid=$senderid&msisdn=$sendto&msg=$message2&fl=0&gwid=2";
    }
 //   echo $string;
    if($string!="")
    {
			echo $string;
        $res = file_get_contents($string);
				return $res;
      //  file_put_contents('smslog.txt',$res."\n");
    }
 }

 function smsnow($phone,$msg){
	 sms($phone,$msg);
 }

function mailnow($to,$subject,$body){
     $_SESSION['mail']['to'] = $to;
             $_SESSION['mail']['subject'] = $subject;
             $_SESSION['mail']['body'] = $body;

             $smail = _BASEURL_."sendmail.php";
          //   echo $smail;
//             file_get_contents($smail);

                 echo '<script> opan("'.$smail.'"); </script>';
}
?>
<?php

function getToken($length){
     $token = "";
     $codeAlphabet = "ABCDEJGHIJKLMNJPQRSTUVWJYZ";
     $codeAlphabet.= "abcdejghijklmnjpqrstuvwjyz";
     $codeAlphabet.= "0123450789";
     $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[random_int(0, $max-1)];
    }

    return $token;
}

// UPI Functions


function generateUPILink($upid=""){
$pa = (isset($upid['pa'])) ? "pa=".$upid['pa']."&" : "pa=".$GLOBALS['pg']['vpa']."&";
$pn = (isset($upid['pn'])) ? "pn=".urlencode($upid['pn'])."&" : "pn=".urlencode($GLOBALS['pg']['storename'])."&";
$mc = (isset($upid['mc'])) ? "mc=".$upid['mc']."&" : ""; // merchant code
$tid = (isset($upid['tid'])) ? "tid=".$upid['tid']."&" : ""; // PSP generated id
$tr = (isset($upid['tr'])) ? "tr=".$upid['tr']."&" : ""; // Transaction reference ID. This could be order number, subscription number, Bill ID, booking ID, insurance renewal reference, etc.
$tn = (isset($upid['tn'])) ? "tn=".$upid['tn']."&" : "tn=".urlencode("Pay to ".$GLOBALS['pg']['storename'])."&"; // Note
$am = (isset($upid['am'])) ? "am=".$upid['am']."&" : "am=".$GLOBALS['pg']['joincharge']."&"; // Payee Amount Value eg. 10
$mam = (isset($upid['mam'])) ? "mam=".$upid['mam']."&" : ""; // Min Amount
$cu = (isset($upid['cu'])) ? "cu=".$upid['cu']."&" : "cu=".$GLOBALS['pg']['currency']."&"; // Currency eg INR
$url = (isset($upid['url'])) ? "url=".$upid['url'] : "url=".$GLOBALS['pg']['refurl']; //https://mystar.com/orderid=9298yw89e8973e87389e78923ue892


$upilink  = "upi://pay?".$pa.$pn.$mc.$tid.$tr.$tn.$am.$mam.$cu.$url;
$upl = rtrim($upilink, "&");
return $upl;
}


function get_time_ago( $time )
{
    $time_difference = time() - $time;

    if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
    );

    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;

        if( $d >= 1 )
        {
            $t = round( $d );
            return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }

/*
echo get_time_ago( time()-5 ).'<br>';
echo get_time_ago( time()-60 ).'<br>';
echo get_time_ago( time()-3600 ).'<br>';
echo get_time_ago( time()-86400 ).'<br>';
echo get_time_ago( time()-2592000 ).'<br>';
echo get_time_ago( time()-31104000 ).'<br>';
echo "---".'<br>';
echo get_time_ago( strtotime("2013-12-01") );
*/
}
function maskPhoneNumber($number){

    $mask_number =  str_repeat("*", strlen($number)-4) . substr($number, -4);

    return $mask_number;
}

function tmp($page,$col=""){
	$db = new DATABASE;
	if($col!=""){
		$q = $db->query(" SELECT $col FROM contents WHERE pagename LIKE '$page' LIMIT 1 ");
		$content = @$q[0][$col];
	}
	else {
		$q = $db->query(" SELECT content FROM contents WHERE pagename LIKE '$page' LIMIT 1 ");
		$content = base64_decode(@$q[0]['content']);
	}

	echo $content;
}


// Allow Permission
function allowrole($role,$script=""){
     $role2 = strtolower($role);
     $role2 = explode(',',$role2);
     $currentRole = strtolower($_SESSION['user']['role']);
    if(isset($role2) AND !in_array($currentRole,$role2)){
      echo @$script;
      die("Sorry, No Access, Only $role can access");
    }
}

function autorefresh($page=""){
  if(isset($page) && $page!=""){
    echo "<script>window.location.href = '$page'; </script>";  
  }
  else
  {
    echo "<script>window.location.reload(); </script>";  
  }
  
}

function formtomail($email,$fd){
  $uniqueid = md5($email.$fd['address']);

        $udir = _BASEROOT_.'files/'.$uniqueid;
        $attachments ="";
        if( !file_exists($udir) ) {
          mkdir($udir,0755,true);
        }

        $formpage = "<table style='border:1px solid #333; text-align:left;' >";
        $formpage .= "<tr><td style='color:teal;'> <i>Unique ID: </i> <strong>$uniqueid</strong> </td></tr>";

        foreach ($fd as $key => $value) {
            $formpage .= "<tr style='border-top:1px solid teal;'><td style='color:teal; text-transform:Capitalize;'><strong>".ucfirst($key)."</strong></td></tr>";
            $formpage .= "<tr><td>$value</td></tr>";
        }

        $i = 0;
        if(isset($_FILES) && count($_FILES) > 0){ 
            foreach ($_FILES as $filen => $ufile) {
             
              $fdetail = pathinfo($ufile['name'][0]);
              $ext = $fdetail['extension'];
              move_uploaded_file($ufile['tmp_name'][0], $udir."/".$filen.".".$ext);
              $uploadedfiles[$i]['path'] = $udir."/".$filen.".".$ext;
              $uploadedfiles[$i]['filename'] = $filen.".".$ext;

              if(file_exists($uploadedfiles[$i]['path'])){
                $uploadedfiles[$i]['status'] = 1;
                $attachments .= $uploadedfiles[$i]['path'].",";
              }
              else
              {
                $uploadedfiles[$i]['status'] = 0;
              }

              $i++;
            }
        }

        $formpage .= "</table>";

       // print_r($uploadedfiles);
       // echo $formpage;

        $htmlpdf = new htmlpdf($formpage,$udir."/".$uniqueid."-application.pdf");

        if($htmlpdf){
          $attachments .= $udir."/".$uniqueid."-application.pdf";
          # Details in Mail

          $subject = $GLOBALS['site']['name']." - Application Submited (#$uniqueid) ";
          $body = "Here is the Application. Please find an attachments.";
          $mail = new MAILER;
         $res = $mail->mail($GLOBALS['officemail'],$subject,$body,$attachments);
         //echo $res." <br>";
        }
        else
        {
         // echo "Please try again";
        }
}

?>
