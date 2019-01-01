<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatosServiciospub;
use Focal\AppBundle\Form\DatosServiciospubType;

/**
 * DatosServiciospub controller.
 *
 */
class DatosServiciospubController extends Controller
{

    /**
     * Lists all DatosServiciospub entities.
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
        
        
        $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->findBy(array('idEnc' => $idenc));
        if(!$entity) {
            $dsql ="select sv.id as num, sv.descripcion, 0 as id, 0 as idServicio, 2 as reciben, 0 as estado, 0 as cantDias "
                    . "from FocalAppBundle:AdServiciosPublicos sv  "
                    . "order by sv.id";
                $query = $em->createQuery($dsql);
                $entservp = $query->getResult();
        } else {
            $dsql ="select sv.id as num, sv.descripcion, dt.id, dt.idServicio, dt.reciben, dt.estado, dt.cantDias "
                    . "from FocalAppBundle:AdServiciosPublicos sv  "
                    . "left join FocalAppBundle:DatosServiciospub dt  with (sv.id = dt.idServicio) "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc order by sv.id";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entservp = $query->getResult();
        }            
        return $this->render('FocalAppBundle:DatosServiciospub:index.html.twig', array(
            'entservp' => $entservp,
            'idenc' => $idenc,
            'numboleta' => $numboleta
        ));
    }
    /**
     * Creates a new DatosServiciospub entity.
     *
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idenc = $request->get('idencsp');
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");

        $entities = $em->getRepository('FocalAppBundle:DatosServiciospub')->findBy(Array('idEnc' => $idenc));
        
        if ($entities) {
            foreach ($entities as $ent) {
                $ent->setReciben($request->get('reciben_'.$ent->getIdServicio())); 
                $ent->setEstado($request->get('estado_'.$ent->getIdServicio()));
                //$ent->setCantDias(($request->get('cantDias_'.$ent->getIdServicio())==""?0:$request->get('cantDias_'.$ent->getIdServicio()) > "8"?7:$request->get('cantDias_'.$ent->getIdServicio())));
                $valor = $request->get('cantDias_'.$ent->getIdServicio());
                if($valor == "") {
                    $ent->setCantDias(0);
                } else if($valor > "7") {
                    $ent->setCantDias(7);
                } else {
                    $ent->setCantDias($valor);
                }
                $ent->setUsuarioUltimaModificacion($usuario);
                $ent->setFechaUltimaModificacion($fecha);
            }
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datosserviciospub', array('idenc' => $idenc)));
        }
        
        for($i = 1; $i<=17; $i++) {
            $entity = new DatosServiciospub();
            $sequenceName = 'datos_serviciospub_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;            
            $entity->setId($codCom);
            $entity->setIdEnc($idenc);
            $entity->setidServicio($request->get('num_'.$i));
            $entity->setReciben($request->get('reciben_'.$i)); 
            $entity->setEstado($request->get('estado_'.$i));
            //$entity->setCantDias(($request->get('cantDias_'.$i)==""?0: $request->get('cantDias_'.$i) > "8"?7:$request->get('cantDias_'.$i)));
            $valor = $request->get('cantDias_'.$i);
            if($valor == "") {
                $entity->setCantDias(0);
            } else if($valor > "7") {
                $entity->setCantDias(7);
            } else {
                $entity->setCantDias($valor);
            }
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
        $this->addFlash('success','Datos generados con exito');
        return $this->redirect($this->generateUrl('datosserviciospub', array('idenc' => $idenc)));
    }

    /**
     * Creates a form to create a DatosServiciospub entity.
     *
     * @param DatosServiciospub $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosServiciospub $entity)
    {
        $form = $this->createForm(new DatosServiciospubType(), $entity, array(
            'action' => $this->generateUrl('datosserviciospub_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosServiciospub entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosServiciospub();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosServiciospub:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosServiciospub entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosServiciospub entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosServiciospub:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosServiciospub entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosServiciospub entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosServiciospub:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosServiciospub entity.
    *
    * @param DatosServiciospub $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosServiciospub $entity)
    {
        $form = $this->createForm(new DatosServiciospubType(), $entity, array(
            'action' => $this->generateUrl('datosserviciospub_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosServiciospub entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosServiciospub entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datosserviciospub_edit', array('id' => $id)));
        }

    }
    /**
     * Deletes a DatosServiciospub entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosServiciospub entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosserviciospub'));
    }

    /**
     * Creates a form to delete a DatosServiciospub entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosserviciospub_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
