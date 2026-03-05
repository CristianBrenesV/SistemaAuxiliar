@extends('layouts.principal')

@section('title', 'Página Principal')

@section('content')
<h1 class="display-4">¡Bienvenido, {{ session('user_name', 'Invitado') }}!</h1>
@endsection
