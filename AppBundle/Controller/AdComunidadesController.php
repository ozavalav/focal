<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\AdComunidades;
use Focal\AppBundle\Form\AdComunidadesType;
use Focal\AppBundle\Entity\AdComunidadesIne;
use Focal\AppBundle\Entity\AdAldeasIne;
use Focal\AppBundle\Entity\AdCaseriosIne;

/**
 * AdComunidades controller.
 *
 */
class AdComunidadesController extends Controller
{
    public function borrarregistrocomAction(Request $request, $param){
        
        $idreg = $param;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();  
        $entity = $em->getRepository('FocalAppBundle:AdComunidades')->find($idreg);

        if (!$entity) {
            $response->setData(array('message' => 'false'));
        } else {
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");  
            $entity->setUsuarioUltimaModificacion($usuario);  
            $entity->setFechaUltimaModificacion($fecha);
            
            $entity->setTipoComunidad(99); //tipo comunidad inactiva
            
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    
    public function agregarComunidadAction(Request $request, $param)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $parametros = explode("&&", $param);
        $nomComunidad = $parametros[0];
        $codAldea = $parametros[1];
        $codCaserio = $parametros[2];
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codMuni = substr($codMuni, -2);
    
        /* obtengo el ultimo codigo de la comunidad */
        $dql = 'SELECT distinct max(al.codBarrio) codcomunidad
        FROM FocalAppBundle:AdComunidadesIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2 AND 
        al.codAldea = ?3 AND
        al.codCaserio = ?4' ;
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni); 
        $query->setParameter(3, $codAldea);
        $query->setParameter(4, $codCaserio);
        $entityAld = $query->getResult();

        $nuevoCodCom = (string)intval($entityAld[0]['codcomunidad']) + 1;
        $nuevoCodCom = str_pad($nuevoCodCom,3,"0", STR_PAD_LEFT);
        $codComunidad = $codDep . $codMuni . $codAldea . $codCaserio . $nuevoCodCom;
        
        /* busco si no esta repetido el codigo de la comunidad */
        $entityAld = $em->getRepository('FocalAppBundle:AdComunidadesIne')->findBy(array('codComunidad' => $codComunidad, 'codMunicipio' => $codMuni, 'codDepartamento' => $codDep, 'codCaserio' => $codCaserio ));
        if($entityAld) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: codigo de comunidad ya esta ingresado'));
            return $response;
        }

        /* armo el nuevo registro y lo guardo en la BD. */
        $entityAld = new AdComunidadesIne();
        $entityAld->setCodComunidad($codComunidad);      
        $entityAld->setCodDepartamento($codDep);
        $entityAld->setCodMunicipio($codMuni);
        $entityAld->setCodAldea($codAldea);
        $entityAld->setCodCaserio($codCaserio);
        $entityAld->setCodBarrio($nuevoCodCom);
        $entityAld->setNomBarrio($nomComunidad);

        $em->persist($entityAld);
        $em->flush();

        /* obtengo la nueva lista de aldeas agregada */
        $dql = 'SELECT distinct al.codBarrio, al.nomBarrio
        FROM FocalAppBundle:AdComunidadesIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2 AND
        al.codAldea = ?3 AND
        al.codCaserio = ?4';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni);  
        $query->setParameter(3, $codAldea); 
        $query->setParameter(4, $codCaserio);
        
        $comunidad = $query->getResult();
                 $encoders = array(new XmlEncoder(), new JsonEncoder());
                 $normalizers = array(new GetSetMethodNormalizer());
                 $serializer = new Serializer($normalizers, $encoders);
                 $jsonContent = $serializer->serialize($comunidad, 'json');
                 $response->setData($comunidad);
            return $response;
    }        
    
    public function agregarCaserioAction(Request $request, $param)
    {
       $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $parametros = explode("&&", $param);
        $nomCaserio = $parametros[0];
        $codAldea = $parametros[1];
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codMuni = substr($codMuni, -2);
    
        /* obtengo el ultimo codigo del caserio */
        $dql = 'SELECT distinct max(al.codCaserio) codcaserio
        FROM FocalAppBundle:AdCaseriosIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2 AND 
        al.codAldea = ?3' ;
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni); 
        $query->setParameter(3, $codAldea);
        $entityAld = $query->getResult();

        $nuevoCodCas = (string)intval($entityAld[0]['codcaserio']) + 1;
        $nuevoCodCas = str_pad($nuevoCodCas,3,"0", STR_PAD_LEFT);
        $codComunidad = $codDep . $codMuni . $codAldea . $nuevoCodCas;
        
        /* busco si no esta repetido el codigo de la comunidad */
        $entityAld = $em->getRepository('FocalAppBundle:AdCaseriosIne')->findBy(array('codComunidad' => $codComunidad, 'codMunicipio' => $codMuni, 'codDepartamento' => $codDep ));
        if($entityAld) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: codigo de caserio ya esta ingresado'));
            return $response;
        }

        /* armo el nuevo registro y lo guardo en la BD. */
        $entityAld = new AdCaseriosIne();
        $entityAld->setCodComunidad($codComunidad);      
        $entityAld->setCodDepartamento($codDep);
        $entityAld->setCodMunicipio($codMuni);
        $entityAld->setCodAldea($codAldea);
        $entityAld->setCodCaserio($nuevoCodCas);
        $entityAld->setNomCaserio($nomCaserio);

        $em->persist($entityAld);
        $em->flush();

        /* obtengo la nueva lista de aldeas agregada */
        $dql = 'SELECT distinct al.codCaserio, al.nomCaserio
        FROM FocalAppBundle:AdCaseriosIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2 AND
        al.codAldea = ?3';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni);  
        $query->setParameter(3, $codAldea); 

        $caserios = $query->getResult();
                 $encoders = array(new XmlEncoder(), new JsonEncoder());
                 $normalizers = array(new GetSetMethodNormalizer());
                 $serializer = new Serializer($normalizers, $encoders);
                 $jsonContent = $serializer->serialize($caserios, 'json');
                 $response->setData($caserios);
            return $response;
    }    
    
    public function agregarAldeaAction(Request $request, $param)
    {
       $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $nomAldea = $param;
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codMuni = substr($codMuni, -2);
    
        /* obtengo el ultimo codigo de la aldea */
        $dql = 'SELECT distinct max(al.codAldea) codaldea
        FROM FocalAppBundle:AdAldeasIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni);  
        $entityAld = $query->getResult();

        $nuevoCodAldea = (string)intval($entityAld[0]['codaldea']) + 1;
        $nuevoCodAldea = str_pad($nuevoCodAldea,2,"0", STR_PAD_LEFT);
        $codComunidad = $codDep . $codMuni . $nuevoCodAldea;
        
        /* busco si no esta repetido el codigo de la comunidad */
        $entityAld = $em->getRepository('FocalAppBundle:AdAldeasIne')->findBy(array('codComunidad' => $codComunidad, 'codMunicipio' => $codMuni, 'codDepartamento' => $codDep ));
        if($entityAld) {
            $response->setData(array('message' => 'false','razon' => 'FOCAL: codigo de aldea ya esta ingresado'));
            return $response;
        }

        /* armo el nuevo registro y lo guardo en la BD. */
        $entityAld = new AdAldeasIne();
        $entityAld->setCodComunidad($codComunidad);      
        $entityAld->setCodDepartamento($codDep);
        $entityAld->setCodMunicipio($codMuni);
        $entityAld->setCodAldea($nuevoCodAldea);
        $entityAld->setNomAldea($nomAldea);

        $em->persist($entityAld);
        $em->flush();

        /* obtengo la nueva lista de aldeas agregada */
        $dql = 'SELECT distinct al.codAldea, al.nomAldea
        FROM FocalAppBundle:AdAldeasIne al 
        WHERE al.codDepartamento = ?1 AND 
        al.codMunicipio = ?2';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codDep);
        $query->setParameter(2, $codMuni);  

        $aldeas = $query->getResult();
                 $encoders = array(new XmlEncoder(), new JsonEncoder());
                 $normalizers = array(new GetSetMethodNormalizer());
                 $serializer = new Serializer($normalizers, $encoders);
                 $jsonContent = $serializer->serialize($aldeas, 'json');
                 $response->setData($aldeas);
            return $response;
    }
    
    public function buscarCaserioAction(Request $request, $param)
    {
       $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $aldea = $param;
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codMuni = substr($codMuni, -2);

    //IDENTITY
    $dql = 'SELECT distinct ca.codCaserio, ca.nomCaserio
    FROM FocalAppBundle:AdCaseriosIne ca 
    WHERE ca.codDepartamento = ?1 AND 
    ca.codMunicipio = ?2 AND 
    ca.codAldea = ?3
    order by ca.nomCaserio';
    $query = $em->createQuery($dql);
    $query->setParameter(1, $codDep);
    $query->setParameter(2, $codMuni);
    $query->setParameter(3, $aldea);
    $caserios = $query->getResult();
             $encoders = array(new XmlEncoder(), new JsonEncoder());
             $normalizers = array(new GetSetMethodNormalizer());
             $serializer = new Serializer($normalizers, $encoders);
             $jsonContent = $serializer->serialize($caserios, 'json');
             $response->setData($caserios);
        return $response;
    }

public function buscarComunidadAction(Request $request, $aldea, $caserio)
    {
       $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codMuni = substr($codMuni, -2);

    //IDENTITY
    $dql = 'SELECT distinct ca.codBarrio, ca.nomBarrio
    FROM FocalAppBundle:AdComunidadesIne ca 
    WHERE ca.codDepartamento = ?1 AND 
    ca.codMunicipio = ?2 AND 
    ca.codAldea = ?3 AND
    ca.codCaserio = ?4 AND ca.codBarrio <> ?5
    order by ca.nomBarrio';
    $query = $em->createQuery($dql);
    $query->setParameter(1, $codDep);
    $query->setParameter(2, $codMuni);
    $query->setParameter(3, $aldea);
    $query->setParameter(4, $caserio);
    $query->setParameter(5, '000');
    
    $caserios = $query->getResult();
             $encoders = array(new XmlEncoder(), new JsonEncoder());
             $normalizers = array(new GetSetMethodNormalizer());
             $serializer = new Serializer($normalizers, $encoders);
             $jsonContent = $serializer->serialize($caserios, 'json');
             $response->setData($caserios);
        return $response;
    }    
    /**
     * Lists all AdComunidades entities.
     *
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
       
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        
        $dsql ="select co.id, co.codMunicipio, co.codComunidad, mu.nombre as nombreMunicipio, co.nombre, co.descripcion  " //tc.descripcion as tipoComunidad, 
                . "from FocalAppBundle:AdComunidades co "
                . "join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = co.codMunicipio) "
                //. "join FocalAppBundle:AdTipoComunidad tc with (tc.id = co.tipoComunidad)"
                . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $entities = $query->getResult();
            

        return $this->render('FocalAppBundle:AdComunidades:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AdComunidades entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AdComunidades();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        
        
        if ($form->isValid()) {
            $codAldea = $request->get('aldea');
            $codCaserio = $request->get('caserio');
            $codComunidad = $request->get('comunidad');
            $codcomcompleto = $codMuni.$codAldea.$codCaserio.$codComunidad;
            /* verifico ya existe la comunidad */
            $entityact = $em->getRepository('FocalAppBundle:AdComunidades')->findBy(array('codComunidad'=>$codcomcompleto));
            if(!$entityact) { 
            
            $entity->setCodComunidad($codcomcompleto);
            $entity->setcodMunicipio($codMuni);
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);
            
            /*$tipoCom = $entity->getTipoComunidad()->getId(); */
            $entity->setTipoComunidad(0);
            
            $sequenceName = 'ad_comunidades_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            
            $codCom = $codMuni . $newId;
            $entity->setId($codCom);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('adcomunidades'));
            } else {
              $this->addFlash('warning','Comunidad ya esta agregada');  
            }
        }

        $codDM = str_split($codMuni,2);
        
        $dsql ="select distinct co.codAldea, co.nomAldea "
                . "from FocalAppBundle:AdComunidadesIne co "
                . "where co.codMunicipio = :municipio "
                . "and co.codDepartamento = :departamento "
                . "order by co.codAldea";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codDM[1]);
            $query ->setParameter('departamento', $codDep);
            $aldeas = $query->getResult();  
            
        return $this->render('FocalAppBundle:AdComunidades:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'aldeas' => $aldeas
        ));
    }

    /**
     * Creates a form to create a AdComunidades entity.
     *
     * @param AdComunidades $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AdComunidades $entity)
    {
        $form = $this->createForm(new AdComunidadesType(), $entity, array(
            'action' => $this->generateUrl('adcomunidades_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new AdComunidades entity.
     *
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
       
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codDM = str_split($codMuni,2);
        
        $dsql ="select distinct co.codAldea, co.nomAldea "
                . "from FocalAppBundle:AdAldeasIne co "
                . "where co.codMunicipio = :municipio "
                . "and co.codDepartamento = :departamento "
                . "order by co.codAldea";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codDM[1]);
            $query ->setParameter('departamento', $codDep);
            $aldeas = $query->getResult();    
            
        $entity = new AdComunidades();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:AdComunidades:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'aldeas' => $aldeas,
        ));
    }

    /**
     * Finds and displays a AdComunidades entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdComunidades')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdComunidades entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdComunidades:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AdComunidades entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdComunidades')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdComunidades entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:AdComunidades:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AdComunidades entity.
    *
    * @param AdComunidades $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AdComunidades $entity)
    {
        $form = $this->createForm(new AdComunidadesType(), $entity, array(
            'action' => $this->generateUrl('adcomunidades_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing AdComunidades entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:AdComunidades')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AdComunidades entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('adcomunidades'));
        }

        return $this->render('FocalAppBundle:AdComunidades:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AdComunidades entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:AdComunidades')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AdComunidades entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adcomunidades'));
    }

    /**
     * Creates a form to delete a AdComunidades entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adcomunidades_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
