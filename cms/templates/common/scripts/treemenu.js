var persisteduls = new Object();

function JSTreeMenu(treeid, inputBoxId, imageDirectory, enablepersist, persistdays) {
	this.imageDirectory = imageDirectory;
	this.createTree(treeid, inputBoxId, enablepersist, persistdays);
}

JSTreeMenu.prototype = {
	createTree: function(treeid, inputBoxId, enablepersist, persistdays){
		this.treeId = treeid;
		this.enablePersist = enablepersist;
		this.persistDays = persistdays;
		this.inputBoxId = inputBoxId;
		this.closefolder = this.imageDirectory + "/ddtree_closed.gif";
		this.openfolder = this.imageDirectory + "/ddtree_open.gif";
		this.prevSelected = null;

		var ultags = document.getElementById(this.treeId).getElementsByTagName("ul");
		if (typeof persisteduls[this.treeId]=="undefined")
			persisteduls[this.treeId]=(enablepersist==true && this.getCookie(this.treeId)!="")? this.getCookie(this.treeId).split(",") : ""
		for (var i = 0; i < ultags.length; i++)
			this.buildSubTree(this.treeId, ultags[i], i);

		var self = this;
		var spanTags = document.getElementById(this.treeId).getElementsByTagName('span');
		for(var i = 0; i < spanTags.length; i++) {
			spanTags[i].onclick =
						function(e) {
							if(this.className != 'ddtree_inaccessible' && this.className != 'ddtree_selected') {
								if(self.inputBoxId != '')
									document.getElementById(self.inputBoxId).value = this.title;
								this.className = 'ddtree_selected';
								if(self.prevSelected) {
									self.prevSelected.className = 'ddtree_accessible';
								}
								self.prevSelected = this;
							}

							if(typeof e!="undefined") {
								e.stopPropagation();
							}
							else {
								event.cancelBubble = true;
							}
						};
		}
		var hrefTags = document.getElementById(this.treeId).getElementsByTagName('a');
		for(var i = 0; i < hrefTags.length; i++) {
			hrefTags[i].onclick = function(e) {
				if(typeof e!="undefined") {
					e.stopPropagation();
				}
				else {
					event.cancelBubble = true;
				}
			};
		}
		if (this.enablePersist == true) { //if enable persist feature
			var durationdays=(typeof this.persistDays == "undefined")? 1 : parseInt(this.persistDays);
			this.dotask(window, function() { self.rememberstate(self.treeId, durationdays) }, "unload"); //save opened UL indexes on body unload
		}
	},

	buildSubTree: function(treeid, ulelement, index){
		ulelement.parentNode.className="submenu";

		if (typeof persisteduls[treeid] == "object") { //if cookie exists (persisteduls[treeid] is an array versus "" string)
			if (this.searcharray(persisteduls[treeid], index)){
				ulelement.setAttribute("rel", "open");
				ulelement.style.display = "block";
				ulelement.parentNode.style.backgroundImage = "url(" + this.openfolder + ")";
			}
			else
				ulelement.setAttribute("rel", "closed");
		} //end cookie persist code
		else if (ulelement.getAttribute("rel")==null || ulelement.getAttribute("rel")==false) //if no cookie and UL has NO rel attribute explicted added by user
			ulelement.setAttribute("rel", "closed")
		else if (ulelement.getAttribute("rel")=="open") //else if no cookie and this UL has an explicit rel value of "open"
			this.expandSubTree(ulelement) //expand this UL plus all parent ULs (so the most inner UL is revealed!)

		var self = this;
		ulelement.parentNode.onclick=function(e){
			var submenu=this.getElementsByTagName("ul")[0];

			if (submenu.getAttribute("rel") == "closed") {
				submenu.style.display = "block";
				submenu.setAttribute("rel", "open");
				ulelement.parentNode.style.backgroundImage="url(" + self.openfolder + ")";
			}
			else if (submenu.getAttribute("rel")=="open"){
				submenu.style.display = "none";
				submenu.setAttribute("rel", "closed");
				ulelement.parentNode.style.backgroundImage = "url(" + self.closefolder + ")";
			}

			self.preventpropagate(e);
		}

		ulelement.onclick=function(e){
			self.preventpropagate(e)
		}
	},

	expandSubTree: function(ulelement){ //expand a UL element and any of its parent ULs
		var rootnode = document.getElementById(this.treeId);
		var currentnode = ulelement;
		currentnode.style.display = "block";
		currentnode.parentNode.style.backgroundImage = "url(" + this.openfolder + ")";

		while (currentnode!=rootnode){
			if (currentnode.tagName == "UL"){ //if parent node is a UL, expand it too
				currentnode.style.display = "block";
				currentnode.setAttribute("rel", "open"); //indicate it's open
				currentnode.parentNode.style.backgroundImage="url(" + this.openfolder + ")"
			}
			currentnode = currentnode.parentNode
		}
	},

	flatten: function(action){ //expand or contract all UL elements
		var ultags = document.getElementById(this.treeId).getElementsByTagName("ul");
		for (var i = 0; i < ultags.length; i++) {
			ultags[i].style.display = (action == "expand") ? "block" : "none";
			var relvalue = (action == "expand") ? "open" : "closed";
			ultags[i].setAttribute("rel", relvalue);
			ultags[i].parentNode.style.backgroundImage = (action=="expand") ? "url(" + this.openfolder + ")" : "url(" + this.closefolder + ")";
		}
	},

	rememberstate: function(durationdays){ //store index of opened ULs relative to other ULs in Tree into cookie
		var ultags = document.getElementById(this.treeId).getElementsByTagName("ul");
		var openuls = new Array();

		for (var i = 0; i < ultags.length; i++){
			if (ultags[i].getAttribute("rel")=="open")
				openuls[openuls.length] = i; //save the index of the opened UL (relative to the entire list of ULs) as an array element
		}
		if (openuls.length == 0) //if there are no opened ULs to save/persist
			openuls[0]="none open"; //set array value to string to simply indicate all ULs should persist with state being closed
		this.setCookie(this.treeId, openuls.join(","), durationdays); //populate cookie with value treeid=1,2,3 etc (where 1,2... are the indexes of the opened ULs)
	},

////A few utility functions below//////////////////////

	getCookie: function(Name) { //get cookie value
		var re = new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
		if (document.cookie.match(re)) //if cookie found
			return document.cookie.match(re)[0].split("=")[1]; //return its value
		return "";
	},

	setCookie: function(Name, value, days) { //set cookei value
		var expireDate = new Date();
		//set "expstring" to either future or past date, to set or delete cookie, respectively
		var expstring=expireDate.setDate(expireDate.getDate()+parseInt(days));
		document.cookie = Name + "=" + value + "; expires="+expireDate.toGMTString()+"; path=/";
	},

	searcharray: function(thearray, value) { //searches an array for the entered value. If found, delete value from array
		var isfound = false;
		for (var i = 0; i < thearray.length; i++) {
			if (thearray[i] == value) {
				isfound = true;
				thearray.shift(); //delete this element from array for efficiency sake
				break;
			}
		}
		return isfound;
	},

	preventpropagate: function(e) { //prevent action from bubbling upwards
		if (typeof e != "undefined")
			e.stopPropagation()
		else
			event.cancelBubble=true
	},

	dotask: function(target, functionref, tasktype) { //assign a function to execute to an event handler (ie: onunload)
		var tasktype=(window.addEventListener)? tasktype : "on"+tasktype
		if (target.addEventListener)
			target.addEventListener(tasktype, functionref, false)
		else if (target.attachEvent)
			target.attachEvent(tasktype, functionref)
	}
}