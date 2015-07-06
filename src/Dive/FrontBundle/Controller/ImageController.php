<?php

namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Dive\FrontBundle\Entity\User;
use Dive\FrontBundle\Entity\ImageCache;


/**
 * @Route("/images/search")
 */


class ImageController extends BaseController
{

    public function getCurl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function getWikiImage($keywords,$offset){
        $searchUrl = "http://nl.wikipedia.org/w/api.php?action=query&list=search&srsearch=".urlencode($keywords)."&srprop=&srlimit=1&format=json&sroffset=" . $offset;
        $searchJson = json_decode($this->getCurl($searchUrl),true);
        if (count($searchJson['query']["search"]) > 0){
            $url = "http://nl.wikipedia.org/w/api.php?action=query&titles=".urlencode($searchJson['query']["search"][0]['title'])."&prop=pageimages&format=json&pithumbsize=900";
            $imageJson = json_decode($this->getCurl($url),true);
            $id = key($imageJson['query']['pages']);
            if ($id && isset($imageJson['query']['pages'][$id]['thumbnail'])){
                return $imageJson['query']['pages'][$id]['thumbnail']['source'];
            }

        }
        return false;
    }



    public function getCurlPost($url,$fields){
        $ch = curl_init();
        $fields_string = json_encode($fields, JSON_NUMERIC_CHECK);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function getOpenCultuurDataImage($keywords,$offset){
        $searchUrl = "http://api.opencultuurdata.nl/v0/search";
        $post = array("query" => addslashes($keywords . ' -NSB -Jeugdstorm'),
            "filters" => array(
              "media_content_type" => array("terms" => array("image/jpeg"))
              ),
            "size" => 5);
        $jsonData = $this->getCurlPost($searchUrl,$post);
        if (!$jsonData) {
            return false;
        }
        try{
            $searchJson = json_decode($jsonData,true);
            if (count($searchJson['hits']['hits']) > 0){
                foreach($searchJson['hits']['hits'] as $hit){
                    if (count($hit['_source']['media_urls']) > 0){
                        $url = $hit['_source']['media_urls'][0]['url'];
                        if ($url){
                            return $url;
                        }
                    }
                }

            }

        } catch(Exception $error) {
            return false;
        }
        return false;
    }

  /**
     * @Route("/cache/flush/yesiamsure")
     */
  public function cacheFlushAction()
  {
    die('Deprecated, run from command line');
    }


    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $this->saveSession();
        $keywords = $this->getRequest()->get('keywords');
        $keywords = strtolower($keywords);
        $keywords = str_replace(array('.jpg',';',':','&gt','&lt','&amp','&quot'),'',$keywords);
        $maxSize = $this->getRequest()->get('size',500);
        $offset = 0;
        $maxOffset = 2;
        $image = $this->getRepo('ImageCache')->findOneBy(array('keywords'=>$keywords, 'size'=>$maxSize));

        if ($image){
            if ($image->getUrl()){
                $this->imageReturn($image->getUrl());
            } else{
                $this->returnNotFound();
            }
            die();
        }
        $image = new ImageCache();
        $image->setKeywords($keywords);
        $image->setSize($maxSize);
        // try wikipedia
        while($offset < $maxOffset){
            $imageUrl = $this->getWikiImage($keywords,$offset);
            if ($imageUrl){
                $image->setUrl($this->storeImage($imageUrl, $maxSize));
                $image->setSource($imageUrl);
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($image);
                $manager->flush();
                $this->imageReturn($image->getUrl());
                die();
            }
            $offset++;
        }

        // try Open Cultuur Data
        $imageUrl = $this->getOpenCultuurDataImage($keywords,$offset);
        if ($imageUrl){
            $image->setUrl($this->storeImage($imageUrl,$maxSize));
            $image->setSource($imageUrl);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($image);
            $manager->flush();
            $this->imageReturn($image->getUrl());
            die();
        }
        $offset++;

        $image->setUrl('');
        $image->setSource('');
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($image);
        $manager->flush();
        $this->returnNotFound();
        die();
    }

    public function storeImage($imageUrl, $maxSize){
        // whitelist sizes
        if (!in_array($maxSize, array(500,1100))) { $maxSize = 500; }
        //$img = file_get_contents($imageUrl);
        $img = $this->getCurl($imageUrl);

        $im = imagecreatefromstring($img);

        $width = imagesx($im);

        $height = imagesy($im);


        if ($width > $height){
            $newwidth = $maxSize;
            $newheight = $maxSize / ($width/$height);
        } else{
            $newheight = $maxSize;
            $newwidth = $maxSize / ($height/$width);
        }


        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        $filename = '/cache/'.time().md5($imageUrl) .'_'.$maxSize.'.jpg';
        $cacheFolder = $this->getRequest()->server->get('DOCUMENT_ROOT');
        imagejpeg($thumb,$cacheFolder.$filename, 60);

        imagedestroy($thumb);


        imagedestroy($im);
        return $filename;
    }

    public function returnNotFound(){
        $request = $this->getRequest();
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $this->imageReturn($url . '/img/imagenotfound.png');
        die();
    }

    public function imageReturn($imageUrl){
        $ext = pathinfo($imageUrl, PATHINFO_EXTENSION);
        switch($ext){
            case 'png': header("Content-Type: image/png"); break;
            case 'gif': header("Content-Type: image/gif"); break;
            default:
            header("Content-Type: image/jpeg");
            break;
        }
        if ($this->getRequest()->get('proxy','')){
            echo $this->getCurl($imageUrl);
            die();
        } else{
            header('Location: '.$imageUrl);
        }

    }





}
