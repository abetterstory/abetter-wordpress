@debug('default:components/byline/byline.blade.php')
<span class="component--byline">
	<date>{{ _wp_date('Y-m-d',_wp_id()) }}</date>
	&nbsp;|&nbsp;
	<span>{{ ucwords(_wp_author_meta('display_name',_wp_property('post_author'))) }}</span>
</span>
