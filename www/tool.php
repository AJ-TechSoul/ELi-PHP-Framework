<?php
if(isset($_POST) && count($_POST) > 0){
	$vars = $_POST;
	$fields = explode(",", $vars['fieldnames']);
	$mods = @$vars['modal'];
	$modname = $vars['modulename'];
	$tbl = $vars['tablename'];
	$submittype = @$vars['submittype'];


// ==================
	// Table  -- Success
	$sql = "SELECT *, CASE WHEN status > 0 THEN 'active' ELSE 'inactive' END statustxt FROM ".$tbl." WHERE 1";

	$table = '<table class="edt"><thead><tr>';
	$trs = "";
	$l = 0;
	foreach($fields as $f){
		if($l==0){
			$trs.='<td class="status-field"><span onclick="statusupdate(this)" data-value="{{status}}" data-id="{{id}}" data-action="users" class="status {{statustxt}}">{{statustxt}}</span> {{'.$f.'}}</td>';
		}
		else
		{
			$trs .= '<td>{{'.$f.'}}</td>';
		}
		$table .= '<th>'.strtoupper($f).'</th>';
		$l++;	
	}
	$table .= '</tr></thead><tbody>';
	$table .= '<?php
				$token = $_SESSION["token"];
				$db = new DATABASE;
				$tmp->viewer($db->query("'.$sql.'"),\'<tr id="row{{id}}">
								'.$trs.'
							<td class="elitable-actions">
								<span class="mdi mdi-pencil btn blue white-text small" onclick="editData(this,\\\'#edit'.$tbl.'mod\\\',\\\'getdatajson\\\')" data-t="'.$tbl.'" csrf="\'.$token.\'" data-id="{{id}}"></span>
								<span class="mdi mdi-close btn red white-text small" onclick="delrow(this,\\\'#row\\\')" csrf="\'.$token.\'"  data-id="{{id}}" data-action="'.$tbl.'"></span>
							</td>
							</tr>\');
				?>';
	$table .= '</tbody></table>';


// ==================
	// Modals
if(@$mods['add']){

$modadd = "";

$modadd .= '<!--Add '.$tbl.'-->
			<div id="add'.$tbl.'mod" class="modal">
				<div class="modal-container r-container">
					<i class="mdi mdi-close modalclosebtn"></i>
					<div class="modal-header"><h5 class="pad-b-2">Add '.$modname.'</h5></div>
						<div class="modal-body pad-t-2">
							<form action="p/add'.$tbl.'" id="'.$tbl.'addfrm" method="POST" enctype="multipart/form-data">
								<section class="">';


foreach($fields as $f){
	$modadd .= '<input type="text" name="'.$f.'" label="'.strtoupper($f).'" >';
}
									
$modadd .= '<span class="btn small clr mdi mdi-check radius-1" onclick="'.$submittype.'(\'#'.$tbl.'addfrm\')" >SAVE</span>
										<input type="hidden" name="csrf" value="<?php echo $_SESSION[\'token\'] ?>">
								</section>			
							</form>
						</div>
					</div>
				</div>';


//  POST section
$postadd = '
case "add'.$tbl.'":
        $tbl = "'.$tbl.'";
        $formdata = $v->need("'.@$vars['fieldnames'].'");
        echo $req->addrow($tbl,$formdata,$required="'.@$vars['fieldnames'].'",$unique="id",$successmsg="Successfully added data",$failmsg="Unable to add data",$duplicatemsg="Duplicate record found");
    break;	';
	
}


if(@$mods['edit']){

$modedit = "";

$modedit .= '<!--Edit '.$tbl.'-->
			<div id="edit'.$tbl.'mod" class="modal">
				<div class="modal-container r-container">
					<i class="mdi mdi-close modalclosebtn"></i>
					<div class="modal-header"><h5 class="pad-b-2">Update '.$modname.'</h5></div>
						<div class="modal-body pad-t-2">
							<form action="p/edit'.$tbl.'" id="'.$tbl.'editfrm" method="POST" enctype="multipart/form-data">
								<section class="">';


foreach($fields as $f){
	$modedit .= '<input type="text" name="'.$f.'" label="'.strtoupper($f).'" >';
}
									
$modedit .= '<span class="btn small clr mdi mdi-check radius-1" onclick="'.$submittype.'(\'#'.$tbl.'editfrm\')" >SAVE</span>
<input type="hidden" name="id" value="">
										<input type="hidden" name="csrf" value="<?php echo $_SESSION[\'token\'] ?>">
								</section>			
							</form>
						</div>
					</div>
				</div>';	

// POST Section

$postedit = '
case "edit'.$tbl.'":
        $tbl = "'.$tbl.'";
        $formdata = $v->need("id,'.@$vars['fieldnames'].'");
        echo $req->updaterow($tbl,$formdata,$required="id",$unique="id",$successmsg="Successfully updated data",$failmsg="Unable to update data",$duplicatemsg="Duplicate record found");
    break;	';			



}





// ==================
}
else {
	echo "Please Fill";
}

?>

<!DOCTYPE html>
<html lang="en" smooth>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Eli Tools v0.1</title>
	<style>
		fieldset { border:none; padding:0px; margin:0px;inset:0px; margin:1em 0px; }
		fieldset legend { padding:.5em 1em; background:rgba(0, 0, 0, 1.0); border-radius:var(--radius); color:white; margin:0.2em; margin-bottom:1em; }
		.list-group { min-width:250px }
		textarea.copy { min-height:300px!important; font-family:monospace; text-align:left; text-indent:.5em; white-space:pre-line; background-color:black; color:white; font-size: 0.7em!important; position:relative; }
	</style>
</head>
<body>

<main class="g la1 ma1 s1 xs1 ggap-5 pad-5">
<ul class="list-group w-p100">
	<li><a href="tool.php"><strong>Tool</strong></a></li>
</ul>		

<article>
<h6><strong>EliTable + Modal Generator + POST Generator</strong></h6>
<br>

<form action="#resut" method="POST">
	<!-- Field Table -->
<fieldset>
	<legend><strong>Fields & Names</strong></legend>
	<div class="input-group-h">
		<input type="text" name="tablename" label="Table Name" value="<?php echo @$_POST['tablename'] ?>" required>
		<input type="text" name="modulename" label="Module Name" value="<?php echo @$_POST['modulename'] ?>" required>
	</div>
	<textarea name="fieldnames"  label="Field Names comma delimiter"><?php echo @$_POST['fieldnames'] ?></textarea>
</fieldset>

<fieldset>
	<legend><strong>Modals</strong></legend>
	<div class="input-group-h">
		<input type="checkbox" name="modal[add]" value="1" label="Add Modal" />
		<input type="checkbox" name="modal[edit]" value="1" label="Edit Modal" />
	</div>
	<div class="input-group-h">
	<input type="radio" name="submittype" value="modalSubmit" label="modalSubmit">	
	<input type="radio" name="submittype" checked value="validSubmit" label="validSubmit">	
	</div>
</fieldset>

<input type="submit" class="btn big default" value="Generate" >


</form>

<fieldset id="resut">
	<legend>Result</legend>

<strong>DataTable</strong>
<textarea class=" black radius pad copy" onclick="copy(this)">
	<?php echo htmlentities(@$table) ?>
</textarea>
<hr class="margin-2">
<strong>Modal Add</strong>
<div class="input-group-h">

<textarea class=" black radius pad copy" onclick="copy(this)">
	<?php echo htmlentities(@$modadd) ?>
</textarea>
<textarea class=" black radius pad copy" label="post.php" onclick="copy(this)">
	<?php echo htmlentities(@$postadd) ?>
</textarea>
	
</div>

<hr class="margin-2">
<strong>Modal Edit</strong>

<div class="input-group-h">

<textarea class=" black radius pad copy" onclick="copy(this)">
	<?php echo htmlentities(@$modedit) ?>
</textarea>
<textarea class=" black radius pad copy" label="post.php" onclick="copy(this)">
	<?php echo htmlentities(@$postedit) ?>
</textarea>

</div>


</fieldset>

</article>

</main>	
	
	<script src="https://cdn.jsdelivr.net/gh/aj-techsoul/elicss@4.0.2/eli.min.js" ></script>
	<script>
		function copy(field){

  // Get the text field
  var copyText = field;

  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

  // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);
  
  // Alert the copied text
  // Swal.fire("Copied");

		}

	</script>
</body>
</html>