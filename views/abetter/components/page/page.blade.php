@debug('default:components/page/page.blade.php')

@php

class PageComponent extends ABetter\Wordpress\Component {
	public function build() {

		$this->content = ($content = _render($this->slot ?: _wp_content())) ? $content : _wp_fake();

		$this->content = _wp_autotitle($this->content);

	}
}

$Page = new PageComponent();

@endphp

<section class="component--page">

	@style('page.scss')
	@style('styles/typography.scss')

	<content class="block--typography responsive animated">

		{!! $Page->content !!}

	</content>

</section>
