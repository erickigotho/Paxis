<?php
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');

class Company {
    public function __construct(){  
    }  
	
	public function validateEmail($email){  
        $test = preg_match(EMAIL_PATTERN, $email);  
        return $test;  
    }
	
	public function updateCompanyInfo($companyId, $companyName) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($companyId) || empty($companyId)
			|| !isset($companyName) || empty($companyName)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$updateSql = "UPDATE companies
								 SET name = ?
							  WHERE id = ?";
			
			if ($updateStmt = $mysqli->prepare($updateSql)) {
				$updateStmt->bind_param("si", $companyName, $companyId);
				
				if($updateStmt->execute()) {
					$result['success'] = true;
					$result['message'] = "Company successfully updated.";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request. 1";
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
	
	public function getCompanyInfo($companyId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					name,
					date_created
				FROM companies
				WHERE id=?
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $companyId);
			$stmt->execute();
			$stmt->bind_result($companyName, $dateCreated);
			
			while ($stmt->fetch()) {
				$data = array(
							 'id'=>$companyId
							,'name'=>$companyName
							,'dateCreated'=>$dateCreated
						);
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
	
	public function addCompany($companyName) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($companyName) || empty($companyName)){
			$result['message'] = 'Please provide a company name.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$companyName = $mysqli->real_escape_string($companyName);
			
			$sql = "INSERT INTO companies(name, date_created, is_active) values(?, NOW(), 1)";
					
			if ($insertStmt = $mysqli->prepare($sql)) {
				$insertStmt->bind_param("s", $companyName);
				
				if($insertStmt->execute()) {
					$result['success'] = true;
					$result['message'] = "The company was added successfully!";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request." .$mysqli->error;
				}
				
				$insertStmt->close();
			} else {
				$result['message'] = "Sorry, there has been a problem processing your request.";
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getCompanyList($isJson = true) {
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
					date_created
				FROM companies
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($companyId, $companyName, $dateCreated);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$companyId
							,'name'=>$companyName
							,'dateCreated'=>$dateCreated
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
	
}
?>
