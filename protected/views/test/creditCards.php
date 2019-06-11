<div class="page-header">
	<h1>VISA, MASTER CARD, DISCOVER</h1>
</div>

<div class="row">
	
	<div class="col-sm-12">
		<table class="table table-bordered table-hover">
			<tr>
				<th>#</th>
				<th>Import Customer Primary Key</th>
				<th>Customer Name</th>
				<th>Credit Card Type</th>
				<th>Credit Card Number</th>
				<th>Digits</th>
			</tr>
			
			<?php 
				if( $visaMasters )
				{
					$ctr = 1;
					
					$vCustomers = array();
					foreach( $visaMasters as $visaMaster )
					{
						echo '<tr>';
							echo '<td>'.$ctr.'</td>';
							echo '<td>'.$visaMaster->customer->import_customer_primary_key.' - '.$visaMaster->customer->id.'</td>';
							echo '<td>'.$visaMaster->customer->firstname.' '.$visaMaster->customer->lastname.'</td>';
							echo '<td>'.$visaMaster->credit_card_type.'</td>';
							echo '<td>**** **** **** ' . substr($visaMaster->credit_card_number, -4).'</td>';
							echo '<td>'.strlen($visaMaster->credit_card_number).'</td>';
						echo '</tr>';
						
						$ctr++;
						
						$vCustomers[$visaMaster->customer->id] = $visaMaster->customer->id;
					}
					
					// echo implode(',',$vCustomers);
				}
			?>
		</table>
	</div>
	
</div>

<div class="page-header">
	<h1>AMEX</h1>
</div>

<div class="row">
	
	<div class="col-sm-12">
		<table class="table table-bordered table-hover">
			<tr>
				<th>#</th>
				<th>Import Customer Primary Key</th>
				<th>Customer Name</th>
				<th>Credit Card Type</th>
				<th>Credit Card Number</th>
				<th>Digits</th>
			</tr>
			
			<?php 
				if( $amexs )
				{
					$ctr = 1;
					
					$aCustomers = array();
					foreach( $amexs as $amex )
					{
						echo '<tr>';
							echo '<td>'.$ctr.'</td>';
							echo '<td>'.$amex->customer->import_customer_primary_key.' - '.$amex->customer->id.'</td>';
							echo '<td>'.$amex->customer->firstname.' '.$amex->customer->lastname.'</td>';
							echo '<td>'.$amex->credit_card_type.'</td>';
							echo '<td>**** **** **** ' . substr($amex->credit_card_number, -4).'</td>';
							echo '<td>'.strlen($amex->credit_card_number).'</td>';
						echo '</tr>';
						
						$ctr++;
						
						$aCustomers[$amex->customer->id] = $amex->customer->id;
					}
					
					// echo implode(',',$aCustomers);
				}
			?>
		</table>
	</div>
	
</div>