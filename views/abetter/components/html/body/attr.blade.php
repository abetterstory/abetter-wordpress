 id="{{ (isset($post->post_name)) ? 'page--'.$post->post_name : '' }}"
 class="{{ (isset($post->post_type)) ? 'type--'.$post->post_type : '' }}"
 @debug('data-component="~/views/THEME/components/html/body/attr.blade.php"','attr')
