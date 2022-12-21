module.popup = function(params) {               // 파라미터 받아서 popup 모듈 이용하여 control하는부분
    var beforeCallback = params.beforeCallback; // 파라미터 받아서 변수선언 
    var openCallback   = params.openCallback;
    var closeCallback  = params.closeCallback;
    var $link          = params.$link;

    var temp = $link.magnificPopup({                    // magnificPopup 이용 
        type            : 'inline',
        preloader       : false,
        removalDelay    : 300,
        mainClass       : 'mfp-zoom-in',
        fixedContentPos : false,
        fixedBgPos      : true,
        overflowY       : 'auto',
        closeBtnInside  : true,
        callbacks: {
            beforeOpen: function() {
                if(beforeCallback instanceof Function) // Functiond의 자료형 묻는 구문 ex beforeCallback func이라면 T/F 
                    beforeCallback();
            },
            open: function() {
                if(openCallback instanceof Function){
					alert(openCallback);
                    openCallback();
				}
            },
            close: function() {
                if(closeCallback instanceof Function){
                    closeCallback();
				}
            }
        }
    });

	var control = {
        _popup: temp,
        open: function() {
            let self = control;
            self._popup.magnificPopup('open');
        },
        close: function() {
            let self = control;
            self._popup.magnificPopup('close');
        }
	}

	return control;
}
