<?php

namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Dive\FrontBundle\Entity\User;
use Dive\FrontBundle\Entity\Comment;
use Dive\FrontBundle\Entity\DiveEntity;


/**
 * @Route("/comment")
 */


class CommentController extends BaseController
{


     /**
     * @Route("/add")
     * @Method({"POST"})
     */
     public function addAction()
     {
        $uid = $this->getRequest()->get('uid',0);

        $user = $this->getUser();
        if (false && !$user){
            return $this->getJSONError('No user logged in');
        } else {
         $entity = $this->getRepo('DiveEntity')->findOneBy(array('uid'=>$uid));
         $manager = $this->getDoctrine()->getManager();
         if (!$entity){
            $entity = new DiveEntity();
            $entity->setUID($uid);
            $manager->persist($entity);
        }
        $request = $this->getRequest();
        $comment = new Comment();
        $comment->setEntity($entity);
        $body = $request->get('body');
        if (!$body){
            return $this->getJSONError('Empty comment given');
        }
        $comment->setBody($body);
        $comment->setOwner($user);

        $manager->persist($comment);
        $manager->flush();

        $result = array(
            'success'=>true,
            'data'=> $comment->jsonSerialize()
            );

    }
    return $this->getJSONResponse($result);
}
}
