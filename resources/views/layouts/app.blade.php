<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>@yield('title', 'Laravel Shop') - Laravel电商</title>
  <link href="/css/app.css" rel="stylesheet"/>
</head>
<body>
  <div id="app" class="{{ route_class() }}-page">
      @include('layouts._header')
      @include('layouts._message')
      <div class="container">
        @yield('content')
      </div>
      @include('layouts._footer')
  </div>
  <script src="/js/app.js"></script>
  @yield('scripts')
</body>
</html>
