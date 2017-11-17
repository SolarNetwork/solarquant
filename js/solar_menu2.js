
var solarMenu = {	
	init: function(){
		//this.hideDivs();
		this.setLiHover();	
	},
	hideDivs: function(){
		var allMenDivs = $ES('div', 'mainNav');
		allMenDivs.addClass('hide');
	},
	setLiHover: function(){
		var allLi = $$('.hasSub').addEvents({
			'mouseenter': function(){		
				this.removeClass('hidSub');									
			},				
			'mouseleave': function(){					
				this.addClass('hidSub');													
			}
		});		
	}
	
};


window.onDomReady( function() {
		solarMenu.init();
	}
);
