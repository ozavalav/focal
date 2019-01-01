<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\AdOcupaciones;
use Focal\AppBundle\Form\AdOcupacionesType;

/**
 * AdOcupaciones controller.
 *
 */
class AdOcupacionesController extends Controller
{

    /**
     * Lists all AdOcupaciones entities.
     *
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:AdOcupaciones')->findAll();

        return $this->render('FocalAppBundle:AdOcupaciones:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdOcupaciones entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdOcupaciones();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','Datos creados con exito');
            return $this->redirect($this->generateUrl('adocupaciones_new'));
        }

        return $this->render('FocalAppBundle:AdOcupaciones:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdOcupaciones entity.
     *
     * @param AdOcupaciones $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdOcupaciones $entity)
    {
        $form = $this->createForm(new AdOcupacionesType(), $entity, array(
            'action' => $this->generateUrl('adocupaciones_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new AdOcupaciones entity.
     *
     */
    public function newAction()
    {
        $entity = new AdOcupaciones();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdOcupaciones:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdOcupaciones entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdOcupaciones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdOcupaciones entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdOcupaciones:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdOcupaciones entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdOcupaciones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdOcupaciones entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdOcupaciones:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdOcupaciones entity.
    *
    * @param AdOcupaciones $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdOcupaciones $entity)
    {
        $form = $this->createForm(new AdOcupacionesType(), $entity, array(
            'action' => $this->generateUrl('adocupaciones_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing AdOcupaciones entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdOcupaciones')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdOcupaciones entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('adocupaciones'));
        }

        return $this->render('FocalAppBundle:AdOcupaciones:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdOcupaciones entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdOcupaciones')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdOcupaciones entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adocupaciones'));
    }

    /**
     * Creates a form to delete a AdOcupaciones entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adocupaciones_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
