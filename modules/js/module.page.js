module.page = function(params)
{
	/**
	 * totalData: 전체 데이터 수 
	 * currentPage: 현재 유저가 보고 있는 페이지번호 
	 * pageCount: 화면에서 보여져야 하는 페이지 수 
	 * viewPageCount: 화면에서 보여지는 데이터 수
	 */
	if(params === undefined || params === null){
		return;
	}

	const totalData = params.total;
	const pageCount = params.pageCount;
	const viewPageCount = params.viewPageCount;
	const pagingId = params.id;
	const key = params.key;

	const $btnFirstPage = $("#btn_"+key+"_first_page");
	const $btnPrevPage = $("#btn_"+key+"_prev_page");
	const $btnNextPage = $("#btn_"+key+"_next_page");
	const $btnLastPage = $("#btn_"+key+"_last_page");
	const $pageGroup = $("#"+pagingId);

	let currentPage = params.currentPage;
	if(currentPage < 1){
		currentPage = 1;
	}

	let totalPage = Math.ceil(totalData/viewPageCount);
	let pageGroup = Math.ceil(currentPage/pageCount);
	let lastPageGroup = Math.ceil(totalPage/pageCount);

	let lastPage = pageGroup * pageCount;
	if (lastPage > totalPage){
		lastPage = totalPage;
	}

	let firstPage = 1;
	if(lastPage > 1){
		firstPage = lastPage-(pageCount-1);

		if(firstPage < 1){
			firstPage = 1;
		}
	}

	let nextStartPage = lastPage+1;
	let firstEndPage = firstPage-1;

	let $liPaging = "";
	let i;

	// 정적태그 삭제 및 숨김처리
	$pageGroup.children("li").remove();

	// 첫번째 페이지그룹이면 숨김
	if(currentPage == 1){
		$btnPrevPage.css("visibility", "hidden");
	}else{
		$btnPrevPage.css("visibility", "visible");
	}

	if(firstPage == 1) {
		$btnFirstPage.css("visibility", "hidden");
	}else{
		$btnFirstPage.css("visibility", "visible");
	}

	if(pageGroup >= lastPageGroup){
		$btnNextPage.css("visibility", "hidden");
		$btnLastPage.css("visibility", "hidden");
	}else {
		$btnNextPage.css("visibility", "visible");
		$btnLastPage.css("visibility", "visible");
	}
	// 페이징 추가
	for(i =  firstPage; i<= lastPage; i++){
		// 키 생성
		let $li = "li_" + key + "_" + i;
		let $a = "page_" + key + "_" + i;

		$liPaging += "<li id='"+$li+"'><a id='"+$a+"' href='' class='paging_click'>"+i+"</li>";
	}

	// 페이징 추가
	$pageGroup.html($liPaging);
	$btnNextPage.attr("data-next_page", nextStartPage);
	$btnLastPage.attr("data-last_page", totalPage);
	$btnFirstPage.attr("data-first_page", 1);
	$btnPrevPage.attr("data-prev_page", firstEndPage);

	// 유저가 클릭한 버튼에 클래스 주기
	$("#li_" + key + "_" + currentPage).addClass("on");
}
