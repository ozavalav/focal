<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Focal\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\AppConst;

/**
 * Description of LoginController
 *
 * @author mfusuario
 */
class LoginController extends Controller {
    
    /**
     * @Route("/login", name="login_route")
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        //get the login error if there is one'

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
        $em = $this->getDoctrine()->getManager();
        
        //Obtener los periodos activos
        $entper = $em->getRepository('FocalAppBundle:AdPeriodos')->findBy(array('estado' => AppConst::ESTADO_GENERAL_ACTIVO));
        $cantper = count($entper);
        return $this->render(
            'FocalAppBundle:AdUser:login.html.twig',
            array(
                'entper' => $entper,
                'cantper' => $cantper,
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error
            )
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // este controller no se ejecutará,
        // ya que la route se maneja por el sistema de seguridad
    }
    
    public function guardarPeriodoAction($p_periodo) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->set('_periodo', $p_periodo);
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $response->setData($p_periodo);
        return $response;
    }
}
