<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\DatosFuerzaIngresos;
use Focal\AppBundle\Form\DatosFuerzaIngresosType;

use Focal\AppBundle\Entity\DatosFuerzaOtros;
use Focal\AppBundle\Form\DatosFuerzaOtrosType;

/**
 * DatosFuerzaIngresos controller.
 *
 */
class DatosFuerzaIngresosController extends Controller
{
    public function borrarregistroflAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $idenc = $parametros[0];
        $codDept = $parametros[1];
        $codMuni = $parametros[2];
        
        $idreg = $param;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager(); 
        
        $dsql ="delete "
                    . "from FocalAppBundle:DatosFuerzaIngresos dt "
                    . "where dt.codDepartamento = :departamento and dt.codMunicipio = :municipio and dt.idEnc = :idenc ";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('departamento', $codDept);
                $query ->setParameter('idenc', $idenc);
                $resultado = $query->getResult();
        //$entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($idreg);

        if (!$resultado) {
            $response->setData(array('message' => 'false', 'razon' => 'No se encontraron registros'));
        } else {
            
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    /**
     * Lists all DatosFuerzaIngresos entities.
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

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->findBy(array('idEnc' => $idenc));

        if(!$entity) {
            $dsql ="select dt.id as idFamilia, dt.idEnc, dt.nombre, dt.sexo, dt.edad, 0 as nivele, 0 as estadoCivil, 0 as profesion, 0 as ocupacion, 0 as trabaja, 0 as ingresos, 0 as num "
                    . "from FocalAppBundle:DatosdFamilia dt  "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc "
                    . "and dt.edad >= 10 "
                    . "order by dt.id";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entedu = $query->getResult();
        } else {
            $dsql ="select dt.idEnc, dt.idFamilia, fa.nombre, fa.sexo, fa.edad, dt.nivele, dt.estadoCivil, dt.profesion, dt.ocupacion, dt.trabaja, dt.ingresos, dt.num  "
                    . "from FocalAppBundle:DatosFuerzaIngresos dt "
                    . "join FocalAppBundle:DatosdFamilia fa  with (fa.id = dt.idFamilia) "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entedu = $query->getResult();
        } 
        
        $entned = $em->getRepository('FocalAppBundle:AdNivelEducativo')->findBy(array(),  array('orden' => 'ASC'));
        $entpro = $em->getRepository('FocalAppBundle:AdProfesiones')->findBy(array(),  array('descripcion' => 'ASC'));
        $entocu = $em->getRepository('FocalAppBundle:AdOcupaciones')->findBy(array(),  array('descripcion' => 'ASC'));
        
        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc'=>$idenc));
        if ($entity) {
            //$entdfo = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->find($entity[0]->getId());
            $form = $this->createEditForm($entity[0]);
            $rifam = $entity[0]->getRangoIngresofam();
            $rrem = $entity[0]->getRangoRemesas();
        } else {
            $entity = new DatosFuerzaOtros();
            $form   = $this->createCreateForm($entity);
            $rifam = "";
            $rrem = "";
        }
        
        return $this->render('FocalAppBundle:DatosFuerzaIngresos:index.html.twig', array(
            'entities' => $entedu,
            'entpro' => $entpro,
            'entocu' => $entocu,
            'entned' => $entned,
            'idenc' => $idenc,
            'numboleta' => $numboleta,
            'rrem' => $rrem,
            'rifam' => $rifam,
            'codmun' => $codMuni,
            'coddep' => $codDept,
            'form'  => $form->createView()
        ));
    }
    /**
     * Creates a new DatosFuerzaIngresos entity.
     *
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idenc = $request->get('idencfi');
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        
        $entities = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->findBy(Array('idEnc' => $idenc, 'codDepartamento' => $codDep, 'codMunicipio' => $codMuni));
        
        if ($entities) {
            $totalIngFam = 0;
            foreach ($entities as $ent) {
                $ent->setNivele($request->get('nivele_'.$ent->getNum())); 
                $ent->setEstadoCivil($request->get('estadoc_'.$ent->getNum())); 
                $ent->setTrabaja($request->get('trabaja_'.$ent->getNum()));
                $ent->setProfesion($request->get('profesion_'.$ent->getNum()));
                $ent->setOcupacion($request->get('ocupacion_'.$ent->getNum()));
                $ent->setIngresos($request->get('ingresos_'.$ent->getNum()));
                
                $totalIngFam = $totalIngFam + $request->get('ingresos_'.$ent->getNum());
                
                $ent->setUsuarioUltimaModificacion($usuario);
                $ent->setFechaUltimaModificacion($fecha);
            }
            
            /* Actualizo el el rango de ingreso familiar y la cantidad */
            $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc' => $idenc, 'codDepartamento' => $codDep, 'codMunicipio' => $codMuni));
            if($entity) {
                $totaling = $totalIngFam + $entity[0]->getCantRemesas();
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
                $entity[0]->setRangoIngresoFam($rngfam);
                $entity[0]->setCantIngresoFam($totaling);
            }            
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datosfuerzaingresos', array('idenc' => $idenc)));
        }
        
        $totalIngFam = 0;
        for($i = 1; $i<=$request->get('numtotalreg'); $i++) {
            $entity = new DatosFuerzaIngresos();
            $sequenceName = 'datos_fuerza_ingresos_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;  
            $entity->setId($codCom);
            $entity->setIdEnc($idenc);
            $entity->setNivele($request->get('nivele_'.$i)); 
            $entity->setEstadoCivil($request->get('estadoc_'.$i)); 
            $entity->setTrabaja($request->get('trabaja_'.$i));
            $entity->setProfesion($request->get('profesion_'.$i));
            $entity->setOcupacion($request->get('ocupacion_'.$i));
            $entity->setIngresos($request->get('ingresos_'.$i)); 
            
            $totalIngFam = $totalIngFam + $request->get('ingresos_'.$i);
            
            $entity->setNum($request->get('num_'.$i));
            $entity->setIdFamilia($request->get('idfam_'.$i));
            
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
        
        /* Actualizo el el rango de ingreso familiar y la cantidad */
        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc' => $idenc, 'codDepartamento' => $codDep, 'codMunicipio' => $codMuni));
        if($entity) {
            $totaling = $totalIngFam + $entity[0]->getCantRemesas();
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
            $entity[0]->setRangoIngresoFam($rngfam);
            $entity[0]->setCantIngresoFam($totaling);
            $em->flush();
            
        }
        
        $this->addFlash('success','Datos generados con exito');
        return $this->redirect($this->generateUrl('datosfuerzaingresos', array('idenc' => $idenc)));
    }

    /**
     * Creates a form to create a DatosFuerzaIngresos entity.
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
     * Displays a form to create a new DatosFuerzaIngresos entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosFuerzaIngresos();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosFuerzaIngresos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosFuerzaIngresos entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaIngresos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosFuerzaIngresos:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosFuerzaIngresos entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaIngresos entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosFuerzaIngresos:edit.html.twig', array(
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
     * Edits an existing DatosFuerzaIngresos entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosFuerzaIngresos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('datosfuerzaingresos', array('idenc' => $idenc)));
        }

        return $this->render('FocalAppBundle:DatosFuerzaIngresos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatosFuerzaIngresos entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosFuerzaIngresos entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosfuerzaingresos'));
    }

    /**
     * Creates a form to delete a DatosFuerzaIngresos entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosfuerzaingresos_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
