<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatosVivienda;
use Focal\AppBundle\Form\DatosViviendaType;

/**
 * DatosVivienda controller.
 *
 */
class DatosViviendaController extends Controller
{

    /**
     * Lists all DatosVivienda entities.
     *
     */
    public function indexAction(Request $request, $idenc)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        $entdg = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=>$idenc));
        $numboleta = $entdg[0]->getNumBoleta();
        $codcomunidad = $entdg[0]->getCodColonia();
        
        $session = $request->getSession();
        $session->set('_num_boleta', $numboleta);
        $session->set('_cod_comunidad', $codcomunidad);
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');

        $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->findBy(array('idEnc' => $idenc));
        
        if ($entity) {
            $form = $this->createEditForm($entity[0]);
        } else {
            $entity = new DatosVivienda();
            $form   = $this->createCreateForm($entity);
        }

        return $this->render('FocalAppBundle:DatosVivienda:index.html.twig', array(
            'form'  => $form->createView(),
            'idenc' => $idenc,
            'numboleta' => $numboleta,
        ));
    }
    /**
     * Creates a new DatosVivienda entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatosVivienda();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $sequenceName = 'datos_vivienda_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            
            $codCom = (int)$codMuni . $newId;
            $entity->setId((int)$codCom);
            
            /* Guarda los valores por defecto */
            $entity->setCodMunicipio($codMuni);
            $entity->setCodDepartamento($codDep);
            $entity->setCodComunidad($codComu);
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha); 
            
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','Datos generados con exito');
            return $this->redirect($this->generateUrl('datosvivienda', array('idenc' => $entity->getIdEnc())));
        }

    }

    /**
     * Creates a form to create a DatosVivienda entity.
     *
     * @param DatosVivienda $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosVivienda $entity)
    {
        $form = $this->createForm(new DatosViviendaType(), $entity, array(
            'action' => $this->generateUrl('datosvivienda_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosVivienda entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosVivienda();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosVivienda:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosVivienda entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosVivienda entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosVivienda:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosVivienda entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosVivienda entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosVivienda:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosVivienda entity.
    *
    * @param DatosVivienda $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosVivienda $entity)
    {
        $form = $this->createForm(new DatosViviendaType(), $entity, array(
            'action' => $this->generateUrl('datosvivienda_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosVivienda entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosVivienda entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datosvivienda', array('idenc' => $entity->getIdEnc())));
        }

        /*return $this->render('FocalAppBundle:DatosVivienda:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));*/
    }
    /**
     * Deletes a DatosVivienda entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosVivienda entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosvivienda'));
    }

    /**
     * Creates a form to delete a DatosVivienda entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosvivienda_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
