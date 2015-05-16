<div id="wrap" class="lar_stats">
	
	<?php
	$link_id = $_GET['link_id'];
	
	$stats = get_post_meta($link_id, PLUGIN_PREFIX.'stats',true);

	$original_url =  get_post_meta($link_id, PLUGIN_PREFIX.'url',true); 	
	$keywords =  get_post_meta($link_id, PLUGIN_PREFIX.'keywords',true); 	
	?>

	<div id="link_brief">
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th><?php _e('Original Link','links-auto-replacer-pro'); ?></th>
					<th><?php _e('Link Keywords','links-auto-replacer-pro'); ?></th>
					<th><?php _e('Total Clicks','links-auto-replacer-pro'); ?></th>
					<th><?php _e('Actions','links-auto-replacer-pro'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a href="<?php echo $original_url; ?>" target="_blank"><?php echo $original_url; ?></a></td>
					<td><?php echo implode(',',$keywords); ?></td>
					<td><?php echo $stats['total_visits']; ?></td>
					<td><a href="<?php echo get_edit_post_link($link_id); ?>"><?php _e('Edit','links-auto-replacer-pro'); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div id="lar_visits">
		<h3><?php _e('Visits','links-auto-replacer-pro'); ?></h3>
				<canvas id="visits_canvas" class="lar_canvas_unit" style="padding: 10px;background: #ffffff;"></canvas>	
		
	</div>


	<div class="lar_canvas">
		<h3><?php _e('Countries','links-auto-replacer-pro'); ?></h3>
		<div  id="vmap" class="lar_canvas_unit" style=" height: 400px;"></div>
	</div>

	<div class="lar_canvas">
		
		
			<h3><?php _e('Browsers','links-auto-replacer-pro'); ?></h3>
		
			<canvas id="chart-area" class="lar_canvas_unit" width="500" height="500"/>
		

	</div>

	<div class="lar_canvas">
		<h3><?php _e('Platforms','links-auto-replacer-pro'); ?></h3>
		
		
			<canvas id="platforms_canvas" class="lar_canvas_unit" />
		

	</div>

	

	<script>
		//map data
		var sample_data = {<?php foreach($stats['countries'] as $code => $count): ?>"<?php echo $code; ?>":"<?php echo $count; ?>"<?php endforeach; ?>};
		
		
		var browsersData = [
				<?php foreach($stats['browsers'] as $browser => $count): ?>
				{
					value: <?php echo $count; ?>,
					color:getRandomColor(),
					highlight: getRandomColor(),
					label: "<?php echo $browser; ?>"
				},
				<?php endforeach; ?>
				

			];

	var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
	var randomColorFactor = function(){ return Math.round(Math.random()*255)};

		var barChartData = {
			labels : [<?php foreach($stats['platforms'] as $platform => $count): ?>"<?php echo $platform ?>",<?php endforeach; ?>],
			datasets : [
				
				{
					fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,0.8)",
					highlightFill : "rgba(151,187,205,0.75)",
					highlightStroke : "rgba(151,187,205,1)",
					data : [<?php foreach($stats['platforms'] as $platform => $count): ?><?php echo $count ?>,<?php endforeach; ?>]
				}
			]

		}



		var lineChartData = {
			labels : [<?php foreach($stats['visits'] as $day => $count): ?>"<?php echo date('d M Y',strtotime($day)); ?>",<?php endforeach; ?>],
			datasets : [
				
				{
					label: "Visits",
					fillColor : "rgba(151,187,205,0.2)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(151,187,205,1)",
					data : [<?php foreach($stats['visits'] as $day => $count): ?><?php echo $count ?>,<?php endforeach; ?>]
				}
			]

		}

			



	</script>
</div>



