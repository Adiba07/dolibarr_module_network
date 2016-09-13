<?php

	require('../config.php');
	dol_include_once('/network/class/network.class.php');
	
	if(empty($user->rights->network->read)) exit; // pas les droit de lecture

	$langs->load('network@network');
	
?>
$(document).ready(function() {
	
	setInterval(function() {
		
		$.ajax({
			url:"<?php echo dol_buildpath('/network/script/interface.php',1) ?>"
			,data:{
				get:"comments-for-me"				
			}
			,dataType:"json"
		}).done(function(TMessage) {
			
			for(x in TMessage) {
				
				msg = TMessage[x];
				
				alertMessage = "["+msg.origin +"] "+ msg.author + " : " +msg.comment;
				
				$.jnotify(alertMessage,"netmsg", true);
				
			}
			
			
		});
		
	},5000);
	
});
	