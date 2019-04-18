@debug('default:components/byline/byline.blade.php')
<span class="component--byline">
	<date>{{ get_the_date('Y-m-d',_wp_id()) }}</date>
	&nbsp;|&nbsp;
	<span>{{ ucwords(get_the_author_meta('display_name',_wp_property('post_author'))) }}</span>
</span>
