<div class="row">
	<div class="col-sm-12">
		
		<div id="calendar-details" class="col-sm-4">
		
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Lead Name </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer's Calendar </div>

					<div class="profile-info-value">
						<span>
							<?php echo CHtml::dropDownList('Calendar[id]','', array(), array('id'=>'calendar-select', 'prompt'=>'- SELECT -', 'disabled'=>true)); ?>
							
							<button class="btn btn-info btn-minier load-calendar-btn" type="button">Load</button>
						</span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Name </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Address </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Phone # </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Appointment Locations </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Max appointments per week</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Min appointments per day </div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Min days out</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Max days out</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>		

				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Notes</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Directions</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Landmarks</div>

					<div class="profile-info-value">
						<span></span>
					</div>
				</div>
			</div>
		</div>
		
		<div id="calendar-wrapper" class="col-sm-8">
			<div id="calendar"></div>
		</div>
	</div>
</div>