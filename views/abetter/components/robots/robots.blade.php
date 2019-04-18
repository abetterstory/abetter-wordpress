@php

$robots_agent = "*";
$robots_disallow = "/";
$robots_allow = NULL;

if (strtolower(env('APP_ENV')) == 'production') {
	$robots_disallow = "";
}

echo ($robots_agent !== NULL) ? "User-agent: {$robots_agent}".PHP_EOL : "";
echo ($robots_disallow !== NULL) ? "Disallow: {$robots_disallow}".PHP_EOL : "";
echo ($robots_allow !== NULL) ? "Allow: {$robots_allow}".PHP_EOL : "";

@endphp

@debug('default:components/robots/robots.blade.php','txt')
