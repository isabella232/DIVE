/*
AjaxLog, logging Javascript events
 */

function AjaxLog(){};

AjaxLog.basePath = 'ajaxlog/';
AjaxLog.Levels = ['info','error','security','debug'];
AjaxLog.debug = true;
AjaxLog.logs = [];

AjaxLog.Log = function(action, details, referer, level){
	if (typeof(level) == 'undefined'){
		level = info;
	}
	if (typeof(referer) == 'undefined'){
		referer = window.location.url;
	}
	if (action && details){
		$.post(AjaxLog.basePath + level, { 'action':action, 'details': details, 'referer':referer});
		if (AjaxLog.debug){
			AjaxLog.logs.push({
					'action': action,
					'details': details,
					'referer':referer,
					'level': level
				});

		}
	} else{
		console.log('action or details not set');
	}
}

AjaxLog.info = function(action,details,referer){
	AjaxLog.Log(action,details,referer,'info');
}

AjaxLog.error = function(action,details,referer){
	AjaxLog.Log(action,details,referer,'error');
}

AjaxLog.security = function(action,details,referer){
	AjaxLog.Log(action,details,referer,'security');
}

AjaxLog.debug = function(action,details,referer){
	AjaxLog.Log(action,details,referer,'debug');
}