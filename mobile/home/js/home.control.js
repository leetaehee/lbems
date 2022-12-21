let control;

$(document).ready(function(){
	control = createControl();
	control.requestUsageByLiTags();
	control.request();
});

function createControl()
{
	let control = {
		request: function()
		{
			let self = control;
            let params = [];
            let data = [];

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params',  value: JSON.stringify(data)}
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

			// 에너지 자립률 
			self.updateIndependenceData();
			// 에너지 사용현황
			self.updateEnergyUsedData();
			// 에너지 소비량 대비 생산현황
			self.updateProductionData();
			// 용도별 에너지 사용현황 (금일)
			self.updateUsageUsedData();
        },
		updateIndependenceData: function() 
		{
			let self = control;
			let data = self.data['independence_data']['year'];

			let rate = data['rate'];
			let grade = data['grade'];

			// 자립률 등급 막대 그래프 초기화
			$(".li_grade > span, .li_grade > b").removeClass();
			// 자립률 원형 그래프 초기화
			chartIndependence.update([0, 100]);

			$labelIndependenceGrade.html(data['grade']); // 등급
			$labelIndependencePercent.html(module.utility.addComma(rate.toFixed(0))); // 비율

			if (rate > 0) {
				rate = module.utility.getValidPercent(rate); // 그래프 비율 조정
				chartIndependence.update([rate, 100 - rate]); // 자립률 그래프 설정

				// 자립률 등급 막대그래프
				$("#li_grade_section_" + grade + " > span").addClass("fcOren b");
				$("#li_grade_section_" + grade + " > b").addClass("fc39 b");
			}
		},
		updateEnergyUsedData: function()
		{
			let self = control;

			let data = self.data['energy_data'];
			if (data === undefined || data === '' || data === null) {
				return;
			}

			// 전기 사용량
			const electricData = data['electric'];
			const nowInfo = electricData['nows'];
			const lastInfo = electricData['lasts'];

			const electricNowUsed = Object.values(nowInfo['data']);
			const electricLastUsed = Object.values(lastInfo['data']);
			const electricNowPrice = module.utility.getSumOfValues(nowInfo['price']);
			const electricLastPrice = module.utility.getSumOfValues(lastInfo['price']);

			const nowUsedSum = module.utility.getSumOfValues(electricNowUsed);
			const lastUsedSum = module.utility.getSumOfValues(electricLastUsed);

			// 목표값
			$labelElectricTarget.html(module.utility.addComma(electricData['target']));

			// 사용량 
			$labelNowMonthElectricUsed.html(module.utility.addComma(nowUsedSum));
			$labelLastMonthElectricUsed.html(module.utility.addComma(lastUsedSum));

			// 요금
			$labelNowMonthElectricPrice.html(module.utility.addComma(electricNowPrice));
			$labelLastMonthElectricPrice.html(module.utility.addComma(electricLastPrice));
		},
		updateProductionData: function()
		{
			let self = control;

			let data = self.data['independence_data'];
			if (data === undefined || data === '' || data === null) {
				return;
			}

			let dailyConsumption = data['daily']['consumption'];
			let dailyProduction = data['daily']['production'];
			let dailyProgressPercent = Math.floor((dailyProduction/dailyConsumption) * 100);
			let dailyProgressRate = module.utility.getValidPercent(dailyProgressPercent);

			let monthConsumption = data['month']['consumption'];
			let monthProduction = data['month']['production'];
			let monthProgressPercent = Math.floor((monthProduction/monthConsumption) * 100);
			let monthProgressRate = module.utility.getValidPercent(monthProgressPercent);

			let yearConsumption = data['year']['consumption'];
			let yearProduction = data['year']['production'];
			let yearProgressPercent = Math.floor((yearProduction/yearConsumption) * 100);
			let yearProgressRate = module.utility.getValidPercent(yearProgressPercent);

			// 전일
			$labelPredayConsumptionUsed.html(module.utility.addComma(dailyConsumption));
			$labelPredayProductionUsed.html(module.utility.addComma(dailyProduction));
			$progressGraphPredayProduction.css("width", dailyProgressRate + "%");

			// 전월
			$labelMonthConsumptionUsed.html(module.utility.addComma(monthConsumption));
			$labelMonthProductionUsed.html(module.utility.addComma(monthProduction));
			$progressGraphMonthProduction.css("width", monthProgressRate + "%");

			// 금년
			$labelYearConsumptionUsed.html(module.utility.addComma(yearConsumption));
			$labelYearProductionUsed.html(module.utility.addComma(yearProduction));
			$progressGraphYearProduction.css("width", yearProgressRate + "%");
		},
		updateUsageUsedData: function()
		{
			let self = control;
			let data = self.data['usage_data'];
			if (data === undefined || data === '' || data === null) {
				return;
			}

			let usages = data['usage'];
			let distributions = data['distribution'];

			let valueLength = Object.keys(distributions).length;

			// 데이터없음 필드 합쳐서 생성
			let arraySize = valueLength + 1;
			let arrayIndex = 1;
			let pieDistributions = Array.from({length:arraySize}, () => 0);

			// 사용량
			$.each(usages, function(key, value) {
				$("#label_" + key + "_used").html(value.toFixed(0));
			});

			// 분포도
			$.each(distributions, function(key, value) {
				pieDistributions[arrayIndex] = value;
				arrayIndex++;
			});

			let useSum = module.utility.getSumOfValues(pieDistributions);
			if (useSum < 1) {
				pieDistributions[0] = 100;
			}

			chartPieUse.update(LABEL_USAGES, COLOR_USAGES, pieDistributions);
		},
		getProgressRate: function(standardValue, compareValue)
		{
			let progressRate = Math.floor((standardValue/compareValue) * 100);

			return module.utility.getValidPercent(progressRate);
		},
		requestUsageByLiTags: function()
		{
			const USAGE_LABELS = CONFIGS['mobile']['usage']['label'];
			const USAGE_COLORS = CONFIGS['mobile']['usage']['color'];
			const USAGE_KEYS = CONFIGS['mobile']['usage']['key'];

			let liTags = [];
			let liString = '';

			$.each(USAGE_LABELS, function(index, item){
				if (index === 0) {
					return true;
				}

				let $spanItemColor = $("<span></span>");
				$spanItemColor.css('background-color', "rgb(" + USAGE_COLORS[index] + ")");

				let $spanUsed = $("<span></span>").attr({
					'id' : 'label_' + USAGE_KEYS[index] + '_used'
				}).html(0);

				liTags.push("<li>");
				liTags.push("<p>" + $spanItemColor[0].outerHTML + " " + item + " " + "</p>");
				liTags.push("<b>" + $spanUsed[0].outerHTML + " kwh</b>");
				liTags.push("</li>");
			});

			// 배열을 문자열로 변환
			liString = liTags.join('');

			$("#li_usage > tr").remove();
			$liUsage.append(liString);
		},
	};

	return control;
}