<!DOCTYPE html>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content={{ csrf_token() }}>
    <meta itemprop="name" property="og:title" name="twitter:title" content="{{ config('app.name', 'Laravel') }}">
    <meta itemprop="description" property="og:description" name="twitter:description" content="{{config('app.name', 'Laravel')}}">
    <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }} - {{env('APP_FULL_NAME')}}">
    <meta property="og:type" content="web system">
    <meta property="og:url" content="{{ env('APP_URL', '#') }}">
    <meta property="og:locale" content="pt_BR">
    <meta name="theme-color" content="#268A2A">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>

    @ResourceCrudEasyJquery()
    @FontAwesomeV4()
    @ResourceCrudEasyDatatablesExtraCss()
    @ResourceCrudEasyStylesCss()
    @select2easyCss()
    <link rel="stylesheet" href="{{ asset('vendor/select2easy/select2/css/select2-bootstrap.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        <div class="sidebar-brand d-none d-md-flex">
            {{ config('app.name', 'Laravel') }}
            {{-- TODO ajustar no siderbar-close icon --}}
        </div>
        @include('layouts.navigation')
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>

    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        <header class="header header-sticky mb-4">
            <div class="container-fluid">
                <button class="header-toggler px-md-0 me-md-3" type="button"
                        onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
                    <svg class="icon icon-lg">
                        <use xlink:href="{{ asset('icons/coreui.svg#cil-menu') }}"></use>
                    </svg>
                </button>
                <a class="header-brand d-md-none" href="#">
                    <svg width="118" height="46" alt="CoreUI Logo">
                        <use xlink:href="{{ asset('icons/brand.svg#full') }}"></use>
                    </svg>
                </a>
                <ul class="header-nav d-none d-md-flex">
                    <li class="nav-item">
                        {{ config('app.name', 'Laravel') }}
                    </li>
                </ul>
                <ul class="header-nav ms-auto">

                </ul>
                <ul class="header-nav ms-3">
                    <li class="nav-item dropdown">
                        <a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()?->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0">
                            {{--<a class="dropdown-item" href="{{ route('profile.show') }}">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('icons/coreui.svg#cil-user') }}"></use>
                                </svg>
                                {{ __('My profile') }}
                            </a>--}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('icons/coreui.svg#cil-account-logout') }}"></use>
                                    </svg>
                                    {{ __('Logout') }}
                                </a>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="header-divider"></div>
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    @includeWhen(!empty($breadcrumb), 'layouts.breadcrumb')
                </nav>
            </div>
        </header>
        <div class="body flex-grow-1 px-3">
            @include('layouts.title')
            <div class="container-fluid">
                @yield('content')
            </div>
            {{--<button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button>

            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">

                        <strong class="me-auto">Bootstrap</strong>
                        <small>11 mins ago</small>
                        <button type="button" class="btn-close" data-coreui-dismiss="toast" aria-label="Close">x</button>
                    </div>
                    <div class="toast-body">
                        Hello, world! This is a toast message.
                    </div>
                </div>
            </div>--}}
        </div>
        <footer class="footer">
            <div>
                &copy; {{ now()->year }}
                {{ config('app.time', 'Power-up by package gsferro/resource-crud-easy') }}
            </div>
            <div class="ms-auto">
                Version&nbsp;1.0.0
            </div>
        </footer>
    </div>

    @ResourceCrudEasyDatatablesPlugin()
    @ResourceCrudEasyPlugins()
    @select2easyJs()
    <script src="{{ asset('js/coreui.bundle.min.js') }}"></script>
    {{--<script type="module">
        import { Toast } from '@coreui/coreui'

        Array.from(document.querySelectorAll('.toast'))
            .forEach(toastNode => new Toast(toastNode))
    </script>--}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: true
        });
        $(function(){
            {{-- select2easy run --}}
            $('.select2easy:not(".select2-hidden-accessible")').each((id, el) => {
                $(el).select2easy({
                    // minimumInputLength: 4
                })
            });
            $( '.dropdown-toggle' ).dropdown();

            /*const toastTrigger = document.getElementById('liveToastBtn')
            const toastLiveExample = document.getElementById('liveToast')
            if (toastTrigger) {
                toastTrigger.addEventListener('click', () => {
                    const toast = new coreui.Toast(toastLiveExample)
                    toast.show()
                })
            }*/
        })
    </script>
    <!-- <script src="//unpkg.com/alpinejs" defer></script>-->
    @yield('js')
</body>
</html>
