<button class="btn btn-grey btn-xs manual-dial-btn" data-toggle="modal" data-target="#dialPadModal" style="margin-right:40px;">DIAL PAD</button>

<button class="btn btn-yellow btn-circle hold-call-btn disabled">MUTE</button>				
										

<?php 
	if( $xfrs )
	{
		echo '
			<div class="btn-group">
				<button data-toggle="dropdown" class="btn btn-purple btn-circle dropdown-toggle transfer-call-btn-group disabled" aria-expanded="true">
					XFR
				</button>

				<ul class="dropdown-menu dropdown-default">
		';
		
			foreach( $xfrs as $xfr )
			{
				echo '<li>';
					echo '<a href="#" class="transfer-list-btn" phone_number="'.$xfr->phone_number.'">'.$xfr->name.'</a>';
				echo '</li>';
			}
		
		echo '
				</ul>
			</div>

		';
	}
	else
	{
		echo '<button class="btn btn-purple btn-circle transfer-call-btn disabled">XFR</button>';
	}
?>

<?php 
	if( $xfrs )
	{
		echo '
			<div class="btn-group">
				<button data-toggle="dropdown" class="btn btn-success btn-circle dropdown-toggle conference-call-btn-group disabled" aria-expanded="true">
					CONF
				</button>

				<ul class="dropdown-menu dropdown-default">
		';
		
			foreach( $xfrs as $xfr )
			{
				echo '<li>';
					echo '<a href="#" class="conference-list-btn" phone_number="'.$xfr->phone_number.'">'.$xfr->name.'</a>';
				echo '</li>';
			}
		
		echo '
				</ul>
			</div>

		';
	}
	else
	{
		echo '<button class="btn btn-success btn-circle conference-call-btn disabled">CONF</button>';
	}
?>
	
<?php /*<button class="btn btn-success btn-circle conference-call-btn disabled">CONF</button>*/ ?>

<button class="btn btn-danger btn-circle end-call-btn disabled">END</button>
