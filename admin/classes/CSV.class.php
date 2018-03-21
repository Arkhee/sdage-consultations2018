<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CSV
 *
 * @author yb
 */
class CSV implements Iterator {
	public $data=array();
	private $num_header=0;
	private $num_data=1;
	public $headers=array();
	public $headers_index=array();
	private $_isFile=false;
	private $_isFileLoaded=false;
	protected $file_name="";
	protected $delimiter = ';';
	protected $enclosure='';
	private $_pointer=0;
	private $_normalise;
	
	public function __construct($fichier="",$num_header=0,$num_data=1,$normalise=0)
	{
		$this->_normalise = $normalise;
		$this->setDataLineNumber($num_data);
		$this->setHeaderLineNumber($num_header);
		if($fichier!="")
		{
			$this->setFileName($fichier);
			$this->readFile($fichier);
		}
	}
	
	/*
	 * Fonctions implement pour iterator
	 */
	
	function rewind() {
		$this->_pointer=0;
	}
	
	function current() {
	  return $this->getDataOneLineWithHeaders($this->_pointer);
	}
	
	
	function key() {
	  return $this->_pointer;
	}
	
	function next() {
	  $this->_pointer++;
	  if(!isset($this->data[$this->_pointer])) return false;
	}
	function valid() {
	  return isset($this->data[$this->_pointer]);
	}
	
	
	// Fonctions d'initialisation
	
	public function setHeaderLineNumber($num_header)
	{
		$this->num_header=$num_header;
	}
	
	public function setDataLineNumber($num_data)
	{
		$this->num_data=$num_data;
	}
	
	public function setDelimiter($delimiter)
	{
		$this->delimiter=$delimiter;
	}
	
	public function setEnclosure($enclosure)
	{
		$this->enclosure=$enclosure;
	}
	
	public function setFileName($fichier)
	{
		$this->_isFile=false;
		if(file_exists($fichier) && is_readable($fichier))
		{
			$this->_isFile=true;
			$this->file_name=$fichier;
			return true;
		}
		return false;
	}
	
	public function getHeaderLineNumber()
	{
		return $this->num_header;
	}
	
	public function getDataLineNumber()
	{
		return $this->num_data;
	}
	
	public function getDelimiter()
	{
		return $this->delimiter;
	}
	
	public function getEnclosure()
	{
		return $this->enclosure;
	}
	
	public function getFileName()
	{
		return $this->file_name;
	}
	
	public function writeFile()
	{
		if($this->_isFile)
		{
			if(!$fh=fopen($this->file_name,"wt")) return false;
			if(Tools::isArray($this->headers) && count($this->headers)>0)
			{
				fputcsv($fh, Tools::arrayKeys($this->headers),$this->delimiter,$this->enclosure);
			}
			if(Tools::isArray($this->data) && count($this->data)>0)
			{
				foreach($this->data as $curdata)
				{
					if(Tools::isArray($curdata) && count($curdata)>0)
					{
						fputcsv($fh,$curdata,$this->delimiter,$this->enclosure);
					}

				}
			}
			fclose($fh);
			return true;
		}
		return false;
	}
	
	public function isRead()
	{
		return $this->_isFileLoaded;
	}
	
	public function isFileOk()
	{
		return $this->_isFile;
	}
	
	public function readFile()
	{
		if($this->_isFile)
		{
			if(!$fh=fopen($this->file_name,"rt")) return false;
			$this->_isFileLoaded=true;
			$index=0;
			$delimiter=$this->delimiter; $enclosure=$this->enclosure;
			while($ligne=fgetcsv($fh,0,$delimiter))
			{
				if(Tools::isArray($ligne) && count($ligne)>0)
				{
					if($index==$this->num_header)
					{
						foreach($ligne as $key=>$curheader)
						{
							$this->headers[utf8_encode($curheader)]=utf8_encode($key);
							$this->headers_index[utf8_encode($key)]=utf8_encode($curheader);
						}
					}
					if(
						($this->num_data>$this->num_header && $index>=$this->num_data) // Cas des données situées après les entêtes
						|| ($this->num_data<$this->num_header && $index>=$this->num_data && $index<$this->num_header)) // Cas des données situées avant les entêtes
					{
						foreach($ligne as $curkey => $curval)
						{
							$curval=Tools::Word2UTF8($curval);
							$ligne[$curkey]=utf8_encode($curval);
						}
						$this->data[]=$ligne;
					}
					$index++;
				}
			}
			fclose($fh);
			return true;
		}
		return false;
	}
	
	// Fonctions de lecture / écriture
	
	public function cleanFile()
	{
		$this->_isFile=false;
		$this->data=array();
		$this->headers=array();
		$this->headers_index=array();
		return true;
	}
	
	public function getNbLines()
	{
		return count($this->data);
	}
	
	public function getNbHeaders()
	{
		return count($this->headers);
	}
	
	public function getHeaders()
	{
		return Tools::arrayKeys($this->headers);
	}
	
	public function getHeadersIndexes()
	{
		return Tools::arrayKeys($this->headers_index);
	}
	
	public function setHeaders($headers)
	{
		$this->headers=array();
		if(Tools::isArray($headers) && count($headers)>0)
		{
			$i=0;
			foreach($headers as $key => $header)
			{
				$this->headers[$header]=$i++;
			}
			$this->headers_index=array();
			foreach($this->headers as $key => $val)
			{
				$this->headers_index[$val]=$key;
			}
			return true;
		}
		return false;
	}
	
	public function setData($data)
	{
		$this->data=array();
		if(Tools::isArray($data) && count($data)>0)
		{
			$index=0;
			foreach($data as $keydata => $valdata)
			{
				if(Tools::isArray($valdata) && count($valdata)>0)
				{
					foreach($valdata as $valeur)
					{
						$this->data[$index][]=$valeur;
					}
					$index++;
				}
			}
			return true;
		}
		return false;
	}
	
	
	public function getDataOneLineWithHeaders($row)
	{
		$row=(int)$row;
		$arrData=array();
		if($row<0 || $row>$this->getNbLines()) return $arrData;
		foreach($this->headers_index as $col => $key)
		{
			$arrData[$key]=$this->getData($row,$col);
		}
		return $arrData;
	}
	
	public function getData($row,$col)
	{
		if(isset($this->data[$row][$col])) return $this->data[$row][$col];
		return false;
	}
	
	public function getDataWithHeader($row,$header)
	{
		if(!isset($this->headers[$header])) return false;
		if(!isset($this->data[$row])) return false;
		if(!isset($this->data[$row][$this->headers[$header]])) return false;
		return $this->data[$row][$this->headers[$header]];
	}
			
	public function downloadFile()
	{
		if(Tools::isArray($this->headers) && count($this->headers)>0)
			$filesize=strlen($this->enclosure.Tools::arrayImplode($this->enclosure.$this->delimiter.$this->enclosure,$this->headers).$this->enclosure);
		if(Tools::isArray($this->data) && count($this->data)>0)
		{
			foreach($this->data as $curdata)
			{
				$filesize+=strlen($this->enclosure.Tools::arrayImplode($this->enclosure.$this->delimiter.$this->enclosure,$curdata).$this->enclosure);
			}
		}
		$this->getDownloadHeaders($this->file_name,$filesize);
		if(Tools::isArray($this->headers) && count($this->headers)>0)
		{
			echo $this->enclosure.Tools::arrayImplode($this->enclosure.$this->delimiter.$this->enclosure,$this->headers).$this->enclosure;
		}
		if(Tools::isArray($this->data) && count($this->data)>0)
		{
			foreach($this->data as $curdata)
			{
				echo ($this->enclosure.Tools::arrayImplode($this->enclosure.$this->delimiter.$this->enclosure,$curdata).$this->enclosure);
			}
		}
		die();
	}

	function downloadFileFromDisk()
	{
		if(!$this->_isFile) return false;
		if(file_exists($input_filename) && is_readable($input_filename))
		{
			$this->getDownloadHeaders($this->file_name,filesize($this->file_name));
			$fh=fopen($this->file_name,"rb");
			while(!feof($fh))
				echo fread($fh,8192);
			fclose($fh);
			die();
		}
		else
			return false;
	}

    private function getDownloadHeaders($filename,$filesize)
    {
		// get_contenttype.inc.php - PHProjekt Version 4.0
		// copyright  �  2000-2003 Albrecht Guenther  ag@phprojekt.com
		// www.phprojekt.com
		// Author: Albrecht Guenther
		
		header ("Expires: Mon, 10 Dec 2001 08:00:00 GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
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
		
		// fallback if no download type is set
		$file_download_type = "attachment";
		
		$contenttype = $this->getContentType($filename);
		if(substr($contenttype,0,5)=="image")
		{
			header("Content-Type: ".$contenttype);
		}
		else
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			//header("Content-Type: application/force-download");
			//header("Content-Type: application/octet-stream");
			//header("Content-Type: application/download");
			header("Content-Type: ".$contenttype);
			header("Content-Disposition: attachment; filename=\"".basename($name)."\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$filesize);	
		}
		
		//echo "Fin de l'envoi des ent�tes";
	}

	private function getContentType($filename)
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
	                      "ppt" => "application/vnd.ms-powerpoint",
	                      "doc" => "application/msword",
	                      "rtf" => "text/rtf",
	                      "zip" => "application/zip",
	                      "mp3" => "audio/mpeg",
	                      "pdf" => "application/pdf",
	                      "tgz" => "application/x-gzip",
	                      "gz"  => "application/x-gzip",
	                      "vcf" => "text/vcf");
		$path_parts = pathinfo($filename);
		$myExtension=strtolower($path_parts["extension"]);
		if(isset($contenttypes[$myExtension]))
			$contenttype=$contenttypes[$myExtension];
	   return $contenttype;
	}
	
	public function getDataCurrentLine($col)
	{
		return $this->getData($this->_pointer, $col);
	}
	public function getDataCurrentLineFromHeader($colname)
	{
		if(isset($this->headers)  && Tools::isArray($this->headers) && count($this->headers)>0 && isset($this->headers[$colname]))
		{
			$colnb=$this->headers[$colname];
			return $this->getDataCurrentLine($colnb);
		}
		return false;
	}
	
	public function setFirst()
	{
		$this->_pointer=0;
		if(!Tools::isArray($this->data) || count($this->data)<=0) return false;
		return true;
	}
	
	public function setNext()
	{
		if($this->_pointer<count($this->data))
		{
			$this->_pointer++;
			return true;
		}
		return false;
	}
	
	public function setPrev()
	{
		if($this->_pointer>0)
		{
			$this->_pointer--;
			return true;
		}
		return false;
	}
	
	public function setLast()
	{
		if(Tools::isArray($this->data) && count($this->data)>0)
		{
			$this->_pointer=count($this->data)-1;
			return true;
		}
		return false;
	}
	
}
