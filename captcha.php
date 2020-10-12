<?php
	/* Settings */
	$size = 15;  				/* font size */
	$position = 0.5; 			/* spacing */
	$psize = $size*0.6; 		/* upper index size */
	$font = "./7.ttf"; 		
	$maxpower = 3; 				/* maximum power of polynomial */
	$minvars = 2; 				/* minimum number of variables */
	$maxvars = 3; 				/* maximum number of variables */

	/* Randomize */
	list($usec, $sec) = explode(' ', microtime());
	$mt = (float) $sec + ((float) $usec * 100000);
	srand($mt);

	/* Generate polynomial */
	$letters = array();
	for ($i=ord("a");$i<=ord("z");$i++) {
		$letter = chr($i);
		if ($letter == "o" || $letter == "l" || $letter == "e" || $letter == "i") { continue; }
		$letters[] = $letter;
	}
	$parts = array();
	$cnt = rand($minvars,$maxvars);
	for ($i=0;$i<$cnt;$i++) { /* all variables */
		$idx = rand(0,count($letters)-1);
		$letter = $letters[$idx];
		
		for ($j=$maxpower;$j>=1;$j--) { /* all powers */
			if (rand(1,2) == 1) { continue; }
			$modifier = rand(1,9);
			if (rand(1,2) == 1) { $modifier *= -1; }
			$parts[] = array("letter"=>$letter,"modifier"=>$modifier,"power"=>$j);
		}
		
		unset($letters[$idx]);
		$new = array();
		foreach ($letters as $l) { $new[] = $l; }
		$letters = $new;
	}
	
	$power = (count($parts) ? $parts[0]["power"] : 1);
	$solution = (count($parts) ? $parts[0]["modifier"] : 0);
	$variable = (count($parts) ? $parts[0]["letter"] : $letters[rand(0,count($letters)-1)]);
	
	for ($i=1;$i<=$power;$i++) { $solution *= $i; } /* adjust solution by product of powers */
	shuffle($parts);
	
	if (!count($parts) || rand(1,2)==1) { /* constant part */
		$modifier = rand(1,9);
		if (rand(1,2) == 1) { $modifier *= -1; }
		$parts[] = array("letter"=>"", "modifier"=>$modifier, "power"=>1);
	}
	
	/* Initialize image */
	$captcha = imageCreate(1000,50);
	$background = imageColorAllocate($captcha, 235, 235, 255);
	$black = imageColorAllocate($captcha, 0, 0, 0);
//	imageColorTransparent($captcha, $background);

	/* Draw formula */
	if (rand(1,2) == 1) { /* minus before formula */
		$solution *= -1;
		imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, "–");
		$position += 0.7;
	}
	
	/* Partials */
	imagettftext($captcha, $size, 0, (($position+0.25)*25), 17, $black, $font, "∂");
	if ($power != 1) { 
		imagettftext($captcha, $psize, 0, (($position+0.7)*25), 10, $black, $font, $power);
	}
	imagettftext($captcha, $size, 0, (($position)*25), 30, $black, $font, "―");
	imagettftext($captcha, $size, 0, (($position+0.4)*25), 30, $black, $font, "―"); /* hack for monospace */
	imagettftext($captcha, $size, 0, (($position+0.65)*25), 30, $black, $font, "―"); /* hack for monospace */
	imagettftext($captcha, $size, 0, ($position*25), 43, $black, $font, "∂");
	$position += 0.5;
	imagettftext($captcha, $size, 0, ($position*25), 43, $black, $font, $variable);
	if ($power != 1) { 
		imagettftext($captcha, $psize, 0, (($position+0.5)*25), 36, $black, $font, $power);
	}
	$position += 0.7;
	
	imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, "(");
	$position += 0.5;
	for ($i=0;$i<count($parts);$i++) {
		$p = $parts[$i];
		$modifier = $p["modifier"];
		$letter = $p["letter"];
		$power = $p["power"];
		$pre = false;
		if ($i || $modifier < 0) { $pre = ($modifier > 0 ? "+" : "–"); }
		if ($pre) {
			imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, $pre);
			$position += 0.75;
		}
		if (abs($modifier) != 1 || !$letter) {
			imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, abs($modifier));
			$position += 0.6;
		}
		imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, $letter);
		if ($power != 1) {
			imagettftext($captcha, $psize, 0, (($position+0.5)*25), 20, $black, $font, $power);
			$position += 0.1;
		}
		if ($letter) { $position += 0.75; }
		
	}
	/* End */
	imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, ")");
	$position++;
	imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, "=");
	$position++;
	imagettftext($captcha, $size, 0, ($position*25), 30, $black, $font, $solution);
	$position+=strlen($solution)/2 + 0.5;
	
	$c_old = $captcha;
	$captcha = imagecreate($position*25, imagesy($c_old));
	imagecopy($captcha, $c_old, 0, 0, 0, 0, imagesx($captcha), imagesy($captcha));

	/* Cache control */
	header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	/* Display the image */
	header('Content-type: image/gif');
	imageGif($captcha);
	imageDestroy($captcha);
?>

