@php
$site->env = env('APP_ENV');
$site->version = env('APP_VERSION');
$site->lang = get_locale();
@endphp
 lang="{{ $site->lang }}"
 dir="ltr"
 l10n=""
 env="{{ $site->env }}"
 version="{{ $site->version }}"
 xmlns:og="http://ogp.me/ns#"
 class=""
 @debug('data-component="~/views/THEME/components/html/attr.blade.php"','attr')
