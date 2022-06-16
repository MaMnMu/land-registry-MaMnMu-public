<?php

require "vendor/autoload.php";

use eftec\bladeone\bladeone;

$Views = __DIR__ . '\Views';
$Cache = __DIR__ . '\Cache';

$Blade = new BladeOne($Views, $Cache);

session_start();

// http://webapps.fpmadridonline.es/
$wsdlFile = 'http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC/OVCCallejero.asmx?WSDL';

// Creamos una instancia de SoapClient para poder hacer llamadas al servicio web que colocamos como parámetro.
$clienteSOAP = new SoapClient($wsdlFile);

// Si se ha pulsado el botón de consultar datos, entramos aquí
if (isset($_POST['enviar'])) {

    $provincia = filter_input(INPUT_POST, 'provincia');
    $municipio = filter_input(INPUT_POST, 'municipio');
    $tipovia = filter_input(INPUT_POST, 'tipovia');
    $via = filter_input(INPUT_POST, 'via');
    $numero = filter_input(INPUT_POST, 'numero');
    $parametros = [$provincia, $municipio, $tipovia, $via, $numero];
    $_SESSION['parametros'] = $parametros;

    // Hacemos la solicitud de los datos del catastro con el método Consulta_DNPLOC y los convertimos a json.
    $datos = $clienteSOAP->Consulta_DNPLOC($provincia, $municipio, $tipovia, $via, $numero)->any;
    $datos_xml = new SimpleXMLElement($datos);
    $datos_json = json_decode(json_encode($datos_xml));

    // Si lerr está definido, significa que hemos obtenido un error y mostraremos el valor des.
    if (isset($datos_json->lerr)) {
        $hojacat = $datos_json->lerr->err->des;
        echo $Blade->run('catastro', ['hojacat' => $hojacat, 'numero' => $numero, 'name' => $_SESSION['name'], 'parametros' => $parametros]);
    } else {

        // Si no hay error, hay que comprobar la cantidad de inmuebles que hemos obtenido ya que dependiendo de si es 1 o más, las propiedades varian.
        $numinmuebles = $datos_json->control->cudnp;
        if ($numinmuebles == 1) {
            $info = $datos_json->bico->bi;

            // Guardamos los datos que obtenemos en una sesión para usarlos más tarde.
            $_SESSION['info'] = $info;
            $rc = $info->idbi->rc;

            // Ahora lo que buscamos es la hoja catastral que la obtenemos combinando estas propiedades.
            $hojacat = $rc->pc1 . $rc->pc2;
        } else {
            $info = $datos_json->lrcdnp->rcdnp;
            $_SESSION['info'] = $info;

            // Cuando hay varios inmuebles, rcdnp es un array por lo que hemos de recorrerlo para obtener las hojas catastrales y meterlas en un nuevo array.
            foreach ($info as $dato) {
                $hojacat[] = $dato->rc->pc1 . $dato->rc->pc2;
            }

            // Como puede darse el caso de que haya una sola hoja catastral con varias referencias catastrales, debemos hacer un array_unique para quedarnos solo con una hoja catastral.
            $hojacat = array_unique($hojacat);
        }
        
        // Creamos el mapa de esta forma, pasandole la provincia, municipio, via y número que hemos obtenido
        $map = "https://www.google.com/maps?width=400&amp;height=400&amp;hl=es&amp;q=" . $provincia . "," . $municipio . "," . $via . "," . $numero . "&amp;t=k&amp;z=18&amp;ie=UTF8&amp;iwloc=B&amp;output=embed";
        $_SESSION['map'] = $map;
        echo $Blade->run('catastro', ['hojacat' => $hojacat, 'numero' => $numero, 'name' => $_SESSION['name'],
            'parametros' => $parametros, 'map' => $map]);
    }

// Si el usuario ha pulsado en el enlace de la hoja catastral, entramos aquí
} else if (isset($_GET['hojacat'])) {

    $hojacat = $_GET['hojacat'];

    // Como recibimos la hoja catastral entera, hemos de dividirla en 2 partes para más adelante
    $pc1 = substr($hojacat, 0, 7);
    $pc2 = substr($hojacat, 7);
    $info = $_SESSION['info'];

    // Si $info es un array, entonces hay varias referencias catastral, hay que recorrerlas y quedarnos con las que coincidan la hoja catastral (pc1 y pc2).
    if (gettype($info) == 'array') {
        foreach ($info as $datos) {
            if ($datos->rc->pc1 == $pc1 && $datos->rc->pc2 == $pc2) {

                // Si ha coincidido, hemos de meter los siguientes datos necesarios en arrays, para luego mostrarlos en la vista
                $refcat[] = $datos->rc->pc1 . $datos->rc->pc2 . $datos->rc->car . $datos->rc->cc1 . $datos->rc->cc2;
                $via[] = $_SESSION['parametros'][3];
                $numero[] = $_SESSION['parametros'][4];
                $loint = $datos->dt->locs->lous->lourb->loint;
                $planta[] = $loint->pt;
                $puerta[] = $loint->pu;
                if (isset($loint->es)) {
                    $escalera[] = $loint->es;
                }
            }
        }
    } else {

        // Si $info es un string, implica que solo hay una referencia catastral, por que metemos cada dato en strings.
        $rc = $info->idbi->rc;
        $refcat = $rc->pc1 . $rc->pc2 . $rc->car . $rc->cc1 . $rc->cc2;
        $via = $_SESSION['parametros'][3];
        $numero = $_SESSION['parametros'][4];
        $loint = $info->dt->locs->lous->lourb->loint;
        $planta = $loint->pt;
        $puerta = $loint->pu;
        if (isset($loint->es)) {
            $escalera = $loint->es;
        }
    }
    if (isset($escalera)) {
        echo $Blade->run('catastro', ['refcat' => $refcat, 'via' => $via, 'numero' => $numero,
            'escalera' => $escalera, 'planta' => $planta, 'puerta' => $puerta, 'name' => $_SESSION['name'],
            'parametros' => $_SESSION['parametros'], 'map' => $_SESSION['map']]);
    } else {
        echo $Blade->run('catastro', ['refcat' => $refcat, 'via' => $via, 'numero' => $numero,
            'planta' => $planta, 'puerta' => $puerta, 'name' => $_SESSION['name'],
            'parametros' => $_SESSION['parametros'], 'map' => $_SESSION['map']]);
    }


// Si el usuario ha pulsado en el enlace de la referencia catastral, entramos aquí.
} else if (isset($_GET['refcat'])) {

    $refcat = $_GET['refcat'];

    // Hacemos una nueva consulta con el método Consulta_DNPRC con la referencia catastral que tenemos para que nos muestre sus datos asociados.
    $datos = $clienteSOAP->Consulta_DNPRC('', '', $refcat)->any;
    $datos_xml = new SimpleXMLElement($datos);
    $datos_json = json_decode(json_encode($datos_xml));

    $bi = $datos_json->bico->bi;

    // Metemos los datos que necesitamos en strings, aquí no será necesario usar nunca arrays ya que solo estamos consultando una referencia catastral.
    $localizacion = $bi->ldt;
    $clase = $bi->idbi->cn;
    $uso = $bi->debi->luso;
    $superficie = number_format($bi->debi->sfc, 0, ",", ".");

    // Consultamos la API Rest que hemos creado para obtener el precio del metro cuadrado por municipio de esta forma
    $municipio = $_SESSION['parametros'][1];
    $municipio = str_replace(' ', '%20', $municipio);
    $preciom2 = json_decode(file_get_contents("http://localhost:80/public/municipio/$municipio"), true)[0]['precio'];
    $precioest = number_format($bi->debi->sfc * $preciom2, 0, ",", ".");

    // Hemos de tener cuidado ya que algunas referencias catastrales no tienen definido un año de construcción.
    if (isset($bi->debi->ant)) {
        $año = number_format($bi->debi->ant, 0, ",", ".");
        echo $Blade->run('catastro', ['refcat' => $refcat, 'localizacion' => $localizacion, 'clase' => $clase,
            'uso' => $uso, 'superficie' => $superficie, 'año' => $año, 'name' => $_SESSION['name'],
            'parametros' => $_SESSION['parametros'], 'map' => $_SESSION['map'], 'precio' => $precioest]);
    } else {
        echo $Blade->run('catastro', ['refcat' => $refcat, 'localizacion' => $localizacion, 'clase' => $clase,
            'uso' => $uso, 'superficie' => $superficie, 'name' => $_SESSION['name'],
            'parametros' => $_SESSION['parametros'], 'map' => $_SESSION['map'], 'precio' => $precioest]);
    }
}
