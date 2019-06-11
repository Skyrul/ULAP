<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	$isGuest = (Yii::app()->user->isGuest) ? Yii::app()->user->isGuest : 0;
	$cs->registerCoreScript('jquery');
	$cs->registerCoreScript('jquery.ui');
	$cs->registerScript('globalVars', '
	
		yii = {
			urls: {
				baseUrl: '. CJSON::encode(Yii::app()->request->baseUrl) . ',
				absoluteUrl: '. CJSON::encode(Yii::app()->createAbsoluteUrl('')) . ',
			},
			user: {
				isGuest: '.$isGuest.',
			}
			
		}
		
		
	', CClientScript::POS_HEAD);
?>

<?php echo $content; ?>
