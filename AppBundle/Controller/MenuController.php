<?php

namespace Focal\AppBundle\Controller;

use Focal\AppBundle\Entity\AppConst;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class MenuController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        
        
        $em = $this->getDoctrine()->getManager();
        $entityUser = $em->getRepository('FocalAppBundle:AdUser')->findBy(array('username' => $usuario, 'idEstado' => AppConst::ESTADO_GENERAL_ACTIVO));
        
        if (!$entityUser) {
            throw $this->createNotFoundException('FOCAL: No es posible encontrar el usuario.');
        }
        $codDepartamento = $entityUser[0]->getCodDepartamento();
        $codMunicipio = $entityUser[0]->getCodMunicipio();
        $idComunidad = $entityUser[0]->getIdComunidad();
        $acceso = $entityUser[0]->getAcceso();
        
        $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDepartamento));
        $nombreDepartamento = $entDepto[0]->getNombre();
        $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMunicipio));
        $nombreMunicipio = $entMuni[0]->getNombre();
        
        
        /*$entityCli = $em->getRepository('AppBundle:AdClinica')->findBy(array('idClinica' => $idClinica));
        if (!$entityCli) {
            throw $this->createNotFoundException('SIMEC: No es posible encontrar la Clínica.');
        }
        $nombreClinica = $entityCli[0]->getNombre();
        $nombrePropietario = $entityCli[0]->getPropietario(); 
        */
        $session = $request->getSession();
        $session->set('_cod_municipio', $codMunicipio);
        $session->set('_cod_departamento', $codDepartamento);
        $session->set('_id_comunidad', $idComunidad);
        
        $session->set('_nombre_municipio', $nombreMunicipio);
        $session->set('_nombre_departamento', $nombreDepartamento);
        $session->set('_cuenta_usuario', $usuario);
        $session->set('_acceso', $acceso);
        
        /* Captura la variable periodo que fue selecionado en el Login esta variable se
         * estable por medio de Ajax en la patalla de login.html.twig
         */
        $periodo = $session->get('_periodo');
        
        $query = "select 
            max(fecha_creacion) ultfecha, 
            count(*) totalenc,
            sum(case when estado = 1  then 1 else 0 end) encpen,
            sum(case when usuario_creacion = :usr then 1 else 0 end) encxusr 
            from datos_generales
            where cod_departamento = :dep and cod_municipio = :mun and periodo = :per ";
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDepartamento);
            $stmt->bindValue('mun',$codMunicipio);
            $stmt->bindValue('usr',$usuario);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtgen = $stmt->fetchAll();
        
        $strmenu = $this->forward('Focal\AppBundle\Controller\ArbolMenuController::menuAction',['acceso' => $acceso ]);    
        $session->set('_menu',$strmenu->getContent());
        return $this->render('FocalAppBundle:Menu:index.html.twig', array('dtgen' => $dtgen[0], 'menu' => $strmenu->getContent()));
    }
    
    public function menuAction()
    {
        return $this->render('AppBundle:Menu:menu.html.twig');
    }
}
