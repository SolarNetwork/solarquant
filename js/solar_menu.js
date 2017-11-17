
var solarMenu = {	
	init: function(){
		this.setLiHover();
		this.setPageScroll();
		this.hideDivs();
	},
	hideDivs: function(){
		var allMenDivs = $ES('div', 'mainNav');
		allMenDivs.each(function(md){
			var subInvis = new Fx.Style(md, 'opacity').set(0);		
		});		
	},
	setLiHover: function(){
		var allLi = $$('.hasSub').addEvents({
			'mouseenter': function(){		
				var liSub = this.getElement('div');				
				var subFade = new Fx.Style(liSub, 'opacity', {duration:500});
				subFade.start(0,1);									
			},				
			'mouseleave': function(){					
				var liSub = this.getElement('div');				
				var subFade = new Fx.Style(liSub, 'opacity', {duration:400});
				subFade.start(1,0);										
			}
		});		
	},
	setPageScroll: function(){
		var scroll = new Fx.Scroll(window, {
			duration: 700,
			transition: Fx.Transitions.Quad.easeInOut
		});
		$$('.scrollLink').each(function(el){				
			el.addEvent('click', function(event){
				event = new Event(event).stop();
				scroll.toTop();
			});
		});
	}	
	
};


window.onDomReady( function() {
		solarMenu.init();
	}
);
