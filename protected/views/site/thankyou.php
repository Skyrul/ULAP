<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<div class="space-6"></div>

<div class="col-sm-10 col-sm-offset-1">
	<div class="page-header center">
		<h2>
			<span class="blue">Thank you for your purchase. You will receive a confirmation email shortly.</span>
		</h2>
	</div>
</div>
