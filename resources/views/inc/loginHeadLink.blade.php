    @if(Request::segment(1) == 'login' || Request::segment(1) == 'register' || Request::segment(1) == 'forgot-password' || Request::segment(1) == 'new-password')
        <!-- Link Styles -->
        <link href="{{ asset('/public/assets/css/app.css'); }}" rel="stylesheet" />
    @else
        <link rel="stylesheet" href="{{ asset('/public/assets/css/style.css') }}">
    @endif