<?php 

if( $xfrAddressBooks )
{
	foreach( $xfrAddressBooks as $xfrAddressBook )
	{
		echo '<tr>';
		
			echo '<td>'.$xfrAddressBook->phone_number.'</td>';
			
			echo '<td>'.$xfrAddressBook->name.'</td>';
			
			echo '<td class="center">';
				echo '<button id="'.$xfrAddressBook->id.'" class="btn btn-info btn-minier btn-edit-xfr"><i class="fa fa-pencil"></i> Edit</button>';
				echo '<button id="'.$xfrAddressBook->id.'" style="margin-left:5px;"class="btn btn-danger btn-minier btn-delete-xfr"><i class="fa fa-times"></i> Delete</button>';
			echo '</td>';
			
		echo '</tr>';
	}
}
else
{
	echo '<tr><td colspan="3">No results found.</td></tr>';
}

?>