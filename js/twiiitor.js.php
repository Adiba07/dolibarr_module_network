<?php

	require('../config.php');

?>
var cache = [];

$(document).ready(function() {
	
	$div = $('<div class="tabBar"><strong><?php echo $langs->trans('NanoSocial') ?> #<?php echo GETPOST('ref') ?></strong> <a href="javascript:showSociogram();"><img src="<?php echo dol_buildpath('/twiiitor/img/users_relation.png',1) ?>" border="0" align="absmiddle" /></a></div>');
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


function showSociogram() {
	
	$('#sociogram').remove();
	
	$('body').append('<div id="sociogram"><canvas width="800" height="600"></canvas></div>');
	
	$("#sociogram").dialog({
		title:"Sociogram"
		,modal:true
		,width:800
		
	});

	var sys = arbor.ParticleSystem(1000, 600, 0.5) // create the system with sensible repulsion/stiffness/friction
    sys.parameters({gravity:true}) // use center-gravity to make the graph settle nicely (ymmv)
    sys.renderer = Renderer("#sociogram canvas") // our newly created renderer will have its .init() method called shortly by sys...

    // add some nodes to the graph and watch it go...
    sys.addEdge('Alexis','cousin')
    sys.addEdge('Alexis','dirigeant')
    sys.addEdge('ATM','dirigeant')
    sys.addEdge('Alexis','maîtresse')
    sys.addEdge('maîtresse','Marie')
    sys.addEdge('Alexis','marié')
    sys.addEdge('cousin','Bob')
    sys.addEdge('marié','Daphné')
    sys.addEdge('amant','Robert')
    sys.addEdge('Daphné','amant')
    

	
	
}

var Renderer = function(canvas){
    var canvas = $(canvas).get(0)
    var ctx = canvas.getContext("2d");
    var particleSystem

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
          ctx.strokeStyle = "rgba(0,0,0, 2)"
          ctx.lineWidth = 1
          ctx.beginPath()
          ctx.moveTo(pt1.x, pt1.y)
          ctx.lineTo(pt2.x, pt2.y)
          ctx.stroke()
        })

        particleSystem.eachNode(function(node, pt){
          // node: {mass:#, p:{x,y}, name:"", data:{}}
          // pt:   {x:#, y:#}  node position in screen coords

          // draw a rectangle centered at pt
          //var w = 100
          //ctx.fillStyle = (node.data.alone) ? "orange" : "black"
          //ctx.fillRect(pt.x-w/2, pt.y-w/2, w,w)*/
          ctx.font = "30px Arial";
          ctx.fillStyle = "blue";
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
