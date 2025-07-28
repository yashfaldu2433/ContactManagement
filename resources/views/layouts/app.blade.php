<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex flex-column vh-100">

        {{-- Header --}}
        @include('includes.header')

        <div class="d-flex flex-grow-1">
            {{-- Sidebar --}}
            @include('includes.sidebar')

            {{-- Main Content --}}
            <div class="flex-grow-1 p-3">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
        @include('includes.footer')

    </div>

    @yield('scripts')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
