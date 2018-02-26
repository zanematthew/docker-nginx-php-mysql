@extends('layouts.app')

@section('body_class', 'login')
@section('title', 'Password Reset Confirm')

@section('content')
<div class="box">
    <div class="main">
        @if (session('status'))
            {{ session('status') }}
        @endif
        <form class="form" role="form" method="POST" action="{{ route('password.request') }}">
            {{ csrf_field() }}
            <input type="hidden" name="token" value="{{ $token }}">
            {{ $errors->has('email') ? ' has-error' : '' }}
            {{ $errors->has('password') ? ' has-error' : '' }}
            {{ $errors->has('password_confirmation') ? ' has-error' : '' }}
            @if ($errors->has('email'))
                {{ $errors->first('email') }}
            @endif
            @if ($errors->has('password'))
                {{ $errors->first('password') }}
            @endif
            @if ($errors->has('password_confirmation'))
                {{ $errors->first('password_confirmation') }}
            @endif

            <div class="row icon-row">
                <span class="input-field-icon"><icon name="envelope"></icon></span>
                <input id="email" type="email" name="email" value="{{ $email or old('email') }}" placeholder="Email" required autofocus>
            </div>
            <div class="row icon-row">
                <span class="input-field-icon"><icon name="lock"></icon></span>
                <input id="password" type="password" name="password" placeholder="New Password" required>
            </div>
            <div class="row icon-row">
                <span class="input-field-icon"><icon name="lock"></icon></span>
                <input id="password-confirm" type="password" name="password_confirmation" placeholder="Confirm New Password" required>
            </div>
            <button type="submit" class="btn-primary">Reset Password</button>
        </form>
    </div>
</div>
@endsection
