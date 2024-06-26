<?php
function dep($data, $tit = null)
{
    $date = date('Y-m-d H:i:s');
    $format = print_r("<div><small>* BEG Debug [{$tit}] > $date | -> ");
    if (isset($data)) {
        $format .= print_r('<pre>');
        $format .= print_r($data);
        $format .= print_r('</pre>');
    } else
        $format .= print_r(' *null* ');
    $format = print_r("<- | END Debug ></small></div>");
    return $format;
}
function getImageNamesFromDirectory($directorio, $shuffle = false): array
{
    $patrones = ['*.jpg', '*.png', '*.gif', '*.webp'];
    $nombresDeImagenes = [];
    $directorioServer = rootM . $directorio;
    $directorioNav = routeM . $directorio;

    foreach ($patrones as $patron) {
        // glob() busca archivos que coinciden con el patrón
        foreach (glob($directorioServer . '/' . $patron) as $archivo) {
            // basename() obtiene el nombre del archivo desde la ruta completa
            $nombresDeImagenes[] = $directorioNav . basename($archivo);
        }
    }
    if ($shuffle) {
        shuffle($nombresDeImagenes);
    }
    return $nombresDeImagenes;
}

function get_config($section, $group, $string = null)
{
    // Verificar si la sección y el grupo existen.
    if (isset(cfg[$section][$group])) {
        // Si se ha proporcionado un valor para $string, buscar ese valor específico.
        if ($string !== null) {
            // Devuelve el valor específico si existe, de lo contrario devuelve null.
            return cfg[$section][$group][$string] ?? null;
        } else {
            // Si $string no se proporciona, devuelve todo el grupo como un array.
            return cfg[$section][$group];
        }
    } else {
        // Devuelve null si la sección o el grupo no existen.
        return null;
    }
}

function removeSpecialChars(string $texto): string
{
    return preg_replace('/[^a-zA-Z0-9]/', '', $texto);
}

if (!function_exists('startConfigs')) {
    function startConfigs(array $configs)
    {
        $currentTime = time(); // Tiempo actual en segundos.
        $configLoadTime = $_SESSION["cfg"]["load_time"] ?? 0; // Tiempo en el que la configuración fue cargada por última vez.
        $timeElapsed = ($currentTime - $configLoadTime) > ($_ENV["TIME_RELOAD_CONFIG"] ?? 60); // Verifica si han pasado más de 5 minutos.

        $configVersion = $_SESSION["cfg"]["version"] ?? null;
        $appVersion = $_ENV['VERSION'] ?? null;
        $isVersionChange = ($configVersion != $appVersion);

        // Verifica si es necesario actualizar la configuración debido a un cambio de versión o porque el tiempo de vida útil ha expirado.
        if ($isVersionChange || $timeElapsed) unset($_SESSION["cfg"]);

        foreach ($configs as $name) {
            // Recarga la configuración si es necesario.
            if (!isset($_SESSION["cfg"][$name]) || $isVersionChange || $timeElapsed) {
                $conf = parse_ini_file(root['c'] . "config.d/{$name}.ini", TRUE);
                $configEnd = null;
                foreach ($conf as $x => $xval) {
                    foreach ($xval as $y => $yval)
                        $configEnd[$x][$y] = $yval;
                }
                $_SESSION["cfg"][$name] = $configEnd;
            }
        }

        // Actualiza la versión de la configuración y el tiempo de carga.
        $_SESSION["cfg"]['version'] = $appVersion;
        $_SESSION["cfg"]['load_time'] = $currentTime; // Registra el tiempo actual como el nuevo tiempo de carga.

        return $_SESSION["cfg"];
    }
}


function getRealIpAddress(): string
{
    $ipAddress = '';

    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    }
    // check for IP passed from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Check if multiple IP addresses exist in header
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipList as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $ipAddress = $ip;
                break;
            }
        }
    }
    // check for remote address
    else {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }

    return $ipAddress;
}

function vImg($ruta, $nombre, $thumb = TRUE, $pthumb = 't_', $retHtml = [])
{ //v1.5
    //$ruta. Ruta o subcarpeta definida dentro de la RAIZi (carpeta de imagenes)
    //$nombre. Nombre del Archivo
    //$thumb. TRUE o FALSE en caso de querer recuperar thumb
    //$pthumb PREFIJO de Thumb
    //RAIZ must be named RAIZ0 depends the root folder
    $imgRet['n'] = route['i'] . cfg['sys']['noimage']; //'images/struct/no_image.jpg';
    $imgRet['t'] = $imgRet['n'];
    $imgRet['s'] = FALSE; //Verify if file exist is default FALSE
    if ($nombre) {
        //echo '<hr>RAIZ. '.RAIZ.$ruta.$nombre;
        //echo '<hr>$RAIZ. '.$RAIZ.$ruta.$nombre;
        if (file_exists(rootM . $ruta . $nombre)) {
            $imgRet['s'] = TRUE; //FILE EXIST RETURN TRUE AND ALL DATA (link normal, link thumb, file name original)
            $imgRet['f'] = $nombre;
            $imgRet['n'] = routeM . $ruta . $nombre;
            $imgRet['t'] = $imgRet['n'];
            if ($thumb == TRUE) {
                if (file_exists(rootM . $ruta . $pthumb . $nombre)) {
                    $imgRet['t'] = routeM . $ruta . $pthumb . $nombre;
                }
            }
        }
    }
    //Direct Return HTML Code *********** TERMINAR ESTE CODIGO
    if ($retHtml) {
        foreach ($retHtml as $key => $valor) {
            if ($key != 'tip')
                $paramCode = ' ' . $key . ' = ' . '"' . $valor . '"';
        }
        switch ($retHtml['tip']) {
            case 'imgn':
                $imgRet['code'] = '<img src="' . $imgRet['n'] . '" ' . $paramCode . '>';
                break;
            case 'imgt':
                $imgRet['code'] = '<img src="' . $imgRet['t'] . '" ' . $paramCode . '>';
                break;
            case 'aimg':
                $imgRet['code'] = '<a href="' . $imgRet['n'] . '" ' . $paramCode . '><img src="' . $imgRet['t'] . '"></a>';
                break;
        }
    }
    return $imgRet;
}

function changeDateEnglishToSpanish($fecha = null, $option = "all")
{
    if (!$fecha) $fecha = date("l, j \\d\\e F \\d\\e Y");
    $nuevafecha = null;
    $spanish_days = array(
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    );

    $spanish_months = array(
        'January' => 'enero',
        'February' => 'febrero',
        'March' => 'marzo',
        'April' => 'abril',
        'May' => 'mayo',
        'June' => 'junio',
        'July' => 'julio',
        'August' => 'agosto',
        'September' => 'septiembre',
        'October' => 'octubre',
        'November' => 'noviembre',
        'December' => 'diciembre'
    );
    switch ($option) {
        case "day":
            $nuevafecha = strtr($fecha, $spanish_days);
            break;
        case "month":
            $nuevafecha = strtr($fecha, $spanish_months);
            break;
        case "all":
            $nuevafecha = strtr($fecha, $spanish_days);
            $nuevafecha = strtr($nuevafecha, $spanish_months);
            break;
    }
    return $nuevafecha;
}
