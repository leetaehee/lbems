module.excel = function() {
	var control = {
        exportExcel: function(d, fileName) {
            const sheetName = "data";
            // step 1. workbook 생성
            var wb = XLSX.utils.book_new();
            // step 2. 시트 만들기
            var newWorksheet = XLSX.utils.aoa_to_sheet(d);
            // step 3. workbook에 새로만든 워크시트에 이름을 주고 붙인다.
            XLSX.utils.book_append_sheet(wb, newWorksheet, sheetName);
            // step 4. 엑셀 파일 만들기
            var wbout = XLSX.write(wb, {bookType:'xlsx',  type: 'binary'});
            // step 5. 엑셀 파일 내보내기
            saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fileName);
        },
	}

	return control;
}

function s2ab(s) {
    var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
    var view = new Uint8Array(buf);  //create uint8array as viewer
    for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet
    return buf;
}

function download_excel(energy_sensor, name) {

    var excelHandler = {

            getExcelFileName : function(){

                var file_name = energy_sensor+'-'+name;

                var i;
                var value = excel_name_params[name][0];
                file_name += "-"+value;
                if (value=='total') {
                    i = 2; // dong 이 total 이면 ho 는 건너뜀
                } else {
                    i = 1;
                }


                while (i<excel_name_params[name].length) {
                    var value = excel_name_params[name][i];
                    file_name += "-"+value;
                    i++;
                }

                file_name +=".xlsx";
                return file_name;
            },

            getSheetName : function(){
                return name+' data';
            },

            getExcelData : function(){

                var data = excel_data[energy_sensor][name];
                var converted_data = [];
                var row = [];

                for (var header in data) {
                    if (header == 'raw') header = "시간";
                    row.push(header);
                }
                converted_data.push(row);

                // excel_data 은 name 인덱스가 2번 들어감
                var len = data[name].length;
                for (var i=0; i<len; i++) {
                    var row = [];
                    for (var column in data) {
                        row.push(data[column][i]);
                    }
                    converted_data.push(row);
                }
                return converted_data;
            },
            getWorksheet : function(){
                return XLSX.utils.aoa_to_sheet(this.getExcelData());
            }
    }



}



