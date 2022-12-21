let control;

$(document).ready(function() {
	module.utility.initYearSelect($selectYear, gServiceStartYm);

	control = createControl();
	control.request();
});

function createControl()
{
	let control = {
		selectedMonthGrapth : MONTH_TYPE,
		selectedYear : $selectYear.val(),
		request: function() {
            let self = control;

			let params = [];
            let data = [];

			data.push(
				{name: "graph_type", value: self.selectedMonthGrapth},
				{name: "selected_year", value: self.selectedYear}
			);

			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
				{name: "params", value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestCallback,
				callbackParams: null,
				showAlert: true
			};
			
			module.request(requestParams);
		},
		requestCallback: function(data, params)
		{
            let self = control;
			self.data = data;

			if (self.selectedMonthGrapth != "") {
				// 일별 그래프 출력
				self.updateZeroEnergyDailyGraph();
				// 일별 그래프 검색 해제
				self.selectedMonthGrapth = "";
			} else {
				// 금년 에너지 자립률 및 등급 표시
				self.updateYearZeroEnergy();
				// 금월 에너지 자립률 및 등급 표시 
				self.updateMonthZeroEnergy();
				// 월별 그래프 출력
				self.updateZeroEnergyMonthGraph();
			}
		},
		updateYearZeroEnergy: function()
		{
			// 금년 에너지 자립률 및 등급 정보 변경

			let self = control;
			let yearData = self.data['year'];
			let indecatiorPercent = yearData['indecatior_percent'];

			// 등급별 색상 리턴
			let colors = self.getGradeColor(indecatiorPercent);

			// 등급별로 색상 변경 
			self.updateYearZeroEnergyColor(colors);
			// 금년 에너지 자립률 및 등급에 대해 프로그레스바 조정
			self.updateYearZeroEnergyProgressBar(indecatiorPercent);

			// 금년 등급 표기
			$dustfcDom.html(yearData['grade']);
			// 금년 자립률 표기
			$currentGrade.html(parseInt(indecatiorPercent) + ' %');
		},
		updateYearZeroEnergyColor: function(colors)
		{
			// 등급 컬러 변경

			let len = $dusts.length;

			for(let i = 0; i < len; i++){
				let removeCls = "";

				let cls = $dusts[i].prop('class');
				let temp = cls.split(' ');

				if(temp.length > 1){
					for(let j = 0; j < temp.length; j++){
						if ($.inArray(temp[j], $colorList[i]) != -1) {
							removeCls = temp[j];
							continue;
						}
					}
				}else{
					removeCls = temp[0];
				}
				
				$dusts[i].removeClass(removeCls).addClass(colors[i]);
			}
		},
		updateYearZeroEnergyProgressBar: function(value)
		{
			let progrssbarRate = 94; // 자립률 (프로그레스바)
			let tempPercent = parseInt(value);

			if (tempPercent >= 20  && tempPercent < 40) {
				progrssbarRate = 94;
			} else if (tempPercent >= 40  && tempPercent < 60) {
				progrssbarRate = 72;
			} else if (tempPercent >= 60  && tempPercent < 80) {
				progrssbarRate = 50;
			} else if (tempPercent >= 80  && tempPercent < 100) {
				progrssbarRate = 30;
			} else if (tempPercent >= 100) {
				progrssbarRate = 8;
			}

			$dustDom.css("left", progrssbarRate+"%");
		},
		getGradeColor: function(percent)
		{
			let gradeColors; // 자립률에 대해 등급 색상 구하기 (연간)
			let tempPercent = parseInt(percent);

			if (tempPercent >= 20  && tempPercent < 40){
				gradeColors = ['dust4', 'dustb4', 'dustbc4', 'dustfc4'];
			}else if(tempPercent >= 40  && tempPercent < 60){
				gradeColors = ['dust3', 'dustb3', 'dustbc3', 'dustfc3'];
			}else if(tempPercent >= 60  && tempPercent < 80){
				gradeColors = ['dust5', 'dustb5', 'dustbc5', 'dustfc5'];
			}else if(tempPercent >= 80  && tempPercent < 100){
				gradeColors = ['dust1', 'dustb1', 'dustbc1', 'dustfc1'];
			}else if(tempPercent >= 100){
				gradeColors = ['dust6', 'dustb6', 'dustbc6', 'dustfc6'];
			}else{
				gradeColors = ['dust4', 'dustb4', 'dustbc4', 'dustfc4'];
			}

			return gradeColors;
		},
		getGradeColorClass: function(percent)
		{
			let gradeColorClass; // 자립률에 대해 등급 색상 클래스구하기
			let tempPercent = parseInt(percent);

			if (tempPercent >= 20  && tempPercent < 40) {
				gradeColorClass = 'on5';
			} else if (tempPercent >= 40  && tempPercent < 60) {
				gradeColorClass = 'on4';
			} else if (tempPercent >= 60  && tempPercent < 80) {
				gradeColorClass = 'on3';
			} else if (tempPercent >= 80  && tempPercent < 100) {
				gradeColorClass = 'on2';
			} else if (tempPercent >= 100) {
				gradeColorClass = 'on1';
			} else {
				gradeColorClass = 'on5';
			}

			return gradeColorClass;
		},
		getGradeHoverColorClass: function(percent)
		{
			let gradeHoverColorClass; // 자립률에 대해 등급 색상 클래스구하기
			let tempPercent = parseInt(percent);

			if (tempPercent >= 20  && tempPercent < 40) {
				gradeHoverColorClass = 'hv5';
			} else if (tempPercent >= 40  && tempPercent < 60) {
				gradeHoverColorClass = 'hv4';
			} else if (tempPercent >= 60  && tempPercent < 80) {
				gradeHoverColorClass = 'hv3';
			} else if (tempPercent >= 80  && tempPercent < 100) {
				gradeHoverColorClass = 'hv2';
			} else if (tempPercent >= 100) {
				gradeHoverColorClass = 'hv1';
			} else {
				gradeHoverColorClass = 'hv5';
			}

			return gradeHoverColorClass;
		},
		updateMonthZeroEnergy: function()
		{
			// 월별 자립률 및 등급 출력
			let self = control;
			let monthData = self.data['month'];

			let len = Object.keys(monthData).length;
			if(len > 0){
				let i = 0;
				for(let key in monthData){
					let spanZeroId = $spanZeroMonths[i].prop("id");
					let $monthDom = $spanZeroMonths[i].closest('li').find('button');

					$("#"+spanZeroId).html(monthData[key]['grade_str']);
					
					$monthDom.attr('data-key', key);
					$monthDom.attr('data-value', monthData[key]['value']);

					i++;
				}
			}
		},
		updateZeroEnergyMonthGraph: function()
		{
			// 월별 그래프 출력 
			let self = control;
			let monthData = self.data['month'];

			let labels = [];
			let co2Emissions = [];
			let productions = [];
			let consumptions = [];

			let i = 0;
			for(let key in monthData){
				labels[i] = monthData[key]['month'];
				co2Emissions[i] = monthData[key]['co2Emission'];
				productions[i] = monthData[key]['production'];
				consumptions[i] = monthData[key]['consumption'];

				i++;
			}

			self.monthCacheData = monthData;

			charts[0].update(labels, co2Emissions, productions, consumptions, [], []);
		},
		updateZeroEnergyDailyGraph: function()
		{
			// 일별 그래프 출력 
			let self = control;
			let dailyData = self.data['daily'];

			let labels = [];
			let co2Emissions = [];
			let productions = [];
			let consumptions = [];

			let i = 0;
			for(let key in dailyData){
				labels[i] = dailyData[key]['day'];
				co2Emissions[i] = dailyData[key]['co2Emission'];
				productions[i] = dailyData[key]['production'];
				consumptions[i] = dailyData[key]['consumption'];

				i++;
			}

			charts[1].update(labels, co2Emissions, productions, consumptions, [], []);
		},
		onButtonMonthClicked: function($this)
		{
			let self = control;

			let $li = $this.closest("li");
			let index = $li.index();
			let key = String($li.find("button").data("key"));
			let value;
			let cls;

			key = self.selectedYear +""+key.substring(4);
			value = Number(self.monthCacheData[key]['value']);

			// 등급에 대한 컬러 지정
			cls = self.getGradeColorClass(value);

			if(self.selectedYear == ""){
				alert("연도를 선택하세요.");
				return;
			}

			if(value == 0){
				// 등급이 산출되지 않은 경우 아무것도 출력하지 않음.
				//alert("조회된 내용이 없어 그래프를 출력할 수 없습니다.");
				return;
			}

			// 월 표기
			$dailyGraphMonth.html("("+self.monthCacheData[key]['month']+")");

			// 월 클릭이벤트 삭제
			self.initActiveStatus();
			self.updateActiveStatus(cls, index);

			// 일별 그래프 검색 설정
			self.selectedMonthGrapth = key;
			self.request();
		},
		onButtonMouse: function($this)
		{
			let self = control;

			let $li = $this.closest("li");
			let index = $li.index();
			let key = String($li.find("button").data("key"));
			let value;
			let cls;

			key = self.selectedYear +""+key.substring(4);
			value = Number(self.monthCacheData[key]['value']);

			// 등급에 대한 컬러 지정
			cls = self.getGradeHoverColorClass(value);

			// 월 클릭이벤트 삭제
			if (value > 0) {
				self.updateHoverStatus(cls, index);
			}
		},
		initActiveStatus: function()
		{
			let $ul = $(".zero_btn > li");

			for(let i = 0; i < $ul.length; i++){
				for(let j = 0; j < 12; j++){
					// li
					let $liCls = $liZeroMonths[j].prop('class');
					let liTemp = $liCls.split(' ');

					// span
					let $spanCls = $spanZeroMonths[j].prop('class');
					let spanTemp = $spanCls.split(' ');

					$btnZeroMonths[j].removeClass().addClass("btn_month");
					if(liTemp.length > 0){
						$liZeroMonths[j].removeClass();
						$spanZeroMonths[j].removeClass();
					}
				}
			}
		},
		updateHoverStatus: function(cls, index)
		{
			// 마우스 이벤트 시 hover 클래스 지정

			// li
			let $liCls = $liZeroMonths[index].prop('class');
			let liTemp = $liCls.split(' ');

			let removeClass = '';

			for (let i = 0; i < $monthHoverColors.length; i++){
				if ($.inArray($monthHoverColors[i], liTemp) != -1) {
					removeClass =$monthHoverColors[i];
					break;
				}
			}

			$liZeroMonths[index].removeClass(removeClass).addClass(cls);
			$btnZeroMonths[index].removeClass(removeClass).addClass(cls);
			$spanZeroMonths[index].removeClass(removeClass).addClass(cls);
		},
		updateActiveStatus: function(cls, index)
		{
			$liZeroMonths[index].addClass(cls);
			$btnZeroMonths[index].addClass(cls);
			$spanZeroMonths[index].addClass(cls);
		},
		onBarLineChartClicked: function(params)
		{
			let self = control;
			let p = params;

			let key;
			let monthCacheData;
			let val = parseInt(p['productions']) + parseInt(p['consumptions']);

			let month = p['month'].replace("월", "");
			let tempMonth = month;

			let index = Number(month-1);

			if(p['month'].indexOf("일") == -1){
				// 월별 그래프 클릭시에만 작동
				monthCacheData = self.monthCacheData;

				if(month < 10){
					month = "0" + month;
				}

				if(self.selectedYear == ""){
					alert("연도를 선택하세요.");
					return;
				}

				if(val == 0){
					//alert("조회된 내용이 없어 그래프를 출력할 수 없습니다.");
					return;
				}

				// 월 표기
				$dailyGraphMonth.html("("+tempMonth+"월)");

				//키 설정
				key = self.selectedYear+ "" + month;

				// 등급에 대한 컬러 지정
				cls = self.getGradeColorClass(monthCacheData[key]['value']);

				// 월 버튼 색상변경 
				self.initActiveStatus();
				self.updateActiveStatus(cls, index);

				// 일별 그래프 검색 설정
				self.selectedMonthGrapth = key;
				self.request();
			}
        },
		onSelectInitialize: function($this)
		{
			// 연도 바꿀 경우 정보 초기화
			let self = control;

			if($this.val() == ""){
				alert("연도를 선택하세요.");
				return;
			}

			// 일별그래프 초기화
			charts[1].clear();
			charts[1].update();

			// 월 표기 초기화
			$dailyGraphMonth.html("");

			// 월별 아이콘 초기화
			self.initActiveStatus();
			// 연도 지정
			self.selectedYear = $selectYear.val();
			// 월별 그래프 불러오기
			self.request();
		},
		
	};

	charts.forEach(function(item, index, array) {
		item._callback = control.onBarLineChartClicked;
    });

	$btnMonth.on("click", function() {
		let self = control;
		self.onButtonMonthClicked($(this));
	});

	$btnMonth.on("mouseover", function(){
		let self = control;
		self.onButtonMouse($(this), 'over');
	});

	$selectYear.on("change", function() {
		let self = control;
		self.onSelectInitialize($(this));
	});

	return control;
}