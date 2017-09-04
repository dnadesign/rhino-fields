<script type="text/javascript">
	window.onload = function() {		
		var timeout,
			timerField = document.getElementById("$ID"),
			displayField = document.getElementById("display-$ID");
		
		function updateTime() {
			var currentTime = timerField.value,
				ss = currentTime.split(':'),
				dt = new Date();

			dt.setHours(ss[0]);
			dt.setMinutes(ss[1]);
			dt.setSeconds(ss[2]);

			var dt2 = new Date(dt.valueOf() + 1000);
			var ts = dt2.toTimeString().split(" ")[0];

			timerField.value = ts;
			displayField.innerHTML = ts;
			timeout = setTimeout(updateTime, 1000);
		}

		timeout = setTimeout(updateTime, 1000);
	}
</script>

<input type="hidden" name="$name" id="$ID" value="$value" />
<span id="display-$ID">00:00:00</span>
