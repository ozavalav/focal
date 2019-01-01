<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\DatosFuerzaOtros;
use Focal\AppBundle\Form\DatosFuerzaOtrosType;

/**
 * DatosFuerzaOtros controller.
 *
 */
class DatosFuerzaOtrosController extends Controller
{

    /* 
     * Agregar nuevas enfermedades 

     **/
    public function calcularingresototalAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $idenc = $parametros[0];
        $cantr = $parametros[1];

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();      
        
        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc' => $idenc));
        
        $dsql ="select sum(dt.ingresos) as tifam "
                . "from FocalAppBundle:DatosFuerzaIngresos dt "
                . "where dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('idenc', $idenc);
            $entIngTotal = $query->getResult();
        
        $totaling = $entIngTotal[0]['tifam'] + $cantr;
        
        if($totaling <= 1000) {
           $rngfam = 1; 
        } else if($totaling > 1000 && $totaling <= 2000){
            $rngfam = 2;
        } else if($totaling > 2001 && $totaling <= 4000) {
            $rngfam = 3;
        } else if($totaling > 4001 && $totaling <= 8000) {
            $rngfam = 4;
        } else if($totaling > 8001 && $totaling <= 12000) {
            $rngfam = 5;
        } else if($totaling > 12001 && $totaling <= 20000) {
            $rngfam = 6;
        } else if($totaling > 20001 && $totaling <= 30000) {
            $rngfam = 7;
        } else if($totaling > 30001 && $totaling <= 50000) {
            $rngfam = 8;    
        } else {
            $rngfam = 9;
        }
        
        $response->setData(array('message' => 'true', 'rangoi' => $rngfam, 'canti' => $totaling));  
               
        return $response;
 
    }
    
    
    /**
     * Lists all DatosFuerzaOtros entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findAll();

        return $this->render('FocalAppBundle:DatosFuerzaOtros:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatosFuerzaOtros entity.
     *
     */
    public function createAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $entity = new DatosFuerzaOtros();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $session = $request->getSession();
            
            $codMuni = $session->get('_cod_municipio');
            $codDep = $session->get('_cod_departamento');
            $codComu = $session->get('_cod_comunidad');
            
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");

            $sequenceName = 'datos_fuerza_otros_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            
            $codCom = $codMuni . $newId;  
            $entity->setId($codCom);
            
            /* verifica que la propiedad de rangoRemesas exista en caso que no se reciban remesas */
            $tipo = gettype($entity->getRangoRemesas());
            if ($tipo !== 'NULL') {
                $rngt = $entity->getRangoRemesas()->getId();
            } else {
                $rngt = 0;
            }

            if($rngt >= 1) {
                $entity->setRangoRemesas($entity->getRangoRemesas()->getId());
            } else {
                $entity->setRangoRemesas(0);
            }
            $entity->setRangoIngresofam($entity->getRangoIngresofam()->getId());
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
            $this->addFlash('success','Datos generados con exito');
            return $this->redirect($this->generateUrl('datosfuerzaingresos', array('idenc' => $entity->getIdEnc())));
        }
    }

    /**
     * Creates a form to create a DatosFuerzaOtros entity.
     *
     * @param DatosFuerzaOtros $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosFuerzaOtros $entity)
    {
        $form = $this->createForm(new DatosFuerzaOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosfuerzaotros_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosFuerzaOtros entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosFuerzaOtros();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosFuerzaOtros:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosFuerzaOtros entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaOtros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosFuerzaOtros:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosFuerzaOtros entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaOtros entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosFuerzaOtros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosFuerzaOtros entity.
    *
    * @param DatosFuerzaOtros $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosFuerzaOtros $entity)
    {
        $form = $this->createForm(new DatosFuerzaOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosfuerzaotros_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosFuerzaOtros entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaOtros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            
            if($entity->getRangoRemesas()) {
                $entity->setRangoRemesas($entity->getRangoRemesas()->getId());
            }
            
            if($entity->getRangoIngresofam()){
                $entity->setRangoIngresofam($entity->getRangoIngresofam()->getId());
            }
            
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datosfuerzaingresos', array('idenc' => $entity->getIdEnc())));
        }

        /*return $this->render('FocalAppBundle:DatosFuerzaOtros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));*/
    }
    /**
     * Deletes a DatosFuerzaOtros entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosFuerzaOtros entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosfuerzaotros'));
    }

    /**
     * Creates a form to delete a DatosFuerzaOtros entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosfuerzaotros_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
