<div class="modal fade" id="customerPopupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document" style="width:40%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<?php //<button type="button" class="close" data-dismiss="modal">&times;</button> ?>
				<h4 class="modal-title" style="color:#FFFFFF;"><?php echo $leadHopperEntry->customer->company->company_name.' - '.$leadHopperEntry->customer->getFullName(); ?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="col-sm-3">
							<div class="center">
								<span class="profile-picture">
									<img id="avatar" class=" img-responsive" src="<?php echo $leadHopperEntry->customer->company->getImage(); ?>">
								</span>
							</div>
						</div>
						
						<div class="col-sm-9">
							<div class="profile-user-info profile-user-info-striped">
								<div class="profile-info-row">
									<div class="profile-info-name"> Customer </div>

									<div class="profile-info-value">
										<span><?php echo $leadHopperEntry->customer->getFullName(); ?></span>
									</div>
								</div>

								<div class="profile-info-row">
									<div class="profile-info-name"> Company </div>

									<div class="profile-info-value">
										<span><?php echo $leadHopperEntry->customer->company->company_name; ?></span>
									</div>
								</div>

								<div class="profile-info-row">
									<div class="profile-info-name"> Skill </div>

									<div class="profile-info-value">
										<span><?php echo $leadHopperEntry->skill->skill_name; ?></span>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> Agent Note </div>

									<div class="profile-info-value">
										<span><?php echo $leadHopperEntry->customer->notes; ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<?php 
						if($leadHopperEntry->customer->getVoice())
						{
							echo '<audio autoplay src="'.$leadHopperEntry->customer->getVoice().'" id="audio"></audio>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer center">
				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal" disabled>Close (<span class="popupDelayCtr"></span>)</button>
			</div>
		</div>
	</div>
</div>