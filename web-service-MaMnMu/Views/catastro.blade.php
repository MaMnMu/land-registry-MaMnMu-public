@extends('master')
@section('content')

<div class="titulo">
    <h1>INFORMACIÓN CATASTRAL</h1>
</div>
<div id="cont2">
    <p>Bienvenido, {{$name}}</p><br>
    <button onclick="location.href = 'index.php?logout'">Cerrar sesión</button>
</div>
<div id="cont">
    <div class="form">
        <h2>Consulta de datos catastrales</h2><br>
        @if (!isset($parametros))
        <form action="datosCatastro.php" method="POST">
            <label for="provincia">Provincia:</label>
            <input type="text" id="provincia" name="provincia" required /><br><br>
            <label for="municipio">Municipio:</label>
            <input type="text" id="municipio" name="municipio" size="30" required /><br><br>
            <label for="tipovia">Tipo de via:</label>
            <input type="text" id="tipovia" name="tipovia" required /><br><br>
            <label for="via">Via:</label>
            <input type="text" id="via" name="via" size="30" required /><br><br>
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" required /><br><br>
            <button type="submit" id="comprobar" name="enviar" value="Comprobar Datos">Comprobar datos</button>
        </form>
        @else 
        <!-- En caso de que $parámetros este definido, los ponemos en el value para que el usuario no tenga que escribirlo todo de nuevo -->
        <form action="datosCatastro.php" method="POST">
            <label for="provincia">Provincia:</label>
            <input type="text" id="provincia" name="provincia" value="{{$parametros[0]}}" required /><br><br>
            <label for="municipio">Municipio:</label>
            <input type="text" id="municipio" name="municipio" value="{{$parametros[1]}}" size="30" required /><br><br>
            <label for="tipovia">Tipo de via:</label>
            <input type="text" id="tipovia" name="tipovia" value="{{$parametros[2]}}" required /><br><br>
            <label for="via">Via:</label>
            <input type="text" id="via" name="via" value="{{$parametros[3]}}" size="30" required /><br><br>
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" value="{{$parametros[4]}}" required /><br><br>
            <button type="submit" id="comprobar" name="enviar" value="Comprobar Datos">Comprobar datos</button>
        </form>
        @endif
    </div>
    @if (isset($map))
    <div id="map">
        <iframe src="{{$map}}"></iframe>
    </div>
    @endif
</div>

<!-- Si $hojacat está definido, signfica que el usuario ha hecho la primera petición de los datos y por tanto, 
mostramos el número y la hoja catastral -->
@if (isset($hojacat)) 
<table>
    <tr>
        <th>Número</th>
        <th>Hoja catastral</th>
    </tr>
    <!-- Si $hojacat es un array significa que hay varias referencias catastrales y por ello, tenemos que ir recorriendo cada elemento -->
    @if (gettype($hojacat) == 'array') 
    @foreach ($hojacat as $dato)
    <tr>
        <td>{{$numero}}</td>
        <td><a href='datosCatastro.php?hojacat={{$dato}}'>{{$dato}}</a></td>
    </tr>
    @endforeach
    @elseif (gettype($hojacat) == 'string') 
    <tr>
        <!-- En caso de que $hojacat sea un string, puede ser que solo haya una referencia catastral o que haya un error, 
        entonces si la longitud es 14 (que es lo que ocupa la hoja catastral) pues tenemos la hoja catastral y 
        si ocupa distinto, tenemos un error -->
        @if (strlen($hojacat) == 14)
        <td>{{$numero}}</td>
        <td><a href='datosCatastro.php?hojacat={{$hojacat}}'>{{$hojacat}}</a></td>
        @else
        <td></td>
        <td>{{$hojacat}}</td>
        @endif
    </tr>
    @endif
</table>
@endif

<!-- Si hemos recibido $planta significa que el usuario ha pulsado en una hoja catastral y ha hecho la segunda petición -->
@if (isset($planta)) 
<table>
    <tr>
        <th>Via</th>
        <th>Número</th>
        @if (isset($escalera))
        <th>Escalera</th>
        @endif
        <th>Planta</th>
        <th>Puerta</th>
        <th>Referencia catastral</th>
    </tr>
    <!-- Si $refcat es un array, implica que traemos varios array con cada dato que necesitamos por lo que hay que recorrerlos todos a la vez -->
    @if (gettype($refcat) == 'array') 
    @for ($i = 0; $i < count($refcat); $i++)
    <tr>
        <td>{{$via[$i]}}</td>
        <td>{{$numero[$i]}}</td>
        @if (isset($escalera))
        <td>{{$escalera[$i]}}</td>
        @endif
        <td>{{$planta[$i]}}</td>
        <td>{{$puerta[$i]}}</td>
        <td><a href='datosCatastro.php?refcat={{$refcat[$i]}}'>{{$refcat[$i]}}</a></td>
    </tr>
    @endfor
    <!-- Si es un string, entonces solo traemos un dato de cada tipo -->
    @elseif (gettype($refcat) == 'string') 
    <tr>
        <td>{{$via}}</td>
        <td>{{$numero}}</td>
        @if (isset($escalera))
        <td>{{$escalera}}</td>
        @endif
        <td>{{$planta}}</td>
        <td>{{$puerta}}</td>
        <td><a href='datosCatastro.php?refcat={{$refcat}}'>{{$refcat}}</a></td>
    </tr>
    @endif
</table>
@endif

<!-- Por último, si recibimos $localización es por que el usuario ha pulsado en una referencia catastral y ha hecho la tercera petición -->
<!-- En este caso, solo vamos ha recibir strings, nunca arrays, por lo que simplemente mostramos los datos -->
@if (isset($localizacion))
<table>
    <tr>
        <th colspan="2">Datos descriptivos del inmueble</th>
    </tr>
    <tr>
        <td>Referencia catastral</td>
        <td>{{$refcat}}</td>
    </tr>
    <tr>
        <td>Localización</td>
        <td>{{$localizacion}}</td>
    </tr>
    <tr>
        <td>Clase</td>
        <td>{{$clase}}</td>
    </tr>
    <tr>
        <td>Uso</td>
        <td>{{$uso}}</td>
    </tr>
    <tr>
        <td>Superficie construida</td>
        <td>{{$superficie}}</td>
    </tr>
    <tr>
        <td>Precio estimado</td>
        <td>{{$precio}} €</td>
    </tr>
    <!-- Hay que tener cuidado porque ciertos inmuebles no tienen establecido un año de construcción -->
    @if (isset($año))
    <tr>
        <td>Año de construcción</td>
        <td>{{$año}}</td>
    </tr>
    @endif
</table>
@endif

@endsection
