const timeDefault    = "0000-00-00 00:00";
const fineDustLabel  = ['좋음', '보통', '나쁨', '매우나쁨'];
const fineDustLevel  = [30, 80, 150, 151];
const ultraDustLevel = [15, 35, 75, 76];
const colorClasses   = ['dust_blue', 'dust_green', 'dust_orange', 'dust_red'];
const fineDustColors = ['#80a4c3', '#7fc694', '#fab88c', '#ffa8a8'];
const defaultColor   = '#32a1ff';
const fineDustMax    = 151;

function createFinedustController(params) {
	var url             = params.url;
	var $dustTime       = params.$dustTime;
	var $dustPm10Now    = params.$dustPm10Now;
	var $dustPm10Day    = params.$dustPm10Day; //기상청 현재 미세먼지 데이터로 변경
	var $dustPm25       = params.$dustPm25;
	var $dustStatus     = params.$dustStatus;
	var $dustBar        = params.$dustBar;
	var $dustNowColor   = params.$dustNowColor;
	var $dustDayColor   = params.$dustDayColor;
	var $ultraFineColor = params.$ultraFineColor;

	var controller = {
		_url             : url,
		_$dustTime       : $dustTime,
		_$dustPm10Now    : $dustPm10Now,
		_$dustPm10Day    : $dustPm10Day,
		_$dustPm25       : $dustPm25,
		_$dustStatus     : $dustStatus,
		_$dustBar        : $dustBar,
		_$dustNowColor   : $dustNowColor,
		_$dustDayColor   : $dustDayColor,
		_$ultraFineColor : $ultraFineColor,
		init: function() {
			this.clear.call(this);
			this.update.call(this);
			this.setColor.call(this, 0, 0, 0);
		},
		clear: function() {
			this._$dustTime.text(timeDefault);
			this._$dustPm10Now.text('0');
			this._$dustPm10Day.text('0');
			this._$dustPm25.text('0');
			this._$dustStatus.text('');

			this._$dustBar.css("width", "0%");
			this._$dustBar.css("background", defaultColor);
		},
		setColor: function(level, station, ultra) {
			this._$dustNowColor.removeClass();
			this._$dustDayColor.removeClass();
			this._$ultraFineColor.removeClass();

			var className = colorClasses[level] 
			
			this._$dustNowColor.addClass(className);

			var className = colorClasses[station] 
			this._$dustDayColor.addClass(className);

			var className = colorClasses[ultra] 

			this._$ultraFineColor.addClass(className);
		},
		_request: function(requestOption, params, callback) {
			let self = this;

			$.ajax({
				"url"		 : url + requestOption,
				"async"      : true, 									
				"type"       : 'POST', 					    		
				"data"       : params, 								
				"dataType"   : 'json',
				"beforeSend" : function(jqXHR) {
					//self._loadingWindow.show();
				}, 					
				"success"    : function(jqXHR) {
					if(callback instanceof Function) {
						callback.call(self, jqXHR);
					}
				}, 										
				"error"		 : function(data, status, err) {
					alert('데이터 수신에 실패하였습니다');
				}, 		
				"complete"   : function(jqXHR) {
					//self._loadingWindow.hide();
				} 				
			});
		},
		update: function() {
			const requestOption = "";

			var requestParams = [
			//	{name: "start", value: start},
			];

			var callback = controller.updateCallback;
			controller._request.call(controller, requestOption, requestParams, callback);
		},
		getTime() {
			var d = new Date();
			return d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + " " + 
			("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
		},
		updateCallback: function(data) {
			if(data === null || data === undefined)
				return;

			if(data.data === null || data.data === undefined)
				return;

			if(data.data.PM10 === undefined)
				return;
			
			if(data.datagov === undefined || data.datagov.PM10 === undefined)
				return;

			this._$dustTime.text(this.getTime());
			this._$dustPm10Now.text(data.data.PM10);
			this._$dustPm10Day.text(data.datagov.PM10);
			this._$dustPm25.text(data.data.PM25);

			var temp       = this.getFinedustLabelColors(data.data.PM25, true);
			var label      = temp.label;
			var ultraIndex = temp.index;

			this._$dustStatus.text(label);

			var temp       = this.getFinedustLabelColors(data.data.PM10);
			var color      = temp.color;
			var index      = temp.index;

			var proportion = this.getProportion.call(this, data.data.PM10, color, index);

			var temp       = this.getFinedustLabelColors(data.datagov.PM10);
			var station    = temp.index;

			this.updateBar.call(this, proportion, color);
			this.setColor.call(this, index, station, ultraIndex);
		},
		getProportion(data, color, index) {
			var percentWeight = 25 * index;
			var divider       = fineDustLevel[index];

			index = index - 1;

			var dataWeight = 0;

			if(index >= 0)
				dataWeight = fineDustLevel[index];

			var temp = divider - dataWeight;
			temp = temp == 0 ? 1 : temp;
			
			var	proportion = (data - dataWeight) / temp * 100;
			proportion     = percentWeight + proportion * 25 / 100;
			proportion     = parseInt(proportion);
			
			if(isNaN(proportion)) {
				proportion = 0;
			}

			if(proportion < 0)
				proportion = 0;

			if(proportion > 100)
				proportion = 100;


			return proportion;
		},
		updateBar: function(proportion, color) {
			this._$dustBar.css("width", proportion + "%");
			this._$dustBar.css("background", color);
		},
		getFinedustLabelColors(dust, isultra = false) {
			var level = -1;
			var levelArr = fineDustLevel;

			if(isultra == true)
				levelArr = ultraDustLevel;

			var len = levelArr.length;

			if(isultra == true)
				levelArr = ultraDustLevel;

			for(var i = 0; i < len; i++) {
				if(dust <= levelArr[i]) {
					level = i;
					break;
				}
			}

			len = fineDustLabel.length;

			if(level <= -1 || level > len - 1)
				level = len - 1;

			return {
				index: level,
				label:fineDustLabel[level],
				color:fineDustColors[level]
			};
		},
	};

	return controller;
}
