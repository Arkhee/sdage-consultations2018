<?php
class MyPageTemplate extends Template 
{ 
    function MyPageTemplate($template_name,$path="") 
    { 
    	if($path=="")
    		$path=dirname(__FILE__);
        $basedir = str_replace('\\', '/', $path) 
            .'/templates/'.$template_name; 
        $this->set_rootDir($basedir); 
    } 

    function getHtml($string) 
    { 
        return htmlentities($string, ENT_QUOTES, 'ISO-8859-1'); 
    } 

} 

class MyTableTemplate extends Template 
{ 
    function MyTableTemplate($template_name,$path="") 
    { 
       if($path=="")
    		$path=dirname(__FILE__);
        $basedir = str_replace('\\', '/', $path) 
             .'/templates/'.$template_name; 
        $basedir=str_replace("//","/",$basedir);
        $this->set_rootDir($basedir); 
    } 

    function getHtml($string) 
    { 
        return htmlentities($string, ENT_QUOTES, 'ISO-8859-1'); 
    } 

} 
?>