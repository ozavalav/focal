<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatosSeguridadParticipacion;
use Focal\AppBundle\Form\DatosSeguridadParticipacionType;

/**
 * DatosSeguridadParticipacion controller.
 *
 */
class DatosSeguridadParticipacionController extends Controller
{

    /**
     * Lists all DatosSeguridadParticipacion entities.
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
        
        $dsql ="select 
                sum(case when dt.sexo=1 then 1 else 0 end) canthm18,
                sum(case when dt.sexo=2 then 1 else 0 end) cantmm18
                from FocalAppBundle:DatosdFamilia dt
                where dt.idEnc = :idenc and dt.edad >=18 and dt.codMunicipio = :municipio";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entityM18 = $query->getResult(); 
        $canthm18 = $entityM18[0]['canthm18'];
        $cantmm18 = $entityM18[0]['cantmm18'];
        
            
        $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->findBy(array('idEnc'=>$idenc));
        if ($entity) {
            //$entdsp = $em->getRepository('FocalAppBundle:DatossGeneral')->find($entity[0]->getId());
            $form = $this->createEditForm($entity[0]);
            //$mtdsel = $entdsp->getMetodo();
        } else {
            $entitysp = new DatosSeguridadParticipacion();
            $form   = $this->createCreateForm($entitysp);
            //$mtdsel = '';
        }

        //$entities = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->findAll();

        return $this->render('FocalAppBundle:DatosSeguridadParticipacion:index.html.twig', array(
            'entities' => $entity,
            'idenc' => $idenc,
            'canthm18' => $canthm18,
            'cantmm18' => $cantmm18,
            'numboleta' => $numboleta,
            'form'  => $form->createView()  
        ));
    }
    /**
     * Creates a new DatosSeguridadParticipacion entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatosSeguridadParticipacion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $sequenceName = 'datos_seguridad_participacion_id_seq';
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
            return $this->redirect($this->generateUrl('datosseguridadpar', array('idenc' => $entity->getIdEnc())));
        }

    }

    /**
     * Creates a form to create a DatosSeguridadParticipacion entity.
     *
     * @param DatosSeguridadParticipacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosSeguridadParticipacion $entity)
    {
        $form = $this->createForm(new DatosSeguridadParticipacionType(), $entity, array(
            'action' => $this->generateUrl('datosseguridadpar_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosSeguridadParticipacion entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosSeguridadParticipacion();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosSeguridadParticipacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosSeguridadParticipacion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosSeguridadParticipacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosSeguridadParticipacion:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosSeguridadParticipacion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosSeguridadParticipacion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosSeguridadParticipacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosSeguridadParticipacion entity.
    *
    * @param DatosSeguridadParticipacion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosSeguridadParticipacion $entity)
    {
        $form = $this->createForm(new DatosSeguridadParticipacionType(), $entity, array(
            'action' => $this->generateUrl('datosseguridadpar_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosSeguridadParticipacion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosSeguridadParticipacion entity.');
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
            return $this->redirect($this->generateUrl('datosseguridadpar', array('idenc' => $entity->getIdEnc())));
        }

    }
    /**
     * Deletes a DatosSeguridadParticipacion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosSeguridadParticipacion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosseguridadpar'));
    }

    /**
     * Creates a form to delete a DatosSeguridadParticipacion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosseguridadpar_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
