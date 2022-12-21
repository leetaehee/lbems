module.utility = {
    getBemsUnits: function()
    {
        return [
            'Wh',
            'm3',
            'm3',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'Wh',
            'ℓ',
            'Wh'
        ];
    },
    getBemsUnits2: function()
    {
        return [
            'kWh',
            'm3',
            'm3',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'ℓ',
            'kWh',
            '㎍/m³'
        ];
    },
    getBemsUnits3: function()
    {
        return [
            'kWh',
            'kWh',
            'kWh',
            'kWh',
            'm3',
            'kWh'
        ];
    },
    getBemsUnits2Names: function()
    {
        return [
            '전기',
            '가스',
            '수도',
            '조명',
            '냉방',
            '전열',
            '승강',
            '급탕',
            '난방',
            '보일러',
            '환기',
            '태양광',
            '급수배수펌프',
            '등유',
            '설비',
            '미세먼지'
        ];
    },
    getBemsEnergyKeyNames: function()
    {
        return [
            'electric',
            'gas',
            'water',
            'electric_light',
            'electric_cold',
            'electric_elechot',
            'electric_elevator',
            'electric_hotwater',
            'electric_heating',
            'electric_boiler',
            'electric_vent',
            'solar',
            'electric_water',
            'oil_dyu',
            'equipment',
            'finedust'
        ];
    },
    getBemsFacilityUnits: function()
    {
        return ['%'];
    },
    getSumOfValues: function(arr)
    {
        let temp = Object.values(arr);

        return module.utility.sumOfFloatArray(temp);
    },
    getSumAmountOfValues: function(arr)
    {
        let temp = Object.values(arr);

        return module.utility.sumAmountOfFloatArray(temp);
    },
    getValidPercent: function(percent)
    {
        if (isNaN(percent) == true) {
            return 0;
        }

        if (percent < 0) {
            return 0;
        }

        if (percent > 100) {
            return 100;
        }

        return percent;
    },
    dateFormat: function(date, sepa)
    {
        return date.substring(0, 4) + sepa + date.substring(4, 6) + sepa + date.substring(6, 8);
    },
    getToday: function(sepa = "-")
    {
        let d = new Date();
        let today = d.getFullYear() + ("0" + (d.getMonth() + 1)).slice(-2) + ("0" + d.getDate()).slice(-2);

        return module.utility.dateFormat(today, sepa);
    },
    getCurrentTime: function()
    {
        let d = new Date();

        return d.getFullYear()
            + ("0" + (d.getMonth() + 1)).slice(-2)
            + ("0" + d.getDate()).slice(-2)
            + ("0" + d.getHours()).slice(-2)
            + ("0" + d.getMinutes()).slice(-2)
            + ("0" + d.getSeconds()).slice(-2);
    },
    addMonth: function(val, d)
    {
        let temp = new Date(d.getTime());
        let date = temp.getDate();

        temp.setDate(1);

        temp.setMonth(temp.getMonth() + val);
        temp.setDate(date);

        return temp;
    },
    addDate: function(val, d)
    {
        let temp = new Date(d.getTime());

        temp.setDate(temp.getDate() + val);

        return temp;
    },
    addComma: function(Num)
    {
        let strNum = '' + Num;

        if (isNaN(strNum) || strNum == "") {
            return Num;
        } else {
            let rxSplit = new RegExp('([0-9])([0-9][0-9][0-9][,.])');
            let NumArr = strNum.split('.');
            NumArr[0] += '.';
            do {
                NumArr[0] = NumArr[0].replace(rxSplit, '$1,$2');
            } while (rxSplit.test(NumArr[0]));

            if (NumArr.length > 1) {
                return NumArr.join('');
            } else {
                return NumArr[0].split('.')[0];
            }
        }
    },
    getDateSpan: function(start, end)
    {
        let date1 = new Date(start);
        let date2 = new Date(end);

        let diff = Math.abs(date2.getTime() - date1.getTime());
        diff = Math.ceil(diff / (1000 * 3600 * 24));

        return diff;
    },
    countAnimation: function(el, start, end, marker = '', comma = false)
    {
        start = Number(start);
        end = Number(end);

        let obj;

        let func = function() {
            if (end - start <= 0.1 || obj == null || obj == 0) {
                clearInterval(obj);

                if (comma == true) {
                    el.text(module.utility.addComma(end) + marker);
                } else {
                    el.text(end + marker);
                }

                return;
            }

            let gap = Number(((end - start) * 0.2).toFixed(3));

            if (start > end) {
                start = Number((start - gap).toFixed(3));
            } else {
                start = Number((start + gap).toFixed(3));
            }

            let temp = start.toFixed(2);

            if (comma == true) {
                el.text(module.utility.addComma(temp) + marker);
            } else {
                el.text(temp + marker);
            }
        };

        obj = setInterval(func, 100);

        return obj;
    },
    sumOfIntArray: function(d)
    {
        let len = d.length;
        let sum = 0;

        for (let i = 0; i < len; i++) {
            let temp = parseInt(d[i]);
            sum += temp;
        }

        return sum;
    },
    sumOfFloatArray: function(d)
    {
        let len = d.length;
        let sum = 0;

        for (let i = 0; i < len; i++) {
            let temp = parseFloat(d[i]);
            sum += temp;
        }

        sum = parseFloat(Math.round(sum));

        return sum;
    },
    sumAmountOfFloatArray: function(d)
    {
        let len = d.length;
        let sum = 0;

        for (let i = 0; i < len; i++) {
            let temp = parseFloat(d[i]);
            sum += temp;
        }

        sum = parseFloat(Math.round(sum));

        return sum;
    },
    getDiffPercent: function(val1, val2)
    {
        if (val2 == 0){
            val2 = 1;
        }

        let temp = parseInt(val1 / val2 * 100);

        if (temp > 999) {
            temp = 999;
        }

        if (temp < -999) {
            temp = -999;
        }

        return temp;
    },
    getArrayAverage: function(arr)
    {
        if (typeof(arr) === "object") {
            arr = Object.values(arr);
        }

        if (arr.length == 0) {
            return 0;
        }

        let temp = module.utility.sumAmountOfFloatArray(arr);

        return parseInt(temp / arr.length);
    },
    getArrayMax: function(arr)
    {
        if (arr.length == 0) {
            return 0;
        }

        return Math.max.apply(null, arr);
    },
    getArrayMin: function(arr)
    {
        if (arr.length == 0) {
            return 0;
        }

        return Math.min.apply(null, arr);
    },
    getDecimalPointFromDateType: function (dateType)
    {
        /**
         * 차트 기준으로 작성됨
         */
        let decimalPoint;

        switch (parseInt(dateType)) {
            case 0:
                // 금년
                decimalPoint = 0;
                break;
            case 1:
            case 5:
                // 금월, 기간별 금월
                decimalPoint = 1;
                break;
            case 2:
                // 금일
                decimalPoint = 2;
                break;
            case 3:
                // 5분단위
                decimalPoint = 3;
                break;
        }

        return decimalPoint;
    },
    makeZeroArray: function (array)
    {
        let temp = [];

        if (Array.isArray(array) === false && typeof(array) === 'object') {
            array = Object.values(array);
        }

        if (Array.isArray(array) === false) {
            return temp;
        }

        temp = array.filter(arr => arr !== 0);

        return temp;
    },
    initYearSelect: function($selector, year)
    {
        const date = new Date();
        let currentYear = date.getFullYear();
        let currentMonth = date.getMonth() + 1;

        const selected = " selected='selected'";

        const baseDate = module.utility.getBaseDate();
        const baseYear = baseDate.getFullYear();

        if (currentMonth === 12) {
            // 월말의 경우 내년으로 보여주어야 하는 경우가 있으므로 예외처리
            //currentMonth += 1;
        }

        // 초기화
        $selector.empty();

        for (let thisYear = currentYear; thisYear >= year; thisYear--) {
            let optionSelected = selected;

            optionSelected = (thisYear !== baseYear) ? "" : optionSelected;

            $selector.append(`<option value=${thisYear} ${optionSelected}>${thisYear}</option>`);
        }
    },
    transToElectric: function(energyName, value)
    {
        let transValue = value;

        if (energyName === 'gas' || energyName === 'water') {
            transValue = value * 10.55;
        }

        return transValue;
    },
    getBaseDate: function()
    {
        /*
         * 월은 0부터 시작
         */
        return new Date(2022,11, 20);
    }
};