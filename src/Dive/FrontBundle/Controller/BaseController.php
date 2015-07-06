<?php

namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;

class BaseController extends Controller
{

	    // store session (unlocks session for other calls)
    public function saveSession(){
        $session = $this->get('session');
        $session->save();
    }


	/*
	*	Get current user (if any)
	*/
	public function getUser(){
		$securityContext = $this->container->get('security.context');
		if ($securityContext->getToken()){
			$user = $securityContext->getToken()->getUser();
			if (is_object($user)){
				return $user;
			}
		}
		return null;
	}


	// get repo
	public function getRepo($entity){
		return $this->getDoctrine()->getRepository('DiveFrontBundle:' . $entity);
	}

	public function setNotice($message){
		$this->get('session')->getFlashBag()->add(
			'notice',
			$message
			);
	}

	public function setError($message){
		$this->get('session')->getFlashBag()->add(
			'error',
			$message
			);
	}

	public function getJSONResponse($data){
		$response = new JsonResponse();
		$response->setData($data);
		return $response;
	}

	public function getJSONError($error){
		$response = new JsonResponse();
		$data = array(
                'success'=>false,
                'error'=> $error
                );
		$response->setData($data);
		return $response;
	}
}
