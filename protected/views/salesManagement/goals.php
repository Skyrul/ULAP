<?php 
	$this->pageTitle = 'Engagex | Sales Management | Goals';
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){

			$(document).on("change", ".sales-rep-select", function(){
				
				var sales_rep_account_id = $(this).val();

				$(location).attr("href", yii.urls.absoluteUrl + "/salesManagement/goals?salesRepId=" + sales_rep_account_id)
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'salesGoals'
	));
?>
	
<?php
	foreach(Yii::app()->user->getFlashes() as $key => $message) {
		echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button>' . $message . "</div>\n";
	}
?>

<div class="rows">
	<div class="col-sm-6"> 
		
		<div class="page-header">
			<h1>Monthly Team Goals</h1>
		</div>
		
		<form action="" method="post">
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Sales Count</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyTeamGoal->sales_count; ?>" name="SalesTeamMonthlyGoal[sales_count]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Sales Revenue</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyTeamGoal->sales_revenue; ?>" name="SalesTeamMonthlyGoal[sales_revenue]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Team Accelerator</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyTeamGoal->team_accelerator; ?>" name="SalesTeamMonthlyGoal[team_accelerator]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="col-sm-12 center">
					<div class="space-2"></div>
					<button type="submit" class="btn btn-mini btn-primary team-goals-save-btn"><i class="fa fa-check"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
		
	<div class="col-sm-6"> 
		<div class="page-header">
			<h1>Monthly User Goals</h1>
		</div>
		
		<form action="" method="post">
			<div class="rows">
				<div class="col-sm-12">
					<?php 
						echo CHtml::dropDownList('SalesAccountMonthlyGoal[account_id]', $salesRepId, AccountUser::listSalesAgents(), array('class'=>'form-control sales-rep-select', 'style'=>'width:auto;', 'prompt' => '- Select -')); 
					?>
				</div>
			</div>
			
			<br /><br />
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Sales Count</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyUserGoal->sales_count?>" name="SalesAccountMonthlyGoal[sales_count]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Sales Revenue</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyUserGoal->sales_revenue?>" name="SalesAccountMonthlyGoal[sales_revenue]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Stretch Count</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyUserGoal->stretch_count?>" name="SalesAccountMonthlyGoal[stretch_count]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Commison Rate</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyUserGoal->commission_rate?>" name="SalesAccountMonthlyGoal[commission_rate]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">User Accelerator</div>
						
						<div class="profile-info-value">
							<input type="text" value="<?php echo $monthlyUserGoal->user_accelerator?>" name="SalesAccountMonthlyGoal[user_accelerator]" class="form-control col-sm-12">
						</div>
					</div>
				</div>
			</div>
			
			<div class="rows">
				<div class="col-sm-12 center">
					<div class="space-2"></div>
					<?php
						if( $salesRepId != null )
						{
							echo '<button type="submit" class="btn btn-mini btn-primary user-goals-save-btn"><i class="fa fa-check"></i> Save</button>';
						}
						else
						{
							echo '<button class="btn btn-mini btn-grey user-goals-save-btn" disabled><i class="fa fa-check"></i> Save</button>';
						}
					?>
				</div>
			</div>
		</form>
		
	</div>
</div>