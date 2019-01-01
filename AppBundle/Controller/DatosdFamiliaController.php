<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\DatosdFamilia;
use Focal\AppBundle\Form\DatosdFamiliaType;
use Focal\AppBundle\Form\DatosdOtrosType;
use Focal\AppBundle\Entity\DatosdRangos;
use Focal\AppBundle\Entity\DatosdOtros;

/**
 * DatosdFamilia controller.
 *
 */
class DatosdFamiliaController extends Controller
{
    public function borrarregistrorngAction(Request $request, $param){
        
        $idreg = $param;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();  
        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($idreg);

        if (!$entity) {
            $response->setData(array('message' => 'false'));
        } else {
            $em->remove($entity);
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    
    public function borrarregistrofamAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $idreg = $parametros[0];
        $idDep = $parametros[1];
        $idMun = $parametros[2];
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();  
        $entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->find($idreg);

        /* Borrar datos de Educación */
        $idenc = $entity->getIdEnc();
        $entitye = $em->getRepository('FocalAppBundle:DatosEducacion')->findBy(array('idEnc'=> $idenc, 'codDepartamento' => $codDep, 'codMunicipio'=> $codMuni));
        foreach ($entitye as $ent) {
            $em->remove($ent);
            $em->flush();
        }
        $entityfi = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->findBy(array('idEnc'=> $idenc, 'codDepartamento' => $codDep, 'codMunicipio'=> $codMuni));
        /* Borrar datos de Fuerza laboral e ingresos */
        foreach ($entityfi as $ent) {
            $em->remove($ent);
            $em->flush();
        }
        
        if (!$entity) {
            $response->setData(array('message' => 'false'));
        } else {
            $em->remove($entity);
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    
    public function agregarrangosAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $rango = $parametros[0];
        $cantp = $parametros[1];
        $canth = $parametros[2];
        $cantm = $parametros[3];
        $canthl = $parametros[4];
        $cantml = $parametros[5];
        $idenc = $parametros[6];

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();      
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        /* varifica rango duplicado */
        $entrango = $em->getRepository('FocalAppBundle:DatosdRangos')->findBy(array('idEnc' => $idenc, 'codMunicipio' => $codMuni, 'rango' => $rango ));
        if($entrango) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: rango ya esta ingresado'));
            return $response;
        }
        
        /* Cantidad personas en vivienda p.9 */
        $entdatogeneral = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idenc, 'codMunicipio' => $codMuni ));
        $cantfamilia = $entdatogeneral[0]->getcantPersonasvivienda();
        
        /* Cantidad de personas H/M que ingresas en por rango de edad  p.13 */
        $dsql ="select sum(dt.cantPersonas) as cantpersonas, sum(dt.cantHombres) as canthombres, sum(dt.cantMujeres) as cantmujeres "
                . "from FocalAppBundle:DatosdRangos dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entities = $query->getResult();
        $cantingresada = $entities[0]['cantpersonas'];
        $cantHombresingresados = $entities[0]['canthombres'];
        $cantMujeresingresadas = $entities[0]['cantmujeres'];
        
        /* Cantidad de personas de la familia viven en la vivienda por sexo p.12*/
        $dsql ="select sum(case when dt.sexo=1 then 1 else 0 end) as hombres,"
                . "sum(case when dt.sexo=2 then 1 else 0 end) as mujeres "
                . "from FocalAppBundle:DatosdFamilia dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $idenc);
            $entities = $query->getResult();
        $canthombres = $entities[0]['hombres'];
        $cantmujeres = $entities[0]['mujeres'];
        
        /* Validación por rango ingresado, que las edades de los familiares sean iguales 
             * a la cantidad en los rangos ingresados */
            // definir el rango
            switch ($rango) {
                case 1: $strrango = " dt.edad < 1 "; break;
                case 2: $strrango = " dt.edad >= 1 and dt.edad <= 4 "; break;
                case 3: $strrango = " dt.edad >= 5 and dt.edad <= 6 "; break; 
                case 4: $strrango = " dt.edad >= 7 and dt.edad <= 12 "; break;  
                case 5: $strrango = " dt.edad >= 13 and dt.edad <= 15 "; break;  
                case 6: $strrango = " dt.edad >= 16 and dt.edad <= 18 "; break;  
                case 7: $strrango = " dt.edad >= 19 and dt.edad <= 23 "; break;
                case 8: $strrango = " dt.edad >= 24 and dt.edad <= 30 "; break;
                case 9: $strrango = " dt.edad >= 31 and dt.edad <= 40 "; break;
                case 10: $strrango = " dt.edad >= 41 and dt.edad <= 50 "; break;
                case 11: $strrango = " dt.edad >= 51 and dt.edad <= 64 "; break;
                case 12: $strrango = " dt.edad >= 65 "; break;
            }
            $dsql ="select count(dt.edad) as cantpersonas "
                    . "from FocalAppBundle:DatosdFamilia dt "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and "
                    . $strrango;
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entities = $query->getResult();
            $cantenrango = $entities[0]['cantpersonas'];
        
        if($cantp != $cantenrango) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: Cantidad familiares en este rango '.$cantenrango. ' deben ser iguales a la cantidad ingresada.'));
            return $response;
        }    
        if (($cantingresada+$cantp) <= $cantfamilia && ($cantHombresingresados+$canth) <= $canthombres && ($cantMujeresingresadas+$cantm) <= $cantmujeres) {
            $sequenceName = 'datosd_rangos_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;

            $entity = new DatosdRangos();

            $entity->setId($codCom);
            $entity->setIdEnc($idenc);
            $entity->setCodMunicipio($codMuni);
            $entity->setCodDepartamento($codDep);
            $entity->setCodComunidad($codComu);
            $entity->setRango($rango);
            $entity->setCantPersonas($cantp);
            $entity->setCantHombres($canth);
            $entity->setCantMujeres($cantm);
            $entity->setCantHombresLeen($canthl);
            $entity->setCantMujeresLeen($cantml);

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
                if(($cantingresada+$cantp) == $cantfamilia) {
                    $response->setData(array('message' => 'true', 'cerrar' => 'si'));
                } else {
                    $response->setData(array('message' => 'true', 'cerrar' => 'no'));
                }
            }
            return $response;
        } else {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: Cantidad familiares debera se menor o igual a '.$cantfamilia. ' No puede ingresar a mas familiares.'));
            return $response;
        }
    }
    
    public function agregarfamiliarAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $nombre = $parametros[0];
        $edad = $parametros[1];
        $sexo = $parametros[2];
        $parentesco = $parametros[3];
        $etnia = $parametros[4];
        $partida = $parametros[5];
        $idenc = $parametros[6];

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        
        
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $entingresadas = $em->getRepository('FocalAppBundle:DatosdFamilia')->findBy(array('idEnc' => $idenc, 'codMunicipio' => $codMuni ));
        $entdatogeneral = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idenc, 'codMunicipio' => $codMuni ));
        $cantingresadas = count($entingresadas);
        $cantfamilia = $entdatogeneral[0]->getcantPersonasvivienda();
        
        if ($cantingresadas < $cantfamilia) {
        $sequenceName = 'datosd_familia_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

        $codCom = $codMuni . $newId;
        
        $entity = new DatosdFamilia();

        $entity->setId($codCom);
        $entity->setIdEnc($idenc);
        $entity->setCodMunicipio($codMuni);
        $entity->setCodDepartamento($codDep);
        $entity->setCodComunidad($codComu);
        $entity->setNombre($nombre);
        $entity->setEdad($edad);
        $entity->setSexo($sexo);
        $entity->setIdParentesco($parentesco);
        $entity->setIdEtnia($etnia);
        $entity->setTienePn($partida);
        
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
            if ($cantingresadas + 1 == $cantfamilia) {
                $response->setData(array('message' => 'true','cerrar' => 'si'));
            } else {
                $response->setData(array('message' => 'true','cerrar' => 'no'));
            }
        }
        return $response;
        } else {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: Cantidad familiares debera se menor o igual a '.$cantfamilia. ' No puede ingresar a mas familiares.'));
            return $response;
        }
    }
    
    /**
     * Lists all DatosdFamilia entities.
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
        
        $dsql ="select dt.id, dt.nombre, dt.edad, mu.nombre as nomMunicipio, pa.descripcion as desParentesco, "
                . "dt.sexo, et.descripcion as desEtnia, dt.tienePn "
                . "from FocalAppBundle:DatosdFamilia dt "
                . "left join FocalAppBundle:AdParentesco pa with (pa.id = dt.idParentesco) "
                . "left join FocalAppBundle:AdEtnia et with (et.id = dt.idEtnia) "
                . "left join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = dt.codMunicipio) "
                . "where dt.codDepartamento = :departamento and dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('departamento', $codDept);
            $query ->setParameter('idenc', $idenc);
            $entities = $query->getResult();
            
        $entp = $em->getRepository('FocalAppBundle:AdParentesco')->findAll();
        $ente = $em->getRepository('FocalAppBundle:AdEtnia')->findAll();
        $rnge = $em->getRepository('FocalAppBundle:AdRangosedad')->findBy(array(), array('id'=>'ASC'));
        
        $dsql ="select dt.id, dt.rango, ra.descripcion, dt.cantPersonas, "
                . "dt.cantHombres, dt.cantMujeres, dt.cantHombresLeen, dt.cantMujeresLeen "
                . "from FocalAppBundle:DatosdRangos dt "
                . "left join FocalAppBundle:AdRangosedad ra with (ra.id = dt.rango) "
                . "where dt.codDepartamento = :departamento and dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('departamento', $codDept);
            $query ->setParameter('idenc', $idenc);
            $entitiesRangos = $query->getResult();
            
        //$entitiesRangos = $em->getRepository('FocalAppBundle:DatosdRangos')->findAll();
        $entityOtros = $em->getRepository('FocalAppBundle:DatosdOtros')->findBy(array('idEnc'=>$idenc));
        if ($entityOtros) {
            $entddo = $em->getRepository('FocalAppBundle:DatosdOtros')->find($entityOtros[0]->getId());
            $form = $this->createEditFormOtros($entddo);
            /* Mandar el registro de los nacimientos */
            $entnac = $em->getRepository('FocalAppBundle:DatosdOtrosnac')->findBy(array('idDatossotros' => $entityOtros[0]->getId()));
        } else {
            $entityOtros = new DatosdOtros();
            $form   = $this->createCreateFormOtros($entityOtros);
            $entnac = $em->getRepository('FocalAppBundle:DatosdOtrosnac')->findBy(array('idDatossotros' => $entityOtros->getId()));
        }
        
        return $this->render('FocalAppBundle:DatosdFamilia:index.html.twig', array(
            'entities' => $entities,
            'entitiesr' => $entitiesRangos,
            'entityo' => $entityOtros,
            'entp' => $entp,
            'ente' => $ente,
            'rnge' => $rnge,
            'idenc' => $idenc,
            'coddep' => $codDept,
            'codmun' => $codMuni,
            'numboleta' => $numboleta,
            'form'  => $form->createView(),
            'entnac' => $entnac,
        ));
    }
    /**
     * Creates a new DatosdFamilia entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatosdFamilia();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('datosdfamilia_show', array('id' => $entity->getId())));
        }

        return $this->render('FocalAppBundle:DatosdFamilia:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a DatosdFamilia entity.
     *
     * @param DatosdFamilia $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosdFamilia $entity)
    {
        $form = $this->createForm(new DatosdFamiliaType(), $entity, array(
            'action' => $this->generateUrl('datosdfamilia_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'class' => 'btn-success'));

        return $form;
    }
    
    /**
     * Creates a form to create a DatosdOtros entity.
     *
     * @param DatosdOtros $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateFormOtros(DatosdOtros $entity)
    {
        $form = $this->createForm(new DatosdOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosdotros_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    
    /**
     * Displays a form to create a new DatosdFamilia entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosdFamilia();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosdFamilia:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosdFamilia entity.
     *
     */
    public function showAction(Request $request, $id, $boleta)
    {
        $em = $this->getDoctrine()->getManager();

        //$entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->find($id);
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        $dsql ="select dt.id, dt.nombre, dt.edad, dt.codMunicipio, mu.nombre as nomMunicipio, pa.descripcion as desParentesco, "
                . "dt.sexo, et.descripcion as desEtnia, dt.tienePn, dt.codDepartamento,dt.codComunidad "
                . "from FocalAppBundle:DatosdFamilia dt "
                . "left join FocalAppBundle:AdParentesco pa with (pa.id = dt.idParentesco) "
                . "left join FocalAppBundle:AdEtnia et with (et.id = dt.idEtnia) "
                . "left join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = dt.codMunicipio) "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $boleta);
            $entity = $query->getResult();
            
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdFamilia entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdFamilia:show.html.twig', array(
            'entity' => $entity[0],
            'boleta' => $boleta,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosdFamilia entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->find($id);
             
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdFamilia entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdFamilia:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
            'idenc' => $entity->getIdEnc(),
            'parsel' => $entity->getIdParentesco(),
            'etniasel' => $entity->getIdEtnia(),
        ));
    }

    /**
    * Creates a form to edit a DatosdFamilia entity.
    *
    * @param DatosdFamilia $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosdFamilia $entity)
    {
        $form = $this->createForm(new DatosdFamiliaType(), $entity, array(
            'action' => $this->generateUrl('datosdfamilia_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    
    /**
    * Creates a form to edit a DatosdOtros entity.
    *
    * @param DatosdOtros $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditFormOtros(DatosdOtros $entity)
    {
        $form = $this->createForm(new DatosdOtrosType(), $entity, array(
            'action' => $this->generateUrl('datosdotros_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    
    
    /**
     * Edits an existing DatosdFamilia entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdFamilia entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($entity->getIdParentesco() !== null) {
                $idParentesco = $entity->getIdParentesco()->getId();
                $entity->setIdParentesco($idParentesco);
            }
            if ($entity->getIdEtnia() !== null) {
                $idEtnia = $entity->getIdEtnia()->getId();
                $entity->setIdEtnia($idEtnia);
            }
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            $em->flush();

            return $this->redirect($this->generateUrl('datosdfamilia', array('idenc' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatosdFamilia:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatosdFamilia entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosdFamilia entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosdfamilia'));
    }

    /**
     * Creates a form to delete a DatosdFamilia entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosdfamilia_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
