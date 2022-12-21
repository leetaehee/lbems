module.BuildingList = function($selector)
{
    let control = {
        complexInfo: {},
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            params.push(
                {name: 'requester', value: 'building' },
                {name: 'request', value: 'building_list'},
                {name: 'params', value: JSON.stringify(data)
                });

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: [],
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            self.data = data
            self.updateBuildingSelectBox();
        },
        updateBuildingSelectBox: function()
        {
            let self = control;
            let buildingData = self.data['building_data'];

            let i = 0;
            let optionStr = "<option value=''>건물 선택</option>";

            for(i = 0; i < buildingData.length; i++){
                optionStr += "<option value='"+buildingData[i]['complex_code_pk']+"'>"+buildingData[i]['name']+"</option>";
            }

            $selector.html(optionStr);
        },
    };

    return control;
}
