<?php

	require('../config.php');

?>
var cache = [];

$(document).ready(function() {
	
	$div = $('<div></div>');
	$div.attr('id','twittor-panel');
	$div.append('<textarea name="comment"></textarea>');
	
	$('#id-right').after($div);
	
	setTextTag();
	
});

function setTextTag() {
	
	$('textarea').textcomplete([
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
	], { maxCount: 20, debounce: 500 });

}
