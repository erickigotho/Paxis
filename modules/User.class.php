<?php 
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');

class User {
    public function __construct(){  
    }  
	
	public function validateEmail($email){  
        $test = preg_match(EMAIL_PATTERN, $email);  
        return $test;  
    }
	
	public function confirmUser($code) {
		$isConfirmed = false;
		
		if(empty($code) || !isset($code)) {
			echo "Confirmation code missing.";
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$sql = "SELECT email FROM users WHERE confirmation_code=? AND is_confirmed=0";
			
			if ($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param("s", $code);
				$stmt->execute();
				$stmt->bind_result($dbEmail);
				$stmt->fetch();
				$stmt->close();
				
				if(!empty($dbEmail)) {
					$updateSql = "UPDATE users 
										 SET is_confirmed = 1
								  WHERE email = ?";
									  
					if ($updateStmt = $mysqli->prepare($updateSql)) {
						$updateStmt->bind_param("s", $dbEmail);
						
						if($updateStmt->execute()) {
							$isConfirmed = true;
						} 
						
						$updateStmt->close();
					} 
				}
			}
			
			$mysqli->close();
		}
		
		return $isConfirmed;
	}
	
	public function addUser($email, $password, $firstName, $lastName, $companyId, $userType, $isActive, $phone, $baseUrl) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(empty($email) 
			|| !isset($email) 
			|| empty($password) 
			|| !isset($password) 
			|| empty($firstName) 
			|| !isset($firstName) 
			|| empty($lastName) 
			|| !isset($lastName) 
			|| empty($companyId) 
			|| !isset($companyId) 
			|| empty($userType)
			|| !isset($userType)
			) {
			$result['message'] = 'One of the required fields is missing.';
		} else if(!$this->validateEmail($email)) {
			$result['message'] = 'Please enter a valid email address.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$email = $mysqli->real_escape_string($email);
			$password = $mysqli->real_escape_string($password);
			$firstName = $mysqli->real_escape_string($firstName);
			$lastName = $mysqli->real_escape_string($lastName);
			$phone = $mysqli->real_escape_string($phone);
			
			$sql = "SELECT email FROM users WHERE email=?";
			if ($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param("s", $email);
				$stmt->execute();
				$stmt->bind_result($dbEmail);
			    $stmt->fetch();
				$stmt->close();
				
				if(!empty($dbEmail)) {
					$result['message'] = "The email address you provided already exist.";
				} else {
					$encryptedPass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), $password, MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))));
					
					$salt = "Pax1SpUNchL1stPr0";

					$confirmationCodeBytes = openssl_random_pseudo_bytes(16, $salt);
					$confirmationCode  = bin2hex($confirmationCodeBytes);

					$sql = "INSERT INTO users(email
											, password
											, first_name
											, last_name
											, phone_number
											, company_id
											, date_joined
											, is_active
											, user_type
											, confirmation_code) 
										values(?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
					
					if ($insertStmt = $mysqli->prepare($sql)) {
						$insertStmt->bind_param("sssssiiis", $email, $encryptedPass, $firstName, $lastName, $phone, $companyId, $isActive, $userType, $confirmationCode);
						
						if($insertStmt->execute()) {
							$result['success'] = true;
							$result['message'] = "User successfully added!";
							
							$this->sendEmailReport($firstName . ' ' . $lastName, $email, $password, $confirmationCode, $baseUrl);
						} else {
							$result['message'] = "Sorry, there has been a problem processing your request.";
						}
						
						$insertStmt->close();
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request.";
					}
				}
			}
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getAllUsers($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					users.id, 
					users.email, 
					users.first_name, 
					users.last_name, 
					companies.name as company_name, 
					user_types.name as user_type,
					users.last_login 
				FROM users
					INNER JOIN user_types ON user_types.id = users.user_type
					INNER JOIN companies ON companies.id = users.company_id
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($userId, $email, $firstName, $lastName, $company, $userType, $lastLogin);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$userId
							,'email'=>$email
							,'firstName'=>$firstName
							,'lastName'=>$lastName
							,'company'=>$company
							,'userType'=>$userType
							,'lastLogin'=>$lastLogin
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
	
	public function getAllUsersPerCompany($companyId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					users.id, 
					users.email, 
					users.first_name, 
					users.last_name, 
					companies.name as company_name, 
					user_types.name as user_type,
					users.last_login 
				FROM users
					INNER JOIN user_types ON user_types.id = users.user_type
					INNER JOIN companies ON companies.id = users.company_id
				WHERE users.company_id=?
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $companyId);
			$stmt->execute();
			$stmt->bind_result($userId, $email, $firstName, $lastName, $company, $userType, $lastLogin);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$userId
							,'email'=>$email
							,'firstName'=>$firstName
							,'lastName'=>$lastName
							,'company'=>$company
							,'userType'=>$userType
							,'lastLogin'=>$lastLogin
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
	
	public function getUserInfo($userId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					users.id as userId, 
					users.email, 
					users.first_name, 
					users.last_name, 
					users.phone_number, 
					users.company_id, 
					companies.name,
					users.user_type,
					user_types.name,
					users.last_login,
					users.is_active
				FROM users
					INNER JOIN companies ON companies.id = users.company_id
					INNER JOIN user_types ON user_types.id = users.user_type
				WHERE users.id=?
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $userId);
			$stmt->execute();
			$stmt->bind_result($userId, $email, $firstName, $lastName, $phoneNumber, $companyId, $companyName, $userType, $userTypeName, $lastLogin, $isActive);
			
			while ($stmt->fetch()) {
				$data = array(
								 'id' 		=> $userId
								,'email' 	=> $email
								,'firstName'=> $firstName
								,'lastName'	=> $lastName
								,'phoneNumber'	=> $phoneNumber
								,'companyId'=> $companyId
								,'companyName'=> $companyName
								,'userType'	=> $userType
								,'userTypeName'	=> $userTypeName
								,'lastLogin'=> $lastLogin
								,'isActive'	=> $isActive
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

	public function updateUserInfo($id, $email, $password, $firstName, $lastName, $companyId, $userType, $isActive, $phone, $chkSendEmailPassword) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($id) || empty($id)
		   || !isset($email) || empty($email)
		   || !isset($firstName) || empty($firstName)
		   || !isset($lastName) || empty($lastName)
		   ){
			$result['message'] = 'One of the required fields is missing.';
		} else if(!$this->validateEmail($email)) {
			$result['message'] = 'Please enter a valid email address.';
		} else {
			
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$email = $mysqli->real_escape_string($email);
			$firstName = $mysqli->real_escape_string($firstName);
			$lastName = $mysqli->real_escape_string($lastName);
			
			$isContinueUpdate = false;
			
			if(!empty($password)) {
				$password = $mysqli->real_escape_string($password);
			
				$sql = "SELECT password FROM users WHERE id=?";
				
				if ($stmt = $mysqli->prepare($sql)) {
					$stmt->bind_param("i", $id);
					$stmt->execute();
					$stmt->bind_result($dbPassword);
					$stmt->fetch();
					/*
						Close this before executing another statement 
						or else it will give error in the UPDATE below
						as Commands out of sync; you can't run this command 
						now in your client code
					*/
					$stmt->close();
					
					$decryptedPass = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), base64_decode($dbPassword), MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))), "\0");
					
					if($decryptedPass == $password) {
						$result['message'] = "You cannot reuse that password yet. Please choose a different password.";
					} else {
						$updateSql = "UPDATE users 
										 SET email = ?
											,password = ?
											,first_name = ?
											,last_name = ?
											,phone_number = ?
											,company_id = ?
											,user_type = ?
											,is_active = ?
									  WHERE id = ?";
									  
						$encryptedPass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), $password, MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))));			  
						if ($updateStmt = $mysqli->prepare($updateSql)) {
							$updateStmt->bind_param("sssssiiii", $email, $encryptedPass, $firstName, $lastName, $phone, $companyId, $userType, $isActive, $id);
							
							if($updateStmt->execute()) {
								if($chkSendEmailPassword == 1)
									$this->sendEmailReport_reset($firstName . ' ' . $lastName, $email, $password, $baseUrl);
								
								$result['success'] = true;
								$result['message'] = 'User information updated successfully!';
							} else {
								$result['message'] = "Sorry, there has been a problem processing your request. 1";
							}
							
							$updateStmt->close();
						} else {
							$result['message'] = 'Sorry, there has been a problem processing your request.';
						}
					}
				}
			} else {
				$updateSql = "UPDATE users 
								 SET email = ?
									,first_name = ?
									,last_name = ?
									,phone_number = ?
									,company_id = ?
									,user_type = ?
									,is_active = ?
							  WHERE id = ?";
							  
				if ($updateStmt2 = $mysqli->prepare($updateSql)) {
					$updateStmt2->bind_param("ssssiiii", $email, $firstName, $lastName, $phone, $companyId, $userType, $isActive, $id);
					if($updateStmt2->execute()) {
						$result['success'] = true;
						$result['message'] = "Password successfully updated.";
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request. 1";
					}
					
					$updateStmt2->close();
					
					$result['success'] = true;
					$result['message'] = 'User information updated successfully!';
				} else {
					$result['message'] = 'Sorry, there has been a problem processing your request. 1';
				}
			}
			
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getUserTypes($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					 user_types.id
					,user_types.name
				FROM user_types
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($userTypeId, $name);
			
			$counter = 0;
			while ($stmt->fetch()) {
				$data[$counter] = array(
								 'id' 	=> $userTypeId
								,'name' => $name
							);
				
				$counter++;
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
	
	private function sendEmailReport_reset($name, $recipientEmail, $recipientPassword, $baseUrl) {
		$mailto = $recipientEmail;
		$mailSubject = 'Change of registration on Paxis Pro';
		
		
		$searchArray = array('__NAME__',  '__EMAIL__', '__PASSWORD__', '__BASEADDRESS__');
		$replaceArray = array($name, $recipientEmail, $recipientPassword, $baseUrl);
		
		$emailBody = file_get_contents(dirname(dirname(__FILE__)) . '/email_template/password_reset.tpl');
		$emailBody = str_replace($searchArray, $replaceArray, $emailBody);
		
		// $mailHeaders = "From: Wendell Malpas <wendell.malpas@verifone.com> \r\n";
		// $mailHeaders .= "Reply-To:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
		// $mailHeaders .= "Return-Path:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
		$mailHeaders = "From: Paxis Group <no-reply@Paxisgroup.com> \r\n";
		$mailHeaders .= "Reply-To: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Return-Path: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Bcc: Wendell Malpas <wendell.malpas@gmail.com>\r\n";
		$mailHeaders .= "X-Mailer: PHP v" .phpversion(). "\r\n";
		$mailHeaders .= "MIME-Version: 1.0\r\n";
		$mailHeaders .= "Content-Type: text/html; charset=utf-8";
		
		return mail($mailto, $mailSubject, $emailBody, $mailHeaders);
	}
	
	private function sendEmailReport($name, $recipientEmail, $recipientPassword, $confirmationCode, $baseUrl) {
		$mailto = $recipientEmail;
		$mailSubject = 'Confirm registration on Paxis Pro';
		
		
		$searchArray = array('__NAME__', '__CODE__', '__EMAIL__', '__PASSWORD__', '__BASEADDRESS__');
		$replaceArray = array($name, $confirmationCode, $recipientEmail, $recipientPassword, $baseUrl);
		
		$emailBody = file_get_contents(dirname(dirname(__FILE__)) . '/email_template/confirm_user.tpl');
		$emailBody = str_replace($searchArray, $replaceArray, $emailBody);
		
		// $mailHeaders = "From: Wendell Malpas <wendell.malpas@verifone.com> \r\n";
		// $mailHeaders .= "Reply-To:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
		// $mailHeaders .= "Return-Path:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
		$mailHeaders = "From: Paxis Group <no-reply@Paxisgroup.com> \r\n";
		$mailHeaders .= "Reply-To: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Return-Path: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Bcc: Wendell Malpas <wendell.malpas@gmail.com>\r\n";
		$mailHeaders .= "X-Mailer: PHP v" .phpversion(). "\r\n";
		$mailHeaders .= "MIME-Version: 1.0\r\n";
		$mailHeaders .= "Content-Type: text/html; charset=utf-8";
		
		return mail($mailto, $mailSubject, $emailBody, $mailHeaders);
	}
}
?>
