<?php

	require('../config.php');
	dol_include_once('/network/class/network.class.php');
	
	if(empty($user->rights->network->read)) exit; // pas les droit de lecture

	$langs->load('network@network');

	$element_tag = TNetMsg::getTag(GETPOST('element'), GETPOST('ref'));

?>
var cache = [];

$(document).ready(function() {
	
	$div = $('<div class="tabBar"><div rel="header"><strong><?php echo $langs->trans('Network') ?> <?php echo $element_tag; ?></strong> <a href="javascript:showSociogram(<?php echo GETPOST('id')?>, \'<?php echo GETPOST('element') ?>\', \'<?php echo GETPOST('ref') ?>\');"><img src="<?php echo dol_buildpath('/network/img/users_relation.png',1) ?>" border="0" align="absmiddle" /></a></div></div>');
	$div.attr('id','twittor-panel');
	
	$('#id-right').append($div);
	
	drawNetworkWidget( <?php echo GETPOST('id')?>, "<?php echo GETPOST('element') ?>", "<?php echo GETPOST('ref') ?>" ,$div );
	
});


