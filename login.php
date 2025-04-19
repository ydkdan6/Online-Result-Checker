<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include('./db_connect.php');
  ob_start();
  // if(!isset($_SESSION['system'])){

    $system = $conn->query("SELECT * FROM system_settings")->fetch_array();
    foreach($system as $k => $v){
      $_SESSION['system'][$k] = $v;
    }
  // }
  ob_end_flush();
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login | <?php echo $_SESSION['system']['name'] ?></title>
 	

<?php include('./header.php'); ?>
<?php 
if(isset($_SESSION['login_id']))
header("location:index.php?page=home");

?>

</head>
<style>
	body{
		width: 100%;
	    height: calc(100%);
	    position: fixed;
	    top:0;
	    left: 0;
      align-items:center !important;
	    /*background: #007bff;*/
	}
	main#main{
		width:100%;
		height: calc(100%);
		display: flex;
	}

</style>

<body class="bg-dark">


  <main id="main" >
  	
  		<div class="align-self-center w-100">
		<h4 class="text-white text-center"><b><?php echo $_SESSION['system']['name'] ?> - Admin</b></h4>
  		<div id="login-center" class="bg-dark row justify-content-center">
  			<div class="card col-md-4">
  				<div class="card-body">
  					<form id="login-form" >
  						<div class="form-group">
  							<label for="username" class="control-label text-dark">Username</label>
  							<input type="text" id="username" name="username" class="form-control form-control-sm">
  						</div>
  						<div class="form-group">
  							<label for="password" class="control-label text-dark">Password</label>
  							<input type="password" id="password" name="password" class="form-control form-control-sm">
  						</div>
  						<div class="w-100 d-flex justify-content-center align-items-center">
                <button class="btn-sm btn-block btn-wave col-md-4 btn-primary m-0 mr-1">Login</button>
                <button class="btn-sm btn-block btn-wave col-md-4 btn-success m-0" type="button" id="view_result">View Result</button>
              </div>
  					</form>
  				</div>
  			</div>
  		</div>
  		</div>
  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>
<div class="modal fade" id="view_student_results" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <form id="vsr-frm">
            <div class="form-group">
                <label for="student_code" class="control-label text-dark">Student ID #:</label>
                <input type="text" id="student_code" name="student_code" class="form-control form-control-sm">
              </div>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#view_student_results form').submit()">View</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
      </div>
    </div>
  </div>

</body>
<?php include 'footer.php' ?>
<script>
  $('#view_result').click(function(){
    $('#view_student_results').modal('show')
  })
  
  $('#login-form').submit(function(e){
    e.preventDefault()
    $('#login-form button[type="button"]').attr('disabled',true).html('Logging in...');
    if($(this).find('.alert-danger').length > 0 )
      $(this).find('.alert-danger').remove();
    $.ajax({
      url:'ajax.php?action=login',
      method:'POST',
      data:$(this).serialize(),
      error:err=>{
        console.log(err)
        $('#login-form button[type="button"]').removeAttr('disabled').html('Login');
      },
      success:function(resp){
        if(resp == 1){
          location.href ='index.php?page=home';
        }else{
          $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>')
          $('#login-form button[type="button"]').removeAttr('disabled').html('Login');
        }
      }
    })
  })
  
  // Modified student results verification flow with OTP
  $('#vsr-frm').submit(function(e){
  e.preventDefault(); 
  start_load();

  if($(this).find('.alert-danger').length > 0 )
    $(this).find('.alert-danger').remove();

  $.ajax({
    url: 'ajax.php?action=check_student_and_generate_otp',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    error: function(err) {
      console.log('AJAX error:', err);
      end_load();
      $('#vsr-frm').prepend('<div class="alert alert-danger">An error occurred. Please try again.</div>');
    },
    success: function(resp) {
      console.log('Response:', resp); // ✅ DEBUG LOG

      if (resp.status === 'success') {
        alert("Your OTP is: " + resp.otp);

        $('#view_student_results').modal('hide');

        if ($('#otp_verification_modal').length > 0)
          $('#otp_verification_modal').remove();

        const otp_modal = $(`
          <div class="modal fade" id="otp_verification_modal" role='dialog'>
            <div class="modal-dialog modal-md" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">OTP Verification</h5>
                </div>
                <div class="modal-body">
                  <div class="container-fluid">
                    <div id="otp-alert-container"></div>
                    <form id="otp-verification-form">
                      <div class="form-group">
                        <label for="otp_code" class="control-label text-dark">Enter OTP:</label>
                        <input type="text" id="otp_code" name="otp" class="form-control form-control-sm" required>
                      </div>
                      <div class="countdown text-center mb-3">
                        Time remaining: <span id="otp-timer">05:00</span>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="verify-otp-btn">Verify</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        `);

        $('body').append(otp_modal);

        // ✅ Delay to ensure modal is ready before showing
        setTimeout(() => {
          $('#otp_verification_modal').modal('show');
        }, 100);

        // Timer logic
        let timeLeft = 300;
        const timerDisplay = document.getElementById('otp-timer');

        function updateTimer() {
          if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerDisplay.textContent = "00:00";
            $('#otp-alert-container').html('<div class="alert alert-danger">OTP has expired. Please try again.</div>');
            return;
          }

          const minutes = Math.floor(timeLeft / 60);
          const seconds = timeLeft % 60;
          timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
          timeLeft--;
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);

        // OTP verification logic
        $('#verify-otp-btn').click(function() {
          const otp_value = $('#otp_code').val();
          if (!otp_value) {
            $('#otp-alert-container').html('<div class="alert alert-danger">Please enter the OTP.</div>');
            return;
          }

          $.ajax({
            url: 'ajax.php?action=verify_student_otp',
            method: 'POST',
            data: { otp: otp_value },
            dataType: 'json',
            error: function(err) {
              console.log('OTP verification error:', err);
              $('#otp-alert-container').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            },
            success: function(resp) {
              if (resp.valid) {
                $('#otp-alert-container').html('<div class="alert alert-success">' + resp.message + '</div>');
                setTimeout(function() {
                  $('#otp_verification_modal').modal('hide');
                  location.href = 'student_results.php';
                }, 1500);
              } else {
                $('#otp-alert-container').html('<div class="alert alert-danger">' + resp.message + '</div>');
              }
            }
          });
        });

        $('#otp_verification_modal').on('hidden.bs.modal', function() {
          clearInterval(timerInterval);
        });

      } else {
        $('#vsr-frm').prepend('<div class="alert alert-danger">' + (resp.message || 'Student ID # is incorrect.') + '</div>');
      }

      end_load();
    }
  });
});

  
  $('.number').on('input keyup keypress',function(){
    var val = $(this).val()
    val = val.replace(/[^0-9 \,]/, '');
    val = val.toLocaleString('en-US')
    $(this).val(val)
  })
</script>	
</html>