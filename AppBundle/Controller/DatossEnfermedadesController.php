<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatossEnfermedades;
use Focal\AppBundle\Form\DatossEnfermedadesType;

/**
 * DatossEnfermedades controller.
 *
 */
class DatossEnfermedadesController extends Controller
{

    /**
     * Lists all DatossEnfermedades entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:DatossEnfermedades')->findAll();

        return $this->render('FocalAppBundle:DatossEnfermedades:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatossEnfermedades entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatossEnfermedades();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('datossenfermedades_show', array('id' => $entity->getId())));
        }

        return $this->render('FocalAppBundle:DatossEnfermedades:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a DatossEnfermedades entity.
     *
     * @param DatossEnfermedades $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatossEnfermedades $entity)
    {
        $form = $this->createForm(new DatossEnfermedadesType(), $entity, array(
            'action' => $this->generateUrl('datossenfermedades_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatossEnfermedades entity.
     *
     */
    public function newAction()
    {
        $entity = new DatossEnfermedades();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatossEnfermedades:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatossEnfermedades entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEnfermedades entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossEnfermedades:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatossEnfermedades entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEnfermedades entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossEnfermedades:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'idenc' => $entity->getIdEnc(),
            'enfsel' => $entity->getIdEnfermedad(),
        ));
    }

    /**
    * Creates a form to edit a DatossEnfermedades entity.
    *
    * @param DatossEnfermedades $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatossEnfermedades $entity)
    {
        $form = $this->createForm(new DatossEnfermedadesType(), $entity, array(
            'action' => $this->generateUrl('datossenfermedades_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatossEnfermedades entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->find($id);
         $idActualEnf = $entity->getIdEnfermedad();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEnfermedades entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        
        $dsql ="select dt.id "
                . "from FocalAppBundle:DatossEnfermedades dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and dt.idEnfermedad = :idEnfer and dt.idEnfermedad <> :idActualEnf ";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $entity->getCodMunicipio());
            $query ->setParameter('idenc', $entity->getIdEnc());
            $query ->setParameter('idEnfer', $entity->getIdEnfermedad());
            $query ->setParameter('idActualEnf', $idActualEnf);
            $entEnfer = $query->getResult();
        
        if($entEnfer) {
            $this->addFlash('warning','La enfermedad ya esta ingresada');
            return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $entity->getIdEnc())));
        }
        
        if ($editForm->isValid()) {
            if ($entity->getIdEnfermedad() !== null) {
                $idenf = $entity->getIdEnfermedad()->getId();
                $entity->setIdEnfermedad($idenf);
            }
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            
            $em->flush();

            return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatossEnfermedades:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatossEnfermedades entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatossEnfermedades entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datossenfermedades'));
    }

    /**
     * Creates a form to delete a DatossEnfermedades entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datossenfermedades_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
