let $selectBuildingDong_ = '';
let $selectBuildingFloor_ = '';
let $selectBuildingRoom_ = '';

const defaultSelectOption_ = "<option value='all'>전체</option>";
const defaultSelectDong = 'A';

module.BuildingManager = function(floorSelector = 'select_building_floor', roomSelector = 'select_building_room', dongSelector = 'select_building_dong')
{
    let control = {
        buildingInfos: {},
        energyKey: '',
        complexCodePk: '',
        homeDongCnt : 1,
        floorKeyData: CONFIGS['floor_key_data'],
        request: function(option = 999)
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'option', value: option });
            data.push({ name: 'energy_key', value: self.energyKey });
            data.push({ name: 'complex_code_pk', value: self.complexCodePk });

            params.push(
                {name: 'requester', value: 'building'},
                {name: 'request', value: 'building_manager'},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: [],
                showAlert: true
            };

            self.selectorInit();
            module.subRequest(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            let d = data['building_info'];

            self.buildingInfos = {};
            self.homeDongCnt = parseInt(d[0]['home_dong_cnt']);

            d.forEach(function(x) {
                let homeDongPk = x['home_dong_pk'];
                let homeGrpPk = x['home_grp_pk'];
                let homeHoNm = x['home_ho_nm'];
                let homeHoPk = x['home_ho_pk'];

                if (self.buildingInfos[homeDongPk] === undefined) {
                    self.buildingInfos[homeDongPk] = {};
                }

                if (self.buildingInfos[homeDongPk][homeGrpPk] === undefined) {
                    self.buildingInfos[homeDongPk][homeGrpPk] = {};
                }

                if (self.buildingInfos[homeDongPk][homeGrpPk][homeHoPk] === undefined) {
                    self.buildingInfos[homeDongPk][homeGrpPk][homeHoPk] = {
                        homeHoPk: [],
                        homeHoNm: [],
                    };
                }

                if (self.buildingInfos[homeDongPk][homeGrpPk][homeHoPk].homeHoPk.includes(homeHoPk) === false) {
                    self.buildingInfos[homeDongPk][homeGrpPk][homeHoPk].homeHoPk.push(homeHoPk);
                    self.buildingInfos[homeDongPk][homeGrpPk][homeHoPk].homeHoNm.push(homeHoNm);
                }
            });

            self.updateSelectBox();
        },
        updateSelectBox: function()
        {
            let self = control;
            let d = self.buildingInfos;
            let keys = Object.keys(d);

            $selectBuildingDong_.html(defaultSelectOption_);
            $selectBuildingFloor_.html(defaultSelectOption_);
            $selectBuildingRoom_.html(defaultSelectOption_);

            if (self.homeDongCnt === 1) {
                // 동이 하나이면, 층을 바로 출력
                // 여러개 동이면 dong selectBox 를 통해 검색 가능하도록 변경 할 것
                $selectBuildingDong_.hide();
                self.onDongSelectBoxChanged(defaultSelectDong);
                return;
            }

            keys.forEach(function(x) {
                $selectBuildingDong_.append("<option value='" + x + "'>" + x + "</option>");
            });

            $selectBuildingDong_.on("change", function() {
                self.onDongSelectBoxChanged($(this).val());
            });
        },
        onDongSelectBoxChanged : function(dong)
        {
            let self = control;

            const d = self.buildingInfos;
            const floorInfo = self.floorKeyData;
            const dongCount = self.homeDongCnt;

            $selectBuildingFloor_.html(defaultSelectOption_);

            if (dong === 'all' || d[dong] === undefined) {
                return;
            }

            const keys = Object.keys(d[dong]);

            $("#search_section, .select-floor-section").show();

            if (Object.keys(keys).length < 1
                && CONFIGS['auto_loading'] === true
                && dongCount === 1) {
                $("#search_section, .select-floor-section").hide();
            }

            keys.forEach(function(x) {
                $selectBuildingFloor_.append("<option value='"+x+"'>"+ floorInfo[x]+"</option>");
            });

            $selectBuildingFloor_.on("change", function() {
                self.onFloorSelectBoxChanged(dong, $(this).val());
            });
        },
        onFloorSelectBoxChanged: function(dong, floor)
        {
            let self = control;
            let d = self.buildingInfos[dong];

            $selectBuildingRoom_.html(defaultSelectOption_);

            if (floor == "all" || d[floor] === undefined) {
                return;
            }

            let keys = Object.keys(d[floor]);

            keys.forEach(function(x) {
                let homeHoNm = d[floor][x]['homeHoNm'][0];

                $selectBuildingRoom_.append("<option value='" + x + "'>" + homeHoNm + "</option>");
            });
        },
        setEnergyKey: function(energyKey)
        {
            let self = control;

            self.energyKey = energyKey;
        },
        setComplexCodePk: function(complexCodePk)
        {
            let self = control;

            self.complexCodePk = complexCodePk;
        },
        setFloorKeyData: function(floorKeyData)
        {
            let self = control;

            self.floorKeyData = floorKeyData;
        },
        selectorInit: function()
        {
            $selectBuildingDong_ = $("#" + dongSelector);
            $selectBuildingFloor_ = $("#" + floorSelector);
            $selectBuildingRoom_ = $("#" + roomSelector);

            $("#search_section").css('display', 'block');
        }
    };

    return control;
}
