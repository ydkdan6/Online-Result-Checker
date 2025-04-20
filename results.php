<?php include'db_connect.php' ?>
<div class="col-lg-12">
	<div class="card card-outline card-primary">
		<?php	if(!isset($_SESSION['rs_id'])): ?>
		<div class="card-header">
			<div class="card-tools">
				<a class="btn btn-block btn-sm btn-default btn-flat border-primary" href="./index.php?page=new_result"><i class="fa fa-plus"></i> Add New</a>
			</div>
		</div>
	<?php endif; ?>
		<div class="card-body">
			<table class="table tabe-hover table-bordered" id="list">
				<colgroup>
					<col width="5%">
					<col width="15%">
					<col width="25%">
					<col width="20%">
					<col width="10%">
					<col width="15%">
				</colgroup>
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th>Student Registration Number</th>
						<th>Student Name</th>
						<th>Level</th>
						<th>Course Title</th>
						<th>Average</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					$where = "";
					if(isset($_SESSION['rs_id'])){
						$where = " where r.student_id = {$_SESSION['rs_id']} ";
					}
					$qry = $conn->query("SELECT r.*,concat(s.firstname,' ',s.middlename,' ',s.lastname) as name,s.student_code,concat(c.level,'-',c.section) as class FROM results r inner join classes c on c.id = r.class_id inner join students s on s.id = r.student_id $where order by unix_timestamp(r.date_created) desc ");
					while($row= $qry->fetch_assoc()):
						$subject_qry = $conn->query("SELECT s.subject_code FROM result_items ri 
						LEFT JOIN subjects s ON s.id = ri.subject_id 
						WHERE ri.result_id = ".$row['id']);
					
					if (!$subject_qry) {
						echo "<pre>SQL Error: " . $conn->error . "</pre>";
					} else {
						$subject_codes = [];
						while($sub = $subject_qry->fetch_assoc()) {
							$subject_codes[] = $sub['subject_code'];
						}
						$subject_codes_display = implode(', ', $subject_codes);
					}
										?>
					<tr>
						<th class="text-center"><?php echo $i++ ?></th>
						<td><b><?php echo $row['student_code'] ?></b></td>
						<td><b><?php echo ucwords($row['name']) ?></b></td>
						<td><b><?php echo ucwords($row['class']) ?></b></td>
						<td class="text-center"><b><?php echo $subject_codes_display ?></b></td>
						<td class="text-center"><b><?php echo $row['marks_percentage'] ?></b></td>
						<td class="text-center">
							<?php if(isset($_SESSION['login_id'])): ?>
		                    <div class="btn-group">
		                        <a href="./index.php?page=edit_result&id=<?php echo $row['id'] ?>" class="btn btn-primary btn-flat">
		                          <i class="fas fa-edit"></i>
		                        </a>
		                         <button data-id="<?php echo $row['id'] ?>" type="button" class="btn btn-info btn-flat view_result">
		                          <i class="fas fa-eye"></i>
		                        </button>
		                        <button type="button" class="btn btn-danger btn-flat delete_result" data-id="<?php echo $row['id'] ?>">
		                          <i class="fas fa-trash"></i>
		                        </button>
	                      </div>
	                      <?php elseif(isset($_SESSION['rs_id'])): ?>
	                      	<button data-id="<?php echo $row['id'] ?>" type="button" class="btn btn-info btn-flat view_result">
		                          <i class="fas fa-eye"></i>
		                          View Result
		                        </button>
	                      <?php endif; ?>
						</td>
					</tr>	
				<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>

	$(document).ready(function(){
		$('#list').dataTable()
	$('.delete_result').click(function(){
	_conf("Are you sure to delete this result?","delete_result",[$(this).attr('data-id')])
	})

	$('.view_result').click(function(){
		uni_modal("Result","view_result.php?id="+$(this).attr('data-id'),'mid-large')
	})
	$('.status_chk').change(function(){
		var status = $(this).prop('checked') == true ? 1 : 2;
		if($(this).attr('data-state-stats') !== undefined && $(this).attr('data-state-stats') == 'error'){
			$(this).removeAttr('data-state-stats')
			return false;
		}
		// return false;
		var id = $(this).attr('data-id');
		start_load()
		$.ajax({
			url:'ajax.php?action=update_result_stats',
			method:'POST',
			data:{id:id,status:status},
			error:function(err){
				console.log(err)
				alert_toast("Something went wrong while updating the result's status.",'error')
					$('#status_chk').attr('data-state-stats','error').bootstrapToggle('toggle')
					end_load()
			},
			success:function(resp){
				if(resp == 1){
					alert_toast("result status successfully updated.",'success')
					end_load()
				}else{
					alert_toast("Something went wrong while updating the result's status.",'error')
					$('#status_chk').attr('data-state-stats','error').bootstrapToggle('toggle')
					end_load()
				}
			}
		})
	})
	})
	function delete_result($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_result',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
</script>