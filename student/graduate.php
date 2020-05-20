<?php
	require "header.php";
?>


<main>
<div class="change-tbl">
<h1>Application to Graduate</h1>
<p>In order to apply for graduation, all of your degree requirements must be met AND your Form 1 must have been approved!</p>
<?php
	$user = $_SESSION['user_id'];

	$user_query = "SELECT * FROM student WHERE user_id=" . $user;
	$run_info_query = mysqli_query($conn, $user_query);

	echo "<table>";

		while($row = mysqli_fetch_assoc($run_info_query)){
			$tr = "<tr>";
			$tr .= "<td>Name:</td><td>{$row['fname']} {$row['lname']}</td>";
			$tr .= "</tr>";
			$tr .= "<tr>";
			$tr .= "<td>User ID:</td><td>{$row['user_id']}</td></tr>";
			$tr .= "<tr><td>Program:</td>";
			if($row['program'] == 1){
				$tr .= "<td>MS</td>";
			}
			else if($row['program'] == 2){
				$tr .= "<td>PhD</td>";
			}
			$tr .="</tr>";
			$tr .="<tr><td>Advisor:</td>";
			if($row['adv_id'] == NULL){
				$tr .="<td>None Assigned</td>";
			}
			else{
				$adv_query = "SELECT * FROM faculty WHERE user_id=" . $row['adv_id'];
				$run_adv_query = mysqli_query($conn, $adv_query);
				$result = mysqli_fetch_assoc($run_adv_query);

				$tr .= "<td>{$result['fname']} {$result['lname']}</td>";
			}
			echo $tr;
		}
	echo "</table>";
	echo "<h2>Degree Progress</h2>";
	$user = $_SESSION['user_id'];
		$info_query = "SELECT * FROM student WHERE user_id=" . $user;
		$run_info_query = mysqli_query($conn, $info_query);
		$result = mysqli_fetch_assoc($run_info_query);
		//begin by deciding their program to decide which audit to apply
		$prog = $result['program'];
		echo "<table>";
		if($prog == 1){
			//they are a master's student
			$reqs_met = 0;

			//TODO: GPA Calculation
			$credits = 0;
			$cr_query = "SELECT * FROM enrollment WHERE NOT grade='IP'";
			//with this need to count total number of courses completed
			//and then get total number of attempted credits and add
			//SELECT sum(course_credits) from enrollment where user_id=55555555 and not grade='ip';
			$cred_row = "<tr>";
			$cred_row .= "<td>Completed Credit Hours:</td>";
				$cred_query = "SELECT SUM(course_credits) as creds FROM enrollment where user_id=" . $user . " AND grade NOT IN ('F','IP')";
				$run_cred_query = mysqli_query($conn, $cred_query);
				$creds = mysqli_fetch_assoc($run_cred_query);
			$cred_row .="<td>{$creds['creds']}</td>";
			if($creds['creds'] >= 30){
				$cred_row .= "<td></td><td>30 Credit Minimum [X]</td>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .="<td></td><td>30 Credit Minimum [ ]</td>";
			}
			$cred_row .="</tr>";
				$non_cs = "SELECT count(*) as non_cs FROM enrollment where user_id=" . $user . " AND NOT course_dept='CSCI'";
				$run_non_cs = mysqli_query($conn, $non_cs);
				$non_cs_result = mysqli_fetch_assoc($run_non_cs);
			$cred_row .="<tr><td>Non Computer Science Courses:</td><td>{$non_cs_result['non_cs']}</td>";
			if($non_cs_result['non_cs'] < 4){
				$cred_row .= "<td></td><td>No More than 3 Non CS Courses [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>No More than 3 Non CS Courses [ ]</td></tr>";
			}
				$grades_sub_b = "SELECT count(*) as below_b FROM enrollment where user_id=" .$user. " AND grade IN ('C+','C','F')";
				$run_sub_b = mysqli_query($conn, $grades_sub_b);
				$blw_b_res = mysqli_fetch_assoc($run_sub_b);
			$cred_row .= "<tr><td>Grades Below B:</td><td>{$blw_b_res['below_b']}</td>";
			if($blw_b_res['below_b'] < 3){
				$cred_row .= "<td></td><td>No More than 2 Grades Below B [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>No More than 2 Grades Below B [ ]</td></tr>";
			}
				$core_query = "SELECT count(*) as core FROM enrollment WHERE user_id=" . $user . " AND course_dept='CSCI' AND course_num IN (6212,6221,6461) AND grade NOT IN ('F','IP')";
				$run_core = mysqli_query($conn, $core_query);
				$core_done = mysqli_fetch_assoc($run_core);
			$cred_row .= "<tr><td>Core Courses Passed:</td><td>{$core_done['core']}/3</td>";
			if($core_done['core'] == 3){
				$cred_row .= "<td></td><td>All Required Courses Passed [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>All Required Courses Passed [ ]</td></tr>";
			}

				$grades_query = "SELECT grade, course_credits  FROM enrollment WHERE user_id=" . $user;
				$run_grades = mysqli_query($conn, $grades_query);

				$grade_pts = 0.0;

				while($gr = mysqli_fetch_assoc($run_grades)){
					if($gr['grade'] == 'A'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 4.0);
					}
					else if($gr['grade'] == 'A-'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.7);
					}
					else if($gr['grade'] == 'B+'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.3);
					}
					else if($gr['grade'] == 'B'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.0);
					}
					else if($gr['grade'] == 'B-'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.7);
					}
					else if($gr['grade'] == 'C+'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.3);
					}
					else if($gr['grade'] == 'C'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.0);
					}
				}

				$att_cred = "SELECT SUM(course_credits) as creds FROM enrollment where user_id=" . $user . " AND grade NOT IN ('IP')";
				$run_att_cred = mysqli_query($conn, $att_cred);
				$res = mysqli_fetch_assoc($run_att_cred);
				$gpa = 0.0;
				$gpa = $grade_pts / $res['creds'];

			$cred_row .= "<tr><td>GPA:</td><td>$gpa</td>";
			if($gpa >= 3.0){
				$cred_row .= "<td></td><td>Minimum 3.0 GPA [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>Minimum 3.0 GPA [ ]</td></tr>";
			}

			if($reqs_met == 5){
				$cred_row .= "<tr></tr><tr><td></td><td></td><td>Eligible to Graduate [X]</td></tr>";
			}
			else{
				$cred_row .= "<tr></tr><tr><td></td><td></td><td>Eligible to Graduate [ ]</td></tr>";
			}

			echo $cred_row;
			if($result['form1'] == 1){
				echo "<tr></tr><tr><td></td><td></td><td>Form 1 Accepted [X]</td></tr>";
			}
			else{
				echo "<tr></tr><tr><td></td><td></td><td>Form 1 Accepted [ ]</td></tr>";
			}
			if($reqs_met == 5 && $result['form1'] == 1){
				echo "<h1>Submit Application to Graduate</h1><form action='' method='post'>
	<table><tr><td>Your University ID</td><td><input type='text' name='user_id'></td></tr>
	<tr><td>Your Password</td><td><input type='password' name='pwd'></td></tr>
	<tr><td><button type='submit' name='submit-grad'>Apply to Graduate</button></td></tr>
	</table></form>";
			}
			else{
				echo "<h2>You do not currently meet the graduation requirements, and can not apply to graduate</h2>";
			}
		}
		else if($prog == 2){
			//they are a phd student
			$min_gpa = 3.5;
			$min_credits = 36;
			$min_cs_credit = 30;
			$max_sub_b = 1;

			$reqs_met = 0;

			//TODO: GPA Calculation
			$credits = 0;
			$cr_query = "SELECT * FROM enrollment WHERE NOT grade='IP'";
			//with this need to count total number of courses completed
			//and then get total number of attempted credits and add
			//SELECT sum(course_credits) from enrollment where user_id=55555555 and not grade='ip';
			$cred_row = "<tr>";
			$cred_row .= "<td>Completed Credit Hours:</td>";
				$cred_query = "SELECT SUM(course_credits) as creds FROM enrollment where user_id=" . $user . " AND grade NOT IN ('F','IP')";
				$run_cred_query = mysqli_query($conn, $cred_query);
				$creds = mysqli_fetch_assoc($run_cred_query);
			$cred_row .="<td>{$creds['creds']}</td>";
			if($creds['creds'] >= 36){
				$cred_row .= "<td></td><td>36 Credit Minimum [X]</td>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .="<td></td><td>36 Credit Minimum [ ]</td>";
			}
			$cred_row .="</tr>";
				$non_cs = "SELECT sum(course_credits) as non_cs FROM enrollment where user_id=" . $user . " AND course_dept='CSCI'";
				$run_non_cs = mysqli_query($conn, $non_cs);
				$non_cs_result = mysqli_fetch_assoc($run_non_cs);
				$cs_cred = $creds['creds'] - $non_cs_result['non_cs'];
			$cred_row .="<tr><td>Non Computer Science Credit:</td><td>{$non_cs_result['non_cs']}</td>";
			if($non_cs_result['non_cs'] > 29){
				$cred_row .= "<td></td><td>Minimum 30 CS Credits [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>Minimum 30 CS Credits [ ]</td></tr>";
			}
				$grades_sub_b = "SELECT count(*) as below_b FROM enrollment where user_id=" .$user. " AND grade IN ('C+','C','F')";
				$run_sub_b = mysqli_query($conn, $grades_sub_b);
				$blw_b_res = mysqli_fetch_assoc($run_sub_b);
			$cred_row .= "<tr><td>Grades Below B:</td><td>{$blw_b_res['below_b']}</td>";
			if($blw_b_res['below_b'] < 2){
				$cred_row .= "<td></td><td>No More than 1 Grade Below B [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>No More than 1 Grade Below B [ ]</td></tr>";
			}
				$grades_query = "SELECT grade, course_credits  FROM enrollment WHERE user_id=" . $user;
				$run_grades = mysqli_query($conn, $grades_query);

				$grade_pts = 0.0;

				while($gr = mysqli_fetch_assoc($run_grades)){
					if($gr['grade'] == 'A'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 4.0);
					}
					else if($gr['grade'] == 'A-'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.7);
					}
					else if($gr['grade'] == 'B+'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.3);
					}
					else if($gr['grade'] == 'B'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 3.0);
					}
					else if($gr['grade'] == 'B-'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.7);
					}
					else if($gr['grade'] == 'C+'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.3);
					}
					else if($gr['grade'] == 'C'){
						$grade_pts = $grade_pts + ($gr['course_credits'] * 2.0);
					}
				}

				$att_cred = "SELECT SUM(course_credits) as creds FROM enrollment where user_id=" . $user . " AND grade NOT IN ('IP')";
				$run_att_cred = mysqli_query($conn, $att_cred);
				$res = mysqli_fetch_assoc($run_att_cred);
				$gpa = 0.0;
				$gpa = $grade_pts / $res['creds'];

			$cred_row .= "<tr><td>GPA:</td><td>$gpa</td>";
			if($gpa >= 3.5){
				$cred_row .= "<td></td><td>Minimum 3.5 GPA [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<td></td><td>Minimum 3.5 GPA [ ]</td></tr>";
			}
			$thesis = $result['thesis'];
			if($thesis == 1){
				$cred_row .= "<tr><td></td>";
				$cred_row .= "<td></td><td>Thesis Defense Passed [X]</td></tr>";
				$reqs_met = $reqs_met + 1;
			}
			else{
				$cred_row .= "<tr><td></td>";
				$cred_row .= "<td></td><td>Thesis Defense Passed [ ]</td></tr>";
			}
			if($reqs_met == 5){
				$cred_row .= "<tr></tr><tr><td></td><td></td><td>Eligible to Graduate [X]</td></tr>";
			}
			else{
				$cred_row .= "<tr></tr><tr><td></td><td></td><td>Eligible to Graduate [ ]</td></tr>";
			}

			echo $cred_row;
			if($result['form1'] == 1){
				echo "<tr></tr><tr><td></td><td></td><td>Form 1 Accepted [X]</td></tr>";
			}
			else{
				echo "<tr></tr><tr><td></td><td></td><td>Form 1 Accepted [ ]</td></tr>";
			}
			if($reqs_met == 5 && $result['form1'] == 1){
				echo "<h1>Submit Application to Graduate</h1><form action='' method='post'>
	<table><tr><td>Your University ID</td><td><input type='text' name='user_id'></td></tr>
	<tr><td>Your Password</td><td><input type='password' name='pwd'></td></tr>
	<tr><td><button type='submit' name='submit-grad'>Apply to Graduate</button></td></tr>
	</table></form>";
			}
	else{
				echo "<h2>You do not currently meet the graduation requirements, and can not apply to graduate</h2>";
			}
		}

		echo "</table>";
?>
<?php
	if(isset($_POST['submit-grad'])){
		$user_form = $_POST['user_id'];
		$form_pwd = $_POST['pwd'];

		$check_query = "SELECT * FROM user WHERE user_id= " . $user_form . " AND pwd=". "'" . $form_pwd . "'";
		$run_check = mysqli_query($conn, $check_query);
		$num_rows = mysqli_num_rows($run_check);

		if($num_rows != 0){
			$upd = "UPDATE student SET app_to_grad=1 WHERE user_id=".$user_form;
			$run_upd = mysqli_query($conn, $upd);
			header("Location: graduate.php?graduate=success");
		}
		else{
			header("Location: graduate.php?error=invalid_user");
		}
	}
?>
</div>
</main>


<?php
	require "footer.php";
?>
