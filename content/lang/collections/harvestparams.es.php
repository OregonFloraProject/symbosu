<?php
/*
------------------
Language: Español (Spanish)
------------------
*/

include_once('sharedterms.es.php');

$LANG['PAGE_TITLE'] = 'Par&aacute;metros de B&uacute;squeda de Colecciones';
$LANG['TAXON_HEADER'] = 'Criterios Taxonomicos';
$LANG['INCLUDE_SYNONYMS'] = 'Incluir Sin&oacute;nimos';
$LANG['BUTTON_NEXT_LIST'] = 'Mostrar Lista';
$LANG['BUTTON_NEXT_TABLE'] = 'Mostrar Tabla';
$LANG['SELECT_1-1'] = 'cualquier nombre';
$LANG['SELECT_1-2'] = 'Nombre Cientifico';
$LANG['SELECT_1-3'] = 'Familia Solamente';
$LANG['SELECT_1-4'] = 'Grupo Taxonomico';
$LANG['SELECT_1-5'] = 'Nombre Comun';
$LANG['SEPARATE_MULTIPLE'] = 'Separar m&uacute;ltiples con comas';
$LANG['LOCALITY_CRITERIA'] = 'Datos de la Localidad';
$LANG['COUNTRY'] = 'Pa&iacute;s';
$LANG['STATE'] = 'Estado/Provincia';
$LANG['COUNTY'] = 'Municipio';
$LANG['LOCALITY'] = 'Localidad';
$LANG['ELEV_INPUT_1'] = 'Elevaci&oacute;n (en metros)';
$LANG['ELEV_INPUT_2'] = 'a';
$LANG['LAT_LNG_HEADER'] = 'Latitud y Longitud';
$LANG['LL_BOUND_TEXT'] = 'Coordenadas extremas';
$LANG['LL_BOUND_NLAT'] = 'Latitud Norte';
$LANG['LL_BOUND_SLAT'] = 'Latitud Sur';
$LANG['LL_BOUND_WLNG'] = 'Longitud Oeste';
$LANG['LL_BOUND_ELNG'] = 'Longitud Este';
$LANG['LL_N_SYMB'] = 'N';
$LANG['LL_S_SYMB'] = 'S';
$LANG['LL_W_SYMB'] = 'O';
$LANG['LL_E_SYMB'] = 'E';
$LANG['LL_POLYGON_TEXT'] = 'Polygon (WKT footprint)';
$LANG['LL_P-RADIUS_TEXT'] = 'Punto-Radio';
$LANG['LL_P-RADIUS_LAT'] = 'Latitud';
$LANG['LL_P-RADIUS_LNG'] = 'Longitud';
$LANG['LL_P-RADIUS_RADIUS'] = 'Radio';
$LANG['LL_P-RADIUS_KM'] = 'Kilometros';
$LANG['LL_P-RADIUS_MI'] = 'Millas';
$LANG['MAP_AID'] = 'Definir Coordenadas';
$LANG['COLLECTOR_HEADER'] = 'Datos del Colector';
$LANG['COLLECTOR_LASTNAME'] = 'Apellido del Colector';
$LANG['TITLE_TEXT_2'] = 'Usar comas para separar varios t&eacute;rminos, por ejemplo: Guaymas, Hermosillo, Pitiquito; y guiones para delimitar rangos (usar espacio antes y despu&eacute;s del gui&oacute;n), por ejemplo: 3542,3602,3700 - 3750';
$LANG['COLLECTOR_NUMBER'] = "N&uacute;mero del Colector";
$LANG['COLLECTOR_DATE'] = 'Fecha de Colecta';
$LANG['TITLE_TEXT_3'] = 'Fecha determinada o fecha de inicio del per&iacute;odo';
$LANG['TITLE_TEXT_4'] = 'Fecha final del per&iacute;odo; dejar en blanco si busca para una fecha determinada';
$LANG['SPECIMEN_HEADER'] = 'Cat&aacute;logo de la Colecci&oacute;n';
$LANG['CATALOG_NUMBER'] = 'N&uacute;mero de Cat&aacute;logo';
$LANG['INCLUDE_OTHER_CATNUM'] = 'Includa todos n&uacute;meros de cat&eacute;logo y GUIDs';
$LANG['TYPE'] = 'Limitar a ejemplares tipo';
$LANG['HAS_IMAGE'] = 'Limitar a ejemplares con im&aacute;genes';
$LANG['HAS_GENETIC'] = 'Limitar a ejemplares con datos gen&eacute;ticos';
$LANG['HAS_COORDS'] = 'Limitar a ejemplares con coordenadas geogr&aacute;ficas';
$LANG['INCLUDE_CULTIVATED'] = 'Incluye ejemplares cultivadas/cautivas';
$LANG['TRAIT_HEADER'] = 'Criterios de Rasgos';
$LANG['TRAIT_DESCRIPTION'] = 'La selección de varios rasgos devolverá todos los registros con al menos uno de esos rasgos';

// For compatibility with OregonFlora
$LANG['GENERAL_TEXT_1'] = 'Ingrese uno o m&aacute;s de los criterios de consulta siguientes y haga click en el bot&oacute;n &quot;Search&quot; para ver los resultados.';
$LANG['TAXON_INPUT'] = 'Taxa:';
$LANG['GENERAL_TEXT_2'] = 'Incluir Sin&oacute;nimos del Thesaurus Taxonomico';
$LANG['GENERAL_TEXT_2_MAP'] = $LANG['INCLUDE_SYNONYMS'];
$LANG['TITLE_TEXT_1'] = $LANG['SEPARATE_MULTIPLE'];
$LANG['LOCALITY_HEADER'] = $LANG['LOCALITY_CRITERIA'];
$LANG['COUNTRY_INPUT'] = $LANG['COUNTRY'];
$LANG['STATE_INPUT'] = $LANG['STATE'];
$LANG['COUNTY_INPUT'] = $LANG['COUNTY'];
$LANG['LOCALITY_INPUT'] = $LANG['LOCALITY'];
$LANG['ASSOC_HOST_INPUT'] = 'Huésped:';
$LANG['LL_P-RADIUS_TITLE_1'] = 'Encuentra Coordenada';
$LANG['OTHER_CATNUM'] = 'Otro N&uacute;mero de Cat&aacute;logo:';

// For compatibility with OregonFlora
//Following used in mapboundingbox.php
$LANG['MBB_TITLE'] = 'Mapeador de Coordenadas';
$LANG['MBB_INSTRUCTIONS'] = 'Click para comenzar a dibujar y otra vez para terminar el rect&aacute;ngulo. Click sobre el bot&oacute;n Enviar para transferir Coordenadas.';
$LANG['MBB_NORTHERN'] = 'Latitud Norte';
$LANG['MBB_EASTERN'] = 'Longitud Este';
$LANG['MBB_SOUTHERN'] = 'Latitud Sur';
$LANG['MBB_WESTERN'] = 'Longitud Oeste';

// For compatibility with OregonFlora
//Following used in mappointradius.php
$LANG['MPR_TITLE'] = 'Mapeador de Coordenadas';
$LANG['MPR_INSTRUCTIONS'] = 'Click para capturar coordenadas. Click en el bot&oacute;n Enviar Coordenadas para transferir Coordenadas.';
$LANG['MPR_LAT'] = 'Latitud';
$LANG['MPR_LNG'] = 'Longitud';
$LANG['SUBMIT'] = 'Enviar';

?>
