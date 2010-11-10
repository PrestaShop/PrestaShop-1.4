<?php
//header('Content-Type: text/html; charset=iso-8859-15');
function ote_accent($str){

$str = str_replace("'", " ", $str);

$str = utf8_decode($str);

$ch = strtr($str,

      'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝאבגדהוחטיךכלםמןנעףפץצשתûü‎ÿ',

      'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');

return utf8_encode($ch);

}
