@extends('layouts.app')

@section('body_class', 'login')
@section('title', 'Password Reset')

@section('content')
<div class="box">
    <div class="main">
        @if (session('status'))
            {{ session('status') }}
        @endif

        <form class="form" role="form" method="POST" action="{{ route('password.email') }}">

        {{ csrf_field() }}
        {{ $errors->has('email') ? ' has-error' : '' }}
        @if ($errors->has('email'))
            {{ $errors->first('email') }}
        @endif

        <div class="row icon-row">
            <span class="input-field-icon"><icon name="envelope"></icon></span>
            <input id="email" type="email" class="form-control" name="email" placeholder="Email..." value="{{ old('email') }}" required>
        </div>
        <button type="submit" class="btn-primary">Send Password Reset Link</button>
        </form>
    </div>
</div>
@endsection