<?php

namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Dive\FrontBundle\Entity\User;
use Dive\FrontBundle\Entity\Role;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
/**
 * @Route("/user")
 */


class UserController extends BaseController
{

    public $email = array('contact@frontwise.com' => 'DIVE [Beeld & Geluid / Frontwise / VU]');

    /**
     * @Route("/current")
     * @Template()
     */
    public function currentAction()
    {
        $user = $this->getUser();
        if (!$user){
            return $this->getJSONError('No user logged in');
        } else {
            $collections = array();
            foreach($user->getCollections() as $c){
                $collections[] = $c->jsonSerialize();
            }
            $result = array(
                'success'=>true,
                'data'=> array(
                    'user'=> $user->jsonSerialize(),
                    'collections'=>$collections
                    )
                );
            return $this->getJSONResponse($result);
        }
    }

     /**
     * @Route("/profile")
     * @Template()
     */
     public function profileAction()
     {
        $user = $this->getUser();
        if (!$user){
         return $this->redirect($this->generateUrl('login'));
     }
     return array(
        'user'=>$user
        );
 }



         /**
     * @Route("/signup")
     * @Template()
     */
         public function signupAction(Request $request)
         {
            $session = $request->getSession();

            $error = false;
            $user = new User();


            $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('dive_front_user_signup'))
            ->setAttribute('class','user-signup')
            ->add('name', 'text')
            ->add('password', 'password')
            ->add('email', 'text',array('label'=>'E-mail address'))
            ->add('organisation', 'text',array('required'=>false))
            ->add('signup', 'submit', array('label' => 'Sign up'))
            ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                    // get manager
                $manager = $this->getDoctrine()->getManager();

                    // inactivate account
                $data->setIsActive(false);
                    // create password hash
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($data);
                $pass = $encoder->encodePassword($data->getPassword(), $data->getSalt());
                $data->setPassword($pass);
                $data->setEmail(strtolower($data->getEmail()));
                $data->setUsername($data->getEmail());
                    // user role
                $role = $this->getRepo('Role')->findOneBy(array('role'=>'ROLE_USER'));
                if (!$role){
                    $role = new Role();
                    $role->setName('Users');
                    $role->setRole('ROLE_USER');
                    $manager->persist($role);
                    $manager->flush();
                }
                $data->addUserRole($role);
                    // store user
                $manager->persist($data);
                $manager->flush();
                    // send activitation mail
                $message = \Swift_Message::newInstance()
                ->setSubject('Activate your DIVE account')
                ->setFrom($this->email)
                ->setTo($data->getEmail())
                ->setBody(
                    $this->renderView(
                        'DiveFrontBundle:User:signupEmail.txt.twig',
                        array('user' => $data)
                        )
                    )
                ;
                $this->get('mailer')->send($message);
                    // show confirmation/instructions
                return $this->render(
                    'DiveFrontBundle:User:confirm.html.twig',
                    array(
                        'user' => $data
                        )
                    );
            }

            return array(
                'error'         => $error,
                'form'=>$form->createView()

                );
        }



         /**
     * @Route("/activate/{id}/{hash}")
     */
         public function activateAction(Request $request, $id, $hash)
         {
            $user = $this->getRepo('User')->findOneBy(array('id'=>$id, 'hash'=>$hash));
            if ($user){
                $user->setIsActive(true);
                $user->setHash($user->makeHash());
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();

                // auto login
                $this->authenticateUser($user);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Your account has been activated!'
                    );
                // redirect to home
                return ($this->redirect($this->generateUrl('dive_front_browse_index')));
            }
            throw $this->createNotFoundException('User not found');
        }


        private function authenticateUser(UserInterface $user)
        {
        $providerKey = 'main'; // your firewall name
        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());

        $this->container->get('security.context')->setToken($token);
    }


    /**
     * @Route("/requestPassword")
     * @Template()
     */
    public function requestPasswordAction(Request $request)
    {
        $session = $request->getSession();
    $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        $form = $this->createFormBuilder(array('email'=>$lastUsername))
        ->setAction($this->generateUrl('dive_front_user_requestpassword'))
        ->setAttribute('class','request-password')
        ->add('email', 'text',array('label'=>'E-mail address'))
        ->add('signup', 'submit', array('label' => 'Request new password'))
        ->getForm();
        $error = '';
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = array('message'=>"Email address is not valid.");
            } else{
                $user = $this->getRepo('User')->findOneBy(array('email'=>$data['email']));
                if (!$user){
                    $error = array('message'=>'User not found');
                } else{
                    // get manager
                    $manager = $this->getDoctrine()->getManager();

                    $user->setHash($user->makeHash());
                    // store user
                    $manager->persist($user);
                    $manager->flush();
                    // send activitation mail
                    $message = \Swift_Message::newInstance()
                    ->setSubject('New password for your DIVE account')
                    ->setFrom($this->email)
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'DiveFrontBundle:User:requestPasswordEmail.txt.twig',
                            array('user' => $user)
                            )
                        )
                    ;
                    $this->get('mailer')->send($message);
                    // show confirmation/instructions
                    return $this->render(
                        'DiveFrontBundle:User:requestSent.html.twig',
                        array(
                            'user' => $user
                            )
                        );
                }
            }

        }


        return array(
            'error'=>$error,
            'form'=>$form->createView()
            );
    }



      /**
     * @Route("/newPassword/{id}/{hash}")
     * @Template()
     */
      public function newPasswordAction(Request $request, $id, $hash)
      {

        $user = $this->getRepo('User')->findOneBy(array('id'=>$id, 'hash'=>$hash));
        if (!$user){
          throw $this->createNotFoundException('User not found');
      }

      $form = $this->createFormBuilder($user)
      ->setAttribute('class','user-password')
      ->add('password', 'password')
      ->add('setup', 'submit', array('label' => 'Set password'))
      ->getForm();

      $form->handleRequest($request);

      if ($form->isValid()) {
        $data = $form->getData();
        // get manager
        $manager = $this->getDoctrine()->getManager();

            // create password hash
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $pass = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($pass);
            // store user
        $manager->persist($user);
        $manager->flush();
            // send activitation mail
        $this->authenticateUser($user);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Your password has been changed!'
            );
                // redirect to home
        return ($this->redirect($this->generateUrl('dive_front_browse_index')));
    }



    return array(
        'form'=>$form->createView()

        );
}


}
