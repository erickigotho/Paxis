<?php
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');

class Dynamo
{
	var $id;
	var $columnArray;
	var $table;
	
	function Dynamo($table)
	{
		mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die(mysql_error());
		mysql_select_db(DB_NAME) or die(mysql_error());
		
		$query = "SHOW COLUMNS FROM {$table}";
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results))
		{
			$this->table = $table;
			$count = 0;
			$arrayFields = array();
			while($array = mysql_fetch_array($results))
			{
				if($count == 0)
				{
					$this->id = $array['Field'];
					$count++;
					continue;
				}
				$arrayFields[] = $array['Field'];
			}
			
			$this->columnArray = $arrayFields;
		}
		else
		{
			print $table . " does not exist";
			exit;
		}
	}
	
	function getMaxId()
	{
		$query = "SELECT MAX(`{$this->id}`) AS maxId FROM `{$this->table}`";
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$array = mysql_fetch_array($results);
			return $array['maxId'] + 1;
		}
		else
			return 1;
	}
	
	function add($turnIdOff=false,$destination=false,$upload_type=false,$width=false,$height=false,$exactFit=false)
	{
		if(!$turnIdOff)
			$maxId = $this->getMaxId();
		
		if(in_array('pageOrder',$this->columnArray))
		{
			$query = "SELECT MAX(`pageOrder`) + 1 AS pageOrder FROM `{$this->table}`";
			$arrayAll = $this->customFetchQuery($query);
			if(count($arrayAll) > 0)
				$_REQUEST['pageOrder'] = $arrayAll[0]['pageOrder'];
			else
				$_REQUEST['pageOrder'] = 0;
		}
		
		$query = "INSERT INTO `{$this->table}` (";
		
		if(!$turnIdOff)
		{
			$query .= "`{$this->id}`,";
			$query2 = "VALUES ({$maxId},";
		}
		else
			$query2 = "VALUES (";
			
		for($i=0;$i<count($this->columnArray);$i++)
		{
			$query .= "`".$this->columnArray[$i]."`,";
				
			if($this->columnArray[$i] == "timestamp")
				$query2 .= "NOW(),";
			else	
				$query2 .= "\"".addslashes(stripslashes($_REQUEST[$this->columnArray[$i]]))."\",";
		}
		
		$query = substr($query,0,-1). ")".substr($query2,0,-1).")";
		mysql_query($query) or die(mysql_error());
		
		if(trim($_FILES['file']['tmp_name']) != '')
		{
			if($upload_type == 'pic')
			{
				if($width == false || $height == false)
				{
					$imageResource = $this->getImage($_FILES['file']['tmp_name']);
					imagejpeg($imageResource,$destination.$maxId.".jpg",100);
				}
				else
				{
					$imageResource = $this->getImage($_FILES['file']['tmp_name'],$width,$height,$exactFit);
					imagejpeg($imageResource,$destination.$maxId.".jpg",100);
				}
			}
			else
			{
				if(stristr($_FILES['file']['name'],"."))
				{
					$arrayFile = explode(".",$_FILES['file']['name']);
					move_uploaded_file($_FILES['file']['tmp_name'],$destination.$maxId.".".$arrayFile[count($arrayFile)-1]);
				}
			}
		}
		
		return true;
	}
	
	function edit($destination=false,$upload_type=false,$width=false,$height=false,$exactFit=false)
	{
		if(trim($_REQUEST[$this->id]) != '')
		{	
			if(in_array('pageOrder',$this->columnArray))
			{
				$arrayOne = $this->getOne();
				$_REQUEST['pageOrder'] = $arrayOne['pageOrder'];
			}
			
			$query = "UPDATE `{$this->table}` SET ";
			
			for($i=0;$i<count($this->columnArray);$i++)
			{
				if($this->columnArray[$i] == "timestamp")
					continue;
					
				$query .= "`".$this->columnArray[$i]."` = ";	
				$query .= "\"".htmlentities(addslashes(stripslashes($_REQUEST[$this->columnArray[$i]])))."\",";
			}
			
			$query = substr($query,0,-1) . " WHERE `{$this->id}` = " . $_REQUEST[$this->id];
			mysql_query($query) or die(mysql_error());
			
			if(trim($_FILES['file']['tmp_name']) != '')
			{
				if($upload_type == 'pic')
				{
					if($width == false || $height == false)
					{
						$imageResource = $this->getImage($_FILES['file']['tmp_name']);
						imagejpeg($imageResource,$destination.$_REQUEST[$this->id].".jpg",100);
					}
					else
					{
						$imageResource = $this->getImage($_FILES['file']['tmp_name'],$width,$height,$exactFit);
						imagejpeg($imageResource,$destination.$_REQUEST[$this->id].".jpg",100);
					}
				}
				else
				{
					if(stristr($_FILES['file']['name'],"."))
					{
						$arrayFile = explode(".",$_FILES['file']['name']);
						move_uploaded_file($_FILES['file']['tmp_name'],$destination.$_REQUEST[$this->id].".".$arrayFile[count($arrayFile)-1]);
					}
				}
			}
			
			return true;
		}
		else
			return false;
	}
	
	function delete($destination=false)
	{
		if(trim($_REQUEST[$this->id]) != '')
		{	
			$query = "DELETE FROM `{$this->table}` WHERE `{$this->id}` = ".$_REQUEST[$this->id];
			mysql_query($query) or die(mysql_error());
			
			if($destination)
				@unlink($destination.$_REQUEST[$this->id].".jpg");
			
			return true;
		}
		else
			return false;
	}
	
	function deleteCustom($where)
	{
		$query = "DELETE FROM `{$this->table}` ".$where;
		mysql_query($query) or die(mysql_error());
		return true;
	}
	
	function getOne()
	{
		if(trim($_REQUEST[$this->id]) != '')
		{	
			$query = "SELECT * FROM `{$this->table}` WHERE `{$this->id}` = ".$_REQUEST[$this->id];
			$results = mysql_query($query) or die(mysql_error());
			if(mysql_num_rows($results) > 0)
			{
				$array = mysql_fetch_array($results);
				foreach($array as $key => $value)
					$array[$key] = html_entity_decode(stripslashes($value));
					
				return $array;
			}
			
			return true;
		}
		else
			return false;
	}
	
	function moveUp()
	{
		$query = "SELECT `{$this->id}`,pageOrder FROM `{$this->table}` WHERE `{$this->id}` = ".$_REQUEST[$this->id];
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$array = mysql_fetch_array($results);
			$id = $array[$this->id];
			$pageOrder = $array['pageOrder'];
			
			if($pageOrder > 0)
			{
				$query = "SELECT `{$this->id}` FROM `{$this->table}` WHERE pageOrder = ".($pageOrder - 1);
				$results = mysql_query($query) or die(mysql_error());
				if(mysql_num_rows($results) > 0)
				{
					$array = mysql_fetch_array($results);
					$idBefore = $array[$this->id];
				}
				
				if($id && $idBefore)
				{
					$query = "UPDATE `{$this->table}` SET pageOrder = pageOrder - 1 WHERE `{$this->id}` = $id";
					mysql_query($query) or die(mysql_error());
					$query = "UPDATE `{$this->table}` SET pageOrder = pageOrder + 1 WHERE `{$this->id}` = $idBefore";
					mysql_query($query) or die(mysql_error());
				}
			}
		}
	}
	
	function moveDown()
	{
		$query = "SELECT `{$this->id}`,pageOrder FROM `{$this->table}` WHERE `{$this->id}` = ".$_REQUEST[$this->id];
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$array = mysql_fetch_array($results);
			$id = $array[$this->id];
			$pageOrder = $array['pageOrder'];
			$query = "SELECT `{$this->id}` FROM `{$this->table}` WHERE pageOrder = ".($pageOrder + 1);
			$results = mysql_query($query) or die(mysql_error());
			if(mysql_num_rows($results) > 0)
			{
				$array = mysql_fetch_array($results);
				$idAfter = $array[$this->id];
				if($id && $idAfter)
				{
					$query = "UPDATE `{$this->table}` SET pageOrder = pageOrder + 1 WHERE `{$this->id}` = $id";
					mysql_query($query) or die(mysql_error());
					$query = "UPDATE `{$this->table}` SET pageOrder = pageOrder - 1 WHERE `{$this->id}` = $idAfter";
					mysql_query($query) or die(mysql_error());
				}
			}
		}
	}
	
	function getAll($extra_query=false)
	{
		$query = "SELECT * FROM `{$this->table}`";
		if(trim($extra_query) != '')
			$query .= " ".$extra_query;
		
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$arrayAll = array();
			while($array = mysql_fetch_array($results))
			{
				foreach($array as $key => $value)
					$array[$key] = html_entity_decode(stripslashes($value));
					
				$arrayAll[] = $array;
			}
			return $arrayAll;
		}
	}
	
	function getAllWithId($extra_query=false)
	{
		$query = "SELECT * FROM `{$this->table}`";
		if(trim($extra_query) != '')
			$query .= " ".$extra_query;
		
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$arrayAll = array();
			while($array = mysql_fetch_array($results))
			{
				foreach($array as $key => $value)
					$array[$key] = html_entity_decode(stripslashes($value));
					
				$arrayAll[$array[$this->id]] = $array;
			}
			return $arrayAll;
		}
	}
	
	function getAllWithId_default($extra_query=false,$id_default=false)
	{
		$query = "SELECT * FROM `{$this->table}`";
		if(trim($extra_query) != '')
			$query .= " ".$extra_query;
		
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$arrayAll = array();
			while($array = mysql_fetch_array($results))
			{
				foreach($array as $key => $value)
					$array[$key] = html_entity_decode(stripslashes($value));
				
				if($id_default != false)
					$arrayAll[$array[$id_default]] = $array;
				else
					$arrayAll[$array[$this->id]] = $array;
			}
			
			return $arrayAll;
		}
	}
	
	function customFetchQuery($query)
	{
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$arrayAll = array();
			while($array = mysql_fetch_array($results))
			{
				foreach($array as $key => $value)
					$array[$key] = stripslashes($value);
					
				$arrayAll[] = $array;
			}
			return $arrayAll;
		}
		else
			return array();
	}
	
	function customExecuteQuery($query)
	{
		mysql_query($query) or die(mysql_error());
		return true;
	}
	
	function getImage($imageResource,$width=false,$height=false,$exactFit=false)
	{
		list($width_image,$height_image,$type) = getimagesize($imageResource);
		switch($type)
		{
			case 1:
				$image = imagecreatefromgif($imageResource);
				if($width == false || $height == false)
				{}
				else
				{
					if($exactFit)
						$image = $this->image_ScaleImage($image,$width,$height,1);
					else
						$image = $this->image_ScaleImage($image,$width,$height);
				}
				return $image;
				break;
			case 2:
				$image = imagecreatefromjpeg($imageResource);
				if($width == false || $height == false)
				{}
				else
				{
					if($exactFit)
						$image = $this->image_ScaleImage($image,$width,$height,1);
					else
						$image = $this->image_ScaleImage($image,$width,$height);
				}
				return $image;
				break;
			case 3:
				$image = imagecreatefrompng($imageResource);
				if($width == false || $height == false)
				{}
				else
				{
					if($exactFit)
						$image = $this->image_ScaleImage($image,$width,$height,1);
					else
						$image = $this->image_ScaleImage($image,$width,$height);
				}
				return $image;
				break;
			case 15:
				$image = imagecreatefromwbmp($imageResource);
				if($width == false || $height == false)
				{}
				else
				{
					if($exactFit)
						$image = $this->image_ScaleImage($image,$width,$height,1);
					else
						$image = $this->image_ScaleImage($image,$width,$height);
				}
				return $image;
				break;
			default:
				return  "error";
		}
	}
	
	function image_ScaleImage($image,$new_width,$new_height,$exactFit=false)/**increases the width/height of an image while keeping the aspect ratio of the image*/
	{
		$width = $new_width;
		$height = $new_height;
		$image_width = imagesx($image);/**Determining the current image width*/
		$image_height = imagesy($image);/**Determining the current image height*/
		if($new_width == 0 and $new_height == 0)/**Checking whether width and height are both 0*/
		{
			print "Width and height of image cannot both be 0";
			exit;
		}
		if($new_width < 0 and $new_height < 0)/**Checking whether width and height are below 0*/
		{
			print "The width and height cannot both be below 0";
			exit;
		}
		
		if($image_width <= $new_width && $image_height <= $new_height)
		{
			return $image;
		}
		
		if($new_width > 0 or $new_height > 0)/**If both width and height are correct proceed*/
		{	
			if( isset($new_height) and $new_width<=0)/**If only new_height is given*/
			{
				$ratio = $new_height/$image_height;
				$new_width = $image_width * $ratio;
			}
			elseif($new_height<=0 and isset($new_width))/**If only new width is given*/
			{
				$ratio = $new_width/$image_width;
				$new_height = $image_height * $ratio;
			}
			elseif(isset($new_height) and isset($new_width))/**If both width and height are set*/
			{
				/**The max width and height are the maximum area on the html page that the image can fit into*/
				/**This could be a div / table or any html boundary*/
				
				$max_height = $new_height; 
				$max_width = $new_width;
			
				if($exactFit != false)
				{//Expand the smaller side to fit the image and then trim the bigger side
					$ratio = $image_height/$image_width;/**Working out the image aspect ration*/
					//Comparing width and height to see which is closer to what we want
					if($image_height > $image_width)
						$new_height = $new_width * $ratio;
					else if($image_height < $image_width)
						$new_width = $new_height * 1/$ratio;
				}
				else
				{
					if($image_width > $image_height) /**Special case when width is greater than height*/
					{
						$ratio = $image_height/$image_width;/**Working out the image aspect ration*/
						//$new_width = $new_height; /**Calculating new width*/
						$new_height = $new_width * $ratio; /**Calculating new height*/
					}
					else /**When height is greater than width*/
					{
						$ratio = $image_width/$image_height;
						$virtual_width = $new_height * $ratio;
						while($virtual_width > $max_width) /**Trims down the width and height observing the aspect ration until both fit into the max widht and height*/
						{
							$new_height = $new_height - 0.1;
							$virtual_width = $new_height * $ratio;
						} 
						$new_width = $virtual_width;
					}
				}
			}
		}
		/**Create the new image*/
		$image_dst = imagecreatetruecolor($new_width,$new_height); /**Creating a black background to copy the image onto*/
		imagecopyresampled($image_dst,$image,0,0,0,0,$new_width,$new_height,$image_width,$image_height); /**Resizing takes place here*/
		if($exactFit != false)
		{
			$image_dst1 = imagecreatetruecolor($width,$height); /**Creating a black background to copy the image onto*/
			if($new_width > $width)
			{
				$x = ($new_width - $width)/2;
				$y = 0;
			}
			if($new_height > $height)
			{
				$y = ($new_height - $height)/2;
				$x = 0;
			}
			imagecopy($image_dst1,$image_dst,0,0,$x,$y,$width,$height); /**Resizing takes place here*/
			return $image_dst1;
		}
		//imagedestroy($image); /**Destroy unused image*/
		return $image_dst;
	}
}
?>