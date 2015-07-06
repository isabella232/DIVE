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
 * @Route("/collection")
 */


class CollectionController extends BaseController
{
    /**
     * @Route("/create")
     * @Method({"POST"})
     */
    public function createAction()
    {
        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } else {
            $collection = new Collection();
            $request = $this->getRequest();
            $collection->setTitle($request->get('title','Unnamed Collection'));
            $collection->setPublic($request->get('public',true));
            $collection->setOwner($user);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($collection);
            $manager->flush();

            $result = array(
                'success'=>true,
                'data'=> $collection->jsonSerialize()
                );
        }
        return $this->getJSONResponse($result);
    }

    /**
     * @Route("/{id}/delete")
     * @Method({"POST"})
     */
    public function deleteAction($id)
    {
        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } else {
            $collection = $this->getRepo('Collection')->findOneBy(array('id'=>$id, 'owner'=>$user));
            if (!$collection){
                return $this->getJSONError('Collection not found');
            } else{
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($collection);
                $manager->flush();

                $result = array(
                    'success'=>true,
                'data'=> $id // id of removed item
                );
            }
        }
        return $this->getJSONResponse($result);
    }

    /**
     * @Route("/list/{id}")
     */
    public function listAction($id=0)
    {
        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } else {
            $collections = $user->getCollections();
            $data = array();
            foreach($collections as $c){
                if ($id == 0 || ($id > 0 && $c->getId() == $id)){
                    $data[] = $c->jsonSerialize();
                }
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
     * @Route("/search")
     */
    public function searchAction()
    {
        $keywords = $this->getRequest()->get('keywords','');
        $offset = $this->getRequest()->get('offset',0);
        $limit = $this->getRequest()->get('limit',100);
        if ($keywords == false){
            return $this->getJSONError('No keywords specified');
        }
        $user = $this->getUser();
        $collections = null;
        if ($user && $keywords == 'My collections'){
            $collections = $user->getCollections();
        } elseif(strpos($keywords, 'Collection:') === 0){
            $id = max(0,filter_var($keywords, FILTER_SANITIZE_NUMBER_INT));
            $collections = $this->getRepo('Collection')->findById($id,$user);
        } else {
            $collections = $this->getRepo('Collection')->findByKeywords($keywords,$user,$offset,$limit);
        }
        if (!$collections){
            return $this->getJSONError('No collections found');
        } else{
            $data = array();
            foreach($collections as $collection){
                $data[] = $collection->jsonSerialize();
            }
            $result = array(
                'success'=>true,
                'data'=> $data
                );
        }

        return $this->getJSONResponse($result);
    }


    /**
     * @Route("/entity")
     */
    public function entityAction()
    {
        $uid = $this->getRequest()->get('uid',0);

        $entity = $this->getRepo('DiveEntity')->findBy(array('uid'=>$uid));

        if (!$entity){
            return $this->getJSONError('Entity not found with UID ' . $uid);
        } else {
            $user = $this->getUser();
            $collections = $entity->getCollections();
            $data = array();
            foreach($collections as $c){
                // show public or owned collections
                if ($c->getPublic() || $c->getOwner() == $user){
                    $data[] = $c->jsonSerialize();
                }
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
     * @Route("/entities/")
     */
    public function entitiesAction()
    {
        $uid = $this->getRequest()->get('uids',0);
        $uids = explode(',',$uids);
        $entities = $this->getRepo('DiveEntity')->findBy(array('uid'=>$uids));

        if (!$entities){
            return $this->getJSONError('Entities not found with UIDS ' . implode(',',$uids));
        } else {
            $user = $this->getUser();
            $data = array();
            foreach($entities as $e){
                $subData = array();
                $collections = $e->getCollections();
                foreach($collections as $c){
                    if ($c->getPublic() || $c->getOwner() == $user){
                        $subData[] = $c->jsonSerialize();
                    }
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
     * @Route("/{id}/details")
     * @Method({"GET"})
     */
public function detailsAction($id)
{
    $uid = $this->getRequest()->get('uid',0);
    $user = $this->getUser();
    if (!$user){
        $collection = $this->getRepo('Collection')->findOneBy(array('id'=>$id, 'public'=>true));
    } else {
        $collection = $this->getRepo('Collection')->findById($id, $user);
        if($collection && count($collection > 0)){
            $collection = $collection[0];
        }
    }
    if (!$collection){
        return $this->getJSONError('Collection not found');
    } else{
        $result = array(
            'success'=>true,
            'data'=> $collection->jsonSerialize()
            );
    }

    return $this->getJSONResponse($result);
}

     /**
     * @Route("/{id}/add")
     * @Method({"POST"})
     */
     public function addAction($id)
     {
        $uid = $this->getRequest()->get('uid',false);
        if ($uid == false){
            $uids = $this->getRequest()->get('uids',false);
            if ($uids){
                $uids = explode(",",$uids);
            }
        } else{
            $uids = array($uid);
        }

        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } else {
            if ($id == 'new'){
               $collection = new Collection();
               $request = $this->getRequest();
               $collection->setTitle($request->get('title','Unnamed Collection'));
               $collection->setDescription($request->get('description',''));
               $collection->setPublic($request->get('public',true) || $request->get('public') == 'true');
               $collection->setOwner($user);
               $manager = $this->getDoctrine()->getManager();
               $manager->persist($collection);
               $manager->flush();
           } else{
             $collection = $this->getRepo('Collection')->findOneBy(array('id'=>$id, 'owner'=>$user));
         }
         if (!$collection){
            return $this->getJSONError('Collection not found');
        } elseif (!$uids){
            return $this->getJSONError('No uid specified');
        } else {
            $manager = $this->getDoctrine()->getManager();
            foreach($uids as $uid){
                $entity = $this->getRepo('DiveEntity')->findOneBy(array('uid'=>$uid));
                if (!$entity){
                    $entity = new DiveEntity();
                    $entity->setUid($uid);
                }

                $request = $this->getRequest();
                if (!$collection->getEntities()->contains($entity)){
                    $collection->addEntity($entity);
                }
                $manager->persist($entity);
            }
            $manager->persist($collection);
            $manager->flush();

            $result = array(
                'success'=>true,
                'uid'=>$uid,
                'data'=> $collection->jsonSerialize()
                );
        }
    }
    return $this->getJSONResponse($result);
}


     /**
     * @Route("/{id}/edit")
     * @Method({"POST"})
     */
     public function editAction($id)
     {

        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } 
        $collection = $this->getRepo('Collection')->findOneBy(array('id'=>$id, 'owner'=>$user));
        if (!$collection){
            return $this->getJSONError('Collection not found');
        }
        $request = $this->getRequest();
        $collection->setTitle($request->get('title','Unnamed Collection'));
        $collection->setDescription($request->get('description',''));
        $collection->setPublic($request->get('public',true) || $request->get('public') == 'true');
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($collection);
        $manager->flush();
        $result = array(
            'success'=>true,
            'id'=>$id,
            'data'=> $collection->jsonSerialize()
            );
        return $this->getJSONResponse($result);
    }



 /**
     * @Route("/{id}/remove")
     * @Method({"POST"})
     */
 public function removeAction($id){
    $uid = $this->getRequest()->get('uid',0);
    $user = $this->getUser();
    if (!$user){
        return $this->getJSONError('No user logged in');
    } else {
     $collection = $this->getRepo('Collection')->findOneBy(array('id'=>$id, 'owner'=>$user));
     if (!$collection){
        return $this->getJSONError('Collection not found');
    } else{
        $entity = $this->getRepo('DiveEntity')->findOneBy(array('uid'=>$uid));
        if (!$entity){
            return $this->getJSONError('Entity not found');
        }
        // remove collection if last entity is deleted
        if (!$collection->getEntities()->contains($entity)){
            return $this->getJSONError('Entity not in collection');
        }
        $collection->getEntities()->removeElement($entity);
        $manager = $this->getDoctrine()->getManager();
        if ($collection->getEntities()->count() == 0){
            $manager->remove($collection);
        }  else{
            $manager->persist($collection);
        }
        $manager->flush();

        $result = array(
            'success'=>true,
            'data'=> $collection->jsonSerialize()
            );
    }
}
return $this->getJSONResponse($result);
}
}
