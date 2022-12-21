module.subRequest = function(requestParams)
{
	if (requestParams === undefined || requestParams === null) {
		return;
	}

	const url = requestParams.url;
	const params = requestParams.params;
	const callback = requestParams.callback;
	const callbackParams = requestParams.callbackParams;
	const showAlert = requestParams.showAlert;
	let showLoading = requestParams.showLoading;

	if(showLoading === undefined)
		showLoading = true;

	$.ajax({
		'url' : url,
		'async' : true,
		'type' : 'POST',
		'data' : params,
		'dataType': 'json',
		'timeout' : 600000,
		'beforeSend' : function(jqXHR)
		{
			//if (showLoading && loadingWindow !== null && loadingWindow !== undefined) {
			//	loadingWindow.show();
			//}
		},
		"success" : function(jqXHR)
		{
			if (jqXHR === null || jqXHR === undefined) {
				if (showAlert != false) {
					alert(ErrReceiveData);
				}
				return;
			}

			if (jqXHR.result === false) {
				if (showAlert != false) {
					alert(jqXHR.msg);
				}

				return;
			}

			if (callback instanceof Function) {
				callback.call(self, jqXHR.data, callbackParams);
			}
		},
		"error": function(data, status, err) {
			if (showAlert != false && data.status != 0) {
				alert(ErrReceiveData);
			}
		},
		"complete": function(jqXHR) {
			//if(showLoading && loadingWindow !== null && loadingWindow !== undefined) {
			//	loadingWindow.hide();
			//}
		}
	});
};
