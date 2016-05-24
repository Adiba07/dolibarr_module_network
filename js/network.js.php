<?php

	require('../config.php');
	dol_include_once('/network/class/network.class.php');
	
	if(empty($user->rights->network->read)) exit; // pas les droit de lecture

	$langs->load('network@network');

	$element_tag = TNetMsg::getTag(GETPOST('element'), GETPOST('ref'));

?>
var cache = [];

$(document).ready(function() {
	
	$div = $('<div class="tabBar"><div rel="header"><strong><?php echo $langs->trans('Network') ?> <?php echo $element_tag; ?></strong> <a href="javascript:showSociogram();"><img src="<?php echo dol_buildpath('/network/img/users_relation.png',1) ?>" border="0" align="absmiddle" /></a></div><div rel="writer"></div></div>');
	$div.attr('id','twittor-panel');
	<?php
	
	if(!empty($user->rights->network->write)) {
		
	?>
	
	$writer = $div.find('[rel=writer]');
	
	$writer.append('<input type="text" name="comment" maxlength="140" placeholder="Saisissez une relation (140car. max.)" />');
	$button = $('<input type="button" name="btcomment" class="button" value="<?php echo $langs->trans('CreateTwiiit') ?>">');
	$writer.find('input[name=comment]').keypress(function(e) {
		if(e.which == 13) {
	        addComment();
	    }
	});
	$button.click(function() {
		
		addComment();
		
	});
	
	function addComment() {
		var comment = $('#twittor-panel input[name=comment]').val();
		
		if(comment.trim() == '') return false;
		
		$.ajax({
			url : '<?php echo dol_buildpath('/network/script/interface.php',1) ?>'
			,data:{ 
		      		put:"comment"
		      		,comment:comment
		      		, element:"<?php echo GETPOST('element') ?>"
		      		, ref:"<?php echo GETPOST('ref') ?>"
		      		, id:<?php echo GETPOST('id') ?> 
		     }
		     ,method:'post'
		}).done(function (data) { 
			NetworkLoadComment(); 
			$('#twittor-panel input[name=comment]').val("");
		});
			
	}
	
	$writer.append($button);
	
	<?php
	}
	
	?>
	
	$div.append('<div class="comments"></div>');
	
	$('#id-right').append($div);
	
	NetworkLoadComment();
	
	setTextTag();
	
});
var sysArbor = null;
function getEdge(ref, element, id) {
	
	$.ajax({
		url : '<?php echo dol_buildpath('/network/script/interface.php',1) ?>'
		,data:{ 
	      		get:"graph"
	      		, element:element
	      		, ref:ref
	      		, id:id
	     }
	     ,method:'get'
	     ,dataType:'json'
	}).done(function (data) { 
		
		for (x in data) {
			edge = data[x];
			
			if(edge.from.length>1 && edge.to.length>1) {
				sysArbor.addEdge(edge.from,edge.to,{label:edge.label});	
			}
				
		}
		
	});
	
}

function showSociogram() {
	
	$('#sociogram').remove();
	
	$('body').append('<div id="sociogram"><canvas width="800" height="600"></canvas></div>');
	
	$("#sociogram").dialog({
		title:"Sociogram"
		,modal:true
		,width:800
		
	});

	sysArbor = arbor.ParticleSystem(1000, 600, 0.5) // create the system with sensible repulsion/stiffness/friction
    sysArbor.parameters({gravity:true}) // use center-gravity to make the graph settle nicely (ymmv)
    sysArbor.renderer = Renderer("#sociogram canvas") // our newly created renderer will have its .init() method called shortly by sys...

	getEdge("<?php echo GETPOST('ref') ?>", "<?php echo GETPOST('element') ?>", <?php echo GETPOST('id') ?>);

}

var Renderer = function(canvas){
    var canvas = $(canvas).get(0)
    var ctx = canvas.getContext("2d");
    var particleSystem

	var imgUser = new Image;
	imgUser.src = "<?php echo dol_buildpath("/network/img/user.png",1) ?>";

	var imgDoc = new Image;
	imgDoc.src = "<?php echo dol_buildpath("/network/img/doc.png",1) ?>";

    var that = {
      init:function(system){
       particleSystem = system
        particleSystem.screenSize(canvas.width, canvas.height) 
        particleSystem.screenPadding(80) // leave an extra 80px of whitespace per side
        that.initMouseHandling()
      },
      
      redraw:function(){
        ctx.fillStyle = "white"
        ctx.fillRect(0,0, canvas.width, canvas.height)
        
        particleSystem.eachEdge(function(edge, pt1, pt2){
          // edge: {source:Node, target:Node, length:#, data:{}}
          // pt1:  {x:#, y:#}  source position in screen coords
          // pt2:  {x:#, y:#}  target position in screen coords

          // draw a line from pt1 to pt2
          ctx.save();
          
          if(edge.source.name[0] == '#' || edge.target.name[0] == '#') {
          	ctx.strokeStyle = "rgba(0,100,100, 1)";
            ctx.lineWidth = 2
          }
          else {
          	ctx.setLineDash([5, 15]);	
          	ctx.strokeStyle = "rgba(0,100,0, 1)";
          	ctx.lineWidth = 1
          }
          

          ctx.beginPath();
          //ctx.moveTo(pt1.x, pt1.y);
          //
          
          ctx.moveTo( pt1.x + ( Math.sign( pt2.x - pt1.x ) * 10 ), pt1.y + (Math.sign(pt2.y - pt1.y)  * 10) ); 
          ctx.lineTo(pt2.x - ( Math.sign( pt2.x - pt1.x ) * 10 ), pt2.y - (Math.sign(pt2.y - pt1.y)  * 10));
          
          ctx.stroke();
          
          ctx.restore();
          
          ctx.font = "20px Arial";
          ctx.fillStyle = "orange";
		  ctx.textAlign = "center";
		  ctx.fillText(edge.data.label, pt1.x + ((pt2.x - pt1.x) / 2), pt1.y + ((pt2.y - pt1.y) / 2) );
          
        })

        particleSystem.eachNode(function(node, pt){
          // node: {mass:#, p:{x,y}, name:"", data:{}}
          // pt:   {x:#, y:#}  node position in screen coords

		  
		  var w = 50;
		  
		  if(node.name[0] == "#") {
		  	
		  	 
		  	  ctx.drawImage(imgDoc, pt.x -w/2, pt.y - 80);
		  	  ctx.font = "25px Arial";  
		  	  ctx.fillStyle = "green";			
		  }
		  else {
		      ctx.drawImage(imgUser, pt.x -w/2, pt.y - 70);
          	  ctx.font = "20px Arial";
          	  ctx.fillStyle = "blue";
		  }

          // draw a rectangle centered at pt
          //
          //ctx.fillStyle = (node.data.alone) ? "orange" : "black"
          //ctx.fillRect(pt.x-w/2, pt.y-w/2, w,w)*/
          
		  ctx.textAlign = "center";
          ctx.fillText(node.name, pt.x, pt.y);
        })    			
      },
      
      initMouseHandling:function(){
        // no-nonsense drag and drop (thanks springy.js)
        var dragged = null;

        // set up a handler object that will initially listen for mousedowns then
        // for moves and mouseups while dragging
        var handler = {
          clicked:function(e){
            var pos = $(canvas).offset();
            _mouseP = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)
            dragged = particleSystem.nearest(_mouseP);

            if (dragged && dragged.node !== null){
              // while we're dragging, don't let physics move the node
              dragged.node.fixed = true
            }

            $(canvas).bind('mousemove', handler.dragged)
            $(window).bind('mouseup', handler.dropped)

            return false
          },
          dragged:function(e){
            var pos = $(canvas).offset();
            var s = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)

            if (dragged && dragged.node !== null){
              var p = particleSystem.fromScreen(s)
              dragged.node.p = p
            }

            return false
          },

          dropped:function(e){
            if (dragged===null || dragged.node===undefined) return
            if (dragged.node !== null) dragged.node.fixed = false
            dragged.node.tempMass = 1000
            dragged = null
            $(canvas).unbind('mousemove', handler.dragged)
            $(window).unbind('mouseup', handler.dropped)
            _mouseP = null
            return false
          }
        }
        
        // start listening
        $(canvas).mousedown(handler.clicked);

      },
      
    }
    return that
  }    


function NetworkLoadComment(start) {
	
	if(!start) start = 0;
	
	$.ajax({
		url : '<?php echo dol_buildpath('/network/script/interface.php',1) ?>'
		,data:{ 
	      		get:"comments"
	      		, element:"<?php echo GETPOST('element') ?>"
	      		, ref:"<?php echo GETPOST('ref') ?>"
	      		, id:<?php echo GETPOST('id') ?> 
	      		, start : start
	     }	
	}).done(function (data) { 
		
		if(start>0) {
			$('#twittor-panel div.comments div.showMore').remove();
			$('#twittor-panel div.comments').append(data);
		}
		else{
			$('#twittor-panel div.comments').html(data);	
		}
		
		 
	});
	      
	
}

function networkRemoveComment(commid) {
	if(window.confirm("Etes-vous sûr ?")) {
		$.ajax({
		url : '<?php echo dol_buildpath('/network/script/interface.php',1) ?>'
		,data:{ 
	      		put:"remove-comment"
	      		, id:commid
	     }	
		}).done(function (data) { 
			$('div.comments div.comm[commid='+commid+']').remove();	
		});
		
		
		
	}	
}

function setTextTag() {
	
	$('#twittor-panel input[name=comment]').textcomplete([
	  { // mention strategy
	    match: /(^|\s)@(\w*)$/,
	    search: function (term, callback) {
	    	
	      //callback(cache[term], true);
	      $.getJSON('<?php echo dol_buildpath('/network/script/interface.php',1) ?>', { 
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
	      $.getJSON('<?php echo dol_buildpath('/network/script/interface.php',1) ?>', { 
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
	      return '$1:' + value + ' ';
	    },
	    cache: true
	  }
	  ,{ // mention strategy
	    match: /(^|\s)#(\w*)$/,
	    search: function (term, callback) {
	    	
	      //callback(cache[term], true);
	      $.getJSON('<?php echo dol_buildpath('/network/script/interface.php',1) ?>', { 
	      		q: term
	      		,get:"search-element"
	      		, element:"<?php echo GETPOST('element') ?>"
	      		, ref:"<?php echo GETPOST('ref') ?>"
	      		, id:<?php echo GETPOST('id') ?> 
	      	})
	        .done(function (resp) { callback(resp); })
	        .fail(function ()     { callback([]);   });
	    },
	    replace: function (value) {
	      return '$1#' + value + ' ';
	    },
	    cache: true
	  }
	], { maxCount: 20, debounce: 500 });

}
