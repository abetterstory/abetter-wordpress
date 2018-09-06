@php _debug('cloak');
$site->cloak = TRUE;
@endphp

@if (!empty($site->cloak))
	<style>[v-cloak]{display:none}</style>
@endif
