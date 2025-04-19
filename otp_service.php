<?php
// otp_service.php - Save this file in your project root

class OTPService {
    private $db;
    private $otpLength = 6;
    private $expirySeconds = 300; // 5 minutes
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Generate a random numeric OTP
     * 
     * @param string $studentCode Student ID for which to generate OTP
     * @return string The generated OTP
     */
    public function generateOTP($studentCode) {
        $otp = '';
        for ($i = 0; $i < $this->otpLength; $i++) {
            $otp .= mt_rand(0, 9);
        }
        
        // Store OTP in session along with expiry timestamp
        $_SESSION['student_otp'] = $otp;
        $_SESSION['student_otp_expiry'] = time() + $this->expirySeconds;
        $_SESSION['student_code'] = $studentCode;
        
        return $otp;
    }
    
    /**
     * Verify if the provided OTP matches the stored one and is still valid
     * 
     * @param string $inputOTP The OTP provided by the user
     * @return array Result with valid status and message
     */
    public function verifyOTP($inputOTP) {
        // Check if OTP exists in session
        if (!isset($_SESSION['student_otp']) || !isset($_SESSION['student_otp_expiry'])) {
            return [
                'valid' => false,
                'message' => 'No OTP found. Please request a new one.'
            ];
        }
        
        // Check if OTP is expired
        if (time() > $_SESSION['student_otp_expiry']) {
            $this->clearOTP();
            return [
                'valid' => false,
                'message' => 'OTP has expired. Please try again.'
            ];
        }
        
        // Check if OTP matches
        if ($_SESSION['student_otp'] === $inputOTP) {
            // Keep student_code but clear OTP data
            $studentCode = $_SESSION['student_code'];
            $this->clearOTP();
            $_SESSION['student_code'] = $studentCode; // Restore student code for results page
            $_SESSION['verified'] = true;
            
            return [
                'valid' => true,
                'message' => 'OTP verified successfully!'
            ];
        } else {
            return [
                'valid' => false, 
                'message' => 'Invalid OTP. Please try again.'
            ];
        }
    }
    
    /**
     * Clear the OTP from the session
     */
    public function clearOTP() {
        unset($_SESSION['student_otp']);
        unset($_SESSION['student_otp_expiry']);
    }
    
    /**
     * Get remaining validity time in seconds
     * 
     * @return int Seconds remaining until expiry, or 0 if expired/not set
     */
    public function getRemainingTime() {
        if (!isset($_SESSION['student_otp_expiry'])) {
            return 0;
        }
        
        $remaining = $_SESSION['student_otp_expiry'] - time();
        return ($remaining > 0) ? $remaining : 0;
    }
    
    /**
     * Check if student exists in database
     * 
     * @param string $studentCode The student ID to check
     * @return bool True if student exists, false otherwise
     */
    public function validateStudent($studentCode) {
        $stmt = $this->db->prepare("SELECT id FROM students WHERE student_code = ?");
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("s", $studentCode);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>