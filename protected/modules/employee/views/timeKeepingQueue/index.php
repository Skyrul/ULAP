<div class="page-header">
	<h1>
		Time Keeping
	</h1>
</div>


<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<?php $this->forward('/employee/timeKeepingQueue/list',false); ?>