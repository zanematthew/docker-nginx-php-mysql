@extends('layouts.app')

@section('content')
    <primary-nav></primary-nav>
    <router-view>
        <div class="row is-item grid is-100 align-center">Initializing...</div>
    </router-view>
@endsection
