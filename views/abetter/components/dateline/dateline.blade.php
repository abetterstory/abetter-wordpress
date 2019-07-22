@debug('default:components/dateline/dateline.blade.php')
<span class="component--dateline">
	<span>{{ ucwords(_wp_property('post_type')) }}</span>
	&nbsp;|&nbsp;
	<date>{{ _wp_date('Y-m-d',_wp_id()) }}</date>
</span>
