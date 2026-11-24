<!DOCTYPE html>
<html lang="bn">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <title>দোকানদার লগইন | {{ tenant('shop_name') ?? 'Store Portal' }}</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/azzara.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body.login {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
        }
        .login-header {
            background: #1572e8;
            color: #ffffff;
            padding: 30px 25px 25px;
            text-align: center;
        }
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 22px;
        }
        .login-header p {
            margin-bottom: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .login-body {
            padding: 30px 25px;
        }
        .form-floating-label {
            position: relative;
            margin-bottom: 20px;
        }
        .btn-tenant-login {
            background: #1572e8;
            border: none;
            font-weight: 600;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            letter-spacing: 0.3px;
        }
        .btn-tenant-login:hover {
            background: #1266cf;
        }
    </style>
</head>
<body class="login">
    <div class="login-card">
        <div class="login-header">
            <div class="mb-2">
                <i class="fas fa-store-alt fa-3x"></i>
            </div>
            <h3>{{ tenant('shop_name') ?? 'আমার দোকান' }}</h3>
            <p>দোকানদার / স্টাফ অ্যাডমিন পোর্টাল</p>
            <span class="badge badge-light text-primary mt-2 font-weight-bold px-3 py-1">
                {{ tenant('id') }}.{{ request()->getHost() }}
            </span>
        </div>

        <div class="login-body">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3" style="font-size: 14px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size: 14px;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size: 14px;">
                    @foreach($errors->all() as $err)
                        <div><i class="fas fa-exclamation-circle mr-1"></i> {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('tenant.login.submit') }}" method="POST">
                @csrf
                <div class="form-group px-0 mb-3">
                    <label for="login" class="font-weight-bold text-muted">মোবাইল নম্বর অথবা ইমেইল</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input id="login" name="login" type="text" class="form-control" value="{{ old('login') }}" placeholder="01XXXXXXXXX বা email@example.com" required autofocus>
                    </div>
                </div>

                <div class="form-group px-0 mb-3">
                    <label for="password" class="font-weight-bold text-muted">পাসওয়ার্ড</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input id="password" name="password" type="password" class="form-control" placeholder="আপনার গোপন পাসওয়ার্ড দিন" required>
                    </div>
                </div>

                <div class="form-group px-0 d-flex justify-content-between align-items-center mb-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" value="1">
                        <label class="custom-control-label" for="remember">মনে রাখুন</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-tenant-login">
                    <i class="fas fa-sign-in-alt mr-2"></i> দোকানে প্রবেশ করুন
                </button>
            </form>
        </div>
    </div>
</body>
</html>
