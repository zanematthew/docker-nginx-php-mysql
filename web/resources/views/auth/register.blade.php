@extends('layouts.app')

@section('title', '| Register')

@section('content')
<div class="grid is-100">
    <form class="form" role="form" method="POST" action="{{ route('register') }}">
        {{ csrf_field() }}

        <div class="form-item">
            <a href="/redirect/facebook" class="login-with-facebook-container">
                <icon name="facebook-square"></icon> Login with Facebook
            </a>
        </div>

        <div class="form-item">
            {{ $errors->has('name') ? ' has-error' : '' }}
            {{ $errors->has('email') ? ' has-error' : '' }}
            {{ $errors->has('password') ? ' has-error' : '' }}
            @if ($errors->has('name'))
            {{ $errors->first('name') }}
            @endif
            @if ($errors->has('email'))
            {{ $errors->first('email') }}
            @endif
            @if ($errors->has('password'))
            {{ $errors->first('password') }}
            @endif
        </div>

        <div class="form-item">
            <input id="name" type="text" name="name" placeholder="Name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="form-item">
            <input id="email" type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        </div>
        <div class="form-item">
            <input id="password" type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-item">
            <input id="password-confirm" type="password" name="password_confirmation" placeholder="Password" required>
        </div>
        <button type="submit" class="btn-primary">Register</button>
    </form>
</div>
@endsection
