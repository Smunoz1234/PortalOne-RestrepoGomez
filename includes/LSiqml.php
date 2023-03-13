<?php
//include_once('funciones.php');
function LSiqml($cad)
{
    $search = array("'", ";", "..", "=", "*", "?", "¿", "&", "_", "\\", "\<", "\>", "<script>", "</script>", "<", ">", "\"\"", "\"");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);
    return (trim(utf8_decode($cad_clear)));
}
function LSiqmlLogin($cad)
{
    $search = array("'", "\\", "\<", "\>", "<script>", "</script>", "<", ">", "\"\"", "\"");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);
    return (trim(utf8_decode($cad_clear)));
}

/**
 * Quita caracteres extraños de las observaciones.
 */
function LSiqmlObs($cad)
{
    $search = array("'", "<script>", "</script>", "´", "¨", "\\");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);

    // Código anterior, Ing. Ameth
    //$originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    //$modificadas = 'AAAAAAACEEEEIIIIDNOOOOOOUUUUYbsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    
    //$cad_clear = utf8_decode($cad_clear);
    //$cad_clear = strtr($cad_clear, $originales, $modificadas);
    //$cad_clear=str_replace("Ñ",'N',$cad_clear);
    //$cad_clear=str_replace("ñ",'n',$cad_clear);
    //$cad_clear = preg_replace("/[\r\n|\n|\r]+/", " ", $cad_clear);

    // Código nuevo, Stiven Muñoz Murillo
    $no_permitidas = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "À", "Ã", "Ì", "Ò", "Ù", "Ã™", "Ã ", "Ã¨", "Ã¬", "Ã²", "Ã¹", "ç", "Ç", "Ã¢", "ê", "Ã®", "Ã´", "Ã»", "Ã‚", "ÃŠ", "ÃŽ", "Ã”", "Ã›", "ü", "Ã¶", "Ã–", "Ã¯", "Ã¤", "«", "Ò", "Ã", "Ã„", "Ã‹");
    $permitidas = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E");
    $cad_clear = str_replace($no_permitidas, $permitidas, $cad_clear);
    
    return ($cad_clear);
}
function LSiqmlValor($cad)
{
    $search = array("$", ",", ".");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);
    return (trim($cad_clear));
}
function LSiqmlValorDecimal($cad)
{
    $search = array("$", ",");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);
    return (trim($cad_clear));
}
function LSiqmlName($cad)
{
    $search = array("'", ";", "..", "=", "*", "?", "¿", "&", "\<", "\>", "<script>", "</script>", "<", ">", "\"\"", "\"");
    $replace = "";
    $cad_clear = str_ireplace($search, $replace, $cad);
    return (trim(utf8_decode($cad_clear)));
}
function LSiqmlSaltos($cad)
{
    $string = preg_replace("/[\r\n|\n|\r]+/", " ", $cad);
    return (trim($string));
}
