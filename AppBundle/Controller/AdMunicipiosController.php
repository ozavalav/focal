<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\AdMunicipios;
use Focal\AppBundle\Form\AdMunicipiosType;

/**
 * AdMunicipios controller.
 *
 */
class AdMunicipiosController extends Controller
{

    /**
     * Lists all AdMunicipios entities.
     *
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:AdMunicipios')->findAll();

        return $this->render('FocalAppBundle:AdMunicipios:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdMunicipios entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdMunicipios();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admunicipios_show', array('id' => $entity->getId())));
        }

        return $this->render('FocalAppBundle:AdMunicipios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AdMunicipios entity.
     *
     * @param AdMunicipios $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdMunicipios $entity)
    {
        $form = $this->createForm(new AdMunicipiosType(), $entity, array(
            'action' => $this->generateUrl('admunicipios_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AdMunicipios entity.
     *
     */
    public function newAction()
    {
        $entity = new AdMunicipios();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdMunicipios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AdMunicipios entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdMunicipios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdMunicipios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdMunicipios:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdMunicipios entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdMunicipios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdMunicipios entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdMunicipios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdMunicipios entity.
    *
    * @param AdMunicipios $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdMunicipios $entity)
    {
        $form = $this->createForm(new AdMunicipiosType(), $entity, array(
            'action' => $this->generateUrl('admunicipios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AdMunicipios entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdMunicipios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdMunicipios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admunicipios_edit', array('id' => $id)));
        }

        return $this->render('FocalAppBundle:AdMunicipios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdMunicipios entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdMunicipios')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdMunicipios entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admunicipios'));
    }

    /**
     * Creates a form to delete a AdMunicipios entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admunicipios_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
