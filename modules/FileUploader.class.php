<?php
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');

class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
	 public function __construct(){  
    }   
	 
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
		$this->removeExif($path);
		
        return true;
    }
	
	function removeExif($path)
	{
		$image = imagecreatefromstring(file_get_contents($path));
		$exif = @exif_read_data($path);
		if(!empty($exif['Orientation'])) 
		{
			switch($exif['Orientation']) 
			{
				case 8:
					$image = imagerotate($image,90,0);
					break;
				case 3:
					$image = imagerotate($image,180,0);
					break;
				case 6:
					$image = imagerotate($image,-90,0);
					break;
			}
			
			imagejpeg($image,$path);
		}
	}
	
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}
?>