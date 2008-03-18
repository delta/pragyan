function JSTimer(timerObj, hours, mins, secs) {
	this.init(timerObj, hours, mins, secs);
}

JSTimer.prototype = {
	init:function(timerObj, hours, mins, secs) {
		this.hours = hours;
		this.mins = mins;
		this.secs = secs - 1;
		this.timerObj = timerObj;

		var self = this;
		this.intervalId = setInterval(function() { self.incrementTimer(); }, 1000);
	},

	setStopTime:function(hours, mins, secs) {
		this.stopHours = hours;
		this.stopMins = mins;
		this.stopSecs = secs;

		this.checkTimerExpired();
	},

	incrementTimer:function() {
		this.secs++;

		if(this.secs >= 60) {
			this.mins++;
			this.secs = 0;
		}
		if(this.mins >= 60) {
			this.hours++;
			this.mins = 0;
		}

		hours = this.hours < 10 ? '0' + this.hours : this.hours;
		mins = this.mins < 10 ? '0' + this.mins : this.mins;
		secs = this.secs < 10 ? '0' + this.secs : this.secs;

		this.timerObj.innerHTML = hours + ':' + mins + ':' + secs;
		this.checkTimerExpired();
	},

	checkTimerExpired:function() {
		if(typeof this.stopHours != 'undefined' && typeof this.onStopTimeReached != 'undefined') {
			if(this.hours >= this.stopHours) {
				if(this.hours > this.stopHours) {
					clearInterval(this.intervalId);
					this.onStopTimeReached();
				}
				else if(this.mins >= this.stopMins) {
					if(this.mins > this.stopMins) {
						clearInterval(this.intervalId);
						this.onStopTimeReached();
					}
					else if(this.secs >= this.stopSecs) {
						clearInterval(this.intervalId);
						this.onStopTimeReached();
					}
				}
			}
		}
	},

	stopTimer:function() {
		clearInterval(this.intervalId);
		alert('Timer stopped');
	}
}

function getStyle( elem, styleName ) {
	if (elem.style[styleName])
		return elem.style[styleName];
	else if (elem.currentStyle)
		return elem.currentStyle[styleName];
	else if (document.defaultView && document.defaultView.getComputedStyle) {
		styleName = styleName.replace(/([A-Z])/g,"-$1");
		styleName = styleName.toLowerCase();
		var s = document.defaultView.getComputedStyle(elem,"");
		return s && s.getPropertyValue(styleName);
	}
	else
		return null;
}

function getHeight( elem ) {
    return parseInt( getStyle( elem, 'height' ) );
}

function getWidth( elem ) {
    return parseInt( getStyle( elem, 'width' ) );
}

var submittedOnce = false;

function forceSubmit() {
	if(submittedOnce == false) {
		submittedOnce = true;
		document.getElementsByName('quizform')[0].submit();
	}
}

function showForceSubmitDiv() {
	var documentBody = document.getElementsByTagName('body')[0];
	alert('Time\'s Up!\nClick the Ok button to submit your answers.\nLate submissions will not be considered for evaluation.');
	forceSubmit();
	return;
}
