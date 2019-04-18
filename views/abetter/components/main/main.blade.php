@debug('default:components/main/main.blade.php')

<main class="component--main template--{{ _wp_template() }}">

	@style('main.scss')

	{!! $slot ?? "" !!}

</main>
