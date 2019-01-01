<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\DatossGeneral;
use Focal\AppBundle\Entity\DatossEdadvacunas;
use Focal\AppBundle\Entity\AdEdadvacunas;
use Focal\AppBundle\Form\DatossGeneralType;
use Focal\AppBundle\Entity\DatossEnfermedades;

use Doctrine\ORM\Query\ResultSetMapping;


/**
 * DatossGeneral controller.
 *
 */
class DatossGeneralController extends Controller
{ 
    public function borrarregistroenfAction(Request $request, $param){
        
        $idreg = $param;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();  
        $entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->find($idreg);

        if (!$entity) {
            $response->setData(array('message' => 'false'));
        } else {
            $em->remove($entity);
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    /* 
     * Agregar nuevas enfermedades 

     **/
    public function agregarenfermedadesAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $enfer = $parametros[0];
        $cantma = $parametros[1];
        $canth = $parametros[2];
        $cantm = $parametros[3];
        $cantpr = $parametros[4];
        $cantpu = $parametros[5];
        $idenc = $parametros[6];

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();      
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');

        $dsql ="select dt.id "
                . "from FocalAppBundle:DatossEnfermedades dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and dt.idEnfermedad = :idEnfer";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $query ->setParameter('idEnfer', $enfer);
            $entEnfer = $query->getResult();
        
        if($entEnfer) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: La enfermedad ya esta ingresada.'));
            return $response;
        }
            
        $sequenceName = 'datoss_enfermedades_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

        $codCom = $codMuni . $newId;
        
        $entity = new DatossEnfermedades();

        $entity->setId($codCom);
        $entity->setIdEnc($idenc);
        $entity->setCodMunicipio($codMuni);
        $entity->setCodDepartamento($codDep);
        $entity->setCodComunidad($codComu);
        $entity->setIdEnfermedad($enfer);
        $entity->setCantManifestaron($cantma);
        $entity->setCantHombres($canth);
        $entity->setCantMujeres($cantm);
        $entity->setCantPublica($cantpu);
        $entity->setCantPrivada($cantpr);
        
        /* Guarda los valores por defecto */
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        $entity->setUsuarioCreacion($usuario);
        $entity->setUsuarioUltimaModificacion($usuario);
        $entity->setFechaCreacion($fecha);
        $entity->setFechaUltimaModificacion($fecha);
        
        
        $em->persist($entity);
        $em->flush();
        
        if (!$entity) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: Hubo un error al insertar registro'));
        } else {
            $response->setData(array('message' => 'true'));  
        }
        return $response;
 
    }


    /**
     * Lists all DatossGeneral entities.
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
        
        $dsql ="select dt.id, dt.idEnfermedad, en.descripcion, dt.cantManifestaron, dt.cantHombres, dt.cantMujeres, "
                . "dt.cantPublica, dt.cantPrivada "
                . "from FocalAppBundle:DatossEnfermedades dt "
                . "left join FocalAppBundle:AdEnfermedades en with (en.id = dt.idEnfermedad) "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entEnfer = $query->getResult();
        
        $dsql ="select dt.id, dt.rango as num, ev.descripcion as rango, dt.cantPersonas as cantpersonas, dt.cantHombres as canthombres, dt.cantMujeres as cantmujeres, "
                . "dt.cantCompleta as cantcompleta, dt.cantIncompleta as cantincompleta "
                . "from FocalAppBundle:DatossEdadvacunas dt "
                . "left join FocalAppBundle:AdEdadvacunas ev with (ev.id = dt.rango) "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entityRVac = $query->getResult();  
            
        $dsql ="select dt.cantNacimientos "
                . "from FocalAppBundle:DatosdOtros dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entddemo = $query->getResult();
            
            if($entddemo) {
                $cantNac = $entddemo[0]['cantNacimientos'];
            }else {
                $cantNac = 0;
            }
    //$entityRVac = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->findBy(array('idEnc' => $idenc));
    
    if(!$entityRVac) {
        $sqlstr = "select   
         1 as num,
         '0' as id,
         'Menores de 9 años' as rango, 
         sum(case when edad <= 9 then 1 else 0 end) as cantPersonas,	
         sum(case when edad <= 9 and sexo = 1 then 1 else 0 end) as cantHombres,
         sum(case when edad <= 9 and sexo = 2 then 1 else 0 end) as cantMujeres,
         0 as cantcompleta,
         0 as cantincompleta
        from datosd_familia
        where id_enc = " . $idenc 
        . " union
        select
         2 as num,
         '0' as id,
         'De 10 a 19 años' as rango,
         sum(case when (edad >= 10 and edad <= 19) then 1 else 0 end) as cantPersonas,
         sum(case when (edad >= 10 and edad <= 19) and sexo = 1 then 1 else 0 end) as cantHombres,
         sum(case when (edad >= 10 and edad <= 19) and sexo = 2 then 1 else 0 end) as cantMujeres,
         0 as cantcompleta,
         0 as cantincompleta
        from datosd_familia
        where id_enc = " . $idenc 
        ." union
        select
         3 as num,
         '0' as id,
         'De 20 a 64 años' as rango, 
         sum(case when (edad >= 20 and edad <= 64) then 1 else 0 end) as cantPersonas,
         sum(case when (edad >= 20 and edad <= 64) and sexo = 1 then 1 else 0 end) as cantHombres,
         sum(case when (edad >= 20 and edad <= 64) and sexo = 2 then 1 else 0 end) as cantMujeres,
         0 as cantcompleta,
         0 as cantincompleta
        from datosd_familia
        where id_enc = " . $idenc
        ." union
          select
          4 as num,
          '0' as id,
            'De 65 años y mas' as rango,
            sum(case when edad >= 65 then 1 else 0 end) as cantPersonas,
            sum(case when edad >= 65 and sexo = 1 then 1 else 0 end) as cantHombres,
            sum(case when edad >= 65 and sexo = 2 then 1 else 0 end) as cantMujeres,
            0 as cantcompleta,
            0 as cantincompleta
           from datosd_familia
           where id_enc = ". $idenc . "order by 1";            

        $connection = $em->getConnection();
        $query = $connection->prepare($sqlstr);
        $query->execute();
        $entityRVac = $query->fetchAll();
    }
        $entmp = $em->getRepository('FocalAppBundle:AdMetodoPlan')->findAll();
        $entef = $em->getRepository('FocalAppBundle:AdEnfermedades')->findAll();
        
        $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->findBy(array('idEnc'=>$idenc));
        if ($entity) {
            $entdsg = $em->getRepository('FocalAppBundle:DatossGeneral')->find($entity[0]->getId());
            $form = $this->createEditForm($entdsg);
            $mtdsel = $entdsg->getMetodo();
        } else {
            $entitysg = new DatossGeneral();
            $form   = $this->createCreateForm($entitysg);
            $mtdsel = '';
        }
        
        return $this->render('FocalAppBundle:DatossGeneral:index.html.twig', array(
            'entities' => $entity,
            'rnge' => $entmp,
            'idenc' => $idenc,
            'numboleta' => $numboleta,
            'mtdsel' => $mtdsel,
            'entenf' => $entEnfer,
            'catenf' => $entef,
            'entrvac' => $entityRVac,
            'cantNac' => $cantNac,
            'form'  => $form->createView()            
        ));
    }
    /**
     * Creates a new DatossGeneral entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatossGeneral();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $sequenceName = 'datoss_general_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = (int)$codMuni . $newId;
            
            $entity->setId((int)$codCom);

            if ($entity->getMetodo() !== null) {
                $idMetodo = $entity->getMetodo()->getId();
                $entity->setMetodo($idMetodo);
            }

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
            $this->addFlash('success','Datos guardados con exito');
            return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatossGeneral:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a DatossGeneral entity.
     *
     * @param DatossGeneral $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatossGeneral $entity)
    {
        $form = $this->createForm(new DatossGeneralType(), $entity, array(
            'action' => $this->generateUrl('datossgeneral_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatossGeneral entity.
     *
     */
    public function newAction()
    {
        $entity = new DatossGeneral();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatossGeneral:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatossGeneral entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossGeneral entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossGeneral:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatossGeneral entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossGeneral entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatossGeneral:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatossGeneral entity.
    *
    * @param DatossGeneral $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatossGeneral $entity)
    {
        $form = $this->createForm(new DatossGeneralType(), $entity, array(
            'action' => $this->generateUrl('datossgeneral_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatossGeneral entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->find($id);
        //$idActualEnf = $entity->getIdEnfermedad();
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatossGeneral entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        
        
        /*$dsql ="select dt.id "
                . "from FocalAppBundle:DatossEnfermedades dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and idEnfermedad = :idEnfer and idEnfermedad <> :idActualEnf ";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $entity->getCodMunicipio());
            $query ->setParameter('idenc', $entity->getIdEnc());
            $query ->setParameter('idEnfer', $entity->getIdEnfermedad());
            $query ->setParameter('idAcualEnf', $idActualEnf);
            $entEnfer = $query->getResult();
        
        if($entEnfer) {
            $this->addFlash('warning','La enfermedad ya esta ingresada');
            return $this->render('FocalAppBundle:DatossGeneral:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'idenc' => $entity->getIdEnc(),   
            ));
        }*/

        if ($editForm->isValid()) {
            
            if ($entity->getMetodo() !== null) {
                $idMetodo = $entity->getMetodo()->getId();
                $entity->setMetodo($idMetodo);
            }
            
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datossgeneral', array('idenc' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatossGeneral:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatossGeneral entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatossGeneral entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datossgeneral'));
    }

    /**
     * Creates a form to delete a DatossGeneral entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datossgeneral_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
