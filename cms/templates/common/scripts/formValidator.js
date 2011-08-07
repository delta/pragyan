/* ************************************************************************************* *\
 * The MIT License
 * Copyright (c) 2007 Fabio Zendhi Nagao - http://zend.lojcomm.com.br
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
\* ************************************************************************************* */

var fValidator = new Class({
	options: {
		msgContainerTag: "span",
		msgClass: "fValidator-msg",

		styleNeutral: { "border-color": "#f95"},
		styleInvalid: { "border-color": "#f22","border-width":"2px"},
		styleValid: { "border-color": "#0c0"},
		//styleNeutral: {"border": "1px solid #777777"},
		//styleInvalid: {"border": "1px solid #ff0000"},
		//styleValid: {"border": "1px solid #00ff00"},
		required: {type: "required", re: /[^.*]/, msg: "This is a required field."},
		alpha: {type: "alpha", re: /^[a-z ._-]+$/i, msg: "This field accepts alphabetic characters only."},
		name2: {type: "name2", re: /^[a-z ._-]+$/i, msg: "This field accepts alphabetic characters only."},
		name3: {type: "name3", re: /^[a-z ._-]+$/i, msg: "This field accepts alphabetic characters only."},
		name4: {type: "name4", re: /^[a-z ._-]+$/i, msg: "This field accepts alphabetic characters only."},
		name5: {type: "name5", re: /^[a-z ._-]+$/i, msg: "This field accepts alphabetic characters only."},

		alphanum: {type: "alphanum", re: /^[a-z0-9 ._-]+$/i, msg: "This field accepts alphanumeric characters only."},
		integer: {type: "integer", re: /^[-+]?\d+$/, msg: "Please enter a valid integer."},
		real: {type: "real", re: /^[-+]?\d*\.?\d+$/, msg: "Please enter a valid number."},
		date: {type: "date", re: /^((((0[13578])|([13578])|(1[02]))[\/](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[\/](([1-9])|([0-2][0-9])|(30)))|((2|02)[\/](([1-9])|([0-2][0-9]))))[\/]\d{4}$|^\d{4}$/, msg: "Please enter a valid date (mm/dd/yyyy)."},
		email1: {type: "email1", re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i, msg: "Please enter a valid email."},
		email2: {type: "email2", re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i, msg: "Please enter a valid email."},
		email3: {type: "email3", re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i, msg: "Please enter a valid email."},
		email4: {type: "email4", re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i, msg: "Please enter a valid email."},
		email5: {type: "email5", re: /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i, msg: "Please enter a valid email."},
		phone: {type: "phone", re: /^[\d\s ().-]+$/, msg: "Please enter a valid phone."},
		num: {type: "num", re: /^[\d\s ().-]*$/, msg: "Please enter a numeric value."},
		url: {type: "url", re: /^(http|https|ftp)\:\/\/[a-z0-9\-\.]+\.[a-z]{2,3}(:[a-z0-9]*)?\/?([a-z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/i, msg: "Please enter a valid url."},
		confirm: {type: "confirm", msg: "Confirm Password does not match original Password."},

		onValid: Class.empty,
		onInvalid: Class.empty
	},

	initialize: function(form, options) {
		this.form = $(form);
		this.setOptions(options);

		this.fields = this.form.getElements("*[class^=fValidate]");
		this.validations = [];

		this.fields.each(function(element) {
			if(!this._isChildType(element)) element.setStyles(this.options.styleNeutral);
			element.cbErr = 0;
			var classes = element.getProperty("class").split(' ');
			classes.each(function(klass) {
				if(klass.match(/^fValidate(\[.+\])$/)) {
					var aFilters = eval(klass.match(/^fValidate(\[.+\])$/)[1]);
					for(var i = 0; i < aFilters.length; i++) {
						if(this.options[aFilters[i]]) this.register(element, this.options[aFilters[i]]);
						if(aFilters[i].charAt(0) == '=') this.register(element, $extend(this.options.confirm, {idField: aFilters[i].substr(1)}));
					}
				}
			}.bind(this));
		}.bind(this));

		this.form.addEvents({
			"submit": this._onSubmit.bind(this),
			"reset": this._onReset.bind(this)
		});
	},

	register: function(field, options) {
		field = $(field);
		this.validations.push([field, options]);
		field.addEvent("blur", function() {
			this._validate(field, options);
		}.bind(this));
	},

	_isChildType: function(el) {
		var elType = el.type.toLowerCase();
		if((elType == "radio") || (elType == "checkbox")) return true;
		return false;
	},

	_validate: function(field, options) {
		switch(options.type) {
			case "confirm":
				if($(options.idField).getValue() == field.getValue()) this._msgRemove(field, options);
				else this._msgInject(field, options);
				break;
			default:
				if(options.re.test(field.getValue())) this._msgRemove(field, options);
				else this._msgInject(field, options);
		}
	},

	_validateChild: function(child, options) {
		var nlButtonGroup = this.form[child.getProperty("name")];
		var cbCheckeds = 0;
		var isValid = true;
 		for(var i = 0; i < nlButtonGroup.length; i++) {
			if(nlButtonGroup[i].checked) {
				cbCheckeds++;
				if(!options.re.test(nlButtonGroup[i].getValue())) {
					isValid = false;
					break;
				}
			}
		}
		if(cbCheckeds == 0 && options.type == "required") isValid = false;
		if(isValid) this._msgRemove(child, options);
		else this._msgInject(child, options);
	},

	_msgInject: function(owner, options) {
		if(!$(owner.getProperty("id") + options.type +"_msg")) {
			var msgContainer = new Element(this.options.msgContainerTag, {"id": owner.getProperty("id") + options.type +"_msg", "class": this.options.msgClass})
				.setHTML(options.msg)
				.setStyle("opacity", 0)
				.injectAfter(owner)
				.effect("opacity", {
					duration: 500,
					transition: Fx.Transitions.linear
				}).start(0, 1);
			owner.cbErr++;
			this._chkStatus(owner, options);
		}
	},

	_msgRemove: function(owner, options, isReset) {
		isReset = isReset || false;
		if($(owner.getProperty("id") + options.type +"_msg")) {
			var el = $(owner.getProperty("id") + options.type +"_msg");
			el.effect("opacity", {
				duration: 500,
				transition: Fx.Transitions.linear,
				onComplete: function() {el.remove()}
			}).start(1, 0);
			if(!isReset) {
				owner.cbErr--;
				this._chkStatus(owner, options);
			}
		}
	},

	_chkStatus: function(field, options) {
		if(field.cbErr == 0) {
			field.effects({duration: 500, transition: Fx.Transitions.linear}).start(this.options.styleValid);
			this.fireEvent("onValid", [field, options], 50);
		} else {
			field.effects({duration: 500, transition: Fx.Transitions.linear}).start(this.options.styleInvalid);
			this.fireEvent("onInvalid", [field, options], 50);
		}
	},

	_onSubmit: function(event) {
		event = new Event(event);
		var isValid = true;

		this.validations.each(function(array) {
			if(this._isChildType(array[0])) this._validateChild(array[0], array[1]);
			else this._validate(array[0], array[1]);
			if(array[0].cbErr > 0) isValid = false;
		}.bind(this));

		if(!isValid) event.stop();
		return isValid;
	},

	_onReset: function() {
		this.validations.each(function(array) {
			if(!this._isChildType(array[0])) array[0].setStyles(this.options.styleNeutral);
			array[0].cbErr = 0;
			this._msgRemove(array[0], array[1], true);
		}.bind(this));
	}
});
fValidator.implement(new Events); // Implements addEvent(type, fn), fireEvent(type, [args], delay) and removeEvent(type, fn)
fValidator.implement(new Options);// Implements setOptions(defaults, options)
