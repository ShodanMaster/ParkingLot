@extends('app.layout')
@section('content')
    <div class="container">
        <h1>Welcome to the Home Page</h1>
        <h3>{{auth()->user()->type}}</h3>
        <p>This is a simple Laravel Blade template.</p>
    </div>
@endsection
