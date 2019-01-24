@php

namespace ABetter\Wordpress;

class SitemapComponent extends Component {
	public function build() {

		$this->domain = ($canonical = env('APP_CANONICAL')) ? $canonical : url('/');
		$this->baseurl = $this->domain.'/sitemap';
		$this->query = trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
		$this->types = Post::getPostTypes();
		$this->type = (preg_match('/sitemap\_?([^\.]+)\.xml$/',$this->query,$match)) ? $match[1] : '';
		$this->index = (in_array($this->type,$this->types)) ? FALSE : TRUE;

		if (!$this->index) {
			$this->limit = 999;
			$this->sort = ($this->type == 'page') ? 'menu_order' : 'date';
			$this->order = ($this->type == 'page') ? 'ASC' : 'DESC';
			$this->blacklist = ($this->type == 'page') ? ['search','403','403-forbidden','404','404-not-found'] : [];
			$this->not_in = ($this->blacklist && ($p = new Posts(['post_name__in' => $this->blacklist]))) ? $p->ids : [];
			$this->posts = new Posts(['post_type' => $this->type, 'post__not_in' => $this->not_in, 'orderby' => $this->sort, 'order' => $this->order, 'posts_per_page' => $this->limit]);
			$this->items = $this->posts->items;
		}

	}
}

$Sitemap = new SitemapComponent();

@endphp

@php echo '<?xml version="1.0" encoding="UTF-8"?>' @endphp
@if ($Sitemap->index)
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	@foreach ($Sitemap->types AS $type)
	<sitemap>
		<loc>{{ $Sitemap->baseurl.'_'.$type.'.xml' }}</loc>
	</sitemap>
	@endforeach
</sitemapindex>
@else
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	@foreach ($Sitemap->items AS $item)
    <url>
        <loc>{{ $Sitemap->domain.$item->url }}</loc>
        <lastmod>{{ date('Y-m-d\TH:i:sP',$item->timestamp) }}</lastmod>
        <changefreq>{{ ($item->front) ? 'daily' : 'weekly' }}</changefreq>
        <priority>{{ ($item->front) ? '0.8' : '0.5' }}</priority>
    </url>
	@endforeach
</urlset>
@endif
