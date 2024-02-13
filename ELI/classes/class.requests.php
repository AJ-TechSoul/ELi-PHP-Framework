<?php  

class REQUEST {

	function csrftoken($return=false){
		if($return){
			return @$_SESSION['token'];
		}
		else
		{
			echo @$_SESSION['token'];
		}
	}

	function is_loggedin($data=false){
		if(isset($_SESSION['user']) && is_numeric($_SESSION['user']['id'])){
			if(!$data){
			 	return true;
			}
			else
			{
				return $_SESSION['user'];
			}
		}
		else
		{
			if(!$data){
			 	return false;
			}
			else
			{
				return false;
			}
		}
	}


	function get($variable=""){
		if(isset($variable) && $variable!=""){
			return @$_GET[$variable];
		}
		else
		{
			return @$_GET;
		}
	}

	function post($variable=""){
		if(isset($variable) && $variable!=""){
			return @$_POST[$variable];
		}
		else
		{
			return @$_POST;
		}
	}

	function session($variable=""){
		if(isset($variable) && $variable!=""){
			return @$_SESSION[$variable];
		}
		else
		{
			return @$_SESSION;
		}
	}


	function session_set($array=""){
		if(isset($array) && is_array($array)){
			return $_SESSION[$array];
		}
		else
		{
			return false;
		}
	}
	
	
	function validate($formdata,$requiredfields=""){
		$ret = true;
		$v = new VALIDATOR;
		$requiredf = array();
		$requiredf = explode(",",$requiredfields);	
		//
		if(count($requiredf) > 0){
			$fdkeys = array_keys($formdata);
			foreach($requiredf as $rq){
				if(!in_array($rq,$fdkeys)){
					die("Please fill ".$rq);
				}
			}
		}
		//
		if(isset($formdata) && is_array($formdata) && count($formdata) > 0){
			foreach($formdata as $k=>$v1){
				if(in_array($k,$requiredf)){
					// Required
					if($v->required($v1,$k)){
						// required active
						if(method_exists($v, $k)){
							// Exist check for validation
							$v->$k($v1);	
						}
					}
				}
				else
				{
					// Not Required
					if(isset($v1) && strlen($v1) > 0){
						// required active
						if(method_exists($v, $k)){
							// Exist check for validation
							$v->$k($v1);
						}
					}
				}
			}
		}
		return $ret;
	}

	function login($tbl,$formdata,$redirecttopage="home.php",$session="user"){		
		if(is_array($formdata) && count($formdata)> 0){			
			$fd_a = array();
			$fd_type = array();
			foreach($formdata as $k=>$v){
				$fd_type[$k] = gettype($v);
				switch(gettype($v)){
					case "integer":
						$fd[] = " $k = '$v' ";
					break;
					default:						
						$fd[] = " $k LIKE '$v' ";							
					break;
				}
			}
		
		$where = implode(" AND ",$fd);
		$db = new DATABASE;
		$chk = $db->query("SELECT * FROM $tbl WHERE $where");
		if(count($chk) > 0){
			// Data added to session;
			$_SESSION[$session] = @$chk[0];
			$ret['success'] = true;
			$ret['message'] = "Login Successfully";			
		}
		else
		{
			$ret['success'] = false;
			$ret['message'] = "Invalid Login, Kindly Sign Up!";
		}
		///////////////////////////
		if($ret['success']){
			echo $ret['message'];
			echo ($redirecttopage) ? "<script> setTimeout(function(){
				window.location.href = '$redirecttopage';
			},1500); </script>" : "";
		}
		else
		{
			echo $ret['message'];
		}
		///////////////////////////
		}		
		else
		{
			return false;
		}
	}

	function addrow($tbl,$formdata,$required="",$unique=false,$successmsg="Successfully added data",$failmsg="Unable to add data",$duplicatemsg="Duplicate record found",$sqlquery="",$return=true){

		$v = new VALIDATOR;
		$db = new DATABASE;
		
		$unique = ($unique) ? explode(",",$unique): "";

		$valid = self::validate($formdata,$required);
		if($valid){
		$keys = array_keys($formdata);
		$keys = implode(",",$keys);

		$where = "";

		if(is_array($unique) && count($unique) > 0){
			foreach($unique as $u){
				switch(gettype(@$formdata[$u])){
					case "integer":
						$fd[] = " $u = '".$formdata[$u]."' ";
					break;
					default:						
						$fd[] = " $u LIKE '".@$formdata[$u]."' ";
					break;
				}
			}
			if(isset($fd) && is_array($fd) && count($fd) > 0){
			$where = implode(" AND ",$fd);
				$where = " WHERE ".$where;
			}
		}
		$sql = "SELECT * FROM $tbl $where "; 
		$chk = $db->query($sql);

		if(count($chk)>0){
			// Data Exist
			if($return){
				$ret = array();
				$ret['success'] = false;
				$ret['result'] = $duplicatemsg;
				$ret['data'] = array();
				return json_encode($ret);
			}
			else
			{
				return $duplicatemsg;
			}
		}
		else
		{
			$res = $db->insert_row($tbl,$formdata);
			if($res['success']){
				if($return){
					$iid = @$res['id'];
					if(strlen($sqlquery) > 0 ){ $sqlquery = str_replace("{{id}}",$iid,$sqlquery); }
					$dta = (strlen($sqlquery) > 0) ? $db->query($sqlquery) : $db->query("SELECT *,
					CASE WHEN status > 0 THEN 'active' ELSE 'inactive' END AS status_text 
					FROM $tbl WHERE id='$iid' ");
					$ret = array();
					$ret['success'] = $res['success'];
					$ret['result'] = $successmsg;
					$ret['data'] = @$dta[0];
					return json_encode($ret);
				}
				else
				{
					return $successmsg;
				}
			}
			else
			{
				if($return){
					$ret = array();
					$ret['success'] = $res['success'];
					$ret['result'] = $failmsg;
					$ret['data'] = array();
					return json_encode($ret);
				}
				else
				{
					return $failmsg;
				}
				
			}
		}
		}
		else
		{
			return "please fill data properly";
		}

	}

	function updaterow($tbl,$formdata,$required="",$unique=false,$successmsg="Successfully updated data",$failmsg="Unable to update data",$nodatamsg="Sorry, Record doesn't exist",$sqlquery="",$return=true){
		$v = new VALIDATOR;
		$db = new DATABASE;
		
		$unique = ($unique) ? explode(",",$unique): "";

		$valid = self::validate($formdata,$required);
		if($valid){
		$keys = array_keys($formdata);
		$keys = implode(",",$keys);

		$where = "";

		if(count($unique) > 0){
			foreach($unique as $u){
				switch(gettype(@$formdata[$u])){
					case "integer":
						$fd[] = " $u = '".$formdata[$u]."' ";
					break;
					default:						
						$fd[] = " $u LIKE '".@$formdata[$u]."' ";
					break;
				}
			}
			$where = implode(" AND ",$fd);
		}

		
		$chk = $db->query("SELECT * FROM $tbl WHERE $where ");

		if(count($chk)>0){
			// Data Exist
			$id = (isset($formdata['id'])) ? $formdata['id'] : @$chk[0]['id'];
			$where .= (isset($where) && strlen($where)>0) ? " AND id='$id' ":"";
			$res = $db->update_row($tbl,$formdata,$where);
			if($res['success']){
				if($return){
					if(strlen($sqlquery) > 0 ){ $sqlquery = str_replace("{{id}}",$id,$sqlquery); }
					$dta = (strlen($sqlquery) > 0) ? $db->query($sqlquery) : $db->query("SELECT *,CASE WHEN status > 0 THEN 'active' ELSE 'inactive' END AS status_text  FROM $tbl WHERE id='$id' ");
					$ret = array();
					$ret['success'] = $res['success'];
					$ret['result'] = $successmsg;
					$ret['data'] = @$dta[0];
					return json_encode($ret);
				}
				else
				{
					echo $successmsg;
				}
			}
			else
			{
				if($return){
					$dta = (strlen($sqlquery)  > 0) ? $db->query($sqlquery) : $db->query("SELECT *,CASE WHEN status > 0 THEN 'active' ELSE 'inactive' END AS status_text  FROM $tbl WHERE id='$id' ");
					$ret = array();
					$ret['success'] = $res['success'];
					$ret['result'] = $failmsg;
					$ret['data'] = @$dta[0];
					return json_encode($ret);
				}
				else
				{
					return $failmsg;
				}
			}
		}
		else
		{
			// Data not exists
			return $nodatamsg;
		}
		}
		else
		{
			return "please fill data properly";
		}

	}
}

?>