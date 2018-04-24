<?php
// Version : 0.0.4
define("MAXINDENT", 10);
define("TAB", '|  ');
define("LOG_MAX_SIZE", 1000000);
if(!defined("BR")) define("BR","<br />\n");
$traceactive=true;

/*
 * Insertion phpmailer
 */
use PHPMailer\PHPMailer\PHPMailer;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
if(file_exists(__DIR__."/../local.lib/phpmailer"))
{
	require_once(__DIR__.'/../local.lib/phpmailer/src/PHPMailer.php');
	require_once(__DIR__.'/../local.lib/phpmailer/src/SMTP.php');
	require_once(__DIR__.'/../local.lib/phpmailer/src/POP3.php');
	require_once(__DIR__.'/../local.lib/phpmailer/src/OAuth.php');
}
if(file_exists(__DIR__."/../local.lib/html2pdf"))
{
		require_once(__DIR__."/../local.lib/html2pdf/vendor/autoload.php");
}


if(!function_exists("xdebug_break"))
{
	function xdebug_break()
	{
		return true;
	}
}
class Tools
{
	public static $logpath="";
	public static $logfile="";
	public static $logindent=1;
	
	public static $pathinfo=null;
	public static $baseurl;
	public static $basescripturl;
	public static $_logfile;
	public static $_logfilereset=false;
	public static $_logfileactive=false;
	var $_indent;
	
	
	const ACCENT_STRINGS_FULL =    'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ';
	const NO_ACCENT_STRINGS_FULL = 'SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy';
	//const ACCENT_STRINGS = 'šœžµßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ';
	const ACCENT_STRINGS = 'sozusàaâaäaaçèéêëeiiîïionòóôooouuûuyy';
	const NO_ACCENT_STRINGS = 'sozusaaaaaaaceeeeeiiiiionoooooouuuuyy';
	const ACCENT_ONLY_SSTRINGS = 'àâäçèéêëîïòóôû';
	
	
	static public function getObjectVars($array)
	{
		if(is_object($array) && !self::isArray($array)) return get_object_vars($array);
		if(is_a($array,"ArrayObject"))
		{
			return $array->getArrayCopy();
		}
	}
	
	static public function arrayShift(&$array)
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			$array=$array->getArrayCopy();
			return array_shift($array);
		}
		return array_shift($array);
	}
	
	static public function arrayPop(&$array)
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			$array=$array->getArrayCopy();
			return array_pop($array);
		}
		return array_pop($array);
	}
	static public function arrayKeys($array,$search_value=null,$strict = false)
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			if(!is_null($search_value)) return array_keys($array->getArrayCopy(),$search_value,$strict);
			else return array_keys($array->getArrayCopy());
		}
		if(is_null($search_value)) return array_keys($array);
		return array_keys($array,$search_value,$strict);
	}
	
	static public function arrayKeyExists($key,$array)
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			return array_key_exists($key,$array->getArrayCopy());
		}
		return array_key_exists($key,$array);
	}
	
	static public function arrayImplode($glue,$pieces)
	{
		if(!Tools::isArray($pieces)) return false;
		if(is_a($pieces,"ArrayObject"))
		{
			return implode($glue,$pieces->getArrayCopy());
		}
		return implode($glue,$pieces);
	}
	
	
	static public function arraySearch($needle, $array, $strict=false)
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			return array_search($needle, $array->getArrayCopy(), $strict);
		}
		return array_search($needle, $array, $strict);
	}
	
	static public function arraySlice ( $array , $offset , $length = NULL , $preserve_keys = false )
	{
		if(!Tools::isArray($array)) return false;
		if(is_a($array,"ArrayObject"))
		{
			if(is_null($length)) return array_slice($array->getArrayCopy() , $offset);
			return array_slice($array->getArrayCopy() , $offset , $length, $preserve_keys);
		}
		if(is_null($length)) return array_slice($array , $offset);
		return array_slice($array , $offset , $length, $preserve_keys);
	}
	
	static public function arrayIntersect()
	{
		if(func_num_args()<2) return false;
		$liste=func_get_args();
		foreach($liste as $key => $curTab)
		{
			Tools::assertRealArrayType($curTab);
			$liste[$key]=$curTab;
		}
		
		$resultat=array();
		//echo "Merge de la liste: <pre>".print_r($liste,true)."</pre>";
		foreach($liste as $curTab)
		{
			//echo "Merge de : <pre>".print_r($resultat,true)."</pre> et <pre>".print_r($curTab,true)."</pre>";
			if(!is_array($curTab)) return false;
			$resultat=array_intersect($resultat,$curTab);
			//echo "Resultat intermediaire : <pre>".print_r($resultat,true)."</pre>";
		}
		return $resultat;
	}
	
	
	static public function arrayMerge()
	{
		if(func_num_args()<2) return false;
		$liste=func_get_args();
		foreach($liste as $key => $curTab)
		{
			Tools::assertRealArrayType($curTab);
			$liste[$key]=$curTab;
		}
		
		$resultat=array();
		//echo "Merge de la liste: <pre>".print_r($liste,true)."</pre>";
		foreach($liste as $curTab)
		{
			//echo "Merge de : <pre>".print_r($resultat,true)."</pre> et <pre>".print_r($curTab,true)."</pre>";
			if(!is_array($curTab)) return false;
			$resultat=array_merge($resultat,$curTab);
			//echo "Resultat intermediaire : <pre>".print_r($resultat,true)."</pre>";
		}
		return $resultat;
	}
	
	static public function assertRealArrayType(&$array)
	{
		if(is_a($array,"ArrayObject"))
		{
			$array=$array->getArrayCopy();
		}
	}
	
	
	static public function inArray($needle,$haystack,$strict=false)
	{
		if(is_array($haystack)) return in_array($needle,$haystack,$strict);
		if(is_a($haystack,"ArrayObject")) 
		{
			$arrayCopy=$haystack->getArrayCopy();
			return in_array($needle,$arrayCopy,$strict);
		}
		return false;
	}
	
	static public function arrayValues($array)
	{
		if(is_array($array)) return array_values($array);
		if(is_a($array,"ArrayObject"))
		{
			$arrayCopy=$array->getArrayCopy();
			return array_values($arrayCopy);;
		}
		return false;
	}
	
	
	static public function arrayObjectToArrayData(&$array)
	{
		if(is_a($array,"ArrayObject"))
		{
			$array=$array->getArrayCopy();
		}
	}
	
	static public function arrayUnique($array)
	{
		if(is_array($array)) return array_unique($array);
		if(is_a($array,"ArrayObject")) return array_unique($array->getArrayCopy());
		return false;
	}
	
	static public function isArrayObject($arr)
	{
		return is_a($arr,"ArrayObject");
	}
	
	static public function isArray($arr)
	{
		if(is_array($arr)) return true;
		if(is_a($arr,"ArrayObject")) return true;
		return false;
	}
	
	static public function isInt($val)
	{
		if(is_int($val)) return true;
		if(is_string($val))
		{
			return (strval(intval($val))===$val);
		}
		return false;
	}
	
	static public function accentToRegex($text,$fullaccents=false)
	{
		if($fullaccents)
		{
			return Tools::accentToRegexFull($text);
			$from = str_split(utf8_decode(self::ACCENT_STRINGS_FULL));
			$to   = str_split(strtolower(self::NO_ACCENT_STRINGS_FULL));
			$accent_only=str_split(utf8_decode(self::ACCENT_STRINGS_FULL));
		}
		else
		{
			$from = str_split(utf8_decode(self::ACCENT_STRINGS));
			$to   = str_split(strtolower(self::NO_ACCENT_STRINGS));
			$accent_only=str_split(utf8_decode(self::ACCENT_ONLY_SSTRINGS));
		}
		$text = utf8_decode($text);
		$regex = array();
		$html=array();
		/*
		$indexhtml=0;
		foreach($from as $key => $value)
		{
			if(strpos($text,$from[$key])!==false)
			{
				$html[$indexhtml]=htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1");
				$text=str_replace($from[$key],"##PAROUV####CODEHTML".$indexhtml."##|".$from[$key]."##PARFER##",$text);
				$indexhtml++;
			}
		}
		 * 
		 */
		foreach ($to as $key => $value)
		{
			/*
			if (isset($regex[$value]))
			{
				if(strpos($regex[$value],$from[$key])===false)
				{
					$regex[$value] .= $from[$key];
				}
				else
				{
					continue;
				}
			}
			else
			{
				$regex[$value] = $value;
			}
			 * 
			 */
			$regex[$value]=$to[$key];
			
			if(!isset($html[$to[$key]])) $html[$to[$key]]=$to[$key]."";
			if(!isset($addedHtml)) $addedHtml=array();
			if(!isset($addedHtml[$to[$key]])) $addedHtml[$to[$key]]=array($to[$key]);
			
			if(!in_array($from[$key],$addedHtml[$to[$key]]))
			{
				$addedHtml[$to[$key]][]=$from[$key];
			}
			else
			{
				continue;
			}
			$html[$to[$key]].=(htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1")!="?"?("|".htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1")):"")
							.($from[$key]!="?"?("|".$from[$key]):"");
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
		}

		
		foreach($accent_only as $valchar)
		{
			if(strpos($text,$valchar)!==false)
			{
				$text = str_replace($valchar,"##PAROUV##".$valchar."|".htmlentities ( $valchar , ENT_COMPAT,"iso-8859-1")."##PARFER##",$text);
			}
		}
		
		foreach ($regex as $rg_key => $rg)
		{
			//$text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
			$text = preg_replace("/_{$rg_key}_/", "##PAROUV##".$html[$rg_key]."##PARFER##", $text);
		}
		
		/*
		foreach($html as $indexhtml => $valhtml)
		{
			$text=str_replace("##CODEHTML".$indexhtml."##",$valhtml,$text);
		}
		*/
		// Gestion des caractères spéciaux
		$text=str_replace("(","\\(",$text);
		$text=str_replace(")","\\)",$text);
		$text=str_replace("*","(.*)",$text);
		$text=str_replace("##PAROUV##","(",$text);
		$text=str_replace("##PARFER##",")",$text);
		$text = utf8_encode($text);
		
		return $text;
	}

	public static function evalKeyInArray($key,$array)
	{
		$parts = explode('.', $key);
		if(!count($parts)) return false;
		foreach ($parts as $part) {
			if(!isset($array[$part])) return false;
			$array = $array[$part];
		}
		return $array;
	}
	
	public static function initClasse($ctlname)
	{
		return new $ctlname();
		//apc_delete($ctlname);
		if(function_exists("apc_fetch"))
		{
			if(apc_exists($ctlname))
			{
				$tmp = (apc_fetch($ctlname));
				if(method_exists($tmp,"wakeUp"))
				{
					$tmp->wakeUp();
					return $tmp;
				}
			}
		}
		$tmp=new $ctlname();
		if(function_exists("apc_fetch"))
		{
			apc_store($ctlname,($tmp));
		}
		return $tmp;
	}
	
	static public function accentToRegexFull($text)
	{
		$from = str_split(utf8_decode(self::ACCENT_STRINGS_FULL));
		$to   = str_split(strtolower(self::NO_ACCENT_STRINGS_FULL));
		$text = utf8_decode($text);
		$regex = array();
		$html=array();
		/*
		$indexhtml=0;
		foreach($from as $key => $value)
		{
			if(strpos($text,$from[$key])!==false)
			{
				$html[$indexhtml]=htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1");
				$text=str_replace($from[$key],"##PAROUV####CODEHTML".$indexhtml."##|".$from[$key]."##PARFER##",$text);
				$indexhtml++;
			}
		}
		 * 
		 */
		foreach ($to as $key => $value)
		{
			if (isset($regex[$value]))
			{
				$regex[$value] .= $from[$key];
			}
			else
			{
				$regex[$value] = $value;
			}
			if(!isset($html[$to[$key]])) $html[$to[$key]]=$to[$key]."";
			$html[$to[$key]].=(htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1")!="?"?("|".htmlentities ( $from[$key] , ENT_COMPAT,"iso-8859-1")):"")
							.($from[$key]!="?"?("|".$from[$key]):"");
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
		}

		foreach ($regex as $rg_key => $rg)
		{
			//$text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
			$text = preg_replace("/_{$rg_key}_/", "##PAROUV##".$html[$rg_key]."##PARFER##", $text);
		}
		
		/*
		foreach($html as $indexhtml => $valhtml)
		{
			$text=str_replace("##CODEHTML".$indexhtml."##",$valhtml,$text);
		}
		*/
		// Gestion des caractères spéciaux
		$text=str_replace("(","\\(",$text);
		$text=str_replace("*","(.*)",$text);
		$text=str_replace("##PAROUV##","(",$text);
		$text=str_replace("##PARFER##",")",$text);
		$text = utf8_encode($text);
		
		return $text;
	}

	
	
	public static function ObjectToArray($obj)
	{
		if(is_a($obj,"ArrayObject"))
		{
			return $obj;
		}
		static $profondeur=-1;
		$profondeur++;
		if(Tools::isArray($obj) && isset($obj["__o2a"]) && $obj["__o2a"]=="1") 
		{
			$profondeur--;
			return $obj;
		}
		if(is_object($obj)) $obj = (array) $obj;
		if(Tools::isArray($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = Tools::ObjectToArray($val);
			}
			//$new["__o2a"]="1";
		}
		else $new = $obj;
		$profondeur--;
		if($profondeur==0 && Tools::isArray($new))
		{
			$new["__o2a"]="1";
		}
		return $new;       
	}
	
	
	public static function strStartsWith($haystack, $needle)
	{
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	public static function strEndsWith($haystack, $needle)
	{
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}
	
	public static function StrPosA($haystack, $needle, $offset=0)
	{
		if(!Tools::isArray($needle)) $needle = array($needle);
		foreach($needle as $query)
		{
			$pos=strpos($haystack, $query, $offset);
			if( $pos !== false) return $pos; // stop on first true result
		}
		return false;
	}
		
	
	public static function SysLoadAvg()
	{
		if(!function_exists("sys_getloadavg")) return array(0,0,0);
		else return sys_getloadavg();
	}
	
	public static function HookExec($hookName,$hookParams)
	{
		if(class_exists("adm_hook") && method_exists("adm_hook","Exec"))
		{
			return adm_hook::Exec($hookName,$hookParams);
		}
		return false;
	}
	
	public static function DirCacheFiles($masque="")
	{
		$liste=Tools::ScanFolder(CACHE_FOLDER);
		if($liste!==false)
		{
			foreach($liste as $key=>$val)
			{
				$liste[$key]=CACHE_FOLDER.$val;
			}
		}
		if($masque!="")
		{
			$listemasquees=array();
			foreach($liste as $curfile)
			{
				if(strpos(basename($curfile),$masque)!==false)
				{
					$listemasquees[]=$curfile;
				}
			}
			return $listemasquees;
		}
		return $liste;
	}
	
	
	public static function ListeVersTable($listeTxt,$separateur=",")
	{
		$arrTable=array();
		$listeTxt=trim($listeTxt);
		if($listeTxt!="")
		{
			$arrTypes=explode($separateur,$listeTxt);
			if(Tools::isArray($arrTypes) && count($arrTypes))
			{
				foreach($arrTypes as $curtype)
				{
					$curtype=trim($curtype);
					if($curtype!="")
					{
						$arrTable[]=$curtype;
					}
				}
			}
		}
		return $arrTable;
	}
	
	public static function CreateRefImages($ref,$code,$libelle,$type)
	{
		if(!file_exists(DOSSIER_VUE_TEMPLATE_ICONS))
		{
			if(!mkdir(DOSSIER_VUE_TEMPLATE_ICONS))
			{
				die("Impossible de créer le dossier : ".DOSSIER_VUE_TEMPLATE_ICONS);
			}
		}
		if(!is_dir(DOSSIER_VUE_TEMPLATE_ICONS)) dir("Le dossier de stockage des icones est un fichier : ".DOSSIER_VUE_TEMPLATE_ICONS);
		$paramsicons=  config::get("vue_template_icons_infos");
		$shorttype=str_replace("field_","",$type);
		if(!isset($paramsicons["field"][$shorttype])) $shorttype="default";
		// Création de l'image pour le libellé
		$img_label_basename=md5(Tools::Text2Code($ref))."-label.png";
		$img_label_filename=DOSSIER_VUE_TEMPLATE_ICONS."images/".$img_label_basename;
		$img_field_basename=md5(Tools::Text2Code($ref))."-field.png";
		$img_field_filename=DOSSIER_VUE_TEMPLATE_ICONS."images/".$img_field_basename;
		$font_filename=DOSSIER_VUE_TEMPLATE_ICONS."ressources/AGENCYR.TTF";
		$modele_filename=DOSSIER_VUE_TEMPLATE_ICONS."ressources/modele_".$shorttype.".png";
		if(!file_exists($img_label_filename))
		{
			$lblimage=imagecreate($paramsicons["label"]["width"], $paramsicons["label"]["height"]);
			// Fond blanc et texte bleu
			$bg = imagecolorallocate($lblimage, 255, 255, 255);
			$textcolor = imagecolorallocate($lblimage, 0, 0, 0);
			// Ajout de la phrase en haut à gauche
			//imagestring($lblimage, 5, 0, 0, "(".$code.") ".$libelle, $textcolor);
			imagettftext($lblimage, 11, 0, 0,14, $textcolor, $font_filename, "(".$code.") ".$libelle);
			imagepng( $lblimage,$img_label_filename);
		}
		if(!file_exists($img_field_filename))
		{
			$field_img_infos=$paramsicons["field"][$shorttype];
			// Création de l'image pour le type (texte, checkbox, etc)
			$lblimagefield=imagecreatefrompng($modele_filename);
			imagettftext($lblimagefield, 11, 0, $paramsicons["field"][$shorttype]["offset"],14, $textcolor, $font_filename, "(".$code.")");
			//imagestring($lblimagefield, 5, 0, 0, "(".$code.")", $textcolor);
			imagepng( $lblimagefield,$img_field_filename);
		}
		return array($ref=>array("label"=>$img_label_basename,"labelimg"=>$img_label_basename,"fieldimg"=>$img_field_basename));
		$debug=true;
	}
	
	public static function Translate($text)
	{
		global $langtable;
		if(isset($langtable[$text])) return $langtable[$text];
		return $text;
	}
	
	public static function ScanFolder($folder,$extension="")
	{
		$liste=array();
		$d = dir($folder);
		while (false !== ($entry = $d->read()) )
		{
			if($entry=="." || $entry=="..") continue;
			$curfile=$folder."/".$entry;
			if(!is_file($curfile)) continue;
			if($extension!="")
			{
				$fileinfo=pathinfo($entry);
				if($fileinfo["extension"]!==$extension) continue;
			}
			$liste[]=$entry;
		}
		$d->close();
		return $liste;
	}
	
	public static function ExtraitExpressionsDeRecherche($recherche)
	{
		$regex="/([\"'])(?:(?=(\\\\?))\\2.)*?\\1/i";
		//$recherche="ma recherche text \"avec guillemets\" gezf \"autres guillemets\"";
		$retour=preg_match_all($regex,$recherche ,$resultats);
		//echo "<pre>". var_dump($retour).print_r($resultats,true) ."</pre>";
		if(Tools::isArray($resultats) && count($resultats) && Tools::isArray($resultats[0]) && count($resultats[0]))
		{
			$arrClefsMots=array();
			foreach($resultats[0] as $keymot => $curmot)
			{
				$recherche=str_replace($curmot,"######MOT#".$keymot."######",$recherche);
				$arrClefsMots["######MOT#".$keymot."######"]=$curmot;
			}
		}
		$arrRecherche=preg_split("/[\s,;]+/",$recherche);
		//echo "Recherche : <br/>".$recherche."<br />";
		//echo "Tableau : <pre>".print_r($arrRecherche,true)."</pre>";
		$newArrRecherche=array();
		foreach($arrRecherche as $curMot)
		{
			if(strpos($curMot,"######")===0)
			{
				if(isset($arrClefsMots[$curMot]))
				{
					$newArrRecherche[]=array("type"=>"expression","recherche"=>str_replace("\"","",$arrClefsMots[$curMot]));
				}
			}
			else
			{
				$newArrRecherche[]=array("type"=>"mot","recherche"=>$curMot);
			}
		}
		return $newArrRecherche;
		//echo "Nouveau Tableau : <pre>".print_r($newArrRecherche,true)."</pre>";
	}
	
	public static function ConvertAccents($str)
	{
		$url = $str;
		$url = preg_replace('#Ç#', 'C', $url);
		$url = preg_replace('#ç#', 'c', $url);
		$url = preg_replace('#è|é|ê|ë#', 'e', $url);
		$url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
		$url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
		$url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
		$url = preg_replace('#ì|í|î|ï#', 'i', $url);
		$url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
		$url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
		$url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
		$url = preg_replace('#ù|ú|û|ü#', 'u', $url);
		$url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
		$url = preg_replace('#ý|ÿ#', 'y', $url);
		$url = preg_replace('#Ý#', 'Y', $url);

		return ($url);
	}
	
	public static function RecurseStdClassToArray($arrReal,$castValueFunctionForScalar=null)
	{
		$arrReal=(array)$arrReal;
		foreach($arrReal as $keyreal=>$valreal)
		{
			if(Tools::isArray($valreal) || is_object($valreal)) // ATTENTION Modification de fond !!
			{
				$arrReal[$keyreal]=self::RecurseStdClassToArray($valreal,$castValueFunctionForScalar);
			}
			else
			{
				if((is_int($valreal) || is_string($valreal) || is_float($valreal)))
				{
					if(!is_null($castValueFunctionForScalar))
					{
						$arrReal[$keyreal]=call_user_func($castValueFunctionForScalar,$valreal);
					}
				}
			}
		}
		return $arrReal;
	}
	
	public static function RemoveAccents($str, $charset='utf-8')
	{
		$str = htmlentities($str, ENT_NOQUOTES, $charset);

		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caracteres

		return $str;
	}
	
	public static function getValue($param)
	{
		if(isset($_POST[$param])) return $_POST[$param];
		if(isset($_GET[$param])) return $_GET[$param];
	}
	
	public static function setLogActive($bool=true)
	{
		self::$_logfileactive=$bool;
	}
	public static function setLogfile($filename)
	{
		self::$_logfile=$filename;
	}
	
	public static function setLogFileReset()
	{
		self::$_logfilereset=true;
	}
	
	
	
	
	public static function Log($content)
	{
		if(self::$_logfileactive)
		{
			if(self::$_logfilereset)
			{
				return file_put_contents(self::$_logfile,$content);
			}
			else
			{
				return file_put_contents(self::$_logfile,$content,FILE_APPEND);
			}
			self::$_logfilereset=false;
		}
		//&& $fh=fopen(self::$_logfile,"a+"))
		//	return fputs($fh,$content."\r\n");
		return false;
	}
	
	
	
	public static function LoadJson($fichierJson)
	{
		if(!file_exists($fichierJson) || !is_file($fichierJson) || !is_readable($fichierJson)) return false;
		$tmp= file_get_contents($fichierJson);
		if(empty($tmp)) return false;
		$json=json_decode($tmp);
		if(json_last_error()===JSON_ERROR_NONE) return $json;
		return false;
	}
	
	
	public static function initStatic()
	{
		Tools::initPathInfo();
		Tools::initBaseScriptUrl();
		Tools::initBaseUrl();
	}
	
	public static function initPathInfo()
	{
		if(!isset(self::$pathinfo))
		{
			$path=array();
			if(isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"]!="")
			{
				$pathtxt=$_SERVER["PATH_INFO"];
				$path=explode("/",$pathtxt);
				$file=$path[1];
			}
			self::$pathinfo=$path;
		}
	}

	public static function initBaseUrl()
	{
		if(!isset(self::$baseurl))
		{
			self::$baseurl=BASE_FOLDER_HTTP;
		}
	}
	
	public static function initBaseScriptUrl()
	{
		if(!isset(self::$basescripturl))
		{
			self::$basescripturl=BASE_SCRIPT_HTTP;
		}
	}
	
	public static function getPathInfo()
	{
		if(!isset(self::$pathinfo) || is_null(self::$pathinfo))
		{
			self::initPathInfo();
		}
		return self::$pathinfo;
	}
	
	public static function getControleur()
	{
		$file=ARIA_DEFAULT_CONTROLER;
		
		if(!isset(self::$pathinfo) || is_null(self::$pathinfo))
		{
			self::initPathInfo();
		}
		
		if(isset(self::$pathinfo[1]))
			return self::$pathinfo[1];
		return $file;
	}
	
	public static function getVue($def="")
	{
		if($def=="")
			$def="index";
		
		if(!isset(self::$pathinfo) || is_null(self::$pathinfo))
		{
			self::initPathInfo();
		}
		
		if(isset(self::$pathinfo[2]))
			return self::$pathinfo[2];
		return $def;
	}
	
	public static function getModule()
	{
		if(isset(self::$pathinfo[3]))
			return self::$pathinfo[3];
		return "";
	}
			
	
	public static function getBaseUrl()
	{
		Tools::initBaseUrl();
		return self::$baseurl;
	}
	
	public static function getUrlForComposant($composant)
	{
		return Tools::getBaseScriptUrl()."/".$composant;
	}
	
	public static function getAjaxUrlForComposant($composant,$module="")
	{
		$url=Tools::getBaseScriptUrl()."/ajax/".$composant.($module!=""?("/".$module):"");
		return $url;
	}
	
	
	public static function getBaseScriptUrl()
	{
		Tools::initBaseScriptUrl();
		return self::$basescripturl;
	}
	public static function Display($theObject)
	{
		return "<pre>".print_r($theObject,true)."</pre>\n";
	}
	
	public static function SetSessionVar($theObject,$theName,$theSerialize=true)
	{
		if(session_is_registered($theName))
			session_unregister($theName);
		return $_SESSION[$theName]=(($theSerialize)?serialize($theObject):$theObject);
	}
	
	public static function GetSessionVar($theName,$theSerialize=true)
	{
		if (!session_is_registered($theName))
			return false;
		return ($theSerialize)?unserialize($_SESSION[$theName]):($_SESSION[$theName]);
	}
	
	public static function BindToGlobal($theRefArray,$theArrayAuth=null)
	{
		//echo "Bind de : ".Tools::Display($theRefArray);
		if(Tools::isArray($theRefArray) && count($theRefArray)>0)
		{
			foreach($theRefArray as $key=>$value)
			{
				if(is_null($theArrayAuth) || (Tools::isArray($theArrayAuth) && in_array($key,$theArrayAuth)))
				{
					global $$key;
					$$key=$value;
				}
			}
		}
	}
	
	
	public static function CreeListeIndenteeAvecListeHierarchisee($liste,$consignes=array("reference"=>"code","libelle"=>"libelle","enfants"=>"enfants"),$texteindentation="-- ")
	{
		static $profondeur=-1;
		$profondeur++;
		$reference=(isset($consignes["reference"]) && $consignes["reference"]!="")?$consignes["reference"]:"code";
		$libelle=(isset($consignes["libelle"]) && $consignes["libelle"]!="")?$consignes["libelle"]:"libelle";
		$enfants=(isset($consignes["enfants"]) && $consignes["enfants"]!="")?$consignes["enfants"]:"enfants";
		$listeRetour=array();
		foreach($liste as $id => $item)
		{
			$listeRetour[$item[$reference]]=str_repeat($texteindentation,$profondeur).$item[$libelle];
			if(isset($item[$enfants]) && count($item[$enfants])>0)
			{
				$listeTemporaire=Tools::CreeListeIndenteeAvecListeHierarchisee($item[$enfants],$consignes);
				if(Tools::isArray($listeTemporaire) && count($listeTemporaire)>0)
				{
					$listeRetour=Tools::arrayMerge($listeRetour,$listeTemporaire);
				}
			}
		}
		$profondeur--;
		return $listeRetour;
	}
	
	public static function HierarchiseListe($liste,$consignes=array("reference"=>"parentid","enfants"=>"enfans"))
	{
		$reference=(isset($consignes["reference"]) && $consignes["reference"]!="")?$consignes["reference"]:"parentid";
		$enfants=(isset($consignes["enfants"]) && $consignes["enfants"]!="")?$consignes["enfants"]:"enfants";
		foreach($liste as $id => $item)
		{
			if($item[$reference]!="")
			{
				if(isset($liste[$item[$reference]]))
				{
					$liste[$item[$reference]][$enfants][]=&$liste[$id];
				}
				else
				{
					$liste[$id][$reference]="";
				}
			}
		}
		foreach($liste as $id => $item)
		{
			if($item[$reference]!="") unset($liste[$id]);
		}
		return $liste;
	}
	
	public function Word2UTF8($text)
	{
		return self::MSOffice2UTF8($text);
	}
	
	public function MSOffice2UTF8($text)
	{
		$isUTF8=false;
		if(json_encode($text)===false)
		{
			$text=utf8_encode($text);
			$isUTF8=true;
			
		}
		$text=json_decode(str_replace('\u0092' /*"’" */,"'",json_encode($text)));
		$text=json_decode(str_replace('\u0153' /*"œ" */,"oe",json_encode($text)));
		//$text=utf8_encode($text);
		if($isUTF8) $text=utf8_decode($text);
		return $text;
	}
	
	public static function Text2Bool($text)
	{
		if($text===1 || $test==="1") return true;
		$text=strtolower($text);
		if($text==="x") return true;
		if($text==="oui") return true;
		return false;
	}
	
	public static function Text2Code($text)
	{
		/*
		$text2=utf8_encode($text);
		$text3=utf8_decode($text);
		$nompage = strtolower($text);
		$from=("àáâãäåæîïíìòóôõöøðúùûüéèêëýÿçþß");
		$from2=utf8_decode($from);
		$nompage = strtr($nompage,$from,"aaaaaaaiiiiooooooduuuueeeeyyts");
		*/
		$str = htmlentities($text, ENT_NOQUOTES, "utf-8");
        $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|circ|grave|ring|tilde|uml)\;#', '\1', $str);
        $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str);
        $str = preg_replace('#\&[^;]+\;#', '', $str);
		$str = str_replace(':', '-', $str);
		$str = str_replace('[', '', $str);
		$str = str_replace(']', '', $str);
		$nompage = str_replace("{","",$str);
		$nompage = str_replace("}","",$nompage);
		//$nompage = eregi_replace("[^a-z0-9_:~\\\-|]","_",$nompage);
		$nompage = preg_replace("/[^a-z0-9_:~\\\-|]/i","_",$nompage);
		$nompage = str_replace("|","-",$nompage);
		//$nompage = str_replace("/","_",$nompage);
		while(strpos($nompage,"__")!==false) $nompage=str_replace("__","_",$nompage);
		if(substr($nompage,-1)=="_") $nompage=substr($nompage,0,-1);
		if(substr($nompage,0,1)=="_") $nompage=substr($nompage,1);
		return(strtolower($nompage));
	}
	
	public static function Redirect($url)
	{
		if(!headers_sent())
		{
			header("Location:".$url);
		}
		else
		{
			echo "<script type='text/javascript'>window.location = '".$url."';</script>\r\n";
		}
		die();
	}
	public static function Bind($theRefArray,$theMergeArray=array(),$theReplace=false)
	{
		$arrParams=$theMergeArray;
		//echo "Bind de : ".Tools::Display($theRefArray);
		if(Tools::isArray($theRefArray) && count($theRefArray)>0)
		{
			foreach($theRefArray as $key=>$value)
			{
				if($theReplace==false || !isset($theMergeArray[$key]))
				{
					$arrParams[$key]=$value;
				}
			}
		}
		return $arrParams;
	}
	
	public static function  DisplayGlobals($theGlobalsArray)
	{
		$myReturn="";
		foreach($theGlobalsArray as $val)
		{
			global $$val;
			if(isset($$val))			
				$myReturn.=Tools::Display($val."=".$$val);
			else
				$myReturn.=Tools::Display("Undef:".$val);
		}
		return $myReturn;
	}
	
	public static function InitGlobals($theGlobalsArray,$theDefaultValue="")
	{
		if(Tools::isArray($theGlobalsArray) && count($theGlobalsArray)>0)
			foreach($theGlobalsArray as $curglobal)
			{
				global $$curglobal;
				$$curglobal=$theDefaultValue;
			}
	}

	function GetRandomFileName($theFile,$thePath)
	{
		$myNewFileName=Tools::Scramble()."_".$theFile;
		while(file_exists($thePath.$myNewFileName))
			$myNewFileName=Tools::Scramble()."_".$theFile;
		return $myNewFileName;
	}

	public static function SendXLS($theFileName,&$theData,$theColDefinition=array(),$thePathPre="",$theHasHeader=true)
	{
		$debug_cellformat=true;
		$myArrayTable=$theData;
		$myColOrdered=$theColDefinition;
		$curdir=getcwd();
		$path_include=dirname(__FILE__)."/xlswriter/";
		chdir($path_include);
		require_once 'Writer.php';
		chdir($curdir);
		$tmpfname = @tempnam("","");
		if($tmpfname=="")
		{
			$error_msg.="Erreur dans le nom du fichier<br />\n";
		}
		else
		{
			$workbook = new Spreadsheet_Excel_Writer($tmpfname);
			$workbook->setVersion(8);
			$worksheet =& $workbook->addWorksheet('Titre');
			$cell_format="entete";
			$formats_array[$cell_format] =& $workbook->addFormat();
			$formats_array[$cell_format]->setFontFamily('Arial');
			//$format_Arial->setNumFormat("text");
			$formats_array[$cell_format]->setBold();
			$formats_array[$cell_format]->setSize(10);
			$formats_array[$cell_format]->setNumFormat('@');

			$i=0;
			//echo "Donnees : ".Tools::Display($myArrayTable);
			
			foreach($myArrayTable as $keyarray=>$curarray)
			{
				$j=0;
				foreach($myArrayTable[0] as $keyval=>$headvalue)
				{
					$cell_format="@";
					//echo("Sortie excel avec format : col[".$keyval."]= ".$myColOrdered[$keyval]->export_format_valeur).BR;
					if(!isset($curarray[$keyval])) $curarray[$keyval]="";
					//if($debug_cellformat) echo "Verification existence du format pour ".$keyval." / ".$headvalue." / ".$curarray[$keyval]." ... \n";
					if(isset($myColOrdered[$headvalue]->export_format_valeur))
					{
						//if($debug_cellformat) echo "present. <br >\n";
						$cell_format=$myColOrdered[$headvalue]->export_format_valeur;
					}
					//else
						//if($debug_cellformat) echo "NON present. <br >\n";

					
					$cell_format=((trim($cell_format)=="jj/mm/aaaa")?"@":$cell_format);
					if(!isset($formats_array[$cell_format]))
					{
						$formats_array[$cell_format] =& $workbook->addFormat();
						$formats_array[$cell_format]->setFontFamily('Arial');
						$formats_array[$cell_format]->setSize(10);
						$formats_array[$cell_format]->setNumFormat($cell_format);
					}
					//if($debug_cellformat) die("Formats : ".print_r($formats_array,true));
		
					if($i==0)
						$cell_format="entete";
					//echo $curarray[$keyval].";";
					$worksheet->write($i, $j,strval($curarray[$keyval]),$formats_array[$cell_format]);
					$j++;
				}
				//echo "<br >\n";
				//die();
/*
							foreach($curarray as $keyval=>$curval)
							{
								$worksheet->write($i, $j,($curval),$format_Arial);
								$j++;
							}
*/
				$i++;
			}

			$workbook->close();
			
			/*
			 * Recuperation des donnees et envoi en streaming
			 */
			if(!headers_sent())
			{
				Tools::DL_DownloadProgressive($theFileName,$tmpfname);
				unlink($tmpfname);
				die();
			}
			else
			{
				$error_msg.="Entetes deja envoyes ! Des erreurs dans le script ? Impossible d'envoyer le telechargement<br />\n";
				unlink($tmpfname);
			}
		}
	}

	public static function Split($text)
	{
		if(!is_string($text)) return array();
		$arr=preg_split("/[\s,]+/",$text);
		foreach($arr as $key => $val)
		{
			if(empty($val)) unset($arr[$key]);
		}
		return $arr;
	}
	
	public static function StrVal($val)
	{
		return strval($val);
	}
	
	
	public static function SendCSV($theFileName,$theData,$theHasHeader=true,$theSeparateur=";")
	{
		$mySeparateurCSV=$theSeparateur;
		if(!isset($mySeparateurCSV) || is_null($mySeparateurCSV) || $mySeparateurCSV===false){
			$mySeparateurCSV=";";
		}
		$myCurLine=0;
		$myLineStop=-1;
		$headers=array();
		$headerLine="";
		$myArrayTable=$theData;
		/*
		 * Formatage des donnees
		 */
		if($theHasHeader)
		{
			$first=true;
			foreach($myArrayTable as $keyarray=>$curarray)
			{
				foreach($myArrayTable[0] as $keyval=>$headvalue)
				{
					if($first)
					{
						$headers[]=$keyval;
					}
					if(!isset($curarray[$keyval])) $curarray[$keyval]="";							
					$myArrayTable[$keyarray][$keyval]=str_replace("\"","''",str_replace("\r\n","\n",$curarray[$keyval]));
				}
				$first=false;
			}
			$headerLine="\"".Tools::arrayImplode("\"".$mySeparateurCSV."\"",$headers)."\"\r\n";
		}
		//foreach($curarray as $keyval=>$curvalue)	{$myArrayTable[$keyarray][$keyval]=str_replace("\"","''",str_replace("\r\n","\n",$curvalue));}

		/*
		 * Sortie fichier
		 */
		$tmpFileForDownload = tmpfile();
		fwrite($tmpFileForDownload, $headerLine);
		$myFileSize=0;
		foreach($myArrayTable as $keyarray=>$curarray)
		{
			$myLine= "\"".Tools::arrayImplode("\"".$mySeparateurCSV."\"",$myArrayTable[$keyarray])."\"\r\n";
			$myFileSize+=strlen($myLine);
			fwrite($tmpFileForDownload, $myLine);
			$myCurLine++;
			if($myCurLine>$myLineStop && $myLineStop>0)
				break;
		}
		
		/*
		 * Récuperation des donnees et envoi en streaming
		 */
		if(!headers_sent())
		{
			Tools::DL_Downloadheaders($theFileName,($myFileSize));
			fseek($tmpFileForDownload, 0);
			while(!feof($tmpFileForDownload))
				echo fread($tmpFileForDownload, 1024);
			fclose($tmpFileForDownload); // ceci va effacer le fichier
			die();
		}
		else
		{
			$error_msg.="Entetes deje envoyes ! Des erreurs dans le script ? Impossible d'envoyer le telechargement<br />\n";
		}
	}


	public static function NormalizeCSV($theFile)
	{
		if(file_exists($theFile) && is_writable($theFile))
		{
			$myFileContent=file_get_contents($theFile);
			if($myFileContent===false)
				return false;
			$myFileContent=str_replace("\n"," ",$myFileContent);
			$myFileContent=str_replace("\r ","\r\n",$myFileContent);
			$mySize=file_put_contents($theFile,$myFileContent);
			if($mySize===false)
				return false;
			if(strlen($myFileContent)==$mySize)
				return true;
		}
		return false;
	}
	public static function setLogPath($path)
	{
		self::$logpath=$text;
	}
	public static function setLogName($text)
	{
		self::$logfile=$text;
	}

	public static function getLogPath()
	{
		if(self::$logpath=="") self::$logpath=LOG_FOLDER;
		return self::$logpath;
	}
	
	public static function getLogName()
	{
		if(self::$logfile=="") self::$logfile="tracefile.log";
		return self::$logfile;
	}
	
	public static function getLogFile()
	{
		return self::getLogPath().self::getLogName();
	}
	

	public static function Trace($text)
	{
		$filename=Tools::getLogFile();
		if ($f = fopen($filename, "a+"))
		{
			if(self::$logindent==1)
			{
				fputs($f,"\r\n\r\n=============== Log du ".date("d/m/Y H:i:s")." =====================\r\n");
			}
			fputs($f,"#".self::$logindent."\r\n");
			self::$logindent++;
			fputs($f,print_r($text,true));
			fputs($f,"\r\n");
			
			fclose($f);
		}
	}

	public static function scramble() {
	  srand((double)microtime()*1000000);
	  $str="";
	  $char = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ";
	  while (strlen($str) < 12) { $str .= substr($char,(rand()%(strlen($char))),1); }
	  return $str;
	}

	public static function TimeStartCounter()
	{
		global $starttime;
		$starttime = microtime();
		$startarray = explode(" ", $starttime);
		$starttime = $startarray[1] + $startarray[0];	
		return $starttime;
	}

	public static function TimeStopCounter($time=null)
	{
		global $starttime;
		$curtime=$starttime;
		if(!is_null($time)) $curtime=$time;
		$endtime = microtime();
		$endarray = explode(" ", $endtime);
		$endtime = $endarray[1] + $endarray[0];
		$totaltime = $endtime - $curtime;
		$totaltime = round($totaltime,5);
		return $totaltime;
	}
	public static function TimeSetVal($theDatabase,$theRefId,$theSessId,$theParam,$theVal)
	{
		if($theParam=="") return false;
		Tools::Trace(__FUNCTION__."=>Definition de ".$theParam." - ".$theVal." avec id=".$theSessId." et refid=".$theRefId);
		if(!is_object($theDatabase))
		{
			Tools::Trace(__FUNCTION__."=> base de donnees non definie !!");
			return false;
		}
		$myObj=null;
		$theDatabase->setQuery("SELECT * FROM page_statistics WHERE refid='".$theRefId."' AND id=".$theSessId.";");
		if(!$theDatabase->loadObject($myObj))
		{
			Tools::Trace(__FUNCTION__."=> impossible de charger l'objet");
			return false;
		}
		$myObj->$theParam=$theVal;
		$theDatabase->updateObject("page_statistics",$myObj,"id");
		Tools::Trace(__FUNCTION__."=> requete : ".$theDatabase->getQuery());
		
	}
	
	public static function DisplayTimeCounter()
	{
		echo "<div class=\"time_counter\">".getTranslation("Temps de generation de la page")." :".time_get_counter()."s</div>\n";
	}

	public static function TimeStoreCounter($theDatabase,$theStartTime,$theUserId,$theShowScript=false,$thePath="")
	{
		if(is_object($theDatabase))
		{
			$myEndTime=Tools::TimeStartCounter();
			$myObj=null;
			$myObj->refid=session_id();
			$myObj->userid=$theUserId;
			$myObj->starttime=$theStartTime;
			$myObj->computetime=$myEndTime;
			$myObj->userip=$_SERVER["REMOTE_ADDR"];
			
			$theDatabase->insertObject("page_statistics",$myObj,"id");
			Tools::Trace(__FUNCTION__."=> temps :".$theStartTime);
			Tools::Trace(__FUNCTION__."=> requete : ".$theDatabase->getQuery());
			if($theShowScript)
				echo "<script src=\"".$thePath."stats.php?sessid=".$myObj->id."&refid=".session_id()."\" type=\"text/javascript\"></script>";
		}
	}
	
	public static function DateUserGetDefaultPrefs()
	{
		$obj=new stdClass();
		$obj->ScanFormat="%d/%d/%d";
		$obj->ScanOrder="dmy";
		return $obj;
	}
	
	public static function DateUserToSQL($thePref,$theDateStr)
	{
		if(!isset($thePref->ScanFormat))
			$thePref->ScanFormat="%d-%d-%d";
		switch($thePref->ScanOrder)
		{
			case "dmy":
				list($myDate["d"],$myDate["m"],$myDate["y"])=sscanf($theDateStr,$thePref->ScanFormat);
				break;
			case "ymd":
				list($myDate["y"],$myDate["m"],$myDate["d"])=sscanf($theDateStr,$thePref->ScanFormat);
				break;
		}
		//echo "Date : " .$theDateStr.", Format : ".$thePref->ScanFormat.", Order : ".$thePref->ScanOrder.", Scan : ".$myDate["y"].",".$myDate["m"].",".$myDate["d"]."<br />\n";
		if(strlen($myDate["m"])<2)
			$myDate["m"]="0".$myDate["m"];

		if(strlen($myDate["d"])<2)
			$myDate["d"]="0".$myDate["d"];
		$mySQLDate="";
		$myIntDate=mktime(1,1,1,intval($myDate["m"]),intval($myDate["d"]),intval($myDate["y"]));
		if($myIntDate!==false && $myIntDate!==-1 && intval($myDate["y"])>0 && intval($myDate["m"])>0 && intval($myDate["d"])>0)
			$mySQLDate=$myDate["y"]."-".$myDate["m"]."-".$myDate["d"];
		return $mySQLDate;
	}

	public static function DateSQLToUser($thePref,$theDateStr)
	{
		if($theDateStr=="")
			return "";
		if(!isset($thePref->ScanFormat))
			$thePref->ScanFormat="%d-%d-%d";
		list($myDate["y"],$myDate["m"],$myDate["d"])=sscanf($theDateStr,"%4s-%2s-%2s");
		$myDate=mktime(1,1,1,$myDate["m"],$myDate["d"],$myDate["y"]);
		$myUserDate=date($thePref->DispFormat,$myDate);
		/*
		switch($thePref->ScanOrder)
		{
			case "dmy":
				$myUserDate=sprintf($thePref->ScanFormat,$myDate["d"],$myDate["m"],$myDate["y"]);
				break;
			default:
			case "ymd":
				$myUserDate=sprintf($thePref->ScanFormat,$myDate["y"],$myDate["m"],$myDate["d"]);
				break;
		}
		*/
		return $myUserDate;
	}
	
	
	public static function Tail()
	{
		$debugtail=false;
		
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>debut");
		if(session_id()=="")
			session_start();
		if(!isset($_SESSION["tail_file"]))
			$_SESSION["tail_file"]="";
		$tail_file=$_SESSION["tail_file"];
		$tail_name=$_POST["tail"];
		if(!isset($_SESSION["tail_sess_name"])) $_SESSION["tail_sess_name"]=$_POST["tail"];
		else
		{
			if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fichier deja charge dans la session precedente");
			if($_SESSION["tail_sess_name"]!=$_POST["tail"])
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>le fichier est different, on reinitialise");
				$_SESSION["tail_sess_name"]=$_POST["tail"];
				//$_POST["tail_action"]="reset";
				$tail_file="";
			}
		}
		if($_POST["tail_action"]=="reset") $tail_file="";
		$myNewFileContent="";
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>Fin init");
		if(substr($tail_name,0,7)=="http://")
		{
			$myNewFileContent=file_get_contents($tail_name);
		}
		else
		{
			if(file_exists($tail_name))
				$myNewFileContent=file_get_contents($tail_name);
		}
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fichier charge (".strlen($myNewFileContent).")");
		if($myNewFileContent!="")
		{
			if($tail_file=="")
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>premiere iteration");
				$tail_file=$myNewFileContent;
				$_SESSION["tail_file"]=$tail_file;
				return $tail_file;
			}
			if(strlen($tail_file)<=strlen($myNewFileContent))
			{
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>iterations suivantes");
				$myDiffBuff=substr($myNewFileContent,strlen($tail_file),strlen($myNewFileContent));
				$tail_file=$myNewFileContent;
				$_SESSION["tail_file"]=$myNewFileContent;
				
				if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>iterations suivantes. Difference : (".strlen($myDiffBuff).") : ".$myDiffBuff);
				return $myDiffBuff;
			}
			else
			{
				$_SESSION["tail_file"]=$myNewFileContent;
				return "";
			}
		}
		
		if($debugtail) Tools::Trace(__METHOD__."@".__LINE__."=>fin fichier vide");
		$tail_file=$myNewFileContent;
		$_SESSION["tail_file"]=$tail_file;
		return $myNewFileContent;
	}
	
	public static function Tail_showHTML()
	{
		echo "<html><head><title>Suivi de ".$_GET["tail"]."</title>";
	    echo "<script type=\"text/javascript\" src=\"scripts/prototype.lite.js\"></script>\n";
	    echo "<script type=\"text/javascript\" src=\"scripts/moo.ajax.js\"></script>\n";
		echo "<script  language=\"JavaScript\" type=\"text/javascript\">\n";
		echo "
		    function updateTail(theParam)
		    {
				divtail='divtail';
				if(theParam=='')
				{
					theParam='tail';
				}
				//alert('update');
				//document.getElementById('divcontent').style.backgroundColor='#FFCCCC';
		        new ajax ('tools.class.php', {
		            postBody: 'tail=".$_GET["tail"]."&tail_action='+theParam,
					update: $(divtail), 
		            onComplete : loadComplete
		        });    
		    }
			function myTimer()
			{
				updateTail('');
				timeoutID = window.setTimeout('window.myTimer()',1000);
			}
			function loadComplete()
			{
				//document.getElementById('divcontent').style.backgroundColor='#FFFFFF';
				document.getElementById('divcontent').innerHTML=document.getElementById('divcontent').innerHTML+document.getElementById('divtail').innerHTML;
				//document.getElementById('divtail').innerHTML='';
				//alert(document.getElementById('divtail').innerHTML);
				//alert(document.getElementById('divcontent').innerHTML);
				//window.scrollBy(0,300);
			}
			";
		echo "</script>\n";
		echo "</head>\n";
		echo "<body  onload=\"myTimer();\">\n";
		echo "Fichier suivi : ".$_GET["tail"]."<br />\n";
		echo "<div id=\"divtail\" style=\"width:100%;border:1px solid #EAEAEA;display:none;\"></div>\n";
		echo "<div id=\"divcontent\" style=\"margin:2px;padding:2px;width:80%;border:1px solid #666666;display:block;\"></div>\n</body></html>\n";
		echo "<a href=\"#\" onclick=\"updateTail('reset');return false;\">Reset</a><br/>\n";
		
	}
	
	/*
	 * Fonction CurlPostAsync
	 * Permet d'ouvrir une page en mode asynchrone afin de réaliser un semblant de multi-thread
	 * Note : l'envoi se fait en post
	 * $url : string chemin à ouvrir
	 * $params : tableau contenant les paramètres à envoyer
	 */
	
	
	public static function CurlPostAsync($url, $params)
	{
		foreach ($params as $key => &$val) {
		  if (Tools::isArray($val)) $val = Tools::arrayImplode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
		}
		$post_string = Tools::arrayImplode('&', $post_params);

		$parts=parse_url($url);

		$fp = fsockopen($parts['host'],
			isset($parts['port'])?$parts['port']:80,
			$errno, $errstr, 30);
		if($fp===false)
		{
			return false; //"Erreur ".$errno." : ".$errstr;
		}
		$out = "POST ".$parts['path']." HTTP/1.1\r\n";
		$out.= "Host: ".$parts['host']."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($post_string)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		if (isset($post_string)) $out.= $post_string;

		fwrite($fp, $out);
		//return fclose($fp);
		return $fp;
	}

	
	
	public static function DL_DownloadBuffer($filename,$buffer)
	{
		Tools::DL_Downloadheaders($filename,strlen($buffer));
		echo $buffer;
	}

	/*
	 * Telechargement / affichage d'un fichier à partir de sa source (et redimensionnement d'image à la volée?)
	 */
	public static function DL_DownloadProgressive($file_name, $file_path, $action="download")
	{
		if(file_exists($file_path) && is_readable($file_path))
		{
			Tools::DL_Downloadheaders($file_name,filesize($file_path),$action);
			$fh=fopen($file_path,"rb");
			while(!feof($fh)){
				echo fread($fh,8192);
			}
			fclose($fh);
		}
		else{
			return false;
		}
	}

    public static function DL_Downloadheaders($name, $filesize, $action="download")
    {
		header ("Expires: Mon, 10 Dec 2001 08:00:00 GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS']!='on')
		{
		    header ("Cache-Control: no-cache, must-revalidate");
		    header ("Pragma: no-cache");
		}
		else
		{
		   // for SSL connections you have to replace the two previous lines with
		   header ("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		   header ("Pragma: public");
		}

		//Image: on affiche directement?
		$contenttype = Tools::DL_Content_type($name);
		if($action!="download" && preg_match("/image/i",$contenttype))
		{
			header("Content-Type: ".$contenttype);
		}
		//Pdf: on affiche avec le nom et la taille
		elseif($action!="download" && preg_match("/pdf/i",$contenttype))
		{
			header("Content-Type: ".$contenttype);
			header("Content-Disposition: inline; filename=\"".basename($name)."\";");
			header("Content-Length: ".$filesize);
		}
		//sinon: téléchargement direct
		else
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: ".$contenttype);
			header("Content-Disposition: attachment; filename=\"".basename($name)."\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$filesize);
			//header("Content-Type: application/force-download"); header("Content-Type: application/octet-stream"); header("Content-Type: application/download");
		}
	}

	public static function DL_Content_type($name)
	{
	   // Defines the content type based upon the extension of the file
	   //echo "Le nom est : ".$name."<br>\n";
	   $contenttype = "application/octet-stream";
	   $contenttypes = array("html" => "text/html",
	                      "htm" => "text/html",
	                      "txt" => "text/plain",
	                      "gif" => "image/gif",
	                      "jpeg" => "image/jpeg",
	                      "jpg" => "image/jpeg",
	                      "png" => "image/png",
	                      "sxw" => "application/vnd.sun.xml.writer",
	                      "sxg" => "application/vnd.sun.xml.writer.global",
	                      "sxd" => "application/vnd.sun.xml.draw",
	                      "sxc" => "application/vnd.sun.xml.calc",
	                      "sxi" => "application/vnd.sun.xml.impress",
	                      "xls" => "application/vnd.ms-excel",
	                      "xlsx" => "application/vnd.ms-excel",
	                      "ppt" => "application/vnd.ms-powerpoint",
	                      "pptx" => "application/vnd.ms-powerpoint",
	                      "doc" => "application/msword",
	                      "docx" => "application/msword",
	                      "rtf" => "text/rtf",
	                      "zip" => "application/zip",
	                      "mp3" => "audio/mpeg",
	                      "pdf" => "application/pdf",
	                      "tgz" => "application/x-gzip",
	                      "gz"  => "application/x-gzip",
	                      "vcf" => "text/vcf");
		$path_parts = pathinfo($name);
		$myExtension=strtolower($path_parts["extension"]);
		if(isset($contenttypes[$myExtension]))
			$contenttype=$contenttypes[$myExtension];
	   /*
	   $name = ereg_replace("e"," ",$name);
	   foreach ($contenttypes as $type_ext => $type_name)
	   {
	     if (preg_match ("/$type_ext$/i", $name)) { $contenttype = $type_name; }
	   }
	   */
	   //echo "Contenu pour l'extension ".$myExtension." du fichier ".$name." : ".$contenttype."<br>\n";
	   return $contenttype;
	}
	
	/*
	 * Extension des limites PHP pour certaines actions gourmandes en temps et/ou mémoire (edition, etc)
	 */
	public static function extendPhpTimeMemoryLimit()
	{
		ini_set("max_execution_time","5000");
		ini_set("max_input_time","5000");
		ini_set("mysql.connect_timeout","5000");
		ini_set("default_socket_timeout","5000");
		ini_set("memory_limit","2048M");
	}
	
	
	
	public static function get_server_memory_usage(){

		$free = shell_exec('free');
		$free = (string)trim($free);
		$free_arr = explode("\n", $free);
		$mem = explode(" ", $free_arr[1]);
		$mem = array_filter($mem);
		$mem = Tools::arrayMerge($mem);
		if(isset($mem[1]) && $mem[1]!=0) $memory_usage = $mem[2]/$mem[1]*100;
		else $memory_usage="err";
		return $memory_usage;
	}
	
	
	/**
		* array_merge_recursive does indeed merge arrays, but it converts values with duplicate
		* keys to arrays rather than overwriting the value in the first array with the duplicate
		* value in the second array, as array_merge does. I.e., with array_merge_recursive,
		* this happens (documented behavior):
		*
		* array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
		*     => array('key' => array('org value', 'new value'));
		*
		* array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
		* Matching keys' values in the second array overwrite those in the first array, as is the
		* case with array_merge, i.e.:
		*
		* array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
		*     => array('key' => array('new value'));
		*
		* Parameters are passed by reference, though only for performance reasons. They're not
		* altered by this function.
		*
		* @param array $array1
		* @param array $array2
		* @return array
		* @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
		* @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
		*/
		public static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
		{
			$merged = $array1;

			foreach ( $array2 as $key => &$value )
			{
				if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
				{
					$merged [$key] = Tools::array_merge_recursive_distinct ( $merged [$key], $value );
				}
				else
				{
					$merged [$key] = $value;
				}
			}

			return $merged;
		}

	public static function SendMailInviteAppointment($to, $subject, $message,$apptitle,$appmessage,$apptimestamp,$appheuredebut="0900",$appheurefin="1800",$location="ARIA3")
	{
		$organizer_email=Config::get("sendmail_from_mail");
		$organizer=Config::get("sendmail_from_name");
		
		$mailReply=Config::get("sendmail_replyto_mail");
		$nameReply=Config::get("sendmail_replyto_name");
		
		//$to =$to;
		//$subject = $subject;
		//$message = "Thank you for participating in the Technical Certification training program.\r\n\r\n";
		//$location = "Conf";
		//==================
		$date=date("Ymd");
		$headers .= "MIME-version: 1.0\r\n";
		$headers .= "Content-class: urn:content-classes:calendarmessage\r\n";
		$headers .= "Content-type: text/calendar; method=REQUEST; charset=UTF-8\r\n";
		$messaje = "BEGIN:VCALENDAR\r\n";
		$messaje .= "VERSION:2.0\r\n";
		$messaje .= "PRODID:PHP\r\n";
		$messaje .= "METHOD:REQUEST\r\n";
		$messaje .= "BEGIN:VEVENT\r\n";
		$messaje .= "DTSTART:".$date."T".$appheuredebut."00Z\r\n";
		$messaje .= "DTEND:".$date."T".$appheurefin."Z\r\n";
		$messaje .= "DESCRIPTION: ".$appmessage."\r\n";
		$messaje .= "SUMMARY: ".$apptitle."\r\n";
		$messaje .= "ORGANIZER; CN=\"BARPI ARIA3\":mailto:".$organizer_email."\r\n";
		$messaje .= "Location:" . $location . "\r\n";
		$messaje .= "UID:" . md5(uniqid(mt_rand(), true)) . "@i-carre.net\r\n\r\n";
		$messaje .= "SEQUENCE:0\r\n";
		$messaje .= "DTSTAMP:".date('Ymd').'T'.date('His')."\r\n";
		$messaje .= "END:VEVENT\r\n";
		$messaje .= "END:VCALENDAR\r\n";
		$message .= $messaje;
		return mail($to, $subject, $message, $headers);
		
		
		
		
		

		$participant_name_1 = $to;
		$participant_email_1= $to;

		$location           = $location;
		$date               = date("Ymd",($apptimestamp)); //'20131026';
		$startTime          = $appheuredebut;
		$endTime            = $appheurefin;
		$desc               = $appmessage;

		/*$headers = 'From: '.$organizer.' <'.$organizer_email. '>' . "\r\n" .
		'Reply-To:'. $nameReply.' <'.$mailReply. '>' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();*/
		$headers = 'Content-Type:text/calendar; Content-Disposition: inline; charset=utf-8;\r\n';
		$headers .= "Content-Type: text/plain;charset=\"utf-8\"\r\n"; #EDIT: TYPO

		$message = "BEGIN:VCALENDAR\r\n
		VERSION:2.0\r\n
		PRODID:-//Deathstar-mailer//theforce/NONSGML v1.0//EN\r\n
		METHOD:REQUEST\r\n
		BEGIN:VEVENT\r\n
		UID:" . md5(uniqid(mt_rand(), true)) . "i-carre.net\r\n
		DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\n
		DTSTART:".$date."T".$startTime."00Z\r\n
		DTEND:".$date."T".$endTime."00Z\r\n
		SUMMARY:".$apptitle."\r\n
		ORGANIZER;CN=".$organizer.":mailto:".$organizer_email."\r\n
		LOCATION:".$location."\r\n
		DESCRIPTION:".$appmessage."\r\n
		ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN".$participant_name_1.";X-NUM-GUESTS=0:MAILTO:".$participant_email_1."\r\n
		END:VEVENT\r\n
		END:VCALENDAR\r\n";

		$headers .= $message;
		mail($to, $subject, $message, $headers);    
	}
		
	public static function SendMail($to,$subject,$message)
	{
		$mailFrom=Config::get("sendmail_from_mail");
		$nameFrom=Config::get("sendmail_from_name");
		$mailReply=Config::get("sendmail_replyto_mail");
		$nameReply=Config::get("sendmail_replyto_name");

		$headers = 'From: '.$nameFrom.' <'.$mailFrom. '>' . "\r\n" .
					'Reply-To:'. $nameReply.' <'.$mailReply. '>' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

		return mail($to, $subject, $message, $headers);
	}
	
	public static function SendMailWithNames($mailFrom,$nameFrom,$to,$subject,$message)
	{
		$mailReply=$mailFrom;
		$nameReply=$nameFrom;

		$headers = 'From: '.$nameFrom.' <'.$mailFrom. '>' . "\r\n" .
					'Reply-To:'. $nameReply.' <'.$mailReply. '>' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

		return mail($to, $subject, $message, $headers);
	}
		
	public static function HTML2PDF($html,$file,$download=true)
	{
		try {
				$content = $html;
				$html2pdf = new Html2Pdf('P', 'A4', 'fr');
				//die("TRaitement contenu ...");
				$html2pdf->setDefaultFont('Arial');
				$html2pdf->writeHTML($content);
				if($download) $html2pdf->output($file);
				else return $html2pdf->output($file,"F");
				
			} catch (Html2PdfException $e) {
				$html2pdf->clean();
				$formatter = new ExceptionFormatter($e);
				echo $formatter->getHtmlMessage();
			}
	}
	
	public static function PHPMailer($to,$subject,$message,$pj=array())
	{
		global $ThePrefs;
		/**
		 * This example shows making an SMTP connection with authentication.
		 */
		//Import the PHPMailer class into the global namespace
		
		//SMTP needs accurate times, and the PHP time zone MUST be set
		//This should be done in your php.ini, but this is how to do it if you don't have access to that
		//date_default_timezone_set('Etc/UTC');
		//require '../vendor/autoload.php';
		//Create a new PHPMailer instance
		//echo "Instance phpmailer ...";
		$mail = new PHPMailer;
		//echo "isSMTP ?...";
		//Tell PHPMailer to use SMTP
		/*
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 2;
		//Set the hostname of the mail server
		$mail->Host = $ThePrefs->SMTPHost; //'mail.example.com';
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = $ThePrefs->SMTPPort; //25;
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "tls";
		//Username to use for SMTP authentication
		$mail->Username = $ThePrefs->SMTPUser; //'yourname@example.com';
		//Password to use for SMTP authentication
		$mail->Password = $ThePrefs->SMTPPass; //'yourpassword';
		 * 
		 */
		//Set who the message is to be sent from
		$mail->setFrom($ThePrefs->From,$ThePrefs->FromName);
		//Set an alternative reply-to address
		$mail->addReplyTo($ThePrefs->From,$ThePrefs->FromName);
		//Set who the message is to be sent to
		$mail->addAddress($to);
		//Set the subject line
		$mail->Timeout = 20;
		$mail->Subject = utf8_decode($subject);
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
		$mail->msgHTML(utf8_decode($message), __DIR__);
		//Replace the plain text body with one created manually
		$mail->AltBody = strip_tags(str_replace("</p>","</p>\r\n",str_replace("<br","\r\n<br",$message)));
		//Attach an image file
		if(is_array($pj) && count($pj))
		{
			foreach($pj as $curPJ)
			{
				$mail->addAttachment($curPJ["path"],$curPJ["name"]);
			}
		}
		//$mail->addAttachment('images/phpmailer_mini.png');
		//send the message, check for errors
		//die("User : ".$ThePrefs->SMTPUser.", Host : ".$ThePrefs->SMTPHost.", Port : ".$ThePrefs->SMTPPort."<pre>".print_r($mail,true)."</pre>");
		if (!$mail->send()) {
			return 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			return 'Message sent!';
		}
	}
}

if(isset($_GET["tail"]) || isset($_POST["tail"]))
{
	if(isset($_POST["tail_action"]))
		echo str_replace("\n","<br/>\n",Tools::Tail());
	else
		Tools::Tail_showHTML();		
}



// -------- functions -------------
class clsDateManipulation
{
	var $updebug=false;
	var $daycount=array("first"=>1,"second"=>2,"third"=>3,"fourth"=>4,"fifth"=>5);
	var $dayvalue=array("mon"=>1,"tue"=>2,"wed"=>3,"thu"=>4,"fri"=>5,"sat"=>6,"sun"=>0);
	var $monthvalue=array("jan"=>1,"feb"=>2,"mar"=>3,"apr"=>4,"may"=>5,"jun"=>6,"jul"=>7,"aug"=>8,"sep"=>9,"oct"=>10,"nov"=>11,"dec"=>12);
	var $closeddays=array("sat","sun");
	var $opendays=array("mon","tue","wed","thu","fri");
	
	public function __construct($theDebug=false)
	{
		$this->updebug=$theDebug;
		return true;
	}
	function getNextDay(&$theDay,&$theMonth,&$theYear)
	{
		$theDay++;
		$myNbDayInMonth=date("t",mktime(0,0,0,$theMonth,1,$theYear));
		if($theDay>$myNbDayInMonth)
		{
			$theDay=1; $theMonth++;
			if(intval($theMonth)>12) { $theMonth=1; $theYear++; }
		}
		//echo "getNextDay : ($theDay,$theMonth,$theYear)<br>\n$theYear	return true;
	}
	function getNextValidDay(&$theDay,&$theMonth,&$theYear,$theValidDates,$takefirst=true)
	{
		if($this->updebug) Tools::Trace(__FUNCTION__."@".__LINE__."==> calcul date depuis $theDay,$theMonth,$theYear, count next valid : ".count($theValidDates));
		//echo "getNextValidDay : ".count($theValidDates)."<br>\n";
		if(count($theValidDates)==0) return false;
		$i=0;
		//echo "On ajoute un premier jour avant : $takefirst ($theDay,$theMonth,$theYear)<br>\n";
		if($this->updebug) Tools::Trace(__FUNCTION__."@".__LINE__."==> On ajoute un premier jour avant : $takefirst ($theDay,$theMonth,$theYear)");
		if(!$takefirst) $this->getNextDay($theDay,$theMonth,$theYear);
		// if(!$takefirst) echo "Fait : ($theDay,$theMonth,$theYear)<br>\n";
		while(!in_array(strtolower(date("D",mktime(0,0,0,$theMonth,$theDay,$theYear))),$theValidDates) && $i<=6 )
		{
			if($this->updebug) Tools::Trace(__FUNCTION__."@".__LINE__."==> Jour calculé : ".date("D",mktime(0,0,0,$theMonth,$theDay,$theYear)));
			//echo "Jour calcule : ".date("D",mktime(0,0,0,$theMonth,$theDay,$theYear))."<br>\n";
			$this->getNextDay($theDay,$theMonth,$theYear);
			if($this->updebug) Tools::Trace(__FUNCTION__."@".__LINE__."==> Jour en cours : ($theDay,$theMonth,$theYear)" );
			//echo "Jour en cours : ($theDay,$theMonth,$theYear)<br>\n";
			$i++;
		}
		
		if($i>6) return false;
		return true;
	}
	function getDatum($theDay,$theMonth,$theYear)
	{
		//echo "Date : $theDay/$theMonth/$theYear<br>\n";
		if (strlen($theMonth) == 1) { $theMonth = "0".$theMonth; }
		if (strlen($theDay) == 1) { $theDay = "0".$theDay; }
		$myDatum = "$theYear-$theMonth-$theDay";
		return $myDatum;
	}
	function getDayNumberInMonth($theDaynumber,$theDayname,&$theDay,&$theMonth,&$theYear)
	{
		$myDayNb=$this->daycount[$theDaynumber];
		$myDaysForMonth=date("t",mktime(0,0,0,$theMonth,0,$theYear));
		//echo "Numero : $theDaynumber ($myDayNb / mois : $myDaysForMonth), Jour : $theDayname, Date : $theDay / $theMonth / $theYear<br>\n";
		for($i=1;$i<=intval($myDaysForMonth);$i++)
		{
			//echo "Verification pour le jour : $i/$theMonth/$theYear : ".strtolower(date("D",mktime(0,0,0,$theMonth,$i,$theYear)))."<br>\n";
			if( strtolower(date("D",mktime(0,0,0,$theMonth,$i,$theYear))) == $theDayname )
			{
				//echo "On est le bon jour ! Premier jour trouve ... on ajoute $myDayNb - 1";
				$theDay=$i;
				$theDay=$theDay+7*(intval($myDayNb)-1);
				if($theDay>$myDaysForMonth) return false;
				else return true;
			}			
		}
	}
	
	function timeSub($theBegin,$theEnd)
	{
		list($myBH,$myBM)=sscanf($theBegin,"%2s%2s");
		list($myEH,$myEM)=sscanf($theEnd,"%2s%2s");
		$myHD=intval($myEH)-intval($myBH);
		$myMD=intval($myEM)-intval($myBM);
		if($myMD<0)
		{
			if($myHD>0)
			{
				$myMD+=60;
				$myHD-=1;
			}
			else
			{
				$myMD*=-1;
			}
		}
		$myFinalDiff=60*$myHD+$myMD;
		return $myFinalDiff;
	}
	
	function dateAdd(&$theDay,&$theMonth,&$theYear,$value,$type)
	{
		$theDay=intval($theDay);
		$theMonth=intval($theMonth);
		$theYear=intval($theYear);
		switch($type)
		{
			case "day":
				$theDay+=$value;
				$myMonthNbDay=date("t",mktime(1,1,1,intval($theMonth),1,$theYear));
				while($theDay>$myMonthNbDay)
				{
					$theDay-=$myMonthNbDay;
					$theMonth++;
					if($theMonth>12) { $theMonth=1; $theYear++; }
					$myMonthNbDay=date("t",mktime(1,1,1,intval($theMonth),1,$theYear));
				}
				while($theDay<=0)
				{
					$theMonth--;
					if($theMonth<1) { $theMonth=12; $theYear--; }
					$myMonthNbDay=date("t",mktime(1,1,1,intval($theMonth),1,$theYear));
					$theDay+=$myMonthNbDay;
				}
				break;
			case "month":
				$theMonth+=$value;
				while($theMonth>12) { $theMonth-=12; $theYear++; }
				while($theMonth<1) { $theMonth+=12; $theYear--; }
				break;
			case "year":
				$theYear+=$value;
				break;
			case "week":
				$myNewValue=7*intval($value);
				$myNewInterval="day";
				$this->dateAdd($theDay,$theMonth,$theYear,$myNewValue,$myNewInterval);
				break;
		}
		return true;
	}


	function datumAdd(&$theDatum,$value,$type)
	{
		if(strlen($theDatum)!=10)
			return false;
		list($year,$month,$day)=sscanf($theDatum,"%4s-%2s-%2s");
		if(intval($month)>12 || intval($month<1)) return false;
		if(intval($year<1)) return false;
		$myNbDays=date("t",mktime(1,1,1,intval($month),1,intval($year)));
		if(intval($day)>intval($myNbDays) || intval($day<1)) return false;
		clsDateManipulation::dateAdd($day,$month,$year,$value,$type);
		$theDatum=clsDateManipulation::getDatum($day,$month,$year);
		return true;
	}
}
