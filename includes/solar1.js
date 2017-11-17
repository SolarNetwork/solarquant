

function determineShowHide()
{
var whatIsToggle = document.getElementById("whatIsToggle");
if (whatIsToggle.value == "shown")
{
//alert("onLoad: it says shown ");
}
else
{
//alert("onLoad: it DOES NOT say shown ");
}

resetLinksToBlack()

//whatIs
var isExperiment = document.URL.indexOf('experiment');
var isOpenSource = document.URL.indexOf('openSource');
if (isExperiment != -1 || isOpenSource != -1) 
{
showWhatIsTable()
}
else
{
hideWhatIsTable()
}
//howWork
var hybrid = document.URL.indexOf('hybrid');
var distributed = document.URL.indexOf('distributed');
if (hybrid != -1 || distributed != -1) 
{
showHowWorkTable()
}
else
{
hideHowWorkTable()
}
//theBusiness
var isStateOfTheArt = document.URL.indexOf('stateOfTheArt');
var isModel = document.URL.indexOf('model');
var isIndependent = document.URL.indexOf('independent');
var isBeyond = document.URL.indexOf('beyond');
var isOwning = document.URL.indexOf('owning');

if (isStateOfTheArt != -1 || isModel != -1 || isIndependent != -1 || isBeyond != -1 || isOwning != -1) 
{
showTheBusinessTable()
}
else
{
hideTheBusinessTable()
}

//future
var isVision = document.URL.indexOf('vision');
var isImprovement = document.URL.indexOf('improvement');
var isServices = document.URL.indexOf('services');
if (isVision != -1 || isImprovement != -1 || isServices != -1) 
{
showFutureTable()
}
else
{
	//alert("about to hideFutureTable() ");
hideFutureTable()
}

//BuildIt
var isPhases = document.URL.indexOf('phases');
if (isPhases != -1) 
{
showBuildItTable()
}
else
{
hideBuildItTable()
}
//help out
var isHelpout = document.URL.indexOf('helpout');
var helpOutLink = document.getElementById("helpOutLink");
if (isHelpout != -1) 
{
helpOutLink.innerHTML = 'How can I Help Out?';
helpOutLink.style.color = '#ff6800';
}
else
{
helpOutLink.innerHTML = 'How To Help Out';
helpOutLink.style.color = '#000000';
}

var accordion = new Accordion('h3.atStart', 'div.atStart', {
	opacity: false,
	onActive: function(toggler, element){
		toggler.setStyle('color', '#ff3300');
	},
 
	onBackground: function(toggler, element){
		toggler.setStyle('color', '#222');
	}
}, $('accordion'));

var newTog = new Element('h3', {'class': 'toggler'}).setHTML('Common descent');
 
var newEl = new Element('div', {'class': 'element'}).setHTML('<p>A group of organisms is said to have common descent if they have a common ancestor. In biology, the theory of universal common descent proposes that all organisms on Earth are descended from a common ancestor or ancestral gene pool.</p><p>A theory of universal common descent based on evolutionary principles was proposed by Charles Darwin in his book The Origin of Species (1859), and later in The Descent of Man (1871). This theory is now generally accepted by biologists, and the last universal common ancestor (LUCA or LUA), that is, the most recent common ancestor of all currently living organisms, is believed to have appeared about 3.9 billion years ago. The theory of a common ancestor between all organisms is one of the principles of evolution, although for single cell organisms and viruses, single phylogeny is disputed</p>');
 
accordion.addSection(newTog, newEl, 0);

} //end determineHideShow

function showWhatIsTable()
{
var whatIsArrow = document.getElementById("whatIsArrow");
whatIsArrow.src = 'images/arrowOpen.gif';	
var whatIsLink = document.getElementById("whatIsLink");
whatIsLink.innerHTML = 'What Is It?';
whatIsLink.style.color = '#ff6800';
var whatIsToggle = document.getElementById("whatIsToggle");
whatIsToggle.value = 'shown';
var whatIsTable = document.getElementById("whatIsTable");
whatIsTable.style.display = 'block';
}

function hideWhatIsTable()
{
var whatIsArrow = document.getElementById("whatIsArrow");
whatIsArrow.src = 'images/arrowClosed.gif';	
var whatIsLink = document.getElementById("whatIsLink");
whatIsLink.innerHTML = 'What It Is';
whatIsLink.style.color = '#000000';
var whatIsToggle = document.getElementById("whatIsToggle");
whatIsToggle.value = 'hidden';
var whatIsTable = document.getElementById("whatIsTable");
whatIsTable.style.display = 'none';
}

function showWhatIs()
{
//determine if it's shown
var whatIsToggle = document.getElementById("whatIsToggle");
if (whatIsToggle.value == "hidden")
{
	showWhatIsTable()
}
else
{
	hideWhatIsTable()
}
}
function showHowWorkTable()
{
var howWorkArrow = document.getElementById("howWorkArrow");
howWorkArrow.src = 'images/arrowOpen.gif';	
var howWorkLink = document.getElementById("howWorkLink");
howWorkLink.innerHTML = 'How Would It Work?';
howWorkLink.style.color = '#ff6800';
var howWorkToggle = document.getElementById("howWorkToggle");
howWorkToggle.value = 'shown';
var howWorkTable = document.getElementById("howWorkTable");
howWorkTable.style.display = 'block';
}

function hideHowWorkTable()
{
var howWorkArrow = document.getElementById("howWorkArrow");
howWorkArrow.src = 'images/arrowClosed.gif';	
var howWorkLink = document.getElementById("howWorkLink");
howWorkLink.innerHTML = 'How It Would Work';
howWorkLink.style.color = '#000000';
var howWorkToggle = document.getElementById("howWorkToggle");
howWorkToggle.value = 'hidden';
var howWorkTable = document.getElementById("howWorkTable");
howWorkTable.style.display = 'none';
}

function showHowWork()
{
//determine if it's shown
var howWorkToggle = document.getElementById("howWorkToggle");
if (howWorkToggle.value == "hidden")
{
	showHowWorkTable()
}
else
{
	hideHowWorkTable()
}

}

function showTheBusinessTable()
{
var theBusinessArrow = document.getElementById("theBusinessArrow");
theBusinessArrow.src = 'images/arrowOpen.gif';	
var theBusinessLink = document.getElementById("theBusinessLink");
theBusinessLink.innerHTML = 'The Business?';
theBusinessLink.style.color = '#ff6800';
var theBusinessToggle = document.getElementById("theBusinessToggle");
theBusinessToggle.value = 'shown'
var theBusinessTable = document.getElementById("theBusinessTable");
theBusinessTable.style.display = 'block';
}

function hideTheBusinessTable()
{
var theBusinessArrow = document.getElementById("theBusinessArrow");
theBusinessArrow.src = 'images/arrowClosed.gif';	
var theBusinessLink = document.getElementById("theBusinessLink");
theBusinessLink.innerHTML = 'The Business';
theBusinessLink.style.color = '#000000';
var theBusinessToggle = document.getElementById("theBusinessToggle");
theBusinessToggle.value = 'hidden';
var theBusinessTable = document.getElementById("theBusinessTable");
theBusinessTable.style.display = 'none';
}

function showTheBusiness()
{
//determine if it's shown
var theBusinessToggle = document.getElementById("theBusinessToggle");
if (theBusinessToggle.value == "hidden")
{
	showTheBusinessTable()
}
else
{
	hideTheBusinessTable()
}

}

function showFutureTable()
{
var futureArrow = document.getElementById("futureArrow");
futureArrow.src = 'images/arrowOpen.gif';	
var futureLink = document.getElementById("futureLink");
futureLink.innerHTML = 'A Reliance on the Future';
var futureToggle = document.getElementById("futureToggle");
futureToggle.value = 'shown'
var futureTable = document.getElementById("futureTable");
futureTable.style.display = 'block';
}

function hideFutureTable()
{
var futureArrow = document.getElementById("futureArrow");
futureArrow.src = 'images/arrowClosed.gif';	
var futureLink = document.getElementById("futureLink");
futureLink.innerHTML = 'A Reliance on the Future';
var futureToggle = document.getElementById("futureToggle");
futureToggle.value = 'hidden';
var futureTable = document.getElementById("futureTable");
futureTable.style.display = 'none';
}

function showFuture()
{
//determine if it's shown
var futureToggle = document.getElementById("futureToggle");
if (futureToggle.value == "hidden")
{
	showFutureTable()
}
else
{
	hideFutureTable()
}

}

function showBuildItTable()
{
	
var buildItArrow = document.getElementById("buildItArrow");
buildItArrow.src = 'images/arrowOpen.gif';	
var buildItLink = document.getElementById("buildItLink");
buildItLink.innerHTML = "Let's Build It";
var buildItToggle = document.getElementById("buildItToggle");
buildItToggle.value = 'shown'
var buildItTable = document.getElementById("buildItTable");
buildItTable.style.display = 'block';
}

function hideBuildItTable()
{
	//alert("in hideBuildItTable() ");
var buildItArrow = document.getElementById("buildItArrow");
buildItArrow.src = 'images/arrowClosed.gif';	
var buildItLink = document.getElementById("buildItLink");
buildItLink.innerHTML = "Let's Build It";
var buildItToggle = document.getElementById("buildItToggle");
buildItToggle.value = 'hidden';
var buildItTable = document.getElementById("buildItTable");
buildItTable.style.display = 'none';
}

function showBuildIt()
{
//determine if it's shown
var buildItToggle = document.getElementById("buildItToggle");
if (buildItToggle.value == "hidden")
{
	showBuildItTable()
}
else
{
	hideBuildItTable()
}

}

function resetLinksToBlack()
{
	
	
var whatIsLink = document.getElementById("whatIsLink");
whatIsLink.style.color = '#000000';
var howWorkLink = document.getElementById("howWorkLink");
howWorkLink.style.color = '#000000';
var theBusinessLink = document.getElementById("theBusinessLink");
theBusinessLink.style.color = '#000000';
var futureLink = document.getElementById("futureLink");
futureLink.style.color = '#000000';
var buildItLink = document.getElementById("buildItLink");
buildItLink.style.color = '#000000';
var helpOutLink = document.getElementById("helpOutLink");
helpOutLink.style.color = '#000000';
var evidenceLink = document.getElementById("evidenceLink");
evidenceLink.style.color = '#000000';
var faqLink = document.getElementById("faqLink");
faqLink.style.color = '#000000';


}