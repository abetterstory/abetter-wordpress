@debug('default:components/canvas/canvas.blade.php')

@php

class CanvasComponent extends ABetter\Wordpress\Component {
	public function build() {

		$this->content = ($content = _render($this->slot ?: _wp_content())) ? $content : "";

		if (!$this->content && _wp_template() == 'front') {
			$this->content = _render("@component('components.posts',TRUE)");
		}

	}
}

$Canvas = new CanvasComponent();

@endphp

<section class="component--canvas">

	@style('canvas.scss')

	<content class="block--content">

		{!! $Canvas->content !!}

	</content>

</section>
