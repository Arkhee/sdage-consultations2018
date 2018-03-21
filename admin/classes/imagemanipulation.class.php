class ImageManipulation
{
	var $width;
	var $height;
	var $file;
	var $type;
	var $img_tag;
	var $image_quality=100;
	function ImageManipulation($img="")
	{
		if($img!="")
			$this->load($img);
	}

	function load($img)
	{
		$debug=false;
		$this->file=$img;
		$imagedata = getimagesize($img);
		if($imagedata===false)
		{
			$this->file_name="";
			$this->width="";
			$this->height="";
			$this->type="";
			$this->img_tag="";
			return false;
		}
		$this->file_name=$img;
		$this->width=$imagedata[0];
		$this->height=$imagedata[1];
		$this->type=$imagedata[2];
		$this->img_tag=$imagedata[3];
		if($debug) echo "ImageManipulation / load :<pre>".print_r($this,true)."</pre>\n";
		return true;
	}


	function resize_to_jpg($w,$h)
	{
		$debug=false;
		if($debug) echo __FUNCTION__."@".__LINE__."=> Début<br>\n";
		
		$myDims=$this->get_img_dims($w,$h);
		if($myDims===false)
			return false;
		if($debug) echo __FUNCTION__."@".__LINE__."Creation image<br>\n";
	   $im2 = imagecreatetruecolor($myDims->width, $myDims->height);
		if($debug) echo __FUNCTION__."@".__LINE__."Creation depuis fichier (".$this->file.")<br>\n";
		switch(intval($this->type))
		{
			case 1:
				//echo "L'image est GIF<br>\n";
				$image = imagecreatefromgif($this->file);
				break;
			case 2:
				//echo "L'image est JPG<br>\n";
				$image = ImageCreateFromJpeg($this->file);
				break;
			case 3:
				//echo "L'image est PNG<br>\n";
				$image = ImageCreateFromPng($this->file);
				break;
			default:
				break;
		}
		//echo "Rééchantillonnage<br>\n";
	   if($debug) echo __FUNCTION__."@".__LINE__."Redim vers : ".$myDims->width.",". $myDims->height.",". $this->width.",". $this->height."<br>\n";
	   //$myReturn = imagecopyresized($im2, $image, 0, 0, $new_w, $new_h, $rs_w, $rs_h, $rs_w, $rs_h);
	   $myReturn = imagecopyresampled($im2, $image, 0, 0, 0,0, $myDims->width, $myDims->height, $this->width, $this->height);
	   if($debug) echo __FUNCTION__."@".__LINE__."Redim : ".(($myReturn)?"ok":"erreur")."<br>\n";
	   $myReturn = imagejpeg($im2, $this->file, $this->image_quality);
	   if($debug) echo __FUNCTION__."@".__LINE__."Sauvegarde ".(($myReturn)?"ok":"erreur")."<br>\n";
	   return $myReturn;
	}
	

	function get_img_dims($w=-1,$h=-1)
	{
		$debug=false;
		if($debug) echo __FUNCTION__."@".__LINE__." => début<br>\n";
		if($this->type=="")
		{
			if($debug) echo __FUNCTION__."@".__LINE__." => pb sub type ".$this->type."<br>\n";
			return false;
		}
		
		if($debug) echo __FUNCTION__."@".__LINE__." => Avant : w=".$w." et h=".$h."<br>\n";
		if($w>0 && $h>0)
		{
			
			if ( ($this->width < $this->height))
			{
				$w = ($h / $this->height) * $this->width;
			} else {
				$h = ($w / $this->width) * $this->height;
			}
			
		}
		elseif($w<=0)
		{
			$w = ($h / $this->height) * $this->width;
		}
		elseif($h<=0)
		{
			$h = ($w / $this->width) * $this->height;
		}
		else
			return false;
			
		if($debug) echo __FUNCTION__."@".__LINE__." => Après : w=".$w." et h=".$h."<br>\n";
		$myDims=new stdClass();
		$myDims->width=intval($w);
		$myDims->height=intval($h);
		return $myDims;	
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
	
	function clsDateManipulation($theDebug=false)
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
			//echo "Jour calculé : ".date("D",mktime(0,0,0,$theMonth,$theDay,$theYear))."<br>\n";
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
		//echo "Numéro : $theDaynumber ($myDayNb / mois : $myDaysForMonth), Jour : $theDayname, Date : $theDay / $theMonth / $theYear<br>\n";
		for($i=1;$i<=intval($myDaysForMonth);$i++)
		{
			//echo "Vérification pour le jour : $i/$theMonth/$theYear : ".strtolower(date("D",mktime(0,0,0,$theMonth,$i,$theYear)))."<br>\n";
			if( strtolower(date("D",mktime(0,0,0,$theMonth,$i,$theYear))) == $theDayname )
			{
				//echo "On est le bon jour ! Premier jour trouvé ... on ajoute $myDayNb - 1";
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