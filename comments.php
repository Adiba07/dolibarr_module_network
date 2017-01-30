<?php 

	require 'config.php';
	
	dol_include_once('/network/class/network.class.php');
	
	$conf->dol_hide_topmenu =$conf->dol_hide_leftmenu = 1;
	
	$TCss = $conf->modules_parts['css'];
	$TJS = $conf->modules_parts['js'];
	
	
	$ref = TNetMsg::getRef(GETPOST('fk_object'), GETPOST('type_object'));
	
	$conf->modules_parts['css'] = array('network'=>$TCss['network']);
	$conf->modules_parts['js'] = array('network'=>$TJS['network']);
	
	llxHeader();
	?>
	<style type="text/css">
	body {
	background-color:#fff;
	}
	body, div.fiche,#twittor-panel {
		padding:0 0 0 0;
		margin:0 0 0 0;
	}
	div.tabBar {
		border-top:0 none;
		border-bottom:0 none; 
	}
	</style>
	<div id="twittor-panel" class="tabBar"></div>
	<script type="text/javascript">
		drawNetworkWidget( <?php echo GETPOST('fk_object')?>, "<?php echo GETPOST('type_object') ?>", "<?php echo GETPOST('sub_object') ?>", "<?php echo $ref ?>" ,$('#twittor-panel') );
	</script>
	
	<?php 
	
	
	
	llxFooter();