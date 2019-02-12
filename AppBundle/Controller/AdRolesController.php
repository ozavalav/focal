<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\AdRoles;
use Focal\AppBundle\Form\AdRolesType;


/**
 * AdRoles controller.
 *
 */
class AdRolesController extends Controller
{

    /**
     * Lists all AdRoles entities.
     *
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:AdRoles')->findAll();

        return $this->render('FocalAppBundle:AdRoles:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdRoles entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdRoles();
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
            
            $em->persist($entity);
            $em->flush();
            
            return $this->redirect($this->generateUrl('adroles'));
        }

        return $this->render('FocalAppBundle:AdRoles:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdRoles entity.
     *
     * @param AdRoles $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdRoles $entity)
    {
        $form = $this->createForm(new AdRolesType(), $entity, array(
            'action' => $this->generateUrl('adroles_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AdRoles entity.
     *
     */
    public function newAction()
    {
        $entity = new AdRoles();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdRoles:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdRoles entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdRoles')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdRoles entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdRoles:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdRoles entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdRoles')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdRoles entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdRoles:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdRoles entity.
    *
    * @param AdRoles $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdRoles $entity)
    {
        $form = $this->createForm(new AdRolesType(), $entity, array(
            'action' => $this->generateUrl('adroles_update', array('id' => $entity->getIdRol())),
            'method' => 'PUT',
        ));
        
        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing AdRoles entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdRoles')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdRoles entity.');
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

            return $this->redirect($this->generateUrl('adroles'));
        }

        return $this->render('FocalAppBundle:AdRoles:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdRoles entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdRoles')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdRoles entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adroles'));
    }

    /**
     * Creates a form to delete a AdRoles entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adroles_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
