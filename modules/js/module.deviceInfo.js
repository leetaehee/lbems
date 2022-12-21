module.deviceinfo = function() {
	var control = {
        uuid: function() {
            var u = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,

            function(c) {
                var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });

            return u;
        },
        getDeviceId: function() {
            let self = control;

            var current = window.localStorage.getItem("_DEVICEID_")

            if (current)
                return current;

            var id = self.uuid();
            window.localStorage.setItem("_DEVICEID_",id);

            return id;
        }
    }

    return control;
}
