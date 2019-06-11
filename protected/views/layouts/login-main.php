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

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title><?php echo $this->PageTitle; ?></title>

		<meta name="description" content="top menu &amp; navigation" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
		
		<!-- Yii -->
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/form.css" />

		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/font-awesome.min.css" />

		<!-- page specific plugin styles -->

		<!-- text fonts -->
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace-fonts.css" />
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/jquery.gritter.css" />

		<!-- ace styles -->
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace.min.css" id="main-ace-style" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace-part2.min.css" />
		<![endif]-->
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace-skins.min.css" />
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace-rtl.min.css" />

		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/template_assets/css/ace-ie.min.css" />
		<![endif]-->

		<!-- inline styles related to this page -->

		<!-- ace settings handler -->
		<script src="<?php echo $baseUrl; ?>/template_assets/js/ace-extra.min.js"></script>

		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

		<!--[if lte IE 8]>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/html5shiv.min.js"></script>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/respond.min.js"></script>
		<![endif]-->
	</head>

	<body class="login-layout blur-login" style="background-color:#ffffff !important;">
		<!-- #section:basics/navbar.layout -->
		<?php $this->widget('Header'); ?>

		<!-- /section:basics/navbar.layout -->
		<div class="main-container" id="main-container">
			<script type="text/javascript">
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
			</script>

			<!-- #section:basics/sidebar.horizontal -->
			<?php //this->widget('Navbar'); ?>

			<!-- /section:basics/sidebar.horizontal -->
			<div class="main-content">
			
				<?php 
					if( $_SERVER['SERVER_NAME'] == 'test.engagexapp.com' )
					{
						echo '
							<div class="space-12"></div>
							
							<div class="alert alert-danger center">
								<strong>
									<i class="ace-icon fa fa-exclamation-triangle bigger-130"></i>
									THIS IS A TESTING ENVIRONMENT!
								</strong>
								<br>
							</div>
						';
					}
				?>
			
				<?php echo $content; ?>
			</div><!-- /.main-content -->

			<div class="footer hide">
				<div class="footer-inner">
					<!-- #section:basics/footer -->
					<div class="footer-content">
						<span class="bigger-120">
							<span class="blue"><?php echo Yii::app()->name; ?></span>
							&copy; <?php echo date('Y'); ?>
						</span>
					</div>

					<!-- /section:basics/footer -->
				</div>
			</div>

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->

		<!-- basic scripts -->
		
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='<?php echo $baseUrl; ?>/template_assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		
		
		<!-- page specific plugin scripts -->
		<script src="<?php echo $baseUrl; ?>/template_assets/js/bootstrap.min.js"></script>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/jquery.gritter.min.js"></script>

		<!-- ace scripts -->
		<script src="<?php echo $baseUrl; ?>/template_assets/js/ace-elements.min.js"></script>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/ace.min.js"></script>
		<script src="<?php echo $baseUrl; ?>/template_assets/js/bootbox.min.js"></script>
		
		<script src="<?php echo $baseUrl; ?>/js/common.js"></script>

		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			$( function() {
			 var $sidebar = $('.sidebar').eq(0);
			 if( !$sidebar.hasClass('h-sidebar') ) return;
			
			 $(document).on('settings.ace.top_menu' , function(ev, event_name, fixed) {
				if( event_name !== 'sidebar_fixed' ) return;
			
				var sidebar = $sidebar.get(0);
				var $window = $(window);
			
				//return if sidebar is not fixed or in mobile view mode
				if( !fixed || ( ace.helper.mobile_view() || ace.helper.collapsible() ) ) {
					$sidebar.removeClass('hide-before');
					//restore original, default marginTop
					ace.helper.removeStyle(sidebar , 'margin-top')
			
					$window.off('scroll.ace.top_menu')
					return;
				}
			
			
				 var done = false;
				 $window.on('scroll.ace.top_menu', function(e) {
			
					var scroll = $window.scrollTop();
					scroll = parseInt(scroll / 4);//move the menu up 1px for every 4px of document scrolling
					if (scroll > 17) scroll = 17;
			
			
					if (scroll > 16) {			
						if(!done) {
							$sidebar.addClass('hide-before');
							done = true;
						}
					}
					else {
						if(done) {
							$sidebar.removeClass('hide-before');
							done = false;
						}
					}
			
					sidebar.style['marginTop'] = (17-scroll)+'px';
				 }).triggerHandler('scroll.ace.top_menu');
			
			 }).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			
			 $(window).on('resize.ace.top_menu', function() {
				$(document).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			 });
			
			
			});
		</script>
	</body>
</html>
