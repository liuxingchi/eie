var hintOn = 0;
var effectOn = 0;
var hintTimeoutId = 0;
var URL_ROOT_PATH = '/';

function ajaxPost(url, json, success_func, error_func)
{
	var absUrl = url;
	if (url.substr(0,1)!='/')
		absUrl = URL_ROOT_PATH + url;
	
	$.ajax({
		type:"POST",
		url:absUrl,
		dataType:"json",
		data:$.toJSON(json),
		timeout:5000,
		cache:true,
		async:true,
		success: function (data, textStatus) {
			success_func(data, textStatus);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			error_func(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}


function normPost(url, data, success_func, error_func)
{
	var absUrl = url;
	if (url.substr(0,1)!='/')
		absUrl = URL_ROOT_PATH + url;
		
	$.ajax({
		type:"POST",
		url:absUrl,
		data:data,
		timeout:5000,
		cache:true,
		async:true,
		success: function (data, textStatus) {
			success_func(data, textStatus);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			error_func(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function showHint(obj, cls, msg, error)
{
	var color = error==1?"#FF0000":"#A2A2A2";

	obj.css("color", color);
	obj.empty();
	obj.append(msg);
	if (hintOn==0){
		hintOn = 1;
		obj.addClass(cls);
	}
	if (error==1 && effectOn==0){
		effectOn = 1;
		obj.effect("pulsate", {distance:5, times:3}, 1000, function(){effectOn = 0;});
	}

	clearTimeout(hintTimeoutId);
	hintTimeoutId = setTimeout(function(){obj.hide("blind", {}, 1000, function(){obj.removeClass(cls); obj.empty(); obj.show(); hintOn = 0;});}, 5000);
}

function trim(v)
{
     return v.replace(/(^\s*)(\s*$)/g, '');
}

function isEmpty(v){
	switch (typeof v){
		case 'undefined':
			return true;
		case 'string':
			if (trim(v).length == 0)
				return true;
			break;
		case 'boolean':
			if(!v)
				return true;
			break;
		case 'number':
			if (0 === v)
				return true;
			break;
		case 'object' :
			if (null === v)
				return true;
			if (undefined !== v.length && v.length==0)
				return true;
			for (var k in v){
				return false;
			}
			return true;
			break;
	}
	return false;
}

function getCurrentTimestamp()
{
	return Math.round(new Date().getTime()/1000);
}

function getTimestamp(year, month, day, hour, minute, second)
{
	return Math.round(new Date(Date.UTC(year, month - 1, day, hour, minute, second)).getTime()/1000);
}

function getTimeString(timestamp)
{
	var dt = new Date(timestamp);
	var y = dt.getFullYear();
	var m = dt.getMonth() + 1;
	var d = dt.getDate();
	
	return m+'/'+d+'/'+y;
}

function getTime()
{
	var dt = new Date();
	var y = dt.getFullYear();
	var m = pad(dt.getMonth() + 1);
	var d = pad(dt.getDate());
	var h = pad(dt.getHours());
	var mi = pad(dt.getMinutes());
	var s = pad(dt.getSeconds());
	
	return y+'-'+m+'-'+d+' '+h+':'+mi+':'+s;
}

function pad(str)
{
	var s = new String(str);
	if (s.length<2)
		return '0'+s;
	return s;
}
function print_array(arr){
	for(var key in arr){
		if(typeof(arr[key])=='array'||typeof(arr[key])=='object'){//递归调用  
			print_array(arr[key]);
		}else{
			document.write(key + ' = ' + arr[key] + '<br>');
		}
	}
}
function writeObj(obj){
	var description = "";
	for(var i in obj){  
		var property=obj[i];  
		description+=i+" = "+property+"\n"; 
	}  
	alert(description);
}

function getLocalTime(nS) {     
       return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");      
    }
function getDateToUnix(string) {
                var f = string.split(' ', 2);
                var d = (f[0] ? f[0] : '').split('-', 3);
                var t = (f[1] ? f[1] : '').split(':', 3);
                return (new Date(
                        parseInt(d[0], 10) || null,
                        (parseInt(d[1], 10) || 1) - 1,
                        parseInt(d[2], 10) || null,
                        parseInt(t[0], 10) || null,
                        parseInt(t[1], 10) || null,
                        parseInt(t[2], 10) || null
                        )).getTime() / 1000;
            }
function makeArrayToJsonString(array){
	
	var jsonstr;
	jsonstr="{";
	for(i in array){
		jsonstr += "\"" + i + "\""+ ":" + "\"" + array[i] + "\",";
	}
	jsonstr = jsonstr.substring(0,jsonstr.lastIndexOf(','));
	jsonstr += "}";
	//var t = eval(jsonstr);
	//alert(t[0].www);
	return jsonstr;
	//alert(return (jsonstr));
	}

