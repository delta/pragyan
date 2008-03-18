function JSTimer(containerId, hr, min, sec) {
	this.containerId = containerId;
	this.container = document.getElementById(containerId);
	this.spanObjectId = containerId + '_jsTimer';
	var spanObj = document.createElement('span');
	spanObj.id = this.spanObjectId;
	this.container.appendChild(spanObj);

	this.hour = hr;
	this.minute = min;
	this.second = sec;
	this.timerId = 0;

	this.tickHandlers = new Array();

	this.init();
}

JSTimer.prototype = {
	init: function() {
		var self = this;
		this.timerId = setInterval(function() { self.tick(); }, 1000);
	},

	tick: function() {
		if (++this.second == 60) {
			this.second = 0;
			if (++this.minute == 60) {
				++this.hour;
				this.minute = 0;
			}
		}

		var curTime = this.format(this.hour, this.minute, this.second);
		if (this.tickHandlers[curTime] != null)
			this.tickHandlers[curTime](this.hour, this.minute, this.second);

		document.getElementById(this.spanObjectId).innerHTML = curTime;
	},

	stop: function() {
		clearInterval(this.timerId);
		this.timerId = 0;
	},

	format: function(h, m, s) {
		return (h < 10 ? '0' + h : h) + ':' + (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
	},

	addTickHandler: function(hr, min, sec, func) {
		this.tickHandlers[this.format(hr, min, sec)] = func;
	}
};