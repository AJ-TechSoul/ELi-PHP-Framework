<?php
CLASS SECURITY
{
  // Token is always changing but Result remains same. Good for creating CSRF or API Key or anything

  function encrypt($text,$return=true){

      if(strlen(trim($text)) > 0){
          $encodedtxt = base64_encode($text);
          $encodedtxt = str_replace("=","",$encodedtxt);
          $key = md5($encodedtxt);
          $rand = rand(1,strlen($encodedtxt));
          // echo strlen($encodedtxt);
          // echo $rand;
          $enc1 = substr_replace($encodedtxt, $key, $rand, 0);
          $enc2 = $enc1.md5($rand);
          if($return){
            return $enc2;
          }
          else
          {
            echo $enc2;
          }
      }
  }


  function decrypt($token,$return=true){

      if(isset($token) && strlen(trim($token)) > 0){
          $sn = -1;  
          $splitnumber = substr($token,-32);
          if(strlen($splitnumber)==32){
              $totallen = strlen($token) - 64;
              for ($i=0; $i <= $totallen; $i++) {
                 if($splitnumber == md5($i)){
                    $sn = $i;
                 }
              }

              if($sn>=0){

                $dtoken = substr($token, 0, -32);
                $key = substr($dtoken,$sn,32);

                $ftoken = str_replace($key,"",$dtoken);

                if(md5($ftoken) == $key){
                    $dec = base64_decode($ftoken);
                }
                else
                {
                  return false;
                }
              }

          }
          else
          {
            return false;
          }

          if($return){
            return @$dec;
          }
          else
          {
            echo @$dec;
          }
      }
  }


  function csrf()
    {
        if(!isset($_SESSION)){
          session_start();
          $_SESSION['formStarted'] = true;
        }
        if (!isset($_SESSION['token']))
        {
            $token = md5(date('Y-m-d-H-i').uniqid(rand(), TRUE));
            $_SESSION['token'] = $token;
        }
        return self::encrypt($_SESSION['token']);
    }

  function check_csrf($csrf_got)
    {
        if($_SESSION['token'] == self::decrypt($csrf_got) OR $_SESSION['token'] == $csrf_got)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }


}
?>
