<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\AdEncuestadores;
use Focal\AppBundle\Form\AdEncuestadoresType;

use Focal\AppBundle\Entity\AppConst;

/**
 * AdEncuestadores controller.
 *
 */
class AdEncuestadoresController extends Controller
{
    public function borrarregistroencAction(Request $request, $param){
        
        $idreg = $param;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();  
        $entity = $em->getRepository('FocalAppBundle:AdEncuestadores')->find($idreg);

        if (!$entity) {
            $response->setData(array('message' => 'false'));
        } else {
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");  
            $entity->setUsuarioUltimaModificacion($usuario);  
            $entity->setFechaUltimaModificacion($fecha);
            
            $entity->setEstado(AppConst::ESTADO_GENERAL_INACTIVO);
            
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    /**
     * Lists all AdEncuestadores entities.
     *
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
       
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        $dsql ="select co.id, co.codMunicipio, mu.nombre as nombreMunicipio, co.nombre, co.estado  "
                . "from FocalAppBundle:AdEncuestadores co "
                . "join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = co.codMunicipio) "
                . "where co.codMunicipio = :municipio and co.estado = :estado";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('estado', AppConst::ESTADO_GENERAL_ACTIVO);
            $entities = $query->getResult();

        return $this->render('FocalAppBundle:AdEncuestadores:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdEncuestadores entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdEncuestadores();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);
            $entity->setEstado(AppConst::ESTADO_GENERAL_ACTIVO);
            
            $session = $request->getSession();
            $codMuni = $session->get('_cod_municipio');
            $entity->setcodMunicipio($codMuni);
            
            
            $sequenceName = 'ad_encuestadores_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            
            $codCom = $codMuni . $newId;
            $entity->setId($codCom);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('adencuestadores'));
        }

        return $this->render('FocalAppBundle:AdEncuestadores:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdEncuestadores entity.
     *
     * @param AdEncuestadores $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdEncuestadores $entity)
    {
        $form = $this->createForm(new AdEncuestadoresType(), $entity, array(
            'action' => $this->generateUrl('adencuestadores_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new AdEncuestadores entity.
     *
     */
    public function newAction()
    {
        $entity = new AdEncuestadores();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdEncuestadores:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdEncuestadores entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdEncuestadores')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdEncuestadores entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdEncuestadores:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdEncuestadores entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdEncuestadores')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdEncuestadores entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdEncuestadores:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdEncuestadores entity.
    *
    * @param AdEncuestadores $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdEncuestadores $entity)
    {
        $form = $this->createForm(new AdEncuestadoresType(), $entity, array(
            'action' => $this->generateUrl('adencuestadores_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing AdEncuestadores entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdEncuestadores')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdEncuestadores entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");  
            $entity->setUsuarioUltimaModificacion($usuario);  
            $entity->setFechaUltimaModificacion($fecha);
            $em->flush();

            return $this->redirect($this->generateUrl('adencuestadores'));
        }

        return $this->render('FocalAppBundle:AdEncuestadores:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdEncuestadores entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdEncuestadores')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdEncuestadores entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adencuestadores'));
    }

    /**
     * Creates a form to delete a AdEncuestadores entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adencuestadores_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
