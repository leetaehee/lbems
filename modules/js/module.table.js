$.fn.dataTable.ext.errMode = 'none';

module.table = {};

module.table = function($table, sumColumn, outputName, scrollX, scrollXInner, pageLength_) {
	let _sumColumn = sumColumn;

	if(scrollX === undefined || scrollX == null) {
		scrollX = false;
	}

	let pageLength = pageLength_;

	if(pageLength === undefined || pageLength == null) {
		pageLength = 10;
	}

	var temp = $table.DataTable({
		//"order": [[ 0, 'asc' ], [ 1, 'asc' ]]
		//ordering: true,
		//order: [[0, 'asc']],
		ordering     : false,
		autoWidth    : false,
		lengthChange : true, //show entries
		pageLength   : pageLength,
		searching    : false,
		info         : false,
		processing   : true,
		scrollX      : scrollX,
		scrollXInner : scrollXInner,
		pagingType   : 'full_numbers',
		columnDefs   : [
			{
				"targets"   : "_all",
				"className" : "text-center",
				"orderable" : false,
			},
			{
				"targets"   : 0,
				"orderable" : false
			}
		],
		fnDrawCallback: function(oSettings) {
		},
		footerCallback: function ( row, data, start, end, display ) {
			if(Array.isArray(_sumColumn) == false)
				return;

			var api = this.api(), data;
			var len = api.columns().nodes().length;

			// Remove the formatting to get integer data for summation
			var intVal = function ( i ) {
				return typeof i === 'string' ?
					i.replace(/[\$,]/g, '')*1 :
					typeof i === 'number' ?
					i : 0;
			};

			_sumColumn.forEach((col, index) => {
				if(Number.isInteger(col) != false && len > col) {
					total = api
						.column( col )
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0 );

					if($.isNumeric(total) == true) {
						total = Math.round(total, 2);
						total = module.utility.addComma(total);
					}

					pageTotal = api
						.column( col, { page: 'current'} )
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0 );

					// Update footer
					$( api.column( col ).footer() ).html(
						total
					);
				}
			});
		},
		dom: 'B<"tbl_len_up"<"tbl_len"fl>>rt<"tbl_page_info"i><"tbl_page"p><"clear">',
		language: {
			"info": "전체 _TOTAL_ 개 중 _START_ ~ _END_ 리스트",
			//"lengthMenu": "_MENU_ 개 씩 보기",
			"lengthMenu": "",
			"infoEmpty": " ",
			"infoEmpty": " ",
			"paginate": {
				"first": "처음",
				"previous": "이전",
				"next": "다음",
				"last": "마지막",
			}
		},
		buttons: [
			{
				extend: 'excelHtml5',
				text: '',
				titleAttr: 'Excel',
				footer: true,
				className: 'excel',
				filename: function() {
					var d       = new Date();
					var year    = d.getFullYear();
					var month   = d.getMonth() + 1;
					var date    = d.getDate();
					var seconds = d.getSeconds();
					var minutes = d.getMinutes();
					var hour    = d.getHours();

					return outputName + "_" + year + "-" + month + "-" + date + "-" + hour + minutes + seconds;
				},
				customize: function(xlsx) {
					var sheet = xlsx.xl.worksheets['sheet1.xml'];
					var col = $('col', sheet);
					col.each(function () {
						$(this).attr('width', 15);
					});
				},
				//exportOptions: {
				//	columns: [1, 2]
				//},
				//customize:	function(xlsx) {
				//	var sheet = xlsx.xl.worksheets['sheet1.xml'];
				//
				//	// Loop over the cells in column `C`
				//	$('row c', sheet).each( function () {
				//		// Get the value
				//		var tt = this;
				//		var tt2 = $(this);
				//		//console.log($('is t', this).text());
				//		//console.log($('v', this).text());
				//	});
				//	// Loop over the cells in column `C`
				//	$('row c[r^="C"]', sheet).each( function () {
				//		// Get the value
				//		if ( $('is t', this).text() == 'New York' ) {
				//			$(this).attr( 's', '20' );
				//		}
				//	});
				//}
			},
			{
				extend: 'print',
				text: '',
				titleAttr: 'Print',
				footer: true,
				className: 'print'
			}]
	});

	let controller =  {
		isSet  : false,
		_$table: $table,
		_table : temp,
		update : function(data){
			if(data === undefined || Array.isArray(data) == false || data.length <= 0) {
				this.clear();
				return;
			}

			var len = data.length;

			for(var i = 0; i < len; i++) {
				var temp = data[i];

				if(Array.isArray(temp) === false)
					continue;

				var len2 = temp.length;

				for(var j = 0; j < len2; j++) {
					if($.isNumeric(data[i][j]) == true)
						data[i][j] = data[i][j];
				}
			}

			this.isSet = true;
			this._table.clear().rows.add(data).draw();
		},
		clear  : function() {
			this.isSet = false;
			this._table.clear().draw();
		},
		dispose: function() {
			this._table.clear();
			this._table.destroy();
			this._table = null;
		},
		show: function() {
			this._$table.parent().show();
		},
		hide: function() {
			this._$table.parent().hide();
		},
		callback: null,
		onRowClick: function(data) {
			if(this.callback instanceof Function) {
				this.callback(data);
			}
		},
	};

	$table.find("tbody").on('click', 'tr', function() {
		var data = temp.row(this).data();
		controller.onRowClick.call(controller, data);
	});

	$table.find("tbody").css('cursor', 'pointer');

	return controller;
}

