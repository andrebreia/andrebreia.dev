<!DOCTYPE html>
<html lang="en">

<head>
    @include('_layouts.partials.meta')

    <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

    @include('_layouts.partials.analytics')
</head>

<body class="flex flex-col min-h-screen font-sans text-blue-grey-lighter bg-gradient-primary leading-normal text-lg pt-32 border-t-8 border-brand-secondary">

    <div class="flex-grow">

        @include('_layouts.partials.header')

        <main class="" role="main">
            @yield('content')
        </main>

    </div>

    @include('_layouts.partials.footer')

</body>
</html>
