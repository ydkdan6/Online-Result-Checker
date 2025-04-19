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
    // Check if student exists in database
    $student_code = $_POST['student_code'];
    
    // Use existing database connection from admin_class.php
    $qry = $crud->db->query("SELECT id FROM students WHERE student_code = '$student_code'");
    if($qry->num_rows > 0){
        // Student exists, generate OTP
        $otp = generateOTP();
        
        // Save OTP and student code in session
        $_SESSION['student_otp'] = $otp;
        $_SESSION['student_otp_expiry'] = time() + 300; // 5 minutes expiry
        $_SESSION['student_code'] = $student_code;
        
        // Return success with OTP (in production, you would send OTP via email/SMS)
        echo json_encode(array('status' => 'success', 'otp' => $otp));
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

ob_end_flush();
?>