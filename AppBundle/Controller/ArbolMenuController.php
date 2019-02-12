<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArbolMenu
 *
 * @author mfusuario
 */
class ArbolMenuController extends Controller {
    
    public function menuAction($acceso)
        {   
            global $strArb;
            
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }
            
            $em = $this->getDoctrine()->getManager();

            $dql = "select g.objeto, g.nombre, g.titulo, g.objetoPadre, g.orden, g.tipoObjeto tipo, g.idEstado estado, g.objetoPadre padre "
                    . "from FocalAppBundle:AdGrant g join FocalAppBundle:AdAccesoGrant ag with (g.objeto = ag.objeto and ag.idAcceso = :idacceso and ag.idEstado = :estado) "
                    . "where g.visible = 1 and g.idEstado = :estado "
                    . "order by g.objeto, g.orden";

            $query = $em->createQuery($dql);
            $query ->setParameter('estado',1);
            $query ->setParameter('idacceso',$acceso);
            $menu = $query->getResult();

            $this->construirArbolMenuA($menu, $acceso);
            
            return new response($strArb);
            //return $this->render('AdminBundle:Home:menu.html.twig', array('menu' => $strArb ));

            //return $this->render('MunicipalcatastroBundle:Login:Municipio.html.twig');
        } 

    /* Pintado del menu tipo Acordion */    
    public function construirArbolMenuA($menu, $idAcceso) {
        global $strArb;
        $strArb='<ul class="sidebar-menu" data-widget="tree" id="treeview-menu">';
        $noItem = count($menu);
        if ($noItem) {
        foreach ($menu as $item ) {
            if($item['tipo'] == 1 && $item['padre'] == '0') {
              $strArb.='<li class="header">'.$item['nombre'].'</li>';
            } elseif($item['tipo'] == 1) {
                $strArb.='<li class="treeview"><a href="#"><i class="fa fa-gear"></i> <span>'.$item['nombre'].'</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                $strArb.='<ul class="treeview-menu">';
                $this->pintarHijosA($item['objeto'], $idAcceso);
                $strArb.='</ul></li>';                  
            }elseif($item['tipo'] == 3) {
                $strArb.='<li><a href="/FocalAB/web/app_dev.php/'.$item['nombre'].'"><i class="fa fa-circle-o"></i> '.$item['titulo'].'</a></li>';
            }
        }             
            $strArb.='</ul>';
        }
    }

    public function pintarHijosA($idPadre, $idAcceso) {
        global $strArb;

        $em = $this->getDoctrine()->getManager();

        $dql = "select g.objeto, g.nombre, g.titulo, g.objetoPadre, g.orden, g.tipoObjeto tipo, g.idEstado estado "
                    . "from FocalAppBundle:AdGrant g join FocalAppBundle:AdAccesoGrant ag with (g.objeto = ag.objeto and ag.idAcceso = :idacceso and ag.idEstado = :estado) "
                    . "where g.visible = 1 and g.idEstado = :estado and g.objetoPadre = :idpadre "
                    . "order by g.objeto, g.orden";

        $query = $em->createQuery($dql);
        $query ->setParameter('idpadre', $idPadre);
        $query ->setParameter('idacceso', $idAcceso);
        $query ->setParameter('estado', 1);
        $subMenu = $query->getResult();

        $noItem = count($subMenu); 

        if ($noItem) {
            foreach ($subMenu as $item ) {
                if( $item['tipo'] == 1 ) { //tiene hijos
                    $strArb.='<li class="treeview"><a href="#"><i class="fa fa-gear"></i> <span>'.$item['nombre'].'</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                    $strArb.='<ul class="treeview-menu">';
                    $this->pintarHijosA($item['objeto'], $idAcceso);
                    $strArb.='</ul></li>';
                } else {
                    $strArb.='<li><a href="/FocalAB/web/app_dev.php/'.$item['nombre'].'"><i class="fa fa-circle-o"></i> '.$item['titulo'].'</a></li>';
                }    
            }              
        }
    }
    
    /* Estructura de menu tipo folder de archivos */
    public function construirArbolMenu($menu, $idAcceso) {
        global $strArb;
        $strArb='<ul class="filetree" id="treeview-menu">';
        $noItem = count($menu);
        if ($noItem) {
        foreach ($menu as $item ) {
            if( $item['tipo'] == 1 ) { //tiene hijos
                    $strArb.='<li class="open"><span class="folder"></span>'.$item['nombre'].'<ul>';
                    $this->pintarHijos($item['objeto'], $idAcceso);         
                    $strArb.='</ul></li>';
                    break; //corta el flujo de pintado porque ya pinto todos los hijos porque empezo desde la raiz
                } else {
                    //$strArb.='<li>'.$item['nombre'].'</li>';
                }
            }             
            $strArb.='</ul>';
        }
    }

    public function pintarHijos($idPadre, $idAcceso) {
        global $strArb;

        $em = $this->getDoctrine()->getManager();

        $dql = "select g.objeto, g.nombre, g.titulo, g.objetoPadre, g.orden, g.tipoObjeto tipo, g.idEstado estado "
                    . "from FocalAppBundle:AdGrant g join FocalAppBundle:AdAccesoGrant ag with (g.objeto = ag.objeto and ag.idAcceso = :idacceso and ag.idEstado = :estado) "
                    . "where g.visible = 1 and g.idEstado = :estado and g.objetoPadre = :idpadre "
                    . "order by g.objeto, g.orden";

        $query = $em->createQuery($dql);
        $query ->setParameter('idpadre', $idPadre);
        $query ->setParameter('idacceso', $idAcceso);
        $query ->setParameter('estado', 1);
        $subMenu = $query->getResult();

        $noItem = count($subMenu); 

        if ($noItem) {
            foreach ($subMenu as $item ) {
                if( $item['tipo'] == 1 ) { //tiene hijos
                    $strArb.='<li class="closed"><span class="folder"><a name="#" href="#"></a></span>'.$item['nombre'].'<ul>';
                    $this->pintarHijos($item['objeto'], $idAcceso);
                    $strArb.='</ul></li>';
                } else {
                    $strArb.='<li id="li_XX5" class="last"><a id="a_XX4" href="/sigep/web/app_dev.php/'.$item['nombre'].'"/>'.$item['titulo'].'</a></li>';
                }    
            }              
        }
    }
}
