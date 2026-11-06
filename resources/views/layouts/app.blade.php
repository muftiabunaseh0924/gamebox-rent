<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    @include('partials.head')
</head>
<body class="flex min-h-screen flex-col">
    @include('partials.navbar')

    <main class="mx-auto w-full max-w-[1320px] flex-1 px-4 pb-12 pt-28 md:px-8">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>