	<?php
	
//	Output this header as early as possible
	header('Content-Type: text/plain; charset=utf-8');


//	Ensure no PHP errors are shown in the Ajax response
	@ini_set('display_errors', 0);


//	Load the Q2A base file which sets up a bunch of crucial functions
	require_once '../../as-include/as-base.php';
	as_report_process_stage('init_ajax');		

//	Get general Ajax parameters from the POST payload, and clear $_GET	
	as_set_request(as_post_text('as_request'), as_post_text('as_root'));	
	$output_dir = AS_BLOBS_DIRECTORY."featured/";
	$valid_file_types = array(
		"image/gif",
		"image/png",
		"image/jpeg",
		"image/pjpeg",
	);
	if(isset($_FILES["myfile"]))
	{
		$ret = array();

		$error =$_FILES["myfile"]["error"];
		//You need to handle  both cases
		//If Any browser does not support serializing of multiple files using FormData() 
		if(!is_array($_FILES["myfile"]["name"]) && ($error==0) ) //single file
		{
			$fileName = $_FILES["myfile"]["name"];
			$dir_name = md5(time()) . '/';
			$output_dir = $output_dir . $dir_name;
			if (!file_exists($output_dir)) {
				mkdir($output_dir, 0777, true);
			}
			if ($_FILES["myfile"]["size"] > 1024*1000){
				$error = 'File Size Error: File is too big';
			}elseif(!(in_array($_FILES["myfile"]["type"], $valid_file_types))){
				$error = 'File Type Invalid: Upload an image, other file types are not acceptable';
			}else{
				//rename file if a file with same name exists
				$exts = substr(strrchr($fileName,'.'),1);
				if (file_exists($output_dir.$fileName)) {
					$withoutExt = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
					for($i=2; file_exists($output_dir.$withoutExt.'-'.$i.'.'.$exts); $i++);
					$fileName = $withoutExt.'-'.$i.'.'.$exts;
				}
				// put file in server
				move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$fileName);
				// create thumbnail
				list($originalWidth, $originalHeight) = getimagesize($output_dir.$fileName);
				$new_width = 320;
				$new_height = floor(($originalWidth/$new_width)*$originalHeight);

				// create thumbnail for lists
				$exts = substr(strrchr($fileName,'.'),1);
				$withoutExt = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
				$thumbnailName = $withoutExt.'_.'.$exts;
				createThumbs($output_dir.$fileName,$output_dir.$thumbnailName,320);
				
				$ret[]= $dir_name.$fileName;
				echo json_encode($ret);
				die();
			}
		}
		// if there was an error and file was not successfull uploaded return the error.
		echo json_encode($error);		
	}

	
function createThumbs( $pathToImages, $pathToThumbs, $thumbWidth )
{
	$quality = 100;
	$info = getimagesize($pathToImages);

	// load image and get image size
	if ( $info[2] == IMAGETYPE_JPEG) {
		$image = imagecreatefromjpeg("{$pathToImages}");
	} elseif ( $info[2] == IMAGETYPE_GIF) {
		$image = imagecreatefromgif("{$pathToImages}");
	} elseif ( $info[2] == IMAGETYPE_PNG) {
		$image = imagecreatefrompng("{$pathToImages}");
	}
	
	$width = imagesx( $image );
	$height = imagesy( $image );
	if($width<=$thumbWidth){
		copy($pathToImages,$pathToThumbs);
		return true;
	}
	// calculate thumbnail size
	$new_width = $thumbWidth;
	$new_height = floor( $height * ( $thumbWidth / $width ) );
	
	// create a new temporary image
	$image_resized = imagecreatetruecolor( $new_width, $new_height );

	if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
		$transparency = imagecolortransparent($image);
		$palletsize = imagecolorstotal($image);
		
		if ($transparency >= 0 && $transparency < $palletsize) {
			$transparent_color = imagecolorsforindex($image, $transparency);
			$transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
			imagefill($image_resized, 0, 0, $transparency);
			imagecolortransparent($image_resized, $transparency);
		}
		elseif ($info[2] == IMAGETYPE_PNG) {
			imagealphablending($image_resized, false);
			$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
			imagefill($image_resized, 0, 0, $color);
			imagesavealpha($image_resized, true);
		}
    }

	// copy and resize old image into new image
	imagecopyresized( $image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

	// save thumbnail into a file
    if ( $info[2] == IMAGETYPE_GIF) {
		imagegif($image_resized, "{$pathToThumbs}", $quality);
	} elseif ( $info[2] == IMAGETYPE_JPEG) {
		imagejpeg($image_resized, "{$pathToThumbs}", $quality);
	} elseif ( $info[2] == IMAGETYPE_PNG) {
		$quality = 9 - (int)((0.9*$quality)/10.0);
		imagepng($image_resized, "{$pathToThumbs}", $quality);
	}
    

    return true;
}


	
?>