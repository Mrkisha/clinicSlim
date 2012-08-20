<?php
	session_start();
	require_once('../includes/database.php');
	require_once('../includes/functions.php');
	require_once('../includes/listboxes.php');

	if(empty($_SESSION['URNumber'])){
		redirect_to('searchPatient.php');
	}
	
	// get the patient id
	$sql_id = mysql_query(
				"SELECT `Identifier` FROM `patient` WHERE `URNumber` = {$_SESSION['URNumber']}"
	);
	
	$patient_identifier = '';
	while($row = mysql_fetch_assoc($sql_id)){
		$patient_identifier = $row['Identifier'];
	}
	
	if(!isset($_GET['admission_id']) || !(int)$_GET['admission_id']){
		redirect_to($_SERVER['HTTP_REFERER']);
	}
	
	$admission_id = mysql_real_escape_string($_GET['admission_id']);

	$sql = mysql_query("SELECT * FROM `admission` WHERE `AdmissionID` = $admission_id AND Patient_Identifier = $patient_identifier");
	
	if(mysql_num_rows($sql) == 1){
		foreach(mysql_fetch_assoc($sql) as $key => $value){
			$$key = $value;
		}
	} else {
		header("Location: ../patientViewRecord.php");
	}
	
	// get all conditions for this admission
	$sql_cond = mysql_query("SELECT conditionID FROM admission_condition WHERE admissionID = $admission_id");
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	

	function condiotoonList_admission2($id){
		$results = "";
		$sql = mysql_query("SELECT
								    `diagnoslist`.`Diagnos`
								    , `sidelist`.`Sid`
								    , `sitelist`.`Sit`
								    , `condition`.`ConditionID`
								FROM
								    `diagnoslist`
								    INNER JOIN `condition` 
								        ON (`diagnoslist`.`DiagnListID` = `condition`.`DiagnListID`)
								    INNER JOIN `sidelist` 
								        ON (`sidelist`.`SidID` = `condition`.`SideID`)
								    INNER JOIN `sitelist` 
								        ON (`sitelist`.`SitListID` = `condition`.`SitID`)
								WHERE `condition`.`Patient_Identifier` = $id
							");
										
		while($row = mysql_fetch_assoc($sql)){
			$results .= "<label for='conditionID{$row['ConditionID']}' class='span6'><input type='checkbox' id='conditionID{$row['ConditionID']}' name='conditionID[]' value='{$row['ConditionID']}' /><span  class='label label-success'>".$row['Diagnos'].' '.$row['Sid'].' '.$row['Sit']."</span></label><br />";
		}
		return $results;
	}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Edit Patient</title>
<link rel="stylesheet" href="../css/main2.css">
<link rel="stylesheet" href="../css/ui-lightness/jquery-ui-1.8.20.custom.css">
<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
<script src="../bootstrap/js/bootstrap.min.js"></script>
<style type="text/css">
	.label {
		margin: 3px!important;
	}
</style>
</head>

<body>

<br>
<div class="centerPage">
<nav>
	<a href="../patientViewRecord.php" class="btn btn-info btn-large">View Patient Record</a>
	<a href="../outstandingCases.php" class="btn btn-large">Outstanding Cases</a>
	<a href="../taskList.php" class="btn btn-large btn-inverse">Tasks</a>
	<a href="../wardList.php" class="btn btn-large btn-warning">Ward List</a>
</nav><br>
<div>
	<form id="patient" action="redirect.php" method="post" class="well">
	<?php
	
		$sql = mysql_query("SELECT `Identifier`, `URNumber`, `Surname`, `FirstName`, `DOB`
							FROM `patient`	
							WHERE `URNumber` = {$_SESSION['URNumber']}
							");
		if(mysql_num_rows($sql) == 1){
			while($row = mysql_fetch_assoc($sql)){
				echo "UR #: <span class='badge badge-info'>".$row['URNumber']."</span> 
							Age: <span class='badge badge-info'>".age($row['DOB'])."</span><br>
							Name: <span class='badge badge-info'>".$row['Surname']." ".$row['FirstName']."</span>";
	?>
			<label>Admit</label>
				<input id="" type="text" name="admit" class="span4" value="<?php echo $AdmitDate; ?>"/><br>
			<label>D/C</label>
				<input id="" type="text" name="dc" class="span4" value="<?php echo $DischargeDate; ?>"><br>
			<label>Condition: </label>
				<?php echo condiotoonList_admission2($row['Identifier']); ?>
			<label>Admit type:</label>
				<?php echo admitType(); ?><br>
			<label>Referral Source</label>
				<?php echo referral(); ?><br>
			<label>Consultant</label>
				<?php echo primConsult(); ?><br>
			<label>Ward:</label>
				<?php echo wardList(); ?><br>
			<label>Bed</label>
				<input type="text" name="bed" class="span4" value="<?php echo $Bed; ?>"><br>
			<label>Admit from</label>
				<?php echo admitFrom(); ?><br>
			<label>Discharge to</label>
				<?php echo dischTo(); ?><br>
			<label>status</label>
				<?php echo patient_status(); ?>
			<div><label>Comments</label>
				<textarea name="comments" rows="4" cols="30" class="span6"><?php echo $comments; ?></textarea></div>
			<div id="buttonsBar">
				<input type="submit" value="Save changes, go<?php echo "\r"; ?>to patient record" name="udpateAdmGoToPatient" class="btn btn-success span2">
				<input type="submit" value="Save changes +<?php echo "\r"; ?>add condition" name="updateAndAddCond" class="btn btn-warning span2">
				<input type="submit" value="Save changes +<?php echo "\r"; ?>go to ward list" name="updateAndGotoWard" class="btn btn-danger span2">
				<input type="hidden" value="<?php echo $admission_id ?>" name="admission_id">
			</div>
		</form>
	<?php
			}
		}
	?>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
<script src="../js/jquery-ui-timepicker-addon.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
<script>
	$(document).ready(function(){
		$("input[name='admit']").datepicker({
			dateFormat: 'yy-mm-dd' 
		});
		
		$("input[name='dc']").datepicker({
			dateFormat: 'yy-mm-dd' 
		});
		
		$('form').validate({
			rules: {
				admit: {
					required: true
				},
				conditionID: {
					required: true
				},
				admitType: {
					required: true
				},
				ConsultName: {
					required: true
				},
				wardList: {
					required: true
				},
				bed: {
					required: true
				},
				admitFrom: {
					required: true
				},
				patientStatus: {
					required: true
				}
			}
		});
		
		$('#admitType option[value="<?php echo $AdmitTypeID; ?>"]').attr('selected', true);
		$('#referral option[value="<?php echo $ReferralSource; ?>"]').attr('selected', true);
		$('#ConsultName option[value="<?php echo $consultant; ?>"]').attr('selected', true);
		$('#wardList option[value="<?php echo $Ward; ?>"]').attr('selected', true);
		$('#admitFrom option[value="<?php echo $AdmitFrom; ?>"]').attr('selected', true);
		$('#dischTo option[value="<?php echo $DishargeDest; ?>"]').attr('selected', true);
		$('#patientStatus option[value="<?php echo $PatientStatusID; ?>"]').attr('selected', true);
		
	<?php
		while($row_cond = mysql_fetch_assoc($sql_cond)){
			echo "$('#conditionID".$row_cond['conditionID']."').prop(\"checked\", true);\r";

		}
	?>


	});	
</script>
</body>
</html>
