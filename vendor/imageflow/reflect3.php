<?php
	/*
		-------------------------------------------------------------------
		Easy Reflections v3 by Richard Davey, Core PHP (rich@corephp.co.uk)
		Released 13th March 2007
        Includes code submissions from Monte Ohrt (monte@ohrt.com)
        -------------------------------------------------------------------
		You are free to use this in any product, or on any web site.
        I'd appreciate it if you email and tell me where you use it, thanks.
		Latest builds at: http://reflection.corephp.co.uk
        -------------------------------------------------------------------
		
		This script accepts the following $_GET parameters:
		
		img		        required	The source image to reflect
		height	        optional	Height of the reflection (% or pixel value)
        fade_start      optional    Start the alpha fade from whch value? (% value)
        fade_end        optional    End the alpha fade from whch value? (% value)
        cache           optional    Save reflection image to the cache? (boolean)
        tint            optional    Tint the reflection with this colour (hex)
	*/
    
	//	PHP Version sanity check
	if (version_compare('4.3.2', phpversion()) == 1)
	{
		echo 'This version of PHP is not fully supported. You need 4.3.2 or above.';
		exit();
	}
	
	//	GD check
	if (extension_loaded('gd') == false && !dl('gd.so'))
	{
		echo 'You are missing the GD extension for PHP, sorry but I cannot continue.';
		exit();
	}
	
    //    GD Version check
    $gd_info = gd_info();
    
    if ($gd_info['PNG Support'] == false)
    {
        echo 'This version of the GD extension cannot output PNG images.';
        exit();
    }
    
    if (ereg_replace('[[:alpha:][:space:]()]+', '', $gd_info['GD Version']) < '2.0.1')
    {
        echo 'GD library is too old. Version 2.0.1 or later is required, and 2.0.28 is strongly recommended.';
        exit();
    }

	//	Our allowed query string parameters

    //  To cache or not to cache? that is the question
    if (isset($_GET['cache']))
    {

        if ((int) $_GET['cache'] == 1)
        {

            $cache = true;
        }
        else
        {
            $cache = false;
        }
    }
    else
    {
        $cache = true;
    }
	
	//	img (the image to reflect)
	if (isset($_GET['img']))
	{
		$source_image = $_GET['img'];
		$source_image = str_replace('://','',$source_image);
		//$source_image = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $source_image;
        
        if (file_exists($source_image))
        {
            if ($cache)
            { 
                $cache_dir = dirname($source_image);
                $cache_base = basename($source_image);
                $cache_file = 'refl_' . md5($_SERVER['REQUEST_URI']) . '_' . $cache_base;
                $cache_path = $cache_dir . DIRECTORY_SEPARATOR . $cache_file;

                if (file_exists($cache_path) && filemtime($cache_path) >= filemtime($source_image))
                {
                    // Use cached image
                    $image_info = getimagesize($cache_path);
                    header("Content-type: " . $image_info['mime']);
                    readfile($cache_path);
                    exit();
                }
            }
        }
        else
        {
          echo 'Cannot find or read source image';
          exit();
        }
	}
	else
	{
		echo 'No source image to reflect supplied';
		exit();
	}
	
    //    tint (the colour used for the tint, defaults to white if not given)
    	
    if (isset($_GET['tint']) == false)
    {
        $red = 127;
        $green = 127;
        $blue = 127;
		
	

    }
    else
    {
        //    Extract the hex colour
        $hex_bgc = $_GET['tint'];
        
        //    Does it start with a hash? If so then strip it
        $hex_bgc = str_replace('#', '', $hex_bgc);
        
        switch (strlen($hex_bgc))
        {
            case 6:
                $red = hexdec(substr($hex_bgc, 0, 2));
                $green = hexdec(substr($hex_bgc, 2, 2));
                $blue = hexdec(substr($hex_bgc, 4, 2));
                break;
                
            case 3:
                $red = substr($hex_bgc, 0, 1);
                $green = substr($hex_bgc, 1, 1);
                $blue = substr($hex_bgc, 2, 1);
                $red = hexdec($red . $red);
                $green = hexdec($green . $green);
                $blue = hexdec($blue . $blue);
                break;
                
            default:
                //    Wrong values passed, default to white
                $red = 127;
                $green = 127;
                $blue = 127;
        }
    }

	//	height (how tall should the reflection be?)
	if (isset($_GET['height']))
	{
		$output_height = $_GET['height'];
		
		//	Have they given us a percentage?
		if (substr($output_height, -1) == '%')
		{
			//	Yes, remove the % sign
			$output_height = (int) substr($output_height, 0, -1);

			//	Gotta love auto type casting ;)
			if ($output_height == 100)
			{
                $output_height = "0.99";
			}
			elseif ($output_height < 10)
            {
                $output_height = "0.0$output_height";
            }
            else
			{
				$output_height = "0.$output_height";
			}
		}
		else
		{
			$output_height = (int) $output_height;
		}
	}
	else
	{
		//	No height was given, so default to 50% of the source images height
		$output_height = 0.50;
	}
	
	if (isset($_GET['fade_start']))
	{
		if (strpos($_GET['fade_start'], '%') !== false)
		{
			$alpha_start = str_replace('%', '', $_GET['fade_start']);
			$alpha_start = (int) (127 * $alpha_start / 100);
		}
		else
		{
			$alpha_start = (int) $_GET['fade_start'];
		
			if ($alpha_start < 1 || $alpha_start > 127)
			{
				$alpha_start = 80;
			}
		}
	}
	else
	{
		$alpha_start = 80;
	}

	if (isset($_GET['fade_end']))
	{
		if (strpos($_GET['fade_end'], '%') !== false)
		{
			$alpha_end = str_replace('%', '', $_GET['fade_end']);
			$alpha_end = (int) (127 * $alpha_end / 100);
		}
		else
		{
			$alpha_end = (int) $_GET['fade_end'];
		
			if ($alpha_end < 1 || $alpha_end > 0)
			{
				$alpha_end = 0;
			}
		}
	}
	else
	{
		$alpha_end = 0;
	}

	/*
		----------------------------------------------------------------
		Ok, let's do it ...
		----------------------------------------------------------------
	*/
	
	//	How big is the image?
	$image_details = getimagesize($source_image);
	
	if ($image_details === false)
	{
		echo 'Not a valid image supplied, or this script does not have permissions to access it.';
		exit();
	}
	else
	{
		$width = $image_details[0];
		$height = $image_details[1];
		$type = $image_details[2];
		$mime = $image_details['mime'];
	}
	
	//	Calculate the height of the output image
	if ($output_height < 1)
	{
		//	The output height is a percentage
		$new_height = $height * $output_height;
	}
	else
	{
		//	The output height is a fixed pixel value
		$new_height = $output_height;
	}	

	//	Detect the source image format - only GIF, JPEG and PNG are supported. If you need more, extend this yourself.
	switch ($type)
	{
		case 1:
			//	GIF
			$source = imagecreatefromgif($source_image);
			break;
			
		case 2:
			//	JPG
			$source = imagecreatefromjpeg($source_image);
			break;
			
		case 3:
			//	PNG
			$source = imagecreatefrompng($source_image);
			break;
			
		default:
			echo 'Unsupported image file format.';
			exit();
	}
	
	/*
		----------------------------------------------------------------
		Build the reflection image
		----------------------------------------------------------------
	*/
	



	//	We'll store the final reflection in $output. $buffer is for internal use.
	$output = imagecreatetruecolor($width, $new_height);
	$buffer = imagecreatetruecolor($width, $new_height);
    
    //  Save any alpha data that might have existed in the source image and disable blending
    imagesavealpha($source, true);

    imagesavealpha($output, true);
    imagealphablending($output, false);

    imagesavealpha($buffer, true);
    imagealphablending($buffer, false);

	//	Copy the bottom-most part of the source image into the output
	imagecopy($output, $source, 0, 0, 0, $height - $new_height, $width, $new_height);
	
	//	Rotate and flip it (strip flip method)
    for ($y = 0; $y < $new_height; $y++)
    {
       imagecopy($buffer, $output, 0, $y, 0, $new_height - $y - 1, $width, 1);
    }

	$output = $buffer;

	/*
		----------------------------------------------------------------
		Apply the fade effect
		----------------------------------------------------------------
	*/
	
	//	This is quite simple really. There are 127 available levels of alpha, so we just
	//	step-through the reflected image, drawing a box over the top, with a set alpha level.
	//	The end result? A cool fade.

	//	There are a maximum of 127 alpha fade steps we can use, so work out the alpha step rate

	$alpha_length = abs($alpha_start - $alpha_end);

    imagelayereffect($output, IMG_EFFECT_OVERLAY);

    for ($y = 0; $y <= $new_height; $y++)
    {
        //  Get % of reflection height
        $pct = $y / $new_height;

        //  Get % of alpha
        if ($alpha_start > $alpha_end)
        {
            $alpha = (int) ($alpha_start - ($pct * $alpha_length));
        }
        else
        {
            $alpha = (int) ($alpha_start + ($pct * $alpha_length));
        }
        
        //  Rejig it because of the way in which the image effect overlay works
        $final_alpha = 127 - $alpha;

        //imagefilledrectangle($output, 0, $y, $width, $y, imagecolorallocatealpha($output, 127, 127, 127, $final_alpha));
        imagefilledrectangle($output, 0, $y, $width, $y, imagecolorallocatealpha($output, $red, $green, $blue, $final_alpha));
    }

    /*
		----------------------------------------------------------------
		HACK - Build the reflection image by combining the source 
		image AND the reflection in one new image!
		----------------------------------------------------------------
	*/
		$finaloutput = imagecreatetruecolor($width, $height+$new_height);
		imagesavealpha($finaloutput, true);

		$trans_colour = imagecolorallocatealpha($finaloutput, 0, 0, 0, 127);
		imagefill($finaloutput, 0, 0, $trans_colour);

		imagecopy($finaloutput, $source, 0, 0, 0, 0, $width, $height);
		imagecopy($finaloutput, $output, 0, $height, 0, 0, $width, $new_height);
		$output = $finaloutput;

	/*
		----------------------------------------------------------------
		Output our final PNG
		----------------------------------------------------------------
	*/
	if (headers_sent())
	{
		echo 'Headers already sent, I cannot display an image now. Have you got an extra line-feed in this file somewhere?';
		exit();
	}
	else
	{
	    //	PNG
	    header("Content-type: image/png");
	    imagepng($output);

        // Save cached file
        if ($cache)
        {
            imagepng($output, $cache_path);
        }

		imagedestroy($output);
		exit();
	}
?>