<div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width:405px;">

		<!-- Modal content-->
		<div class="modal-content">
		
			<div class="modal-header clearfix">
				<div class="col-md-7">
					Dialing as # <span class="dialingAs"></span> 
				</div>
				
				<div class="col-md-5 dialpad-time">
					<?php 
						if( !empty($lead->timezone) )
						{
							$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));

							$date->setTimezone(new DateTimeZone( timezone_name_from_abbr($lead->timezone) ));

							echo $date->format('g:i A'); 
						}
					?>
				</div>
			</div>
		
			<div class="modal-body">
				<div class="container">
					<div class="row">
						<div class="col-md-4 phone">
							<div class="row1">
								<div class="col-md-12">
								<input type="tel" name="name" id="manualDialInput" class="form-control tel" />
									<div class="num-pad">
										<div class="span4">
											<div class="num">
												<div class="txt">
													1
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													2
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													3
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													4
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													5
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													6
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													7
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													8
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													9
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													*
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													0 <span class="small">
														<p>
															+</p>
													</span>
												</div>
											</div>
										</div>
										<div class="span4">
											<div class="num">
												<div class="txt">
													#
												</div>
											</div>
										</div>
									</div>
									
									<div class="clearfix"></div>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<?php 
					if( $lead != null )
					{
						$phoneHtmlOptions = array(
							'class' => 'btn btn-sm btn-success green manual-dial-btn',
							'lead_id' => $lead->id,
							'list_id' => $list->id,
							'customer_id' => $customer->id,
							'company_id' => $customer->company_id,
							'skill_id' => $list->skill_id,
							'title' => 'Dial this number',
						);
						
						echo CHtml::button('Dial', $phoneHtmlOptions); 
					}
					else
					{
						echo CHtml::button('Dial', array('class'=>'btn btn-sm btn-success green'));
					}
				?>

				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>