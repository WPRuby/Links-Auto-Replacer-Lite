jQuery(document).ready(function(){
		var ctx = document.getElementById("chart-area").getContext("2d");
		window.myDoughnut = new Chart(ctx).Doughnut(browsersData, {responsive : true});
		
		var ctx_platforms = document.getElementById("platforms_canvas").getContext("2d");
		window.myBar = new Chart(ctx_platforms).Bar(barChartData, {
			responsive : true
		});	

		var ctx_visits = document.getElementById("visits_canvas").getContext("2d");
		window.myLine = new Chart(ctx_visits).Line(lineChartData, {
			responsive: true
		});
});




	

// platforms


function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}