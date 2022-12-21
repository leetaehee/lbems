let control;

$(document).ready(function() {
    createDatepicker();

    control = createControl();

    let makeEnergyButton = module.makeEnergyButton(CONFIGS['auto_loading']);
    makeEnergyButton._callback = control.onMainButtonClicked;
    makeEnergyButton.request();
});

function createDatepicker()
{
    $dateSelect.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true,
        maxDate: 0,
    });

    let d = $.datepicker.formatDate('yy-mm-dd', module.utility.getBaseDate());

    $dateSelect.val(d);
}

function createControl()
{
    let control = {
        selectedDateType: DEFAULT_PERIOD_STATUS,
        selectedDate: $dateSelect.val(),
        selectedOption: btnStartIndex,
        selectedEnergyKey: defaultEnergyKey,
        selectedStartPage: startPage,
        selectedTotalPage: 0,
        selectedSensorNo: EMPTY_STRING,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'date_type', value: self.selectedDateType });
            data.push({ name: 'date', value: self.selectedDate });
            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'energy_key', value: self.selectedEnergyKey});
            data.push({ name: 'start_page', value: self.selectedStartPage });
            data.push({ name: 'view_page_count', value: viewPageCount });
            data.push({ name: 'sensor_no', value: self.selectedSensorNo });

            params.push(
                { name: 'requester', value: requester },
                { name: 'request', value: command },
                { name: 'params', value: JSON.stringify(data) }
            );

            let requestParams =  {
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

            if (data === null) {
                return;
            }

            if (self.selectedSensorNo !== '') {
                self.selectedSensorNo = EMPTY_STRING;
            }

            if (data['summary'] !== undefined) {
                self.updateEfficiencyList();
            }

            self.updateEfficiencyChart();
        },
        updateEfficiencyList: function()
        {
            let self = control;
            let data = self.data;
            let dateType = self.selectedDateType;

            let efficiency = data['summary']['data'];
            let pagingTotal = data['summary']['paging_total'];

            if (efficiency === undefined) {
                return;
            }

            let currentCount = Object.keys(efficiency).length;

            // 차트 소수점 조회
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            let $trEfficiency = '';

            $("#tbody_facility > tr").remove();

            if (currentCount < 1) {
                $trEfficiency = "<tr><td colspan='5'>- 설비 정보가 존재하지 않습니다. -</td></tr>";
            }

            if (currentCount > 0) {
                $.each(efficiency, function(key, items) {
                    let $button = $("<button></button>")
                        .prop({ type: 'button', class: 'btn_show_graph searchBtn Btn', value: 3 })
                        .html('보기');

                    let usage = parseFloat(items['usage']['total']).toFixed(decimalPoint);
                    let efficiency = parseFloat(items['efficiency']['total']).toFixed(decimalPoint);

                    $trEfficiency += `<tr style="cursor: pointer;" data-sensor_no="${key}">`;
                    $trEfficiency += `<td>${key}</td>`;
                    $trEfficiency += `<td><span class="bl_lnb_grn"></span></td>`;
                    $trEfficiency += `<td>${module.utility.addComma(usage)}</td>`;
                    $trEfficiency += `<td>${module.utility.addComma(efficiency)}</td>`;
                    $trEfficiency += `<td>${$button[0].outerHTML}</td>`;
                    $trEfficiency += `</tr>`;
                });
            }

            $tbodyFacility.html($trEfficiency);

            // 페이징처리
            let pageParam = {
                total: pagingTotal,
                currentPage: self.selectedStartPage,
                pageCount: currentCount,
                viewPageCount: viewPageCount,
                id: 'efficiency_paging',
                key: 'efficiency'
            };

            module.page(pageParam);

            // 마지막페이지
            self.selectedTotalPage = Math.ceil(pagingTotal/viewPageCount);
        },
        updateEfficiencyChart: function()
        {
            let self = control;
            let data = self.data;
            let dateType = self.selectedDateType;

            const chartData = data['time'];
            if (chartData === undefined) {
                return;
            }

            let dates = Object.keys(chartData);
            let values = Object.values(chartData);
            let charts = self.getChartLabels(dates, dateType);

            // 차트 소수점 조회
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 그래프 출력
            chartEfficiency.clear();
            chartEfficiency.setUnit('%');
            chartEfficiency.update(charts['labels'], values, decimalPoint);
        },
        onMainButtonClicked: function($this, index)
        {
            // 동적으로 생성한 버튼이므로 .define에서 정의 할 경우 인식을 못함.
            const $buttonGroup = $("#energy_btn_group > button");

            let buttons = [];
            let arrayIndex = $this.index();

            let self = control;

            let $id = $this.prop('id');
            let energyKey = self.getEnergyKeyName($id);

            self.selectedOption = index;
            self.selectedEnergyKey = energyKey;
            self.selectedStartPage = startPage;
            self.selectedTotalPage = 0;

            // 동적 버튼 생성
            $.each($buttonGroup, function(index, item){
                let btnId =  $(this).prop("id");
                let $id = $("#" + btnId);

                buttons[index] = $id;
            });

            buttons.forEach(function(item, index) {
                item.removeClass("on");
            });

            buttons[arrayIndex].addClass("on");

            if (CONFIGS['auto_loading'] === true) {
                self.request();
            }
        },
        getEnergyKeyName: function(id)
        {
            let energyTypes = id.split('btn_');
            let key = energyTypes[1];

            if (key === undefined || key === '') {
                return;
            }

            return energyTypes[1];
        },
        getChartLabels: function(d, chart)
        {
            let labels   = [];
            let tooltips = [];

            switch(chart) {
                case 0:
                    //year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                    });
                    break;
                case 1:
                    //month
                    d.forEach(x => {
                        let month = x.substring(4, 6);
                        let day   = x.substring(6, 8);
                        let label = month + "/" +day;
                        labels.push(label);
                    });
                    break;
                case 2:
                    //day
                    d.forEach(x => {
                        let hour = x.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);
                    });
                    break;
                case 3:
                    //day
                    d.forEach(x => {
                        let minute = x.substring(10, 12);
                        let label = minute + "분";
                        labels.push(label);
                    });
                    break;
                case 5:
                    // 기간별 월 검색
                    d.forEach(x => {
                        let year = x.substring(2, 4);
                        let month = x.substring(4, 6);
                        let label = year + "-" + month;
                        labels.push(label);
                    });
                    break;
                default:
                    break;
            }

            return {
                labels: labels,
                tooltips: tooltips
            };
        },
        clearChart: function()
        {
            chartEfficiency.clear();
            chartEfficiency.update();
        },
        onCalendarChange: function($this)
        {
            let self = control;

            self.selectedDate = $this.val();
        },
        onSearchBtnClicked: function()
        {
            let self = control;

            self.request();
        },
        onPeriodTypeClicked: function($this)
        {
            let self = control;

            self.selectedDateType = parseInt($this.val());
        },
    };

    $dateSelect.on('change', function() {
        control.onCalendarChange($(this))
    });

    $btnSearch.on('click', function() {
        control.onSearchBtnClicked();
    });

    $periodType.on('click', function() {
        control.onPeriodTypeClicked($(this));
    });

    $btnEfficiencyFirstPage.on('click', function() {
        control.selectedStartPage = 1;
        control.request();
    });

    $btnEfficiencyPrevPage.on('click', function() {
        control.selectedStartPage = Number(control.selectedStartPage - 1);
        control.request();
    });

    $btnEfficiencyNextPage.on('click', function() {
        control.selectedStartPage = Number(control.selectedStartPage + 1);
        control.request();
    });

    $btnEfficiencyLastPage.on('click', function() {
        control.selectedStartPage = Number(control.selectedTotalPage);
        control.request();
    });

    $(document).on('click', '.paging_click', function(e) {
        // 페이징번호 클릭시 해당 데이터 조회
        e.preventDefault();

        let self = control;
        let $id = $(this).prop("id");

        let tmp = $id.split('_');

        tmp[2] = Number(tmp[2]);

        self.selectedStartPage = tmp[2];
        self.request();
    });

    $(document).on('click', '.btn_show_graph', function() {
        control.selectedSensorNo = $(this).closest('tr').data('sensor_no');
        control.request();
    })

    return control;
}