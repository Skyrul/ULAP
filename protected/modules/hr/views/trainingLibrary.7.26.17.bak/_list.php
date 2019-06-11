<tr>
	<td><?php echo $data->title; ?></td>
	<td>
		<a id="<?php echo $data->id; ?>" class="btn btn-primary btn-minier <?php echo $data->type == 4 ? 'edit-link' : 'edit-file'; ?>">Edit</a>
		<a id="<?php echo $data->id; ?>" class="btn btn-danger btn-minier delete-file">Delete</a>
	</td>
</tr>