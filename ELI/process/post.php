<?php
$csrf_got = @$_POST['csrf'];
$ValidateEmail = true; // VALIDATE EMAIL (true/false)
$v = new VALIDATOR;
$mail = new MAILER;
$db = new DATABASE;
$req = new REQUEST;
$postedpage = $page[1];


switch ($postedpage)
{
case "addusers":
        $tbl = "users";
        $formdata = $v->need("name,email,phone,password");
        $formdata['password'] = md5($formdata['password']);
        echo $req->addrow($tbl,$formdata,$required="name,email,phone,password",$unique="id",$successmsg="Successfully added data",$failmsg="Unable to add data",$duplicatemsg="Duplicate record found");
    break;  

case "editusers":
        $tbl = "users";
        $formdata = $v->need("id,name,email,phone");
        echo $req->updaterow($tbl,$formdata,$required="id",$unique="id",$successmsg="Successfully updated data",$failmsg="Unable to update data",$duplicatemsg="Duplicate record found");
    break;  


   case "google":
        $google = new GOOGLE;   
        $google->glogin($page[2]);
    break; 

	case "signup":
                if (isset($_POST) && $v->required_fields("name,email,phone,password,cpassword") &&
                    $v->name($_POST['name']) && 
                    $v->phone($_POST['phone']) && 
                    $v->email($_POST['email']) && 
                    $v->password($_POST['password']) && 
                    $v->confirmpass($_POST['password'],$_POST['cpassword']) && 
                    check_csrf($csrf_got))
                {
                    $tbl = "users";
                    $fd = $v->need('name,email,phone,password');
                    $fd['password'] = md5($fd['password']);                    
                    $sql = "SELECT COUNT(id) total FROM $tbl WHERE email LIKE '$fd[email]'  ";
                    $chk = $db->query($sql,"CMS");
                    if ($chk[0]['total'] > 0)
                    {
                        // data exist
                        $result = $db->update_row($tbl, $fd, " email LIKE '$fd[email]' ");
                    }
                    else
                    {
                        //new data
                        $result = $db->insert_row($tbl, $fd);
                    }
                    //$result = $db->insert_row($tbl,$fd);
                    if (!is_array($result['message']))
                    {
                        echo $result['message'];
                    }
                    else
                    {
                        echo "Changes already made before";
                    }
                }
                else
                {
                    echo "Please fill data properly";
                }
            break;

            case "login":

                //  print_r($_POST);
                if (isset($_POST['email']) && isset($_POST['password']) && check_csrf($csrf_got))
                {
                    $tbl = "users";
                    //$name = addslashes($_POST['phone']);
                    $email = addslashes($_POST['email']);
                    $pass = md5($_POST['password']);
                    $sql = "SELECT id,name,phone,email FROM $tbl  WHERE email LIKE '$email'  AND password='$pass' AND status='1' ";
                    // echo $sql;
                    $data = $db->query($sql);

                    if (COUNT($data) > 0)
                    {
                        $_SESSION['user'] = $data[0];
                        $_SESSION['user']['role'] = "members";
                        echo "Successfull Login";

                        //sleep(3);
                        // GET CURRENT PAGES
                        $refer = $_SERVER["HTTP_REFERER"];
                        $ref1 = explode('/', $refer);
                        settype($ref1, 'Array');
                        $ref = end($ref1);

                        $ref2 = explode('?', $ref);
                        settype($ref2, 'Array');
                        $urlquery = end($ref2);
                        $ref = str_replace($urlquery, '', $ref);
                        $ref = str_replace("?", '', $ref);
                        // echo $ref;
                        $redirect = _BASEURL_ . "home.php";
                        //  echo $redirect;
                        ////////////////////
                        echo "<script type='text/javascript'> setTimeout(function(){ window.location = '" . $redirect . "'; }, 1000); </script>";
                    }
                    else
                    {
                        echo "Invalid Login, Try Again!";
                        echo '<script type="text/javascript"> swal("Oops", "Invalid Login, Kindly Try Again!", "error"); </script>';
                    }

                }
                else
                {
                    echo "Invalid Login, Try Again!";
                    echo '<script type="text/javascript"> swal("Oops", "Invalid Login, Kindly Try Again!", "error"); </script>';
                }
            break;


     case "forgotpassword":
        if(isset($_POST['email']) && $v->required($_POST['email'],"Email") && $v->email($_POST['email']) && check_csrf($csrf_got))
        {
            $tbl = (isset($page[2])) ? $page[2] : "users";
            $email = @$_POST['email'];        
            $sql = "SELECT * FROM $tbl WHERE email LIKE '$email' ";
            $res = $db->query($sql);
            if(isset($res) && count($res) < 1){
                $res = $db->query($sql,"CMS");
            }
            if(count($res) > 0){
                $usr = @$res[0];
                //Email exists
                $token = md5($usr['id'].md5($email))."__".time();
                $link = _BASEURL_."RESET-PASSWORD/$tbl/?email=".$email."&token=".$token;

                $subject = "Password Reset Link - ".$GLOBALS['site']['name'];
                $body = "Hi, $usr[name],<br>There was a request to change your password!<br>If did not make this request, just ignore this email. Otherwise, please click the button below to change your password:<br><br> <a href='$link'>Reset Link</a><br><br>or<br><br><a href='$link'>$link</a><br><br><br>Team ".$GLOBALS['site']['name'];
                
                echo "Reset link sent Successfully";
                $mail = new MAILER;
                $m = $mail->mail($email,$subject,$body);
                //echo $m." Successully";
                
            }
            else
            {
                echo "Looks like you are not registered with us.";
            }

        }
        else
        {
            echo "Something not right. Kindly Fill Form all";
        }
    break;

    case "resetpassword":
        if(isset($_POST['tokenid']) && $v->required($_POST['password'],"Password") && $v->confirmpass($_POST['password'],$_POST['cpassword']) && check_csrf($csrf_got))
        {
            $id = base64_decode(base64_decode($_POST['tokenid']));
            $tbl = (isset($page[2])) ? $page[2] : "users";
            $email = @$_POST['email'];        
            $sql = "SELECT * FROM $tbl WHERE id LIKE '$id' ";
            $res = $db->query($sql);
            if(count($res) < 1){
                $res = $db->query($sql,"CMS");
            }

            if(count($res) > 0){
                $fd['password'] = md5($_POST['password']);
                $update = $db->update_row($tbl,$fd," id='$id' ");
                if($update['success']){
                    echo "Password Changed Successully";
                    sleep(1.5);
                    autorefresh("LOGIN");
                }
                else
                {
                    echo "Unable to reset password";
                }
            }
            else
            {
                echo "User doesn't Exist";
            }   
        }
        else
        {
            echo "Try again";
        } 
    break;


case "STATUS_UPDATE":
      if(isset($_POST) && isset($page[2]) && $v->required(@$_POST['id'],"ID") && $v->required(@$_POST['status'],"Status") ){
        $fd = $_POST;
        //print_r($_POST);
        $res = array();
        if($fd['status'] >= 1){
          // Make it zero
          $fd['status'] = 0;
          $res =  $db->update_row($page[2],$fd," id = $fd[id] ");          
        }
        else {
          // Make it 1
          $fd['status'] = 1;
          $res =  $db->update_row($page[2],$fd," id = $fd[id] ");        
        }

            if($res['success']){
                $res['status'] = $fd['status'];
            }
          else
            {
                $res['status'] = $_POST['status'];
            }


          if($res['success']){
            echo json_encode($res);  
          }
          else
          {
              $res['message'] = "Unable to update";
              echo json_encode($res);     
          }
          

      }
      else {
        echo "Try Again";
      }
    break;

    
    case "delrow":
        if(isset($_SESSION['user']['id']) && isset($page[2]) && isset($_POST['id']) &&  is_numeric($_POST['id']))
        {
            $id = $_POST['id'];
                       // DELETE
                        $db = new DATABASE;
                        $res = $db->delete_row($page[2],"id='$id' ");
                       // echo $res['status'];
                        echo $res['message'];
        }
        else
        {
            echo "Please make sure you are login!";
        }
    break;

    default:
        $purl = $page[1];
        $pageurl = VIEW . "$purl";
        if (file_exists($pageurl))
        {
            include ($pageurl);
        }
        else
        {
            echo "404 ERROR";
            //include(VIEW.'404.php');
            
        }
    break;
}

?>
