@extends('master')
@section('content')

<div class="titulo">
    <h1>INICIO DE SESIÓN</h1>
</div>
<div class="form">
    <h3>Para utilizar los servicios de este sitio web es necesario iniciar sesión con una cuenta de Google:</h3><br>
    <button onclick="location.href ='{{$url}}'" id="iniciosesion">Iniciar sesión con Google</button>
</div>

@endsection


