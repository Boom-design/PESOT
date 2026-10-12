{{--
    What a search engine and a chat app see.

    Only the two public pages include this — the landing page and the job list.
    Everything behind a login is kept out of the index by robots.txt, and has
    no business being found by search in the first place.

    $seoTitle and $seoDescription are passed by the page. The description is
    the grey line under the blue link on a results page, and the line under
    the thumbnail when someone pastes the link into Messenger. Left to itself,
    Google writes one from whatever text it finds first, which here is a
    heading split over three lines.
--}}
@php
    $seoTitle       = $seoTitle       ?? 'PESO Cagayan de Oro — Job Vacancies and Employment Services';
    $seoDescription = $seoDescription ?? 'Find job vacancies in Cagayan de Oro City. Register with the Public Employment Service Office, apply for local and overseas work, and join PESO job fairs.';
    $seoImage       = asset('images/hero/PESOSIMAGE.png');

    // /login serves the landing page, because the login form sits on it. Two
    // addresses for one page is the thing a canonical exists to settle, so the
    // page says which of the two is the real one rather than leaving the
    // current URL to answer for it.
    $seoCanonical   = $seoCanonical ?? url()->current();
@endphp

<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $seoCanonical }}">

{{-- Messenger, Facebook and Viber read these when the link is pasted. --}}
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="PESO Cagayan de Oro">
<meta property="og:title"       content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url"         content="{{ $seoCanonical }}">
<meta property="og:image"       content="{{ $seoImage }}">
<meta property="og:locale"      content="en_PH">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image"       content="{{ $seoImage }}">

{{--
    Who the office is, in the form Google reads. The address is the one on the
    footer of this same page; nothing here is invented.
--}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'GovernmentOffice',
    'name'     => 'Public Employment Service Office (PESO) — Cagayan de Oro City',
    'alternateName' => 'PESO CDO',
    'url'      => url('/'),
    'image'    => $seoImage,
    'areaServed' => 'Cagayan de Oro City, Misamis Oriental, Philippines',
    'address'  => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => '2nd Floor New Executive Building, City Hall, Capistrano-Hayes Sts.',
        'addressLocality' => 'Cagayan de Oro',
        'addressRegion'   => 'Misamis Oriental',
        'postalCode'      => '9000',
        'addressCountry'  => 'PH',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
