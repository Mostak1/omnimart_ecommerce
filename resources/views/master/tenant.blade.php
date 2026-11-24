<!DOCTYPE html>
<html lang="bn">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ড্যাশবোর্ড') | {{ tenant('shop_name') ?? 'Store Portal' }}</title>

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/back/js/plugin/webfont/webfont.min.js') }}"></script>
    <script id="setFont" data-src="{{ asset('assets/back/css/fonts.css') }}" src="{{ asset('assets/back/js/plugin/webfont/setfont.js') }}"></script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/azzara.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .badge-owner { background-color: #6861ce; color: #fff; }
        .badge-manager { background-color: #17a2b8; color: #fff; }
        .badge-cashier { background-color: #31ce36; color: #fff; }
        .store-pill {
            background: linear-gradient(135deg, #1572e8, #48abf7);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .table td, .table th { vertical-align: middle !important; }
        .low-stock-pulse {
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { transform: scale(0.95); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.9; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="wrapper">
        <div class="main-header">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="blue">
                <a href="{{ route('tenant.products.index') }}" class="logo text-white font-weight-bold" style="font-size: 16px; text-decoration: none;">
                    <i class="fas fa-store-alt mr-2"></i> {{ Str::limit(tenant('shop_name') ?? 'আমার দোকান', 16) }}
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>
                </button>
                <button class="topbar-toggler more"><i class="fa fa-ellipsis-v"></i></button>
                <div class="navbar-minimize">
                    <button class="btn btn-minimize btn-rounded"><i class="fa fa-bars"></i></button>
                </div>
            </div>

            <!-- Navbar Header -->
            <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue2">
                <div class="container-fluid">
                    <div class="d-none d-md-flex align-items-center">
                        <span class="text-white font-weight-bold mr-2"><i class="fas fa-link mr-1"></i> সাবডোমেইন:</span>
                        <span class="badge badge-light text-primary px-3 py-1 font-weight-bold" style="font-size: 13px;">{{ tenant('id') }}.{{ request()->getHost() }}</span>
                    </div>

                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        @php $authUser = Auth::guard('tenant_web')->user(); @endphp
                        @if($authUser)
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                                <div class="avatar-sm">
                                    <div class="avatar-title rounded-circle border border-white bg-warning text-white font-weight-bold">
                                        {{ strtoupper(substr($authUser->name ?? 'T', 0, 1)) }}
                                    </div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <li>
                                    <div class="user-box">
                                        <div class="u-text">
                                            <h4>{{ $authUser->name }}</h4>
                                            <p class="text-muted mb-1">{{ $authUser->phone ?? $authUser->email }}</p>
                                            <span class="badge badge-{{ $authUser->role == 'owner' ? 'owner' : 'manager' }} text-capitalize">
                                                {{ $authUser->role }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('tenant.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt mr-2"></i> লগআউট করুন
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-wrapper scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="info">
                            <a href="#">
                                <span>
                                    <b>{{ $authUser->name ?? 'দোকানদার' }}</b>
                                    <span class="user-level text-capitalize">{{ $authUser->role ?? 'স্টাফ' }}</span>
                                </span>
                            </a>
                        </div>
                    </div>

                    <ul class="nav">
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">ইনভেন্টরি ও দোকান</h4>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.products.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.products.index') }}">
                                <i class="fas fa-boxes"></i>
                                <p>প্রোডাক্ট ও স্টক</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.pos.index') ? 'active' : '' }}">
                            <a href="{{ route('tenant.pos.index') }}">
                                <i class="fas fa-cash-register"></i>
                                <p>POS ক্যাশ মেমো</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.pos.history') ? 'active' : '' }}">
                            <a href="{{ route('tenant.pos.history') }}">
                                <i class="fas fa-receipt"></i>
                                <p>বিক্রয় ইতিহাস</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.purchases.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.purchases.index') }}">
                                <i class="fas fa-truck-loading"></i>
                                <p>পাইকারি ক্রয় (Purchase)</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.staff.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.staff.index') }}">
                                <i class="fas fa-users-cog"></i>
                                <p>স্টাফ ও ক্যাশিয়ার</p>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('tenant.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('tenant.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <p>দোকান সেটিংস</p>
                            </a>
                        </li>

                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">অ্যাকাউন্ট</h4>
                        </li>

                        <li class="nav-item">
                            <form action="{{ route('tenant.logout') }}" method="POST" id="logout-sidebar-form">
                                @csrf
                                <a href="javascript:void(0)" onclick="document.getElementById('logout-sidebar-form').submit();" class="text-danger">
                                    <i class="fas fa-sign-out-alt text-danger"></i>
                                    <p>লগআউট</p>
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="main-panel">
            <div class="content">
                <div class="page-inner">
                    {{-- Flash alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright ml-auto">
                        &copy; {{ date('Y') }} <b>{{ tenant('shop_name') }}</b> | Powered by OmniMart Multi-Tenant Platform
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="{{ asset('assets/back/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/ready.min.js') }}"></script>
    @yield('scripts')
</body>
</html>
