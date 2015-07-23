<?php
class Tools
{
	function replaceBadCharactersImage($string)
	{
		$arrayCharacters = array("(","!","`","~","@","#","$","%","^","&","*",")","+","{","}","[","]",";",":",'"',"'","<",">",
			",","?","/"," ","\\","|"," ");
		return $this->clean(str_replace($arrayCharacters,"",$string));
	}
	
	function clean($string) 
	{
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	   return preg_replace('/[^A-Za-z0-9\-.]/', '', $string); // Removes special chars.
	}
	function convertArrayToRequest($array)
	{
		if(count($array) > 0)
		{
			foreach($array as $key => $value)
			{
				if($key == 'date_created')
					$_REQUEST[$key] = date("Y-m-d H:i:s",time());
				if($key != 'id')
					$_REQUEST[$key] = $value;
			}
		}
	}
	
	function getSubContractorToEmail($reportId,$property_id,$dynamo)
	{
		$query = "SELECT room_template_items.work_category_id FROM report_room_items 
		INNER JOIN room_template_items ON report_room_items.room_template_item_id = room_template_items.id
		WHERE status_id = 3 AND report_id = ".$reportId;
		
		$array_work_categories_ids = array();
		
		$array_work_categories_ids = $dynamo->customFetchQuery($query);
		
		if(count($array_work_categories_ids) > 0)
		{
			$work_categories_ids_string = '';
			for($i=0;$i<count($array_work_categories_ids);$i++)
				$work_categories_ids_string = $array_work_categories_ids[$i]['work_category_id'].",";
			
			$work_categories_ids_string = substr($work_categories_ids_string,0,-1);
			
			$query = "SELECT sub_contractor_id FROM subcontractors_assign WHERE property_id = ".$property_id." AND work_category_id IN(".$work_categories_ids_string.")";
			
			$array_sub_contractor_ids = array();
			$array_sub_contractor_ids = $dynamo->customFetchQuery($query);
			
			if(count($array_sub_contractor_ids) > 0)
			{
				for($i=0;$i<count($array_sub_contractor_ids);$i++)
					$sub_contractor_id_string = $array_sub_contractor_ids[$i]['sub_contractor_id'].",";
				
				$sub_contractor_id_string = substr($sub_contractor_id_string,0,-1);
				
				$query = "SELECT * FROM sub_contractors WHERE id IN(".$sub_contractor_id_string.")";
				$array_sub_contractors = array();
				$array_sub_contractors = $dynamo->customFetchQuery($query);
				return $array_sub_contractors;
			}
		}
	}
	
	function getSubContractorToEmail_faster($reportId,$property_id,$dynamo)
	{
		$array_sub_contractors = array();
		
		$query = "SELECT room_template_items.work_category_id FROM report_room_items 
		INNER JOIN room_template_items ON report_room_items.room_template_item_id = room_template_items.id
		WHERE status_id = 3 AND report_id = ".$reportId;
		
		$results = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($results) > 0)
		{
			$work_category_id = '';
			while($array = mysql_fetch_array($results))
				$work_category_id .= $array['work_category_id'].",";
		}
		
		if(trim($work_category_id) != '')
		{
			$work_category_id = substr($work_category_id,0,-1);
			$query = "SELECT sub_contractor_id FROM subcontractors_assign WHERE property_id = ".$property_id." AND work_category_id IN(".$work_category_id.")";
			$results = mysql_query($query) or die(mysql_error());
			if(mysql_num_rows($results) > 0)
			{
				$sub_contractor_id = '';
				while($array = mysql_fetch_array($results))
					$sub_contractor_id .= $array['sub_contractor_id'].",";
			}			
		}
		
		if(trim($sub_contractor_id) != '')
		{
			$sub_contractor_id = substr($sub_contractor_id,0,-1);
			$query = "SELECT `email` FROM sub_contractors WHERE id IN(".$sub_contractor_id.")";
			$results = mysql_query($query) or die(mysql_error());
			if(mysql_num_rows($results) > 0)
			{
				while($array = mysql_fetch_array($results))
				{
					$array_sub_contractors[] = $array['email'];
				}
			}
		}
		
		return $array_sub_contractors;
	}
}
?>