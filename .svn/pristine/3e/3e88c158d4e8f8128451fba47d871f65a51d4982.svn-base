<?php

namespace Ydzy\FileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ydzy\FileBundle\Entity\images;

class DefaultController extends Controller
{
    public function CreateAction(Request $request)
    {
        return new Response("API CreateAction");
    }

    /***********************************
	 *
	 * Textimage::uploadImage
	 *
	 ***********************************/
	public function uploadImageAction(Request $request)
	{
		// consts
		$uploadedDirPrefix = '/upload/';
		$origFilePrefix = '0';
		$newFilePrefix = '1';
		$newFilePrefixThumbnail = '2';
		$imageWidth = 640;
		$thumbnailWidth = 200;

		$uploadedFile = $request->files->get('imageFile');

		if (!$uploadedFile->isValid()){
			return new Response('', 415);
		}

		$uploadedFileName = $uploadedFile->getClientOriginalName();
		$filesize = $uploadedFile->getClientSize();
		$fileinfo = GetImageSize($uploadedFile);
		//return new Response($fileinfo[1]/$fileinfo[0]);
		$filewidth = $fileinfo[0]; 
		$fileheight = $fileinfo[1];
		$hashName = md5_file($uploadedFile->getPathname());
		$imageType = exif_imagetype($uploadedFile->getPathname());
		if (!$imageType){
			return new Response('', 415);
		}
		
		$extName = '.jpg';
		switch ($imageType){
			case IMAGETYPE_GIF:
				$extName = '.gif';
				break;
			case IMAGETYPE_JPEG:
				$extName = '.jpg';
				break;
			case IMAGETYPE_PNG:
				$extName = '.png';
				break;
			default:
				return new Response('', 415);
		}

		$baseDir = preg_replace('/app$/si', 'web' . $request->request->get('folder'), $this->get('kernel')->getRootDir());
		$uploadedDir = chunk_split($hashName, 3, '/');
		
		$origImage = $uploadedDirPrefix.$uploadedDir.$origFilePrefix.$extName;
		$newImage = $uploadedDirPrefix.$uploadedDir.$newFilePrefix.$extName;
		$newThumbnail = $uploadedDirPrefix.$uploadedDir.$newFilePrefixThumbnail.$extName;

		if (!is_dir($baseDir.$uploadedDirPrefix.$uploadedDir)){
			//return new Response($baseDir.$uploadedDirPrefix.$uploadedDir);
			if (!mkdir($baseDir.$uploadedDirPrefix.$uploadedDir, 0755, true)){
				return new Response('faild to create folder!', 403);
			}
		}
		$uploadedFile->move($baseDir.$uploadedDirPrefix.$uploadedDir, $origFilePrefix.$extName);
		//echo "../..".$origImage;
		//$waterImage="./logo.png";//水印图片路径     
		//$this->imageWaterMark("http://jx.card-books.com".$origImage,5,"","万吉工程机械交易网",5,"#cccccc");
		//$location = $baseDir.$uploadedDirPrefix.$uploadedDir.$origFilePrefix.$extName;
		//$this->imageWaterMark($location,5,$waterImage);
		if (!$this->make_thumb($baseDir.$uploadedDirPrefix.$uploadedDir.$origFilePrefix.$extName,
							$baseDir.$uploadedDirPrefix.$uploadedDir.$newFilePrefix.$extName,
							$imageWidth)){
								return new Response('', 500);
							}

		if (!$this->make_thumb($baseDir.$uploadedDirPrefix.$uploadedDir.$origFilePrefix.$extName,
							$baseDir.$uploadedDirPrefix.$uploadedDir.$newFilePrefixThumbnail.$extName,
							$thumbnailWidth)){
								return new Response('', 500);
							}

		$images = new images();
		$images->setOriginalImage($origImage);
		$images->setUrl($newImage);
		$images->setMd5($hashName);
		//$images->setFilesize($filesize);
		$images->setThumbnail($newThumbnail);
		$images->setCreateDate(new \DateTime("now"));
		$images->setStatus(1);
		$em = $this->getDoctrine()->getEntityManager();
		$em->persist($images);
		$em->flush();

		$json_r = array(
					'ID' => $images->getId(),
					'original' => $images->getOriginalImage(),
					'Url' => $images->getUrl(),
					'Thumbnail' => $images->getThumbnail(),
        		    'filesize'=>$filesize,
        		    'filename'=>$uploadedFileName,
		            'fileheight'=>$fileheight,
		            'filewidth'=>$filewidth
				);

		$response = new Response(stripslashes(json_encode($json_r)));
		//$response->headers->set('content-type','application/json');
        return $response;
		//return new JsonResponse($json_r);
	}
	
	/***********************************
	 *
	 * Internal::make_thumb
	 *
	 ***********************************/
	private function make_thumb($src, $dest, $desired_width) {
		$imageType = exif_imagetype($src);

		/* read the source image */
		$source_image = null;
		switch ($imageType){
			case IMAGETYPE_JPEG:
				$source_image = imagecreatefromjpeg($src);
				break;
			case IMAGETYPE_PNG:
				$source_image = imagecreatefrompng($src);
				break;
			case IMAGETYPE_GIF:
				$source_image = imagecreatefromgif($src);
				break;
			default:
				return false;
		}
		
		if (!$source_image)
			return false;
			
		$width = imagesx($source_image);
		$height = imagesy($source_image);

		/* find the "desired height" of this thumbnail, relative to the desired width  */
		$desired_height = floor($height * ($desired_width / $width));

		/* create a new, "virtual" image */
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

		/* copy source image at a resized size */
		if (!$virtual_image)
			return false;
			
		if (!imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height))
			return false;

		/* create the physical thumbnail image to its destination */
		switch ($imageType){
			case IMAGETYPE_JPEG:
				return imagejpeg($virtual_image, $dest);
			case IMAGETYPE_PNG:
				return imagepng ($virtual_image, $dest);
			case IMAGETYPE_GIF:
				return imagegif($virtual_image, $dest);
			default:
				return false;
		}

		return true;
	}
	
	   /*    
		* 功能：PHP图片水印 (水印支持图片或文字)    
		* 参数：    
		*     $groundImage   背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式；    
		*     $waterPos     水印位置，有10种状态，0为随机位置；    
		*                 1为顶端居左，2为顶端居中，3为顶端居右；    
		*                 4为中部居左，5为中部居中，6为中部居右；    
		*                 7为底端居左，8为底端居中，9为底端居右；    
		*     $waterImage     图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；    
		*     $waterText     文字水印，即把文字作为为水印，支持ASCII码，不支持中文；    
		*     $textFont     文字大小，值为1、2、3、4或5，默认为5；    
		*     $textColor     文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；    
		*    
		* 注意：Support GD 2.0，Support FreeType、GIF Read、GIF Create、JPG 、PNG    
		*     $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。    
		*     当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。    
		*     加水印后的图片的文件名和 $groundImage 一样。    
		* 作者：longware @ 2004-11-3 14:15:13    
		*/    
		function imageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000")     
		{     
		  $isWaterImage = FALSE;     
		  $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";     
		
		  //读取水印文件     
		  if(!empty($waterImage) && file_exists($waterImage))     
		  {     
			$isWaterImage = TRUE;     
			$water_info = getimagesize($waterImage);     
			$water_w   = $water_info[0];//取得水印图片的宽     
			$water_h   = $water_info[1];//取得水印图片的高     
		
			switch($water_info[2])//取得水印图片的格式     
			{     
				case 1:$water_im = imagecreatefromgif($waterImage);break;     
				case 2:$water_im = imagecreatefromjpeg($waterImage);break;     
				case 3:$water_im = imagecreatefrompng($waterImage);break;     
				default:die($formatMsg);     
			}     
		  }     
		
		  //读取背景图片     
		  if(!empty($groundImage) && file_exists($groundImage))     
		  {     
			$ground_info = getimagesize($groundImage);     
			$ground_w   = $ground_info[0];//取得背景图片的宽     
			$ground_h   = $ground_info[1];//取得背景图片的高     
		
			switch($ground_info[2])//取得背景图片的格式     
			{     
				case 1:$ground_im = imagecreatefromgif($groundImage);break;     
				case 2:$ground_im = imagecreatefromjpeg($groundImage);break;     
				case 3:$ground_im = imagecreatefrompng($groundImage);break;     
				default:die($formatMsg);     
			}     
		  }     
		  else    
		  {     
			die("需要加水印的图片不存在！");     
		  }     
		
		  //水印位置     
		  if($isWaterImage)//图片水印     
		  {     
			$w = $water_w;     
			$h = $water_h;     
			$label = "图片的";     
		  }     
		       
		  switch($waterPos)     
		  {     
			case 0://随机     
				$posX = rand(0,($ground_w - $w));     
				$posY = rand(0,($ground_h - $h));     
				break;     
			case 1://1为顶端居左     
				$posX = 0;     
				$posY = 0;     
				break;     
			case 2://2为顶端居中     
				$posX = ($ground_w - $w) / 2;     
				$posY = 0;     
				break;     
			case 3://3为顶端居右     
				$posX = $ground_w - $w;     
				$posY = 0;     
				break;     
			case 4://4为中部居左     
				$posX = 0;     
				$posY = ($ground_h - $h) / 2;     
				break;     
			case 5://5为中部居中     
				$posX = ($ground_w - $w) / 2;     
				$posY = ($ground_h - $h) / 2;     
				break;     
			case 6://6为中部居右     
				$posX = $ground_w - $w;     
				$posY = ($ground_h - $h) / 2;     
				break;     
			case 7://7为底端居左     
				$posX = 0;     
				$posY = $ground_h - $h;     
				break;     
			case 8://8为底端居中     
				$posX = ($ground_w - $w) / 2;     
				$posY = $ground_h - $h;     
				break;     
			case 9://9为底端居右     
				$posX = $ground_w - $w;     
				$posY = $ground_h - $h;     
				break;     
			default://随机     
				$posX = rand(0,($ground_w - $w));     
				$posY = rand(0,($ground_h - $h));     
				break;       
		  }     
		
		  //设定图像的混色模式     
		  imagealphablending($ground_im, true);     
		
		  if($isWaterImage)//图片水印     
		  {     
			imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//拷贝水印到目标文件           
		  }     
		  else//文字水印     
		  {     
			if( !empty($textColor) && (strlen($textColor)==7) )     
			{     
				$R = hexdec(substr($textColor,1,2));     
				$G = hexdec(substr($textColor,3,2));     
				$B = hexdec(substr($textColor,5));     
			}     
			else    
			{     
				die("水印文字颜色格式不正确！");     
			}     
			imagestring ( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));           
		  }     
		
		  //生成水印后的图片     
		  @unlink($groundImage);     
		  switch($ground_info[2])//取得背景图片的格式     
		  {     
			case 1:imagegif($ground_im,$groundImage);break;     
			case 2:imagejpeg($ground_im,$groundImage);break;     
			case 3:imagepng($ground_im,$groundImage);break;     
			default:die($errorMsg);     
		  }     
		
		  //释放内存     
		  if(isset($water_info)) unset($water_info);     
		  if(isset($water_im)) imagedestroy($water_im);     
		  unset($ground_info);     
		  imagedestroy($ground_im);     
		}
	
}
