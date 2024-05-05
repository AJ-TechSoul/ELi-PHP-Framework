<?php

/**
 *  CLASS & FUNCTIONS CREATED BY AJAY KUMAR (TECHSOUL.IN)
 *  +91 9659040968
 *  techsoul4@gmail.com
 */

?>
<?php
CLASS TEMPLATE {
    function tpl($pagename)
    {
      $pg = VIEW.'inc/'.$pagename;  
        if(file_exists($pg))
        {
            include($pg);
        }
    }


    function subpg($pagename)
    {
      $pg = VIEW.'subpage/'.$pagename;  
        if(file_exists($pg))
        {
            include($pg);
        }
    }
    
    function widget($name,$type,$return="false")
    {
        $gpg = new DATABASE;
        $widget = $gpg->query("SELECT content  FROM `widgets` WHERE `name` LIKE '$name' AND `type` LIKE '$type'",'CMS');
        if(COUNT($widget)>0)
        {
            $content = base64_decode($widget[0]['content']);
        }
        if($return)
        {
            return $content;
        }
        else
        {
            echo $content;
        }
    }
    

function getDataURI($image, $mime = '') {
	return 'data: '.(function_exists('mime_content_type') ? mime_content_type($image) : $mime).';base64,'.base64_encode(file_get_contents($image));
}
    
    function img($imagepath)
    {
      if(file_exists($imagepath))
      {     
       echo  self::getDataURI($imagepath); 
      }
      else
      {
        $noimg = 'data:image/jpeg;base64,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';        
        echo  $noimg;  
      }
    }
    // added on 13 Jan,2021
    // 
    //  <option value="{{id}}" >{{name}}</option>
    // 
    function replace($template,$data_array,$templateadd=false,$return=true){
        if($templateadd){
            if(strpos($template,"<tr") !== false){
                echo str_replace("<tr","<tr template=''",$template);
            }
        }

        if(count($data_array) > 0){
            foreach ($data_array as $key => $value) {
                if ($value !== null) {
                    $template = str_ireplace("{{".$key."}}",$value,$template);
                }
                
            }
            if($return){
                return $template;
            }
            else
            {
                echo $template;
            }
        }
    }

    function viewer($data_array,$template,$templateadd=true,$return=false){
        // var_dump(strpos($template,"<tr"));
        // die();
        if($templateadd){
            if(strpos($template,"<tr") !== false){
                echo str_replace("<tr","<tr template=''",$template);
            }
        }
        if(count($data_array)>0){
            $output = "";
            foreach ($data_array as $data) {
                if($return){
                    // return 
                    $output .= self::replace($template,$data);
                }
                else
                {
                    // echo
                    echo self::replace($template,$data,false);
                }
                
            }
            if($return){
                return $output;
            }
        }
    }



    function inc($templatename,$pageid=0){
        $db = new DATABASE;
        $tmpl = $db->query("SELECT * FROM templates WHERE type LIKE '$templatename' AND pageid = '$pageid' ","CMS");
        if(count($tmpl) > 0){
            $content = base64_decode($tmpl[0]['content']);
            echo $content; 
        }

    }

    function content($pagename,$column="content",$pageid=0,$return=false){
        $db = new DATABASE;
        $tmpl = $db->query("SELECT $column FROM contents WHERE pagename LIKE '$pagename' AND pageid = '$pageid' ","CMS");
        if(count($tmpl) > 0){
            $content = ($column == "content") ? base64_decode($tmpl[0]['content']) : $tmpl[0][$column];
            if($return){
                return $content;
            }
            else
            {
                echo $content;
            }
        }

    }
}
?>