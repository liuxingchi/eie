<?php

namespace Ydzy\FileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ydzy\AdminBundle\Entity\Version;

class FileController extends Controller
{
    public function CreateAction(Request $request)
    {
        return new Response("API CreateAction");
    }

    /***********************************
	 *
	 * Textimage::uploadFile
	 *
	 ***********************************/
	public function uploadFileAction(Request $request)
	{
    		$json = $this->get('json_parser')->parse($request);
    		$em = $this->getDoctrine()->getEntityManager();
    		$uploadedDirPrefix = '/upload/';
    		$uploadedFile = $request->files->get('imageFile');
    		if (!isset($uploadedFile) || !$uploadedFile->isValid()){
						return new Response('', 400);
				}
				$uploadedFileName = $uploadedFile->getClientOriginalName();
				$uploadedFileName = preg_replace('/[\/:*?"<>|&]+/', '_', $uploadedFileName);
				$filesize = $uploadedFile->getClientSize();
				$hashName = md5_file($uploadedFile->getPathname());
				$baseDir = preg_replace('/app$/si', 'web' . $request->request->get('folder'), $this->get('kernel')->getRootDir());
				$uploadedDir = chunk_split($hashName, 3, '/');
				$newFile = $uploadedDirPrefix.$uploadedDir.$uploadedFileName;
				if (!is_dir($baseDir.$uploadedDirPrefix.$uploadedDir)){
					if (!mkdir($baseDir.$uploadedDirPrefix.$uploadedDir, 0755, true)){
						return new Response('403', 403);
					}
				}
				$uploadedFile->move($baseDir.$uploadedDirPrefix.$uploadedDir, $uploadedFileName);
				$upload = $em->getRepository('YdzyAdminBundle:Version')
									->findOneByMd5($hashName);
				if (!$upload){
					$upload = new Version();
					$upload->setName($uploadedFileName);
					$upload->setMd5($hashName);
					$upload->setVersion("");
					$upload->setDescription("");
					$upload->setFilesize($filesize);
					$upload->setFilepath($newFile);
					$upload->setCreateDate(new \DateTime("now"));
					$upload->setUpdateDate(new \DateTime("now"));
					$upload->setStatus(1);
					$em = $this->getDoctrine()->getEntityManager();
					$em->persist($upload);
					$em->flush();
				}
				$uploadId = $upload->getId();

				$json_r = array(
						'ID' => $uploadId,
						'Url' => $newFile,
				        'filesize'=>$filesize,
				        'filename'=>$uploadedFileName
				);
				//$response = new JsonResponse($json_r);
				$response = new Response(stripslashes(json_encode($json_r)));
		    return $response;
	}
	
	
	
	/***********************************
	 *
	 * Textimage::uploadVideo
	 *
	 ***********************************/
	public function uploadVideoAction(Request $request)
	{
	    $uploadedDirPrefix = '/uploadvideo/';
	    $uploadedFile = $request->files->get('imageFile');
	    if (!isset($uploadedFile) || !$uploadedFile->isValid()){
	        return new Response('', 400);
	    }
	    $uploadedFileName = $uploadedFile->getClientOriginalName();
	    $uploadedFileName = preg_replace('/[\/:*?"<>|&]+/', '_', $uploadedFileName);
	    $filesize = $uploadedFile->getClientSize();
	    $hashName = md5_file($uploadedFile->getPathname());
	    $baseDir = preg_replace('/app$/si', 'web' . $request->request->get('folder'), $this->get('kernel')->getRootDir());
	    $uploadedDir = chunk_split($hashName, 3, '/');
	    if (!is_dir($baseDir.$uploadedDirPrefix.$uploadedDir)){
	        if (!mkdir($baseDir.$uploadedDirPrefix.$uploadedDir, 0755, true)){
	            return new Response('403', 403);
	        }
	    }
	    $current = time();
	    //获得到文件的后缀名
	    $extName = pathinfo($uploadedFileName,PATHINFO_EXTENSION);
	    $newFile = $uploadedDirPrefix.$uploadedDir.$current.".".$extName;
	    $uploadedFile->move($baseDir.$uploadedDirPrefix.$uploadedDir, $current.".".$extName);
	    
	
	    $json_r = array(
	        'Url' => $newFile,
	        'filesize'=>$filesize,
	        'filename'=>$uploadedFileName,
	        'filename1'=>$current.".".$extName
	    );
	    //$response = new JsonResponse($json_r);
	    $response = new Response(stripslashes(json_encode($json_r)));
	    return $response;
	}
	
	/***********************************
	 *
	 * uploadEditor
	 *
	 ***********************************/
	public function uploadEditorAction(Request $request)
	{
	    $uploadedDirPrefix = '/uploadeditor/';
	    $uploadedFile = $request->files->get('upload');
	    if (!isset($uploadedFile) || !$uploadedFile->isValid()){
	        return new Response('', 400);
	    }
	    $uploadedFileName = $uploadedFile->getClientOriginalName();
	    $uploadedFileName = preg_replace('/[\/:*?"<>|&]+/', '_', $uploadedFileName);
	    $filesize = $uploadedFile->getClientSize();
	    $hashName = md5_file($uploadedFile->getPathname());
	    $baseDir = preg_replace('/app$/si', 'web' . $request->request->get('folder'), $this->get('kernel')->getRootDir());
	    $uploadedDir = chunk_split($hashName, 3, '/');
	    if (!is_dir($baseDir.$uploadedDirPrefix.$uploadedDir)){
	        if (!mkdir($baseDir.$uploadedDirPrefix.$uploadedDir, 0755, true)){
	            return new Response('403', 403);
	        }
	    }
	    $current = time();
	    //获得到文件的后缀名
	    $extName = pathinfo($uploadedFileName,PATHINFO_EXTENSION);
	    $newFile = $uploadedDirPrefix.$uploadedDir.$current.".".$extName;
	    $uploadedFile->move($baseDir.$uploadedDirPrefix.$uploadedDir, $current.".".$extName);
	    $newFile = "http://eie.ren".$newFile; 
	     echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(2,'$newFile','');</script>";
	     
	     
	     return new Response('',200);
	}
	
	
}
