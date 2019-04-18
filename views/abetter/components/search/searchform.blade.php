@debug('default:components/search/searchform.blade.php')

@php

class SearchFormComponent extends ABetter\Wordpress\Component {
	public function build() {

		$this->search = _wp_page('search');
		$this->search_url = _wp_url($this->search);
		$this->search_label = _dictionary('search_label',NULL,'Search');
		$this->search_placeholder = _dictionary('search_placeholder',NULL,'Search text here…');
		$this->search_query = $_GET['s'] ?? '';
		$this->search_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><style>circle,line{fill:transparent;stroke:#000;stroke-width:2.5px;}</style><g><circle cx="9.6" cy="9.6" r="7.2"/><line x1="14.7" y1="14.7" x2="21.8" y2="21.7"/></g></svg>';

	}
}

$SearchForm = new SearchFormComponent();

@endphp

<section class="component--searchform">

	@style('searchform.scss')

	<form action="{{ $SearchForm->search_url }}">
		<figure>
			<input name="s" value="{{ $SearchForm->search_query }}" placeholder="{{ $SearchForm->search_placeholder }}" autocomplete="off" spellcheck="false" />
		</figure>
		<button type="submit">
			<i class="icon">
				{!! $SearchForm->search_icon !!}
			</i>
		</button>
	</form>


</section>
