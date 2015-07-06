<?php
namespace Dive\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Dive\FrontBundle\Entity\User;

class SecurityController extends BaseController
{
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
                );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        $lostPassword = array(
            'visible' => false,
            'user'=> null
            );

        if ($lastUsername){
            $user = $this->getRepo('User')->findOneBy(array('username'=>$lastUsername));
            if ($user){
                $lostPassword['visible'] = true;
                $lostPassword['user'] = $user;
            }
        }

        return $this->render(
            'DiveFrontBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
                'lostPassword'=> $lostPassword
                )
            );
    }

    public function loginCheckAction()
    {
        return $this->forward('DiveFrontBundle:Security:Login');
    }




    /**
     * @Route("/testuser")
     */
    public function createUserAction() {
        if ($this->container->get('kernel')->getEnvironment() != 'dev'){
            die('forbidden');
        }


        $factory = $this->get('security.encoder_factory');

        $user = new User();
        $email = 'user';
        $password = 'test';

        $encoder = $factory->getEncoder($user);
        $pass = $encoder->encodePassword($password, $user->getSalt());
        $user->setUsername($email);
        $user->setEmail($email);
        $user->setPassword($pass);
        $user->setIsActive(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        return new Response('User created');

    }

}