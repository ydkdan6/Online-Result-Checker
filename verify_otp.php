<!-- verify_otp.php - Add this new file to your project -->
<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include('db_connect.php');
include('otp_service.php');

// If no student code in session, go back to login
if(!isset($_SESSION['student_code'])) {
    header("Location: login.php");
    exit;
}

$otpService = new OTPService($conn);
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>OTP Verification | <?php echo $_SESSION['system']['name'] ?></title>
 	
<?php include('header.php'); ?>

</head>
<style>
	body{
		width: 100%;
	    height: calc(100%);
	    position: fixed;
	    top:0;
	    left: 0;
        align-items:center !important;
	}
	main#main{
		width:100%;
		height: calc(100%);
		display: flex;
	}
    .countdown {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #666;
    }
</style>

<body class="bg-dark">

  <main id="main" >
  	<div class="align-self-center w-100">
		<h4 class="text-white text-center"><b><?php echo $_SESSION['system']['name'] ?> - Student Results</b></h4>
  		<div id="verification-center" class="bg-dark row justify-content-center">
  			<div class="card col-md-4">
  				<div class="card-body">
                    <h5 class="text-center">Verify OTP</h5>
                    <p class="text-center">Please enter the OTP sent to verify your identity.</p>
                    
                    <div id="alert-container"></div>
                    
  					<form id="verify-otp-form">
  						<div class="form-group">
  							<label for="otp" class="control-label text-dark">Enter OTP:</label>
  							<input type="text" id="otp" name="otp" class="form-control form-control-sm" required>
  						</div>
  						<div class="w-100 d-flex justify-content-center">
                            <button type="submit" class="btn-sm btn-block btn-wave col-md-4 btn-primary m-0">Verify</button>
                        </div>
  					</form>
                    
                    <div class="countdown mt-3">
                        Time remaining: <span id="timer">05:00</span>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-primary">Back to Login</a>
                    </div>
  				</div>
  			</div>
  		</div>
  	</div>
  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<?php include 'footer.php' ?>
<script>
    // Set up countdown timer
    let timeLeft = <?php echo $otpService->getRemainingTime(); ?>;
    const timerDisplay = document.getElementById('timer');
    let timerInterval;
    
    function updateTimer() {
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerDisplay.textContent = "00:00";
            $('#alert-container').html('<div class="alert alert-danger">OTP has expired. Please go back and try again.</div>');
            return;
        }
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        timeLeft--;
    }
    
    // Update timer every second if OTP exists
    if (timeLeft > 0) {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    // Handle OTP verification
    $('#verify-otp-form').submit(function(e){
        e.preventDefault();
        
        start_load(); // Assuming you have this function
        
        $.ajax({
            url: 'ajax.php?action=verify_student_otp',
            method: 'POST',
            data: {otp: $('#otp').val()},
            dataType: 'json',
            error: function(err){
                console.log(err);
                end_load(); // Assuming you have this function
            },
            success: function(resp){
                end_load(); // Assuming you have this function
                
                if(resp.valid){
                    $('#alert-container').html('<div class="alert alert-success">'+resp.message+'</div>');
                    setTimeout(function(){
                        location.href = 'student_results.php';
                    }, 1500);
                } else {
                    $('#alert-container').html('<div class="alert alert-danger">'+resp.message+'</div>');
                }
            }
        });
    });
</script>
</html>
