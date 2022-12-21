const updateSunRiseSetTime = 1000 * 60 * 60;
const updateTempHumiTime = 1000 * 60 * 60;
module.weather = {};

module.weather.riseset = function(url, plantPk, $labelRise, $labelSet)
{
	let control = {
		_plantPk: plantPk,
		_obj: 0,
		start: function()
		{
			let self = control;

			self.request();

			if(self._obj !== 0)
				return;

			self._obj = setInterval(function() {
				self.request();
				}
			, updateSunRiseSetTime);
		},
		request: function()
		{
			let self = control;
			let plantPk = self._plantPk;

			let params = [];

			params.push(
				{name: "requester", value: "weather"},
				{name: "request", value: "weather_sun_riseset"},
				{name: "params", value: JSON.stringify(plantPk)}
			);

			let callback = self.requestCallback;

			let requestParams = {
				url: url,
				params: params,
				callback: callback,
				callbackParams: [],
				showAlert: false,
				showLoading: false
			};

			module.request(requestParams);
		},
		requestCallback: function(data, params)
		{
			$labelRise.text(data.sunrise);
			$labelSet.text(data.sunset);
		}
	}

	return control;
}

module.weather.temphumi = function(url, plantPk, $labelTemp, $labelHumi)
{
	let control = {
		_plantPk: plantPk,
		_obj: 0,
		start: function()
		{
			let self = control;

			self.request();

			if(self._obj !== 0)
				return;

			self._obj = setInterval(function() {
				self.request();
				}
			, updateTempHumiTime);
		},
		request: function()
		{
			let self = control;
			let plantPk = self._plantPk;
			let params = [];

			params.push(
				{name: "requester", value: "weather"},
				{name: "request", value: "weather_temp_humi_cur"},
				{name: "params", value: JSON.stringify(plantPk)}
			);

			let callback = self.requestCallback;

			let requestParams = {
				url: url,
				params: params,
				callback: callback,
				callbackParams: [],
				showAlert: true,
				showLoading: false
			};

			module.request(requestParams);
		},
		requestCallback: function(data, params)
		{
			$labelTemp.text(data.temp);
			$labelHumi.text(data.humi);
		}
	}

	return control;
}