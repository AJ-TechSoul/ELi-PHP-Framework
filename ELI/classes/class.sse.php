<?php  
 CLASS SSE
 {
    function sendSSE($data){
    $url = page(PAGE); 
    $pg = @$url[1];

    if(isset($_SESSION['ssetoken'][$pg]) && array_key_exists(md5($data['message']), $_SESSION['ssetoken'][$pg]) ){
      $xdata = $_SESSION['ssetoken'][$pg][md5($data['message'])];
      $xdata['status'] = "old";
      echo "data: " . json_encode($xdata) . "\n\n";
    }
    else
    {
      $data['status'] = "new";
      echo "data: " . json_encode($data) . "\n\n";
      $_SESSION['ssetoken'][$pg] = array();
      $_SESSION['ssetoken'][$pg][md5($data['message'])] = @$data;
    }

    flush();
    }

    function seepreformat($xmsg){
      $data = array(
        'message' => base64_encode($xmsg),
        'timestamp' => time()
      );

     self::sendSSE($data); 
    }
 } 

?>