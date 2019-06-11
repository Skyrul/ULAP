<?php 

$this->pageTitle = 'Engagex - Resource Center';

$cs = Yii::app()->clientScript;
$cs->registerCss(uniqid(), '

	.file-thumb-link{ color:#333333; }
	
	.file-thumb-wrapper{ float:left; width:198px; margin-left:50px; margin-bottom:50px; }
	
	.file-icon{ margin:33px 0 0 80px; }
	
	.file-thumb{ border:1px #CCCCCC solid; height:112px; position:relative; margin:5px 0; }
	.file-thumb:hover{ border:1px #2A6496 solid; }
	.file-thumb a { color:#333333; }
	.file-thumb img { width:100%; max-height:110px; }
	
	.file-thumb-title { text-align:center; }
	.file-thumb-description { text-align:left; }
	
	.file-label { position:absolute; right:0; top:0; }
	
	.media-file{ cursor:pointer; }
');

$cs->registerScript(uniqid(),'
	
	$(document).ready( function(){

		$(document).on("click", ".media-file", function(){
			
			var id = $(this).prop("id");
			var customer_id = "'.$customer->id.'";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/learningCenter/view",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id": id, "customer_id": customer_id },
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

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'learningCenter',
		'customer' => $customer,
	));
?>

<div class="page-header">
	<h1><?php echo isset($customer->company) ? $customer->company->learning_center_label : 'Engagex Resource Center'; ?></h1>
</div>


<?php if($categories): ?>

	<?php foreach($categories as $category): ?>

		<div class="rows">
			<div class="col-sm-12">
				<b><?php echo $category->name; ?></b>

				<br /><br />
					
				<?php
					 $files = CompanyLearningCenterFile::model()->findAll(array(
						'condition' => 'category_id = :category_id AND status=1',
						'params' => array(
							':category_id' => $category->id,
						),
						'order' => 'sort_order ASC',
					));
					
					if( $files )
					{
						foreach( $files as $file )
						{			
							$extension = pathinfo('learningCenterFiles/' . $file->fileUpload->generated_filename, PATHINFO_EXTENSION);
							
							$viewLink = 'javascript:void(0);';
							$viewClass = 'media-file';
							
							if( isset($file->fileUpload) )
							{
								if( in_array($extension, array('mp4', 'avi')) )
								{
									$fileIcon = 'fa-file-movie-o';
								}
								elseif( in_array($extension, array('wav', 'mp3', 'aiff')) )
								{
									$fileIcon = 'fa-file-audio-o';
								}
								else
								{
									$viewLink = Yii::app()->createUrl('admin/learningCenter/download', array('id'=>$file->id, 'customer_id'=>$customer->id));
									$viewClass = '';
								}
							}
						?>
							<a id="<?php echo $file->id; ?>" class="file-thumb-link <?php echo $viewClass; ?>" href="<?php echo $viewLink; ?>" title="<?php echo $file->title; ?>" >
								<div class="file-thumb-wrapper">
									<div class="file-thumb-title"><?php echo $file->title; ?></div>
									
									<div class="file-thumb">
										<?php 
											if(strtotime($file->date_created. ' + 30 days') > strtotime('Now') ) 
											{										
												echo '<span class="label label-danger file-label">New</span>';
											}
										?>
										
										<?php
											if( isset($file->thumbnailFileUpload) )
											{
												echo '<img src="'.Yii::app()->request->baseUrl.'/learningCenterFiles/thumbnails/'.$file->thumbnailFileUpload->generated_filename.'">';
											}
											else
											{
												echo '<i class="fa fa-file fa-3x file-icon"></i>';
											}
										?>
									</div>
									
									<div class="file-thumb-description"><?php echo $file->description; ?></div>
								</div>
							</a>
						<?php
						}
					}
					else
					{
						echo 'No files found.';
					}
				?>

			</div>
		</div>

		<br style="clear:both">
		<br style="clear:both">

	<?php endforeach; ?>
	
<?php endif; ?>

