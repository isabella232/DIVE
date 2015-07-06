<?php

namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Dive\FrontBundle\Entity\Collection;
use Dive\FrontBundle\Entity\DiveEntity;
use Dive\FrontBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/entity")
 */


class DiveEntityController extends BaseController
{
    /**
     * @Route("/count")
     */
    public function countAction()
    {
        $uids = $this->getRequest()->get('uids',0);
        $uids = explode(',',$uids);
        $entities = $this->getRepo('DiveEntity')->findBy(array('uid'=>$uids));
        $user = $this->getUser();
        $data = array();
        foreach($entities as $e){
            $subData = array();
            $subData['comments'] = array('count' => count($e->getComments()) );
            $countOwner =0;
            if ($user){
                foreach($e->getComments() as $c){
                    if ($c->getOwner() == $user){
                        $countOwner++;
                    }
                }
            }
            $subData['comments']['owner'] = $countOwner;
            $collections = $e->getCollections();
            $count = 0;
            $countOwner = 0;
            foreach($collections as $c){
                if ($c->getPublic() || $c->getOwner() == $user){
                    $count++;
                    if ($c->getOwner() == $user){
                        $countOwner++;
                    }
                }
            }
            $subData['collections'] = array(
                'count'=>$count,
                'owner'=>$countOwner
                );

            $data[$e->getUid()]= $subData;
        }
        $result = array(
            'success'=>true,
            'results'=> count($data),
            'data'=>$data
            );
        return $this->getJSONResponse($result);
    }

     /**
     * @Route("/comments")
     */
     public function commentsAction()
     {
        $uid = $this->getRequest()->get('uid',0);

        $entity = $this->getRepo('DiveEntity')->findOneBy(array('uid'=>$uid));

        if (!$entity){
            $result = array(
                'success'=>false,
                'error'=> 'Entity not found with UID ' . $uid
                );
        } else {
            $user = $this->getUser();
            $countOwner = 0;
            $comments = $entity->getComments();
            $data = array();
            foreach($comments as $c){
                $data[] = $c->jsonSerialize();
                if ($user && $c->getOwner() == $user){
                    $countOwner++;
                }
            }
            $result = array(
                'success'=>true,
                'owner' =>$countOwner,
                'results'=> count($data),
                'data'=>$data
                );
        }
        return $this->getJSONResponse($result);
    }

    /**
     * @Route("/comments/multiple/")
     */
    public function multipleCommentsAction()
    {
        $uids = $this->getRequest()->get('uids',0);

        $uids = explode(',',$uids);
        $entities = $this->getRepo('DiveEntity')->findBy(array('uid'=>$uids));

        if (!$entities){
            $result = array(
                'success'=>false,
                'error'=> 'Entities not found with UIDS ' . implode(',',$uids)
                );
        } else {
            $user = $this->getUser();
            $data = array();
            foreach($entities as $e){
                $subData = array();
                $comments = $e->getComments();
                foreach($comments as $c){
                    $subData[] = $c->jsonSerialize();
                }
                $data[$e->getUid()] = $subData;
            }
            $result = array(
                'success'=>true,
                'results'=> count($data),
                'data'=>$data
                );
        }
        return $this->getJSONResponse($result);
    }


     /**
     * @Route("/collections")
     */
     public function collectionsAction()
     {
        $uid = $this->getRequest()->get('uid',0);

        $entity = $this->getRepo('DiveEntity')->findOneBy(array('uid'=>$uid));

        if (!$entity){
            $result = array(
                'success'=>false,
                'error'=> 'Entity not found with UID ' . $uid
                );
        } else {
            $user = $this->getUser();
            $countOwner = 0;
            $collections = $entity->getCollections();
            $data = array();
            foreach($collections as $c){
                if ($c->getPublic() || $c->getOwner() == $user){
                    $data[] = $c->jsonSerialize();
                    if ($user && $c->getOwner() == $user){
                        $countOwner++;
                    }
                }
            }
            $result = array(
                'success'=>true,
                'owner' =>$countOwner,
                'results'=> count($data),
                'data'=>$data
                );
        }
        return $this->getJSONResponse($result);
    }

}