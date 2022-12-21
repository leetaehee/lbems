const dayTable = ['일', '월', '화', '수', '목', '금', '토'];

module.date = function($date, $day, $time) {
	var control = {
		_$date : $date,
		_$day  : $day,
		_$time : $time,
		_id    : null,
		_date  : "",
		_day   : "",
		_time  : "",
		_getDate: function() {
			var d = new Date();
			return d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
		},
		_getTime: function() {
			var d = new Date();
			return ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2) + ":" + ("0" + d.getSeconds()).slice(-2);
		},
		_getDay: function() {
			var today = new Date().getDay();
			var todayLabel = dayTable[today];

			return todayLabel;
		},
		_getAmPm: function() {
			var dt = new Date();
			var h =  dt.getHours(), m = dt.getMinutes();
			return (h > 12) ? 'PM' : 'AM';
		},
		_updateDate: function(d) {
			let self = control;

			self._date = d;
			self._$date.text(d);
		},
		_updateDay: function(d) {
			let self = control;

			self._day = d;
			self._$day.text(d);
		},
		_updateTime: function(d) {
			let self = control;

			self._time = d;
			self._$time.text(d);
		},
		_update: function() {
			let self = control;

			var date = self._getDate();
			var time = self._getTime();
			var day  = self._getDay();

			if(date != self._date) {
				self._updateDate(date);
			}

			if(day != self._day) {
				self._updateDay(day);
			}

			if(time != self._time) {
				self._updateTime(time);
			}
		},
		start: function() {
			let self = control;

			if(self._id != null)
				return;

			self._id = setInterval(function() {
				self._update.call(self);
			}, 1000);
		},
		stop: function() {
			let self = control;

			if(self._id == null)
				return;

			clearInterval(self._id);
		}
	}

	return control;
}
