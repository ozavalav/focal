<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatosdOtros;
use Focal\AppBundle\Entity\DatosdOtrosnac;
use Focal\AppBundle\Form\DatosdOtrosType;

/**
 * DatosdOtros controller.
 *
 */
class DatosdOtrosController extends Controller
{

    /**
     * Lists all DatosdOtros entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:DatosdOtros')->findAll();

        return $this->render('FocalAppBundle:DatosdOtros:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatosdOtros entity.
     *
     */
    public function createAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $entity = new DatosdOtros();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            
            $sequenceName = 'datosd_otros_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;
            $entity->setId($codCom);
            
            
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
            
            
            /* Guarda la informacion de nacimientos */
            $contnac = $request->get('contnac');
            $conth = 0;
            $contm = 0;
            $ordennum = 1;
            for($i=1; $i<=$contnac; $i++ ) {
                if($request->get('tblest_'.$i) == '1') {
                    
                    $entitynac = new DatosdOtrosnac();
                    $entitynac->setCodDepartamento($codDep);
                    $entitynac->setCodMunicipio($codMuni);
                    $entitynac->setCodComunidad($codComu);
                    $entitynac->setIdEnc($entity->getIdEnc());
                    $entitynac->setIdDatossotros($codCom);
                    $entitynac->setNum($ordennum++);
                    $entitynac->setCantidad($request->get('tblcant_'.$i));
                    $entitynac->setCantNinos($request->get('tblcanth_'.$i));
                    $entitynac->setCantNinas($request->get('tblcantm_'.$i));
                    $entitynac->setEdad($request->get('tbledad_'.$i));

                    $conth = $conth + $request->get('tblcanth_'.$i);
                    $contm = $contm + $request->get('tblcantm_'.$i);

                    /* Guarda los valores por defecto */
                    $entitynac->setUsuarioCreacion($usuario);
                    $entitynac->setUsuarioUltimaModificacion($usuario);
                    $entitynac->setFechaCreacion($fecha);
                    $entitynac->setFechaUltimaModificacion($fecha); 
                    $em->persist($entitynac);
                }
            }
            
            $entity->setCantNacimientos($conth + $contm);
            $entity->setCantNacNinos($conth);
            $entity->setCantNacNinas($contm);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('datosdfamilia', array('idenc' => $entity->getIdEnc())));
        }

    }

    /**
     * Creates a form to create a DatosdOtros entity.
     *
     * @param DatosdOtros $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosdOtros $entity)
    {
        $form = $this->createForm(new DatosdOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosdotros_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosdOtros entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosdOtros();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosdOtros:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosdOtros entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdOtros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdOtros:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosdOtros entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdOtros entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdOtros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosdOtros entity.
    *
    * @param DatosdOtros $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosdOtros $entity)
    {
        $form = $this->createForm(new DatosdOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosdotros_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosdOtros entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdOtros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdOtros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");

        if ($editForm->isValid()) {
            
            /* Actualiza la informacion de los nacimientos */
            
            $contnac = $request->get('contnac');
            $contnacactual = $entity->getCantNacimientos();
            $conth = $entity->getCantNacNinos();
            $contm = $entity->getCantNacNinas();
            $ordennum = 1;
            for($i=1; $i<=$contnac; $i++ ) {
                
                /* Creación de uno nuevo */
                if($request->get('tblest_'.$i) == '1') {
                    
                    $entitynac = new DatosdOtrosnac();
                    $entitynac->setCodDepartamento($codDep);
                    $entitynac->setCodMunicipio($codMuni);
                    $entitynac->setCodComunidad($codComu);
                    $entitynac->setIdEnc($entity->getIdEnc());
                    $entitynac->setIdDatossotros($id);
                    $entitynac->setNum($ordennum++);
                    $entitynac->setCantidad($request->get('tblcant_'.$i));
                    $entitynac->setCantNinos($request->get('tblcanth_'.$i));
                    $entitynac->setCantNinas($request->get('tblcantm_'.$i));
                    $entitynac->setEdad($request->get('tbledad_'.$i));
                    
                    $conth = $conth + $request->get('tblcanth_'.$i);
                    $contm = $contm + $request->get('tblcantm_'.$i);
                    
                    /* Guarda los valores por defecto */
                    $entitynac->setUsuarioCreacion($usuario);
                    $entitynac->setUsuarioUltimaModificacion($usuario);
                    $entitynac->setFechaCreacion($fecha);
                    $entitynac->setFechaUltimaModificacion($fecha); 
                    $em->persist($entitynac);
                    $contnacactual++;
                } 
                
                /* Solo actualizar la información */
                if($request->get('tblest_'.$i) == '2') {
                    $entitynac = $em->getRepository('FocalAppBundle:DatosdOtrosnac')->find($request->get('tblidotros_'.$i));
                    
                    $entitynac->setNum($ordennum++);
                    $entitynac->setCantidad($request->get('tblcant_'.$i));
                    $entitynac->setCantNinos($request->get('tblcanth_'.$i));
                    $entitynac->setCantNinas($request->get('tblcantm_'.$i));
                    $entitynac->setEdad($request->get('tbledad_'.$i));
                    /* Guarda los valores por defecto */

                    $entitynac->setUsuarioUltimaModificacion($usuario);
                    $entitynac->setFechaUltimaModificacion($fecha); 
                    $em->persist($entitynac);
                    
                }
                
                /* Eliminar el registro */
                if($request->get('tblest_'.$i) == '0') {
                    $entitynac = $em->getRepository('FocalAppBundle:DatosdOtrosnac')->find($request->get('tblidotros_'.$i));
                    if($entitynac) {
                        $conth = $conth - $request->get('tblcanth_'.$i);
                        $contm = $contm - $request->get('tblcantm_'.$i);
                        $em->remove($entitynac);
                        $contnacactual--;
                    }
                }
            } //final del for
            
            $entity->setCantNacimientos($conth + $contm);
            $entity->setCantNacNinos($conth);
            $entity->setCantNacNinas($contm);            
            
            
            $em->flush();

            return $this->redirect($this->generateUrl('datosdfamilia', array('idenc' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatosdOtros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatosdOtros entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosdOtros')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosdOtros entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosdotros'));
    }

    /**
     * Creates a form to delete a DatosdOtros entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosdotros_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
