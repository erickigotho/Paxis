<?php
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');

class Property {
    public function __construct(){  
    }  
	
	public function getProperties($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					id, 
					name,
					address,
					city,
					state,
					map_link,
					estimates_multiplier,
					zip,
					property_type,
					job_type,
					community,
					date_created,
					status,
					image,
					estimates_emails 	
				FROM properties
				WHERE status = 1 && in_estimates != 1 ORDER BY community,name;
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($propertyId, $name, $address, $city, $state, $mapLink,$estimates_multiplier, $zip, $property_type, $job_type, $community, $dateCreated, $status, $image,$estimates_emails);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$propertyId
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'state'=>$state
							,'mapLink'=>$mapLink
							,'estimates_multiplier'=>$estimates_multiplier
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'dateCreated'=>$dateCreated
							,'status'=>$status
							,'image'=>$image
							,'estimates_emails'=>$estimates_emails
						);
				
				$dataCtr++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function getEstimatesProperties($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					id, 
					name,
					address,
					city,
					state,
					map_link,
					estimates_multiplier,
					zip,
					property_type,
					job_type,
					community,
					date_created,
					status,
					image,
					estimates_emails 	
				FROM properties
				WHERE status = 1 && in_estimates = 1 ORDER BY community,name;
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($propertyId, $name, $address, $city, $state, $mapLink,$estimates_multiplier, $zip, $property_type, $job_type, $community, $dateCreated, $status, $image,$estimates_emails);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$propertyId
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'state'=>$state
							,'mapLink'=>$mapLink
							,'estimates_multiplier'=>$estimates_multiplier
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'dateCreated'=>$dateCreated
							,'status'=>$status
							,'image'=>$image
							,'estimates_emails'=>$estimates_emails
						);
				
				$dataCtr++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function getPropertiesSubContractors($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					id, 
					name,
					address,
					city,
					state,
					map_link,
					zip,
					property_type,
					job_type,
					community,
					date_created,
					status,
					image
				FROM properties
				WHERE status = 1 AND id IN(SELECT property_id FROM subcontractors_assign) ORDER BY community,name;
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($propertyId, $name, $address, $city, $state, $mapLink, $zip, $property_type, $job_type, $community, $dateCreated, $status, $image);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$propertyId
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'state'=>$state
							,'mapLink'=>$mapLink
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'dateCreated'=>$dateCreated
							,'status'=>$status
							,'image'=>$image
						);
				
				$dataCtr++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function addProperty($name, $address, $city, $zip, $link, $estimatesMultiplier, $status, $userId, $emails, $propertyType, $jobType,  $estimatesEmailList, $community,$estimates) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($name) || empty($name)
			|| !isset($address) || empty($address)
			|| !isset($city) || empty($city)
			|| !isset($zip) || empty($zip)
		){
			$result['message'] = 'One of the required fields is missing 123.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			/*$name = $mysqli->real_escape_string($name);
			//$address = $mysqli->real_escape_string($address);
			$city = $mysqli->real_escape_string($city);
			$zip = $mysqli->real_escape_string($zip);
			$community = $mysqli->real_escape_string($community);
			$link = $mysqli->real_escape_string($link);
			$emails = $mysqli->real_escape_string($emails);
			$estimatesEmailList = $mysqli->real_escape_string($estimatesEmailList);*/
			
			if($estimates == 'true')
			{
				$sql = "INSERT INTO properties(name, address, city, map_link, estimates_multiplier, zip, property_type, job_type, estimates_emails, community, date_created, status, created_by, emails, in_estimates) 
									values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
									
				$in_estimates = 1;
			}
			else
			{
				$sql = "INSERT INTO properties(name, address, city, map_link, estimates_multiplier, zip, property_type, job_type, estimates_emails, community, date_created, status, created_by, emails, in_estimates) 
									values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?,?)";
				$in_estimates = 0;
			}
			
			if ($insertStmt = $mysqli->prepare($sql)) {
				if($estimates == true)
				{
					$insertStmt->bind_param("ssssssiissiisi", $name, $address, $city, $link, $estimatesMultiplier, $zip, $propertyType, $jobType, $estimatesEmailList, $community, $status, $userId, $emails,$in_estimates);
				}
				else
				{
					$insertStmt->bind_param("ssssssiissiis", $name, $address, $city, $link, $estimatesMultiplier, $zip, $propertyType, $jobType, $estimatesEmailList, $community, $status, $userId, $emails,$in_estimates);
				}

				if($insertStmt->execute()) {
					$propertyId = $mysqli->insert_id;
					$result['propertyId'] = $propertyId;
					$result['success'] = true;
					$result['message'] = "Property added successfully!";
				} else {
					if(strrpos($mysqli->error, 'Duplicate entry') == false) {
						$result['message'] = "The property name you provided already exist.";
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request.";
					}
				}
				
				$insertStmt->close();
			} else {
				$result['message'] = "Sorry, there has been a problem processing your request. 2";
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function searchProperty($search)
	{
		$result['success'] = false;
		$result['message'] = '';
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$search = $mysqli->real_escape_string($search);
			
		$search = "%{$search}%";
		
		//$data = array();
		
		$query = "SELECT id,name,address,city,zip,map_link,property_type,job_type,community,estimates_emails,estimates_multiplier 
		FROM properties 
		WHERE (name LIKE ? OR address LIKE ? OR city LIKE ? OR state LIKE ? OR map_link LIKE ? OR zip LIKE ? OR community LIKE ? 
		OR emails LIKE ? OR estimates_emails LIKE ? OR estimates_multiplier LIKE ?) AND status = 1 AND in_estimates != 1 ORDER BY community";
		
		if ($stmt = $mysqli->prepare($query)) 
		{
			$stmt->bind_param("ssssssssss", $search,$search,$search,$search,$search,$search,$search,$search,$search,$search);
			$stmt->execute();
			$stmt->bind_result($id, $name, $address, $city, $zip, $mapLink, $property_type, $job_type, $community,$estimates_emails,$estimates_multiplier);
			
			$dataCtr = 0;
			while ($stmt->fetch()) 
			{
				$data[$dataCtr] = array(
							 'id'=>$id
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'zip'=>$zip
							,'mapLink'=>$mapLink
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'estimates_emails'=>$estimates_emails
							,'estimates_multiplier'=>$estimates_multiplier
						);
				$dataCtr++;
			}
			
			$stmt->close();
			
			return $data;
		}
	}
	
	public function searchPropertyArchives($search)
	{
		$result['success'] = false;
		$result['message'] = '';
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$search = $mysqli->real_escape_string($search);
			
		$search = "%{$search}%";
		
		//$data = array();
		
		$query = "SELECT id,name,address,city,zip,map_link,property_type,job_type,community,estimates_emails,estimates_multiplier 
		FROM properties 
		WHERE (name LIKE ? OR address LIKE ? OR city LIKE ? OR state LIKE ? OR map_link LIKE ? OR zip LIKE ? OR community LIKE ? 
		OR emails LIKE ? OR estimates_emails LIKE ? OR estimates_multiplier LIKE ?) AND status = 0 ORDER BY community";
		
		if ($stmt = $mysqli->prepare($query)) 
		{
			$stmt->bind_param("ssssssssss", $search,$search,$search,$search,$search,$search,$search,$search,$search,$search);
			$stmt->execute();
			$stmt->bind_result($id, $name, $address, $city, $zip, $mapLink, $property_type, $job_type, $community,$estimates_emails,$estimates_multiplier);
			
			$dataCtr = 0;
			while ($stmt->fetch()) 
			{
				$data[$dataCtr] = array(
							 'id'=>$id
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'zip'=>$zip
							,'mapLink'=>$mapLink
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'estimates_emails'=>$estimates_emails
							,'estimates_multiplier'=>$estimates_multiplier
						);
				$dataCtr++;
			}
			
			$stmt->close();
			
			return $data;
		}
	}
	
	public function updatePropertyInfo($propertyId, $name, $address, $city, $zip, $link, $estimatesMultiplier, $status, $userId, $emails, $propertyType, $jobType, $estimatesEmailList, $community) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($propertyId) || empty($propertyId)
			|| !isset($name) || empty($name)
			|| !isset($address) || empty($address)
			|| !isset($city) || empty($city)
			|| !isset($zip) || empty($zip)
			|| !isset($userId) || empty($userId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$updateSql = "UPDATE properties
								 SET name = ?
								    ,address = ?
								    ,city = ?
								    ,map_link = ?
									,estimates_multiplier = ?
								    ,zip = ?
									,property_type = ?
									,job_type = ?
									,estimates_emails = ?
									,community = ?
								    ,date_updated = NOW()
								    ,status = ?
								    ,updated_by = ?
									,emails = ?
							  WHERE id = ?";
			
			if ($updateStmt = $mysqli->prepare($updateSql)) {
				$updateStmt->bind_param("ssssssiisssisi", $name, $address, $city, $link, $estimatesMultiplier, $zip, $propertyType, $jobType, $estimatesEmailList, $community, $status, $userId, $emails, $propertyId);
				
				if($updateStmt->execute()) {
					$result['success'] = true;
					$result['message'] = "Property successfully updated.";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request.";
				}
				
				$updateStmt->close();
			} else {
				//$mysqli->error
				$result['message'] = 'Sorry, there has been a problem processing your request.';
			}
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getPropertyInfo($propertyId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					 name
					,address
					,city
					,state
					,map_link
					,estimates_multiplier
					,zip
					,property_type
					,job_type
					,community
					,date_created
					,status
					,emails
					,estimates_emails
					,in_estimates
				FROM properties
				WHERE id=?
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $propertyId);
			$stmt->execute();
			$stmt->bind_result($name, $address, $city, $state, $map_link, $estimates_multiplier, $zip, $property_type, $job_type, $community, $date_created, $status, $emails, $estimates_emails, $in_estimates);
			
			while ($stmt->fetch()) {
				$data = array(
							 'id'=>stripslashes($propertyId)
							,'name'=>stripslashes($name)
							,'address'=>stripslashes($address)
							,'city'=>stripslashes($city)
							,'state'=>stripslashes($state)
							,'map_link'=>stripslashes($map_link)
							,'estimates_multiplier'=>stripslashes($estimates_multiplier)
							,'zip'=>stripslashes($zip)
							,'property_type'=>stripslashes($property_type)
							,'job_type'=>stripslashes($job_type)
							,'community'=>stripslashes($community)
							,'date_created'=>stripslashes($date_created)
							,'status'=>stripslashes($status)
							,'emails'=>stripslashes($emails)
							,'estimates_emails'=>stripslashes($estimates_emails)
							,'in_estimates'=>$in_estimates
						);
			}
			
			$stmt->close();	
			
			$checkSavedReportSql = "SELECT count(*) FROM reports WHERE property_id=? AND is_saved=1";
			
			if ($checkSavedReportStmt = $mysqli->prepare($checkSavedReportSql)) {
				$checkSavedReportStmt->bind_param("i", $propertyId);
				$checkSavedReportStmt->execute();
				$checkSavedReportStmt->bind_result($isSaved);
				$checkSavedReportStmt->fetch();
				$checkSavedReportStmt->close();
				
				$data['isSaved'] = $isSaved;
			} 
		
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function getArchivedProperties($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					id, 
					name,
					address,
					city,
					state,
					map_link,
					estimates_multiplier,
					zip,
					property_type,
					job_type,
					community,
					date_created,
					date_closed,
					status,
					image,
					estimates_emails
				FROM properties
				WHERE status = 0
				ORDER BY date_closed DESC
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($propertyId, $name, $address, $city, $state, $mapLink, $estimates_multiplier, $zip, $property_type, $job_type, $community, $dateCreated, $dateClosed, $status, $image,$estimates_emails);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$propertyId
							,'name'=>$name
							,'address'=>$address
							,'city'=>$city
							,'state'=>$state
							,'mapLink'=>$mapLink
							,'estimates_multiplier'=>$estimates_multiplier
							,'zip'=>$zip
							,'property_type'=>$property_type
							,'job_type'=>$job_type
							,'community'=>$community
							,'dateCreated'=>$dateCreated
							,'dateClosed'=>$dateClosed
							,'status'=>$status
							,'image'=>$image
							,'estimates_emails'=>$estimates_emails
						);
				
				$reportsSql = "SELECT 
									 users.first_name
									,reports.id
									,users.last_name
									,companies.name
								FROM reports
									INNER JOIN users ON users.id = reports.reported_by
									INNER JOIN companies ON companies.id = users.company_id
								WHERE property_id = ?
								ORDER BY date_reported DESC
								";
				
				if ($reportsStmt = $mysqli->prepare($reportsSql)) {
					$reportsStmt->bind_param('i', $propertyId);
					$reportsStmt->execute();
					$reportsStmt->bind_result($reportFirstName, $lastReportId, $reportLastName, $companyName);
					$reportsStmt->fetch();
					
					$data[$dataCtr]['lastReportBy'] = $reportFirstName . ' ' . $reportLastName;
					$data[$dataCtr]['lastReportCompany'] = $companyName;
					$data[$dataCtr]['lastReportId'] = $lastReportId;
					
					$reportsStmt->close();
				}
				
				$dataCtr++;
			}
			
			$stmt->free_result();
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}

	public function archiveProperty($propertyId, $status, $userId, $reportId) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($propertyId) || empty($propertyId)
			|| !isset($userId) || empty($userId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$updatePropertySql = "UPDATE properties
								 SET date_closed = NOW()
								    ,status = ?
								    ,closed_by = ?
							  WHERE id = ?";
			
			if ($updateStmt = $mysqli->prepare($updatePropertySql)) {
				$updateStmt->bind_param("iii", $status, $userId, $propertyId);
				
				if($updateStmt->execute()) {
					
					//Update and close the report
					$updateReportSql = "UPDATE reports
											 SET date_closed = NOW()
												,is_closed = '1'
												,is_saved = '0'
												,is_submitted = '1'
										  WHERE id = ?";
			
					if ($updateReportStmt = $mysqli->prepare($updateReportSql)) {
						$updateReportStmt->bind_param("i", $reportId);
						
						if($updateReportStmt->execute()) {
							$result['success'] = true;
							$result['message'] = "This property is now closed/archived. ";
						}
						
						$updateReportStmt->close();
					}
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request.";
				}
				
				$updateStmt->close();
			} else {
				//$mysqli->error
				$result['message'] = 'Sorry, there has been a problem processing your request.';
			}
			
			
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
}
?>
