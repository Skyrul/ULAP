<?php 
	$this->widget("application.components.HrSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<h1>Update User <small><?php echo $account->fullName; ?></small></h1>

<?php 
$this->renderPartial('_form',array(
	'account'=>$account,
	'accountUser'=>$accountUser,
	'fileupload'=>$fileupload,
));
?>