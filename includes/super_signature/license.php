<?php
$SignWidth=300;
$SignHeight=200;
$SignThick=4;
$SignBackGround;
$SignPenColor;
$SignTransparent=False;
$SignPoints=0;
$SignControl="";

function GetSignatureImageSmooth($signDataSmooth)
{
	if (strlen($signDataSmooth) > 0)
	{
		$signDataSmooth = str_replace('data:image/png;base64,', '', $signDataSmooth);
		$signDataSmooth = str_replace(' ', '+', $signDataSmooth);
		$data = base64_decode($signDataSmooth);

		$im = imagecreatefromstring($data);

		return $im;
	}

	return null;
}

function GetSignatureImage($signData)
{
	$base64DecData = base64_decode($signData);


	$exploded_sign = explode(';', $base64DecData);

	$explode_firstRow = explode(',', $exploded_sign[0]);


	if(count($explode_firstRow) == 8)
	{
		// process ahead if there are enough data in first input

		$SignBackGround = Html2RGB($explode_firstRow[1]);
		$SignWidth = $explode_firstRow[3];
		$SignHeight = $explode_firstRow[4];
		$SignTransparent = strtoupper($explode_firstRow[5]);
		$SignPoints = (integer)$explode_firstRow[6];
		$SignControl = $explode_firstRow[7];

		$im = imagecreatetruecolor((int)$SignWidth,(int)$SignHeight);

		$colBack = imagecolorallocate($im, (int)$SignBackGround[0], (int)$SignBackGround[1], (int)$SignBackGround[2]);

		imagefill($im, 0, 0, $colBack);

		if($SignTransparent == "TRUE")
		{
			imagecolortransparent($im, $colBack);
		}

		// Now get rest of points


		for ( $counter = 1; $counter < count($exploded_sign); $counter ++)
		{
			if(strlen($exploded_sign[$counter]) > 0)
			{
				// Keep processing points
				$exploded_PointData = explode(" ", trim($exploded_sign[$counter]));

				$exploded_FirstPointData = explode(',', $exploded_PointData[0]);

				$SignThick = $exploded_FirstPointData[0];
				$SignPenColor = Html2RGB($exploded_FirstPointData[1]);

				$penColor = imagecolorallocate($im, (int)$SignPenColor[0], (int)$SignPenColor[1], (int)$SignPenColor[2]);


				// Now run loop for rest of the points

				if(count($exploded_PointData) == 2)
				{
					$coXY = explode(',', trim($exploded_PointData[1]));
					ImageFilledArc($im, (int)$coXY[0], (int)$coXY[1], (int)(2 * $SignThick), (int)(2 * $SignThick), 0, 360, $penColor, IMG_ARC_PIE);
				}
				else
				{
					for ( $Incounter = 1; $Incounter < count($exploded_PointData) - 1; $Incounter ++)
					{
						$coXY = explode(',', trim($exploded_PointData[$Incounter]));
						$coXY2 = explode(',', trim($exploded_PointData[$Incounter + 1]));

						imgdrawLine($im, $coXY[0], $coXY[1], $coXY2[0], $coXY2[1], $penColor, $SignThick);
						imgdrawLine($im, $coXY[0], $coXY[1], $coXY2[0], $coXY2[1], $penColor, $SignThick + 1);

					}
				}
			}
		}

		return $im;
	}

	return null;
}

function imgdrawLine($image,$x0, $y0,$x1, $y1,$color,$radius)
{
	if($x0==null || $y0 == null)
		return;

	if($x1==null || $y1 == null)
		return;

	$radius = abs($radius / 2);
	$f = 1 - $radius;
	$ddF_x= 1;
	$ddF_y = -2 * $radius;
	$x= 0;
	$y = $radius;
	imageline($image,(int)$x0, (int)($y0 + $radius),(int)$x1, (int)($y1 + $radius),(int)$color);
	imageline($image,(int)$x0, (int)($y0 - $radius),(int)$x1, (int)($y1 - $radius),(int)$color);
	imageline($image,(int)($x0 + $radius), (int)$y0,(int)($x1 + $radius), (int)$y1,(int)$color);
	imageline($image,(int)($x0 - $radius), (int)$y0,(int)($x1 - $radius), (int)$y1,(int)$color);

	while($x< $y)
	{
		if($f >= 0)
		{
			$y--;
			$ddF_y += 2;
			$f += $ddF_y;
		}
		$x++;
		$ddF_x+= 2;
		$f += $ddF_x;
		imageline($image,$x0 + $x, (int)($y0 + $y),$x1 + $x, (int)($y1+ $y),(int)$color);
		imageline($image,$x0 - $x, (int)($y0 + $y),$x1 - $x, (int)($y1 + $y),(int)$color);
		imageline($image,$x0 + $x, (int)($y0 - $y),$x1 + $x, (int)($y1 - $y),(int)$color);
		imageline($image,$x0 - $x, (int)($y0 - $y),$x1 - $x, (int)($y1 - $y),(int)$color);
		imageline($image,(int)($x0 + $y), $y0 + $x,(int)($x1 + $y), $y1 + $x,(int)$color);
		imageline($image,(int)($x0 - $y), $y0 + $x,(int)($x1 - $y), $y1 + $x,(int)$color);
		imageline($image,(int)($x0 + $y), $y0 - $x,(int)($x1 + $y), $y1 - $x,(int)$color);
		imageline($image,(int)($x0 - $y), $y0 - $x,(int)($x1 - $y), $y1 - $x,(int)$color);

	}
}

function Html2RGB($color)
{
	if ($color[0] == '#')
		$color = substr($color, 1);

	if (strlen($color) == 6)
		list($r, $g, $b) = array($color[0].$color[1],
			$color[2].$color[3],
			$color[4].$color[5]);
	elseif (strlen($color) == 3)
		list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	else
		return false;

	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array($r, $g, $b);
}
