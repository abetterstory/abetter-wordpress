@debug('Default component: ~/views/<theme>/components/html/head/cloak.blade.php')
@php
$site->cloak = TRUE;
@endphp
@if (!empty($site->cloak))
	<style>[v-cloak]{display:none}</style>
@endif
