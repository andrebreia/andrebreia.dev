<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
<link rel="shortcut icon" href="/assets/images/favicon.ico}" type="image/x-icon">
<link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">

<title>André Breia | {{ $title ?? '' }}</title>

<meta name="description" content="{{ $description ?? '' }}">
<meta property="og:title" content="{{ $ogTitle ?? $title ?? '' }}" />
<meta property="og:description" content="{{ $ogDescription ?? $description ?? '' }}" />
<meta property="og:image" content="{{ $ogImage ?? '/assets/images/andre.jpg' }}" />
<meta property="og:url" content="{{ $page->getUrl() }}" />
<meta property="og:type" content="website" />