<?php

	require('../config.php');

?>
var cache = [];

$(document).ready(function() {
	
	$div = $('<div><strong><?php echo $langs->trans('NanoSocial') ?></strong></div>');
	$div.attr('id','twittor-panel');
	$div.append('<textarea name="comment"></textarea>');
	
	$button = $('<input type="button" name="btcomment" value="<?php echo $langs->trans('AddComment') ?>">');
	$button.click(function() {
		$.ajax({
			url : '<?php echo dol_buildpath('/twiiitor/script/interface.php',1) ?>'
			,data:{ 
		      		put:"comment"
		      		,comment:$('#twittor-panel textarea[name=comment]').val()
		      		, element:"<?php echo GETPOST('element') ?>"
		      		, ref:"<?php echo GETPOST('ref') ?>"
		      		, id:<?php echo GETPOST('id') ?> 
		     }
		     ,method:'post'
		}).done(function (data) { 
			TwiiitorLoadComment(); 
		});
			
		
	});
	
	$div.append($button);
	$div.append('<div class="comments"></div>');
	
	$('#id-right').after($div);
	
	TwiiitorLoadComment();
	
	setTextTag();
	
});

function TwiiitorLoadComment() {
	
	$.ajax({
		url : '<?php echo dol_buildpath('/twiiitor/script/interface.php',1) ?>'
		,data:{ 
	      		get:"comments"
	      		, element:"<?php echo GETPOST('element') ?>"
	      		, ref:"<?php echo GETPOST('ref') ?>"
	      		, id:<?php echo GETPOST('id') ?> 
	     }	
	}).done(function (data) { 
		$('#twittor-panel div.comments').html(data); 
	});
	      
	
}

function setTextTag() {
	
	$('#twittor-panel textarea').textcomplete([
	  { // mention strategy
	    match: /(^|\s)@(\w*)$/,
	    search: function (term, callback) {
	    	
	      //callback(cache[term], true);
	      $.getJSON('<?php echo dol_buildpath('/twiiitor/script/interface.php',1) ?>', { 
	      		q: term
	      		,get:"search-user"
	      		, element:"<?php echo GETPOST('element') ?>"
	      		, ref:"<?php echo GETPOST('ref') ?>"
	      		, id:<?php echo GETPOST('id') ?> 
	      	})
	        .done(function (resp) { callback(resp); })
	        .fail(function ()     { callback([]);   });
	    },
	    replace: function (value) {
	      return '$1@' + value + ' ';
	    },
	    cache: true
	  }
	  ,{ // mention strategy
	    match: /(^|\s):(\w*)$/,
	    search: function (term, callback) {
	    	
	      //callback(cache[term], true);
	      $.getJSON('<?php echo dol_buildpath('/twiiitor/script/interface.php',1) ?>', { 
	      		q: term
	      		,get:"search-tag"
	      		, element:"<?php echo GETPOST('element') ?>"
	      		, ref:"<?php echo GETPOST('ref') ?>"
	      		, id:<?php echo GETPOST('id') ?> 
	      	})
	        .done(function (resp) { callback(resp); })
	        .fail(function ()     { callback([]);   });
	    },
	    replace: function (value) {
	      return '$1:' + value + ': ';
	    },
	    cache: true
	  }
	], { maxCount: 20, debounce: 500 });

}
