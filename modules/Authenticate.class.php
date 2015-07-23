<?php
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');

class Authenticate {
	public function __construct(){  
    }  
	
	public function validateEmail($email){  
        $test = preg_match(EMAIL_PATTERN, $email);  
        return $test;  
    } 
	
	public function register($email, $password, $firstname, $lastname) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($email) || empty($email) ||  !isset($password) || empty($password) || !isset($firstname) || empty($firstname) || !isset($lastname) || empty($lastname)){
			$result['message'] = 'All fields are required.';
		} else if(!$this->validateEmail($email)) {
			$result['message'] = 'Please enter a valid email address.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$email = $mysqli->real_escape_string($email);
			$password = $mysqli->real_escape_string($password);
			$firstname = $mysqli->real_escape_string($firstname);
			$lastname = $mysqli->real_escape_string($lastname);
			$isActive = 1;
			$userType = 0;
			
			$sql = "SELECT email FROM users WHERE email=?";
			if ($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param("s", $email);
				$stmt->execute();
				$stmt->bind_result($emailAdd);
			    $stmt->fetch();
				
				if(!empty($emailAdd)) {
					$result['message'] = "The email address you provided already exist.";
				} else {
					$encryptedPass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), $password, MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))));
					
					$sql = "INSERT INTO users(email, password, first_name, last_name, date_joined, is_active, user_type) values(?, ?, ?, ?, NOW(), ?, ?)";
					
					if ($insertStmt = $mysqli->prepare($sql)) {
						$insertStmt->bind_param("ssssii", $email, $encryptedPass, $firstname, $lastname, $isActive, $userType);
						
						if($insertStmt->execute()) {
							$result['success'] = true;
							$result['message'] = "User successfully added!";
						} else {
							$result['message'] = "Sorry, there has been a problem processing your request.";
						}
						
						$insertStmt->close();
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request.";
					}
				}
				
				$stmt->close();
			}  
			
			$mysqli->close();
		}
		
		
		echo json_encode($result, JSON_FORCE_OBJECT);
	}
	
	public function login($email, $password) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($email) || empty($email) ||  !isset($password) || empty($password)){
			$result['message'] = 'All fields are required.';
		} else if(!$this->validateEmail($email)) {
			$result['message'] = 'Please enter a valid email address.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$email = $mysqli->real_escape_string($email);
			$password = $mysqli->real_escape_string($password);
			
			//$sql = "SELECT id, email, password, first_name, last_name, is_active, user_type, company_id FROM users WHERE email=? AND is_confirmed=1";
			$sql = "SELECT id, email, password, first_name, last_name, is_active, user_type, company_id FROM users WHERE email=?";
			$stmt = $mysqli->prepare($sql);
			
			if ($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param("s", $email);
				$stmt->execute();
				$stmt->bind_result($userId, $email, $dbPassword, $firstName, $lastName, $isActive, $userType, $companyId);
			    $stmt->fetch();
				$stmt->close();
				
				if(!empty($email) || isset($email)) {
				
					if($isActive == 0) {
						$this->loginSubcontractor($email, $password, $mysqli);
						//$result['message'] = 'Account disabled.';
						exit;
					} else {
						$decryptedPass = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), base64_decode($dbPassword), MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))), "\0");
						
						if($decryptedPass == $password) {
							$_SESSION['user_id'] = $userId;
							$_SESSION['user_email'] = $email;
							$_SESSION['user_firstname'] = $firstName;
							$_SESSION['user_lastname'] = $lastName;
							$_SESSION['user_type'] = $userType;
							$_SESSION['company_id'] = $companyId;
							
							$updateSql = "UPDATE users SET last_login = NOW() WHERE email = ?";
							
							if ($updateStmt = $mysqli->prepare($updateSql)) {
								$updateStmt->bind_param("s", $email);
								$updateStmt->execute();
								$updateStmt->close();
								
								$result['success'] = true;
								$result['message'] = 'Success!';
							} else {
								$result['message'] = 'Sorry, there has been a problem processing your request. ';
							}
						} else {
							$result['message'] = 'The username or password is invalid.';
						}
					}
				} else {
					$result['message'] = 'The username or password is invalid.';
				}
				
			} else {
				$result['message'] = 'The username or password is invalid.';
			}
			
			$mysqli->close();
		}
		
		echo json_encode($result, JSON_FORCE_OBJECT);
	}
	
	function loginSubcontractor($email, $password, $mysqli)
	{
		$sql = "SELECT id, email, password, first_name, last_name, is_active FROM sub_contractors WHERE email=?";
		$stmt = $mysqli->prepare($sql);
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$stmt->bind_result($userId, $email, $dbPassword, $firstName, $lastName, $isActive);
			$stmt->fetch();
			$stmt->close();
			
			if(!empty($email) || isset($email)) {
				if($isActive == 0) {
					$result['message'] = 'Account disabled.';
					
				} else {
					
					$decryptedPass = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), base64_decode($dbPassword), MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))), "\0");
					
					if($decryptedPass == $password) {
						$_SESSION['user_id'] = $userId;
						$_SESSION['user_email'] = $email;
						$_SESSION['user_firstname'] = $firstName;
						$_SESSION['user_lastname'] = $lastName;
						$_SESSION['user_type'] = 5;
						
						$updateSql = "UPDATE sub_contractors SET last_login = NOW() WHERE email = ?";
						
						if ($updateStmt = $mysqli->prepare($updateSql)) {
							$updateStmt->bind_param("s", $email);
							$updateStmt->execute();
							$updateStmt->close();
							
							$result['success'] = true;
							$result['message'] = 'Success!';
						} else {
							$result['message'] = 'Sorry, there has been a problem processing your request. ';
						}
					} else {
						$result['message'] = 'The username or password is invalid.';
					}
				}
			} else {
				$result['message'] = 'The username or password is invalid.';
			}
			
		} else {
			$result['message'] = 'The username or password is invalid.';
		}
		
		//$mysqli->close();
		echo json_encode($result, JSON_FORCE_OBJECT);
	}
}
?>
