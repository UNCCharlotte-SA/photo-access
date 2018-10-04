	window.alert = function(txt,ALERT_TITLE,ALERT_BUTTON_TEXT) {
		createCustomAlert(txt,ALERT_TITLE,ALERT_BUTTON_TEXT);
	}
				
	function createCustomAlert(txt,ALERT_TITLE,ALERT_BUTTON_TEXT) {
		d = document;

		if(d.getElementById("warningContainer")) return;

		mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
		mObj.id = "warningContainer";
		mObj.style.height = d.documentElement.scrollHeight + "px";

		alertObj = mObj.appendChild(d.createElement("div"));
		alertObj.id = "warningAlertBox";
		if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
			
		alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";
		alertObj.style.visiblity="visible";

		h1 = alertObj.appendChild(d.createElement("h1"));
		h1.appendChild(d.createTextNode(ALERT_TITLE));

		msg = alertObj.appendChild(d.createElement("p"));
		msg.innerHTML = txt;

		btn = alertObj.appendChild(d.createElement("a"));
		btn.id = "closeBtn";
		btn.appendChild(d.createTextNode(ALERT_BUTTON_TEXT));
		btn.href = "#";
		btn.focus();
		btn.onclick = function() { removeCustomAlert();return false; }

		alertObj.style.display = "block";
	}

	function removeCustomAlert() {
		document.getElementsByTagName("body")[0].removeChild(document.getElementById("warningContainer"));
	}
