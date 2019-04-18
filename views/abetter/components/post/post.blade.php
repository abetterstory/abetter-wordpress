@debug('default:components/post/post.blade.php')

@php

class PostComponent extends ABetter\Wordpress\Component {
	public function build() {

		$this->content = ($content = _render($this->slot ?: _wp_content())) ? $content : _wp_fake();

		$this->content = _wp_autotitle($this->content);

		if (!preg_match('/@dateline|@byline/',$this->content)) {
			$this->content = str_replace('</h1>','</h1><p class="dateline">'._render('@dateline').'</p>',$this->content);
		}

	}
}

$Post = new PostComponent();

@endphp

<section class="component--post">

	@style('post.scss')
	@style('styles/typography.scss')

	<content class="block--typography responsive animated">

		{!! $Post->content !!}

	</content>

</section>
