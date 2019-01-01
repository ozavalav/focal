<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\AdDepartamentos;
use Focal\AppBundle\Form\AdDepartamentosType;

/**
 * AdDepartamentos controller.
 *
 */
class AdDepartamentosController extends Controller
{

    /**
     * Lists all AdDepartamentos entities.
     *
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll();

        return $this->render('FocalAppBundle:AdDepartamentos:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdDepartamentos entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdDepartamentos();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('addepartamentos_show', array('id' => $entity->getId())));
        }

        return $this->render('FocalAppBundle:AdDepartamentos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdDepartamentos entity.
     *
     * @param AdDepartamentos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdDepartamentos $entity)
    {
        $form = $this->createForm(new AdDepartamentosType(), $entity, array(
            'action' => $this->generateUrl('addepartamentos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AdDepartamentos entity.
     *
     */
    public function newAction()
    {
        $entity = new AdDepartamentos();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdDepartamentos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdDepartamentos entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdDepartamentos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdDepartamentos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdDepartamentos:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdDepartamentos entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdDepartamentos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdDepartamentos entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdDepartamentos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdDepartamentos entity.
    *
    * @param AdDepartamentos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdDepartamentos $entity)
    {
        $form = $this->createForm(new AdDepartamentosType(), $entity, array(
            'action' => $this->generateUrl('addepartamentos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AdDepartamentos entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdDepartamentos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdDepartamentos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('addepartamentos_edit', array('id' => $id)));
        }

        return $this->render('FocalAppBundle:AdDepartamentos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdDepartamentos entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdDepartamentos')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdDepartamentos entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('addepartamentos'));
    }

    /**
     * Creates a form to delete a AdDepartamentos entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('addepartamentos_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
