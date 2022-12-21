module.cookie = function()
{
	let control = {
		setCookie: function(cName, cValue)
		{
			let expire = new Date();

			//var cDay = (10 * 365 * 24 * 60 * 60);
			expire.setMonth(expire.getMonth() + 1);
			//expire.setTime(expire.getTime() + (10 * 365 * 24 * 60 * 60));
			//expire.setDate(expire.getDate() + cDay);

			let cookies = cName + '=' + escape(cValue) + ";"  // 한글 깨짐을 막기위해 escape(cValue)를 합니다.

			if (expire != null) {
				cookies += 'expires=' + expire.toGMTString() + ';';
			}

			document.cookie = cookies + "path=/;";
		},
		removeCookie: function(cName, cValue)
		{
			let expire = new Date();
			
			expire.setDate(expire.getDate()-1);

			let cookis = cName + '=' + escape(cValue) + ';';

			if (expire != null) {
				cookis += 'expire=' + expire.toGMTString() + ';';
			}

			document.cooke = cookis;
		},
		getCookie: function(cName)
		{
			cName = cName + '=';

			let cookieData = document.cookie;
			let start = cookieData.indexOf(cName);
			let cValue = '';

			if (start != -1) {
				start += cName.length;

				let end = cookieData.indexOf(';', start);

				if (end == -1) {
					end = cookieData.length;
				}
				cValue = cookieData.substring(start, end);
			}

			return unescape(cValue);
		},
	}
	return control;
}