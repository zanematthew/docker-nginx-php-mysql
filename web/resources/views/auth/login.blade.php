@extends('layouts.app')
@section('title', '| Login')

@section('content')
<div class="grid is-100">
    <form class="form" role="form" method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}

        @if ($errors->has('email'))
            {{ $errors->first('email') }}
        @endif
        @if ($errors->has('password'))
            {{ $errors->first('password') }}
        @endif
        <div class="form-item">
            <a href="/redirect/facebook" class="login-with-facebook-container">
                <icon name="facebook-square"></icon> Login with Facebook
            </a>
        </div>
        <div class="form-item">
            <input id="email" type="email" name="email" placeholder="E-mail..." value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-item">
            <input id="password" type="password" class="form-control" name="password" placeholder="Password" required><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
        </div>
        <div class="form-item">
            <button type="submit" class="btn-primary">Login</button>
        </div>

        <div class="form-item align-center">
            <a href="{{ route('password.request') }}">Forgot Your Password?</a> | <a href="{{ route('register') }}">Register</a>
        </div>
    </form>
</div>
@endsection
