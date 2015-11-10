	function formatTimestamp(unix_timestamp) {
		var m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var d = new Date(unix_timestamp*1000);
		
		return timeSince(d);
		
		return [m[d.getMonth()],' ',d.getDate(),', ',d.getFullYear()," ",
			(d.getHours() % 12 || 12),":",(d.getMinutes() < 10 ? '0' : '')+d.getMinutes(),
			" ",d.getHours() >= 12 ? 'PM' : 'AM'].join('');
	}
	
	function formatFileSize(bytes) {
		var s = ['bytes', 'KB','MB','GB','TB','PB','EB'];
		for(var pos = 0;bytes >= 1000; pos++,bytes /= 1024);
		var d = Math.round(bytes*10);
		return pos ? [parseInt(d/10),".",d%10," ",s[pos]].join('') : bytes + ' bytes';
	}
	
	function timeSince(date) {
		var seconds = Math.floor((new Date() - date) / 1000);
		var interval = Math.floor(seconds / 31536000);

		if (interval > 1) {
			return "vor " + interval + " Jahren";
		}
		interval = Math.floor(seconds / 2592000);
		if (interval > 1) {
			return "vor " + interval + " Monaten";
		}
		interval = Math.floor(seconds / 86400);
		if (interval > 1) {
			return "vor " + interval + " Tagen";
		}
		interval = Math.floor(seconds / 3600);
		if (interval > 1) {
			return "vor " + interval + " Stunden";
		}
		interval = Math.floor(seconds / 60);
		if (interval > 1) {
			return "vor " + interval + " Minuten";
		}
		return "vor " + Math.floor(seconds) + " Sekunden";
	}
