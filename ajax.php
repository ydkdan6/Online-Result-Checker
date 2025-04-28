<?php
ob_start();
date_default_timezone_set("Asia/Manila");

$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();
if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}

if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'update_user'){
	$save = $crud->update_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'save_class'){
	$save = $crud->save_class();
	if($save)
		echo $save;
}
if($action == 'delete_class'){
	$save = $crud->delete_class();
	if($save)
		echo $save;
}
if($action == 'save_subject'){
	$save = $crud->save_subject();
	if($save)
		echo $save;
}
if($action == 'delete_subject'){
	$save = $crud->delete_subject();
	if($save)
		echo $save;
}
if($action == 'save_student'){
	$save = $crud->save_student();
	if($save)
		echo $save;
}
if($action == 'delete_student'){
	$save = $crud->delete_student();
	if($save)
		echo $save;
}
if($action == 'save_result'){
	$save = $crud->save_result();
	if($save)
		echo $save;
}
if($action == 'delete_result'){
	$save = $crud->delete_result();
	if($save)
		echo $save;
}

// New OTP handling actions
if($action == 'check_student_and_generate_otp'){
    $student_code = $_POST['student_code'];
    $email = $_POST['email']; // Get the email entered by the user
    
    $qry = $crud->db->query("SELECT id FROM students WHERE student_code = '$student_code'");
    if($qry->num_rows > 0){
        // Student exists, generate OTP
        $otp = generateOTP();
        
        // Save OTP and student code in session
        $_SESSION['student_otp'] = $otp;
        $_SESSION['student_otp_expiry'] = time() + 300; // 5 minutes expiry
        $_SESSION['student_code'] = $student_code;
        
        // Send OTP email
        $response = sendOTPToEmail($otp, $email);
        
        // Return success with OTP (in production, you would send OTP via email/SMS)
        echo json_encode(array('status' => 'success', 'otp' => $otp, 'email_response' => $response));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Student ID # is incorrect.'));
    }
}


if($action == 'verify_student_otp'){
    $input_otp = $_POST['otp'];
    
    // Check if OTP exists in session
    if(!isset($_SESSION['student_otp']) || !isset($_SESSION['student_otp_expiry'])){
        echo json_encode(array(
            'valid' => false,
            'message' => 'No OTP found. Please try again.'
        ));
        exit;
    }
    
    // Check if OTP is expired
    if(time() > $_SESSION['student_otp_expiry']){
        // Clear expired OTP
        unset($_SESSION['student_otp']);
        unset($_SESSION['student_otp_expiry']);
        
        echo json_encode(array(
            'valid' => false,
            'message' => 'OTP has expired. Please try again.'
        ));
        exit;
    }
    
    // Check if OTP matches
    if($_SESSION['student_otp'] === $input_otp){
        // Keep student_code but clear OTP data
        $student_code = $_SESSION['student_code'];
        unset($_SESSION['student_otp']);
        unset($_SESSION['student_otp_expiry']);
        
        // Set verification flag for login2 to check
        $_SESSION['verified'] = true;
        
        echo json_encode(array(
            'valid' => true,
            'message' => 'OTP verified successfully!'
        ));
    } else {
        echo json_encode(array(
            'valid' => false,
            'message' => 'Invalid OTP. Please try again.'
        ));
    }
}

// Helper function to generate OTP
function generateOTP($length = 6) {
    $otp = '';
    for($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}

function sendOTPToEmail($otp, $email) {
    // API approach for Mailtrap
    $url = 'https://send.api.mailtrap.io/api/send';
    $api_key = '81ad296e6249f7accb86bd48abfcd047';
    
    // Prepare email content
    $data = [
        'from' => [
            'email' => 'hello@demomailtrap.co',
            'name' => 'Student Results System'
        ],
        'to' => [
            [
                'email' => $email
            ]
        ],
        'subject' => 'Your OTP Verification Code',
        'html' => "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>OTP Verification</h2>
                <p>Your One-Time Password (OTP) for accessing student results is:</p>
                <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold;'>
                    {$otp}
                </div>
                <p>This code will expire in 5 minutes.</p>
                <p style='font-size: 12px; color: #777; margin-top: 30px;'>
                    This is an automated email. Please do not reply to this message.
                </p>
            </div>
        ",
        'text' => "Your OTP verification code is: {$otp}. This code will expire in 5 minutes.",
        'category' => 'OTP Verification'
    ];
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Process response
    if ($error) {
        return json_encode([
            'success' => false, 
            'error' => "API request failed: " . $error
        ]);
    } else {
        if ($http_code >= 200 && $http_code < 300) {
            return json_encode([
                'success' => true, 
                'message' => 'OTP sent successfully via Mailtrap API'
            ]);
        } else {
            return json_encode([
                'success' => false, 
                'error' => "API request failed with status code: " . $http_code . ", Response: " . $response
            ]);
        }
    }
}


ob_end_flush();
?>