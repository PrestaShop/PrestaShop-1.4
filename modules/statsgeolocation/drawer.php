<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
$img_path="./img/map.png";

function loadBaseImage($img_path)
{
	$img_size = getimagesize($img_path);
	$img_png = imagecreatefrompng($img_path); if (!$img_png) exit(1);

	$img_tc = imagecreatetruecolor($img_size[0], $img_size[1]);
	imagealphablending($img_tc, false);
	imagesavealpha($img_tc, true);
	imagecopy($img_tc, $img_png, 0, 0, 0, 0, $img_size[0], $img_size[1]);
	imagedestroy($img_png);
	
	if (function_exists('imageantialias'))
		imageantialias($img_tc, true);
	return ($img_tc);
}

function drawImage($image)
{
	header("Content-type: image/png");
	imagepng($image);
	imagedestroy($image);
}

function drawCircle($image, $x, $y, $size)
{
	$color = imagecolorallocate($image, 255, 122, 56);
	imagefilledellipse($image, $x, $y, $size, $size, $color); 
}

function drawCircles($image)
{
	$max = 12;
	$min = 2;
	$gap = ($max - $min);
	$total = getTotalElements();
	$result = getCoords();
	
	foreach ($result as $row)
		drawCircle($image, $row['x'], $row['y'], $min + ($gap * ($row['total'] / $total)));
}

function getTotalElements()
{
	$result = Db::getInstance()->ExecuteS('SELECT COUNT(`id_address`) as total FROM `'._DB_PREFIX_.'address` WHERE deleted = 0 AND id_customer IS NOT NULL AND id_customer != 0');
	return (isset($result[0]) ? $result[0]['total'] : 0);
}
	
function getCoords()
{
	return (Db::getInstance()->ExecuteS('SELECT `x`, `y`, COUNT(`id_address`) AS total 
								FROM `'._DB_PREFIX_.'address` a
								LEFT JOIN `'._DB_PREFIX_.'location_coords` lc ON lc.`id_country`=a.`id_country`
								WHERE deleted = 0 AND id_customer IS NOT NULL AND id_customer != 0
								GROUP BY a.`id_country`
								ORDER BY `total` DESC'));
}

$image = loadBaseImage($img_path);
drawCircles($image);
drawImage($image);
?>
