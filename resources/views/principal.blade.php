@extends('layouts.principal')

@section('title', 'Página Principal')

@section('content')
<div class="hero" style="
    background-image: url('{{ asset('images/logo4.png') }}');
    background-size: cover; /* ancho completo */
    background-position: center center; /* centrado vertical y horizontal */
    height: 700px; /* alto razonable para evitar scroll innecesario */
    display: flex;
    align-items: center; /* centra verticalmente */
    justify-content: center; /* centra horizontalmente */
    text-align: center;
">
<h1 class="display-4" style="
    color: black;
    text-shadow:
        1px 1px 0 #fff,
        -1px 1px 0 #fff,
        1px -1px 0 #fff,
        -1px -1px 0 #fff,
        0 2px 2px rgba(0,0,0,0.2); /* sombra suave para profundidad */
    font-weight: 700; /* resalta el texto */
">
    ¡Bienvenido, {{ session('user_name', 'Invitado') }}!
</h1>
</div>
@endsection
