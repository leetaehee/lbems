let $selectAptType_ = '';
let $selectAptDong_ = '';
let $selectAptHome_ = '';

module.aptInfo = function(option, typeSelector = 'select_apt_type', dongSelector = 'select_apt_dong', hoSelector = 'select_apt_home')
{
    let control = {
        homeInfo: {},
        energyKey: '',
        complexCodePk: '',
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'option', value: option });
            data.push({ name: 'energy_key', value: self.energyKey });
            data.push({ name: 'complex_code_pk', value: self.complexCodePk});

            params.push(
                {name: 'requester', value: 'common'},
                {name: 'request', value: 'home_info'},
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
            let d = data['home_info'];

            self.homeInfo = {};

            d.forEach(function(x){
                let home_dong_pk = x['home_dong_pk'];
                let home_ho_pk = x['home_ho_pk'];

                let home_key_pk = x['home_key_pk'];
                let home_type = x['home_type'];
                let home_ho_nm = x['home_ho_nm'];

                if (self.homeInfo[home_type] === undefined) {
                    self.homeInfo[home_type] = {};
                }

                if (self.homeInfo[home_type][home_dong_pk] === undefined) {
                    self.homeInfo[home_type][home_dong_pk] = {
                        home_ho: [],
                        home_key: [],
                        home_ho_nm: [],
                    };
                }

                if (self.homeInfo[home_type][home_dong_pk].home_key.includes(home_key_pk) == false) {
                    self.homeInfo[home_type][home_dong_pk].home_key.push(home_key_pk);
                    self.homeInfo[home_type][home_dong_pk].home_ho.push(home_ho_pk);
                    self.homeInfo[home_type][home_dong_pk].home_ho_nm.push(home_ho_nm);
                }
            });

            self.updateSelectBox();
        },
        updateSelectBox: function()
        {
            let self = control;

            let d = self.homeInfo;
            let keys = Object.keys(d);

            $selectAptType_.html("<option value='all'>전체</option>");
            $selectAptDong_.html("<option value='all'>전체</option>");
            $selectAptHome_.html("<option value='all'>전체</option>");

            keys.forEach(function(x) {
                $selectAptType_.append(`<option value=${x}>${x}타입</option>`);
            });

            $selectAptType_.on("change", function() {
                self.onTypeSelectBoxChanged($(this).val());
            });

            $selectAptDong_.on("change", function() {
                self.onDongSelectBoxChanged($(this).val());
            });

            $selectAptHome_.on("change", function() {
                self.onHomeSelectBoxChanged($(this).val());
            });
        },
        onTypeSelectBoxChanged: function(x) {
            let self = control;
            let d = self.homeInfo;

            const homeType = x;

            $selectAptDong_.html("<option value='all'>전체</option>");
            $selectAptHome_.html("<option value='all'>전체</option>");

            if (homeType === 'all' || d[homeType] === undefined) {
                return;
            }

            let keys = Object.keys(d[homeType]);

            keys.forEach(function(x) {
                $selectAptDong_.append("<option value='" + x + "'>" + x + "동</option>");
            });
        },
        onDongSelectBoxChanged: function(x)
        {
            let self = control;
            let d = self.homeInfo;

            $selectAptHome_.html("<option value='all'>전체</option>");

            let type = $selectAptType_.val();

            if (x === 'all' || d[type] === undefined || d[type][x] === undefined) {
                return;
            }

            let home_ho = d[type][x].home_ho;
            let home_key = d[type][x].home_key;
            let home_ho_nm = d[type][x].home_ho_nm;

            home_ho.forEach(function(x, i) {
                $selectAptHome_.append("<option value='" + home_ho[i] + "'>" + home_ho[i] + "호(" + home_ho_nm[i] +")</option>");
            });
        },
        onHomeSelectBoxChanged: function(x)
        {
        },
        setEnergyKey: function(energyKey)
        {
            control.energyKey = energyKey;
        },
        setComplexCodePk: function(complexCodePk)
        {
            let self = control;

            self.complexCodePk = complexCodePk;
        },
        selectorInit: function()
        {
            $selectAptType_ = $("#" + typeSelector);
            $selectAptDong_ = $("#" + dongSelector);
            $selectAptHome_ = $("#" + hoSelector);
        }
    };

    return control;
}