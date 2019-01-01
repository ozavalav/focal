<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatossEdadvacunas;
use Focal\AppBundle\Form\DatossEdadvacunasType;

/**
 * DatossEdadvacunas controller.
 *
 */
class DatossEdadvacunasController extends Controller
{

    /**
     * Lists all DatossEdadvacunas entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->findAll();

        return $this->render('FocalAppBundle:DatossEdadvacunas:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatossEdadvacunas entity.
     *
     */
    public function createAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        $idenc = $request->get('idencrv');
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        
        $entities = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->findBy(Array('idEnc' => $idenc));
              
        if ($entities) {
            foreach ($entities as $ent) {
                $ent->setCantCompleta(($request->get('cvac_'.$ent->getRango())==""?0:$request->get('cvac_'.$ent->getRango())));
                $ent->setCantIncompleta(($request->get('sinv_'.$ent->getRango())==""?0:$request->get('sinv_'.$ent->getRango())));
                $ent->setUsuarioUltimaModificacion($usuario);
                $ent->setFechaUltimaModificacion($fecha);
            }
            $em->flush();
            
            return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $idenc)));
        }
 
        
        for($i = 1; $i<=4; $i++) {
            $entity = new DatossEdadvacunas();
            $sequenceName = 'datoss_edadvacunas_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;            
            $entity->setId($codCom);
            $entity->setIdEnc($idenc);
            $entity->setRango($request->get('num_'.$i));
            $entity->setCantPersonas($request->get('cantp_'.$i)); 
            $entity->setCantHombres($request->get('canth_'.$i));
            $entity->setCantMujeres($request->get('cantm_'.$i));
            $entity->setCantCompleta(($request->get('cvac_'.$i)==""?0:$request->get('cvac_'.$i)));
            $entity->setCantIncompleta(($request->get('sinv_'.$i)==""?0:$request->get('sinv_'.$i)));
            
            /* Guarda los valores por defecto */
            $entity->setCodDepartamento($codDep);
            $entity->setCodMunicipio($codMuni);
            $entity->setCodComunidad($codComu);
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);
            $em->persist($entity);
            $em->flush();
            
        }  
        
       
        return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $idenc)));
        
    }

    /**
     * Creates a form to create a DatossEdadvacunas entity.
     *
     * @param DatossEdadvacunas $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatossEdadvacunas $entity)
    {
        $form = $this->createForm(new DatossEdadvacunasType(), $entity, array(
            'action' => $this->generateUrl('datossedadvacunas_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatossEdadvacunas entity.
     *
     */
    public function newAction()
    {
        $entity = new DatossEdadvacunas();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatossEdadvacunas:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatossEdadvacunas entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEdadvacunas entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossEdadvacunas:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatossEdadvacunas entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEdadvacunas entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossEdadvacunas:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatossEdadvacunas entity.
    *
    * @param DatossEdadvacunas $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatossEdadvacunas $entity)
    {
        $form = $this->createForm(new DatossEdadvacunasType(), $entity, array(
            'action' => $this->generateUrl('datossedadvacunas_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatossEdadvacunas entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossEdadvacunas entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('datossedadvacunas_edit', array('id' => $id)));
        }

        return $this->render('FocalAppBundle:DatossEdadvacunas:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatossEdadvacunas entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatossEdadvacunas entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datossedadvacunas'));
    }

    /**
     * Creates a form to delete a DatossEdadvacunas entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datossedadvacunas_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
