<?php 

$cs = Yii::app()->clientScript;
$cs->registerCss(uniqid(), '

	.file-thumb-link{ color:#333333; }
	
	.file-thumb-wrapper{ width:130px !important; height:100px; float:left; margin-left:50px; margin-bottom:50px; }
	
	.file-thumb{ border:1px #CCCCCC solid; height:70px; position:relative }
	.file-thumb:hover{ border:1px #2A6496 solid; }
	.file-thumb a { color:#333333; }
	
	.file-thumb-title { text-align:center; }
	.file-thumb-description { text-align:center; }
	
	.file-label { position:absolute; right:0; top:0; }
	
	.media-file{ cursor:pointer; }
');

$cs->registerScript(uniqid(),'
	
	$(document).ready( function(){

		$(document).on("click", ".media-file", function(){
			
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/trainingLibrary/view",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id": id },
				success: function(response) {
					
					console.log(response);
					
					if( response.status == "error" )
					{
						alert(response.message);
						return false;
					}
					
					if(response.html  != "" )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo("body");

					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
		
		});

	});

', CClientScript::POS_END);

?>

<div class="page-header">
	<h1>Training Library</h1>
</div>

<div class="rows">
	<div class="col-sm-12">
		<b>VIDEO</b>
		
		<br /><br />
		
		<?php
			if( $videos )
			{
				$ctr = 0;
				
				foreach( $videos as $video )
				{
					$checkedSecurityGroups = array();
										
					if( $video->security_groups != null )
					{
						$checkedSecurityGroups = explode(',', $video->security_groups);
					}
					
					if( $checkedSecurityGroups && in_array(Yii::app()->user->account->account_type_id, $checkedSecurityGroups) )
					{
					?>
						<a id="<?php echo $video->id; ?>" class="file-thumb-link media-file">
							<div class="file-thumb-wrapper">
								<div class="file-thumb-title"><?php echo $video->title; ?></div>
								<div class="file-thumb">
									<?php 
										if(strtotime($video->date_created. ' + 5 days') > strtotime('Now') ) 
										{										
											echo '<span class="label label-danger file-label">New</span>';
										}
									?>
									<i class="fa fa-file-movie-o fa-3x" style="margin:15px 0 0 48px;"></i>
								</div>
								<div class="file-thumb-description"><?php echo $video->description; ?></div>
							</div>
						</a>
					<?php
					
					$ctr++;
					}
				}
				
				if( $ctr == 0 )
				{
					echo 'No video found.';
				}
			}
			else
			{
				echo 'No video found.';
			}
		?>
		
	</div>
</div>

<br style="clear:both">
<br style="clear:both">

<div class="rows">
	<div class="col-sm-12">
		<b>AUDIO</b>
		
		<br /><br />
		
		<?php
			if( $audios )
			{
				$ctr = 0;
				
				foreach( $audios as $audio )
				{
					$checkedSecurityGroups = array();
										
					if( $audio->security_groups != null )
					{
						$checkedSecurityGroups = explode(',', $audio->security_groups);
					}
					
					if( $checkedSecurityGroups && in_array(Yii::app()->user->account->account_type_id, $checkedSecurityGroups) )
					{
					?>
						<a id="<?php echo $audio->id; ?>" class="file-thumb-link media-file">
							<div class="file-thumb-wrapper">
								<div class="file-thumb-title"><?php echo $audio->title; ?></div>
								<div class="file-thumb">	
									<?php 
										if(strtotime($audio->date_created. ' + 5 days') > strtotime('Now') ) 
										{										
											echo '<span class="label label-danger file-label">New</span>';
										}
									?>
									<i class="fa fa-file-sound-o fa-3x" style="margin:15px 0 0 48px;"></i>								
								</div>
								<div class="file-thumb-description"><?php echo $audio->description; ?></div>
							</div>
						</a>
					<?php
					
					$ctr++;
					}
				}
				
				if( $ctr == 0 )
				{
					echo 'No audio found.';
				}
			}
			else
			{
				echo 'No audio found.';
			}
		?>
		
	</div>
</div>

<br style="clear:both">
<br style="clear:both">

<div class="rows">
	<div class="col-sm-12">
		<b>DOCUMENTS</b>
		
		<br /><br />
		
		<?php
			if( $documents )
			{
				$ctr = 0;
				
				foreach( $documents as $document )
				{
					$checkedSecurityGroups = array();
										
					if( $document->security_groups != null )
					{
						$checkedSecurityGroups = explode(',', $document->security_groups);
					}
					
					if( $checkedSecurityGroups && in_array(Yii::app()->user->account->account_type_id, $checkedSecurityGroups) )
					{
						$extension = pathinfo('learningCenterFiles/' . $document->fileUpload->generated_filename, PATHINFO_EXTENSION);
						
						if( $extension == 'doc' || $extension == 'docx' )
						{
							$fileIcon = 'fa-file-word-o';
						}
						elseif( $extension == 'xls' || $extension == 'xlsx' )
						{
							$fileIcon = 'fa-file-excel-o';
						}
						elseif( $extension == 'ppt' || $extension == 'pptx' )
						{
							$fileIcon = 'fa-file-powerpoint-o';
						}
						elseif( $extension == 'jpg' || $extension == 'tiff' || $extension == 'bmp' )
						{
							$fileIcon = 'fa-file-image-o';
						}
						elseif( $extension == 'pdf' )
						{
							$fileIcon = 'fa-file-pdf-o';
						}
						else
						{
							$fileIcon = 'fa-file';
						}
					?>
						<a class="file-thumb-link" href="<?php echo Yii::app()->createUrl('/hr/trainingLibrary/download', array('id'=>$document->id)); ?>">
							<div class="file-thumb-wrapper">
								<div class="file-thumb-title"><?php echo $document->title; ?></div>
								<div class="file-thumb">	
									<?php 
										if(strtotime($document->date_created. ' + 5 days') > strtotime('Now') ) 
										{										
											echo '<span class="label label-danger file-label">New</span>';
										}
									?>
									<i class="fa <?php echo $fileIcon; ?> fa-3x" style="margin:15px 0 0 48px;"></i>								
								</div>
								<div class="file-thumb-description"><?php echo $document->description; ?></div>
							</div>
						</a>
					<?php
					
					$ctr++;
					}
				}
				
				if( $ctr == 0 )
				{
					echo 'No document found.';
				}
			}
			else
			{
				echo 'No document found.';
			}
		?>
		
	</div>
</div>

<br style="clear:both">
<br style="clear:both">

<div class="rows">
	<div class="col-sm-12">
		<b>LINKS</b>
		
		<br /><br />
		
		<?php
			if( $links )
			{
				$ctr = 0;
				
				foreach( $links as $link )
				{
					$checkedSecurityGroups = array();
										
					if( $link->security_groups != null )
					{
						$checkedSecurityGroups = explode(',', $link->security_groups);
					}
					
					if( $checkedSecurityGroups && in_array(Yii::app()->user->account->account_type_id, $checkedSecurityGroups) )
					{
					?>
						<a class="file-thumb-link" href="<?php echo $this->createUrl('viewLink', array('id'=>$link->id)); ?>" target="_blank">
							<div class="file-thumb-wrapper">
								<div class="file-thumb-title"><?php echo $link->title; ?></div>
								<div class="file-thumb">	
									<?php 
										if(strtotime($link->date_created. ' + 5 days') > strtotime('Now') ) 
										{										
											echo '<span class="label label-danger file-label">New</span>';
										}
									?>
									<i class="fa fa-link fa-3x" style="margin:15px 0 0 48px;"></i>								
								</div>
								<div class="file-thumb-description"><?php echo $link->description; ?></div>
							</div>
						</a>
					<?php
					
					$ctr++;
					}
				}
				
				if( $ctr == 0 )
				{
					echo 'No link found.';
				}
			}
			else
			{
				echo 'No link found.';
			}
		?>
		
	</div>
</div>