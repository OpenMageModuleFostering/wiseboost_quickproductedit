function wbtopbarAjaxPost(ajaxCfgObj)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.open("POST", ajaxCfgObj.url, true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

	xmlhttp.setRequestHeader("Content-length", ajaxCfgObj.params.length);
	xmlhttp.setRequestHeader("Connection", "close");

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200) {
				if (xmlhttp.responseText != '') {
					ajaxCfgObj.onRx(xmlhttp.responseText);
				} else {
					if (ajaxCfgObj.onError)
					{
						ajaxCfgObj.onError('Response is empty');
					}
				}
			} else {
				if (ajaxCfgObj.onError)
				{
					var msg = "Error - " + String(xmlhttp.status) + ' ' + xmlhttp.statusText;
					
					if (xmlhttp.status == 0)
					{ 
						msg = "Error - " + String(xmlhttp.status) + ' - ' + "Server connection failed.";
						setTimeout(function() { ajaxCfgObj.onError(msg); }, 20000); 
						return ;
					}
					else
					{
						if (xmlhttp.statusText == "Unknown")
						{ 
							msg = "Error - " + String(xmlhttp.status);
						}
					}
					
					ajaxCfgObj.onError(msg);
				}
			}
		}
	}

	xmlhttp.send(ajaxCfgObj.params);
}

function wbtopbarRx(data)
{
	wbtopbarRender(data);
}

function wbtopbarRender(html)
{
	if (html != "")
	{
		var body_el = document.getElementsByTagName("body")[0];
		
		if (body_el) 
		{
			var div_el = document.createElement('div');
			div_el.innerHTML = html;
			body_el.appendChild(div_el);
			body_el.style.paddingTop = '28px';
		}
	}
}

function wbtopbarInit(indexbaseurl, cleanbaseurl, params) 
{
	wbtopbarAjaxPost({url: indexbaseurl + "/topbar_check/check", onRx: wbtopbarRx, params: params});
}