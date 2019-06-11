<div class="page-header">
	<h1>
		Time off Request Queue
	</h1>
</div>


<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<?php $this->forward('timeKeepingQueue/list',false); ?>