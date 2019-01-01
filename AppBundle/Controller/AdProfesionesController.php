<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\AdProfesiones;
use Focal\AppBundle\Form\AdProfesionesType;

/**
 * AdProfesiones controller.
 *
 */
class AdProfesionesController extends Controller
{

    /**
     * Lists all AdProfesiones entities.
     *
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:AdProfesiones')->findAll();

        return $this->render('FocalAppBundle:AdProfesiones:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdProfesiones entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdProfesiones();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','Datos creados con exito');
            return $this->redirect($this->generateUrl('adprofesiones_new'));
        }

        return $this->render('FocalAppBundle:AdProfesiones:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdProfesiones entity.
     *
     * @param AdProfesiones $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdProfesiones $entity)
    {
        $form = $this->createForm(new AdProfesionesType(), $entity, array(
            'action' => $this->generateUrl('adprofesiones_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new AdProfesiones entity.
     *
     */
    public function newAction()
    {
        $entity = new AdProfesiones();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdProfesiones:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdProfesiones entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdProfesiones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdProfesiones entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdProfesiones:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdProfesiones entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdProfesiones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdProfesiones entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdProfesiones:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdProfesiones entity.
    *
    * @param AdProfesiones $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdProfesiones $entity)
    {
        $form = $this->createForm(new AdProfesionesType(), $entity, array(
            'action' => $this->generateUrl('adprofesiones_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing AdProfesiones entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdProfesiones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdProfesiones entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('adprofesiones'));
        }

        return $this->render('FocalAppBundle:AdProfesiones:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdProfesiones entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdProfesiones')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdProfesiones entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adprofesiones'));
    }

    /**
     * Creates a form to delete a AdProfesiones entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adprofesiones_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
