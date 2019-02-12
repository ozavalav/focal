<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\AdAccesoGrant;
use Focal\AppBundle\Form\AdAccesoGrantType;

/**
 * AdAccesoGrant controller.
 *
 */
class AdAccesoGrantController extends Controller
{
private $strArb='';
    /**
     * Lists all AdAccesoGrant entities.
     *
     */
    public function indexAction($idacceso)
    {
        
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        global $strArb;
        
        $em = $this->getDoctrine()->getManager();
        
        $dql = "select g.objeto, g.nombre, g.titulo, g.objetoPadre, g.orden, g.tipoObjeto tipo, g.idEstado estado, ag.objeto permiso "
                . "from FocalAppBundle:AdGrant g left join FocalAppBundle:AdAccesoGrant ag with (g.objeto = ag.objeto and ag.idAcceso = :idacceso and ag.idEstado = :estado) "
                . "where g.visible = 1 and g.idEstado = :estado "
                . "order by g.objeto, g.orden";
        
        $query = $em->createQuery($dql);
        $query ->setParameter('estado',1);
        $query ->setParameter('idacceso',$idacceso);
        $menu = $query->getResult();
        
        $this->construirArbolMenu($menu, $idacceso);
        
        return $this->render('FocalAppBundle:AdAccesoGrant:index.html.twig', array(
            'menu' => $menu,
            'idacceso' => $idacceso,
            'arbol' => $strArb,
        ));
    }
    
public function construirArbolMenu($menu, $idAcceso) {
    global $strArb;
    $strArb="<ul>";
    $noItem = count($menu);
    if ($noItem) {
    foreach ($menu as $item ) {
        if( $item['tipo'] == 1 ) { //tiene hijos
                $strArb.='<li id="'.$item['objeto'].'">'.$item['nombre'].'<ul>';
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
        
    $dql = "select g.objeto, g.nombre, g.titulo, g.objetoPadre, g.orden, g.tipoObjeto tipo, g.idEstado estado, ag.objeto permiso "
                . "from FocalAppBundle:AdGrant g left join FocalAppBundle:AdAccesoGrant ag with (g.objeto = ag.objeto and ag.idAcceso = :idacceso and ag.idEstado = :estado) "
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
                $strArb.='<li id="'.$item['objeto'].'">'.$item['nombre'].'<ul>';                    
                $this->pintarHijos($item['objeto'], $idAcceso);
                $strArb.='</ul></li>';
            } else {
                if(empty($item['permiso'])) {
                    $strArb.='<li id="'.$item['objeto'].'">'.$item['titulo'].'</li>';
                } else {
                    $strArb.='<li id="'.$item['objeto'].'" data-jstree=\'{"selected":true}\'>'.$item['titulo'].'</li>';
                }
            }    
        }              
    }
}    
    /**
     * Creates a new AdAccesoGrant entity.
     *
     */
    public function createAction(Request $request)
    {
        
        $opcacceso = $request->get('arbarray');
        $idacceso = $request->get('acceso');
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');        
        
        $em = $this->getDoctrine()->getManager();
        $estado = 1; //$em->getRepository('AdminBundle:AdEstado')->find(1);       
        $accesogrant = $em->getRepository('FocalAppBundle:AdAccesoGrant')->findBy(array('idAcceso' => $idacceso));
        
        foreach ($accesogrant as $objgrant) {
            $em->remove($objgrant);
        }
        $em->flush();

        foreach ($opcacceso as $idobj) {
            $entity = new AdAccesoGrant();
            
            $entity->setIdAcceso($idacceso);
            $entity->setObjeto($idobj);
            $entity->setIdEstado($estado) ;

            /* Guarda los valores por defecto */
            $fecha = new \DateTime("now");
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);

            $em->persist($entity);
        }
        $em->flush();
        $response->setData(array('result'=>'ok', 'message'=>'Operación terminada con exito!'));
        return $response;
    }

    /**
     * Creates a form to create a AdAccesoGrant entity.
     *
     * @param AdAccesoGrant $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdAccesoGrant $entity)
    {
        $form = $this->createForm(new AdAccesoGrantType(), $entity, array(
            'action' => $this->generateUrl('adaccesogrant_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AdAccesoGrant entity.
     *
     */
    public function newAction()
    {
        $entity = new AdAccesoGrant();
        $form   = $this->createCreateForm($entity);

        return $this->render('AdminBundle:AdAccesoGrant:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdAccesoGrant entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:AdAccesoGrant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdAccesoGrant entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AdminBundle:AdAccesoGrant:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdAccesoGrant entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:AdAccesoGrant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdAccesoGrant entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AdminBundle:AdAccesoGrant:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdAccesoGrant entity.
    *
    * @param AdAccesoGrant $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdAccesoGrant $entity)
    {
        $form = $this->createForm(new AdAccesoGrantType(), $entity, array(
            'action' => $this->generateUrl('adaccesogrant_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AdAccesoGrant entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:AdAccesoGrant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdAccesoGrant entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('adaccesogrant_edit', array('id' => $id)));
        }

        return $this->render('AdminBundle:AdAccesoGrant:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdAccesoGrant entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdminBundle:AdAccesoGrant')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdAccesoGrant entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adaccesogrant'));
    }

    /**
     * Creates a form to delete a AdAccesoGrant entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adaccesogrant_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
