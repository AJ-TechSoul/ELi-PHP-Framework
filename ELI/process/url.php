<?php
$defaut = "index.php";
$opage = "page.php";
$afterlogout = "index.php";
if(!isset($_GET['url'])){
	$_GET['url'] = "index.php";
}
$page = explode('/',str_replace(_BASEURL_,'',@$_GET['url']) );
//print_r($page);



switch ($page[0]){

        case "p":
    		include('post.php');
    		break;

        case "v":
    		include('view.php');
    		break;

        case "API":
        include('api.php');
        break;

        case "TEST":
          include(WWW.'test.php');
        break;

        case "Q-TOOL":
          include('qtool/index.php');
        break;


        case "CMS":
          $cms = new CMS;
          $cms->cmsnavigator($page);
        break;

        case "ADMIN":
        if(isset($_SESSION['admin']['id'])){
          include(WWW.'admin.php');
        }
        else {
          include(WWW.'login.php');
        }
        break;

        case "PAY":
        include(WWW.'pay.php');
        break;

        case "USER":
        include('user/url.php');
        break;


        case "LOGIN":
        include(WWW.'login.php');
        break;

        case "LOGOUT":
        unset($_SESSION['user']);
        session_destroy();
        if(!isset($_SESSION['user']))
        {
		      header('location: '.$afterlogout);
        }
		    break;



        default:

        $db = new DATABASE;
        //-------------- GET FREE PAGES FROM DB -------------
        $fp = $db->query("SELECT new_page FROM pages WHERE role LIKE 'FREE' ","CMS");
        if(count($fp) > 0){
          foreach ($fp as $f) {
            $freepages .=",".$f['new_page'];
          }
        }
        //---------------------------------------------------
        $PG = trim($page[0]);
        $avoid = explode(',',"www");
        $free = explode(',',"CMS,index.php,LOGIN,REGISTER,LOGOUT,FORGOTPASSWORD,forgotpassword.php,".$freepages);
        $pageurl = WWW.@$_GET['url'];
        // NEw Page Renaming
        if(isset($page[0])){
          $qp = $page[0];
          $sql = "SELECT * FROM pages WHERE actual_page LIKE '$qp' OR new_page LIKE '$qp' ";
          $np = $db->query($sql,"CMS");
          if(count($np) > 0){
                if($np[0]['new_page']!=$qp){
                  $npg = $np[0]['new_page'];
                  header("location: $npg");
                }
                else {
                  $pageurl = WWW.@$np[0]['actual_page'];
                }
            }
        }

        //


        if(isset($_GET['url']) && file_exists($pageurl) && $_GET['url']!="" )
        {
            if(isset($_SESSION['ssetoken'])){
              unset($_SESSION['ssetoken']);
            }

            if(!in_array($PG,$avoid))
            {
              if(in_array($PG, $free)){
                include($pageurl);
              }
              elseif(!in_array($PG, $free)){
                if(!isset($_SESSION['user']['id'])){
                    $_SESSION['referer'] = $page[0]."/".@$page[1];
                    echo "<script> window.location.href='"._BASEURL_."LOGIN'; </script>";
                }
                if(isset($_SESSION['user']['id'])){
                  include($pageurl);
                }
              }
              else {
                //header('location: '.$defaut);
                include(WWW.$opage);
              }

          }
            else
            {
                echo "403 ERROR - ACCESS DENIED";
            }
        }
        else
        {
            //echo "404, File doesn't Exist";
          // include(WWW.$defaut);
          // include(WWW.$opage);
          if(isset($_GET['url']) && $_GET['url']!=""){
            include(WWW.$opage);
          }
          else {
            include(WWW.$defaut);
          }
        }

}
?>
