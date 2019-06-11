<style>
	table.tbl-call-results { width:70%; border:1px solid #000; border-collapse: separate; border-spacing: 5px 3px; }
	table.tbl-call-results td{ border:none; font-size:9px; }
	
	table.tbl-log { width:100%; border:1px solid #000; border-collapse: separate; border-spacing: 5px 3px; }
	table.tbl-log td{ border:none; font-size:9px; }
	
	table.customer-info { margin-left:100px; }
	table.customer-info td { width:50%; font-weight:bold; font-size:11px; }
</style>

<table class="customer-info">
	<tr>
		<td align="left">Name: <?php echo strtoupper($customer->getFullName()); ?></td>
	</tr>
	
	<tr>
		<td align="left">Service: Policy Review - Oct 2015</td>
	</tr>
	
	<tr>
		<td align="left">Report Range: 11/1/2015 - 11/30/2015</td>
	</tr>
	
	<tr>
		<td align="left"></td>
	</tr>
	
	<tr>
		<td align="left">Callable Names: 125</td>
	</tr>
	
	<tr>
		<td align="left">Appointment Set: 12</td>
	</tr>
	
</table>

<p></p>

<!-- START OF CALL RESULTS -->

<b style="font-size:11px;"><u>CALL RESULTS:</u></b>

<br />

<table class="tbl-call-results">
	<tr>
		<th width="80%"><u>Result</u></th>
		<th align="center" width="20%"><u>Count</u></th>
	</tr>
	<tr>
		<td>Answering Machine</td>
		<td align="center">81</td>
	</tr>
	<tr>
		<td>Answering Machine-Left Message</td>
		<td align="center">70</td>
	</tr>
	
	<?php for($i=1; $i<10; $i++): ?>
	
	<tr>
		<td>Appointment Set**</td>
		<td align="center">14</td>
	</tr>
	
	<?php endfor; ?>
</table>

<p style="font-size:8px;"> 
	** Appointment count in log could be higher than Appointments Set count above. 
	The Appointment Set count removes canceled appointment reported to Engagex that fall within our cancelation policy.
</p>

<p></p>

<!-- START OF APPOINTMENT LOG -->

<b style="font-size:11px;"><u>APPOINTMENT LOG:</u></b>

<br />

<table class="tbl-log">
	<tr>
		<th width="70%"><u>Name</u></th>
		<th align="center" width="30%"><u>Date</u></th>
	</tr>
	
	<?php for($i=1; $i<10; $i++): ?>
	
	<tr>
		<td>Burnside Amy</td>
		<td align="center">11/19/2015 11:00:00</td>
	</tr>
	
	<?php endfor; ?>

</table>


<br />
<br />

<!-- START OF EMAIL LOG -->

<b style="font-size:11px;"><u>EMAIL LOG:</u></b>

<br />

<table class="tbl-log">
	<tr>
		<th width="40%"><u>Name</u></th>
		<th width="30%"><u>Email</u></th>
		<th align="center" width="30%"><u>Date</u></th>
	</tr>
	
	<?php for($i=1; $i<10; $i++): ?>
	
	<tr>
		<td>Burnside Amy</td>
		<td>burnsideamy@mail.com</td>
		<td align="center">11/19/2015 11:00:00</td>
	</tr>
	
	<?php endfor; ?>

</table>

<br />
<br />

<!-- START OF CALL LOG -->

<b style="font-size:11px;"><u>CALL LOG:</u></b>

<br />

<table class="tbl-log">
	<tr>
		<th width="40%"><u>Name</u></th>
		<th align="center" width="30%"><u>Result</u></th>
		<th align="center" width="30%"><u>Called</u></th>
	</tr>
	
	<?php for($i=1; $i<10; $i++): ?>
	
	<tr>
		<td>Aguayo, Alberto</td>
		<td>No Answer/no voicemail</td>
		<td align="center">11/02/2015 17:25:24</td>
	</tr>
	
	<?php endfor; ?>

</table>