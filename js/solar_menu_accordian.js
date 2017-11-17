
var guide = {	
	
	makeAccordions: function(){
		/*	find all the dl elements with the class "Accordion"	*/
		$$('ul.Accordion').each(function(el){
			/*	make a new accordion with the elements in the dl element.			
			
			Element.getElements works like $$() does - it returns an array of
			elements that match that selector. Unlike $$(), getElements starts
			at the Element on which it's called so it acts like a filter.	*/
			new Accordion(el.getElements('a.stretchtoggle'), el.getElements('div.stretcher'));	
				
		})
	},
		
	init: function(){
		this.makeAccordions();		
	}
};
/*	window.onDomReady will call my guide.init on page load, but the "this" will be the window, so
		I bind the guide to the function call explicitly. I wouldn't have this problem if I wrote it
		this way:

window.onDomReady(function()[
	guide.init();
});
		But that's more verbose than it needs to be.
		*/
//window.onDomReady(guide.init.bind(guide));

window.onDomReady( function() {
		guide.init();
	}
);
