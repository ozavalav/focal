<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Form\DatosGeneralesType;
use Focal\AppBundle\Entity\AppConst;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\Response;


/**
 * DatosGenerales controller.
 *
 */
class DatosGeneralesController extends Controller
{

    public function buscarComunidadAction(Request $request, $param)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $codMuni = $param;
        

    //IDENTITY
        $dql = 'SELECT co.codComunidad, co.nombre
        FROM FocalAppBundle:AdComunidades co 
        WHERE co.codMunicipio = ?1 
        order by co.nombre';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codMuni);
        $comunidades = $query->getResult();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($comunidades, 'json');
        $response->setData($comunidades);
        return $response;
    }
    
    public function reporte1Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        
        return $this->render('FocalAppBundle:DatosGenerales:producciondiaria.html.twig', array(
                    'departamentos' => $entities,
        ));
    }

    public function reportecuadro1Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = "";
        return $this->render('FocalAppBundle:DatosGenerales:cuadro1.html.twig', array(
                    'entities' => $entities,
        ));
    }
    public function reportecuadro234Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = "";
        return $this->render('FocalAppBundle:DatosGenerales:cuadro234.html.twig', array(
                    'entities' => $entities,
        ));
    }
    public function reportecuadro56789Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = "";
        return $this->render('FocalAppBundle:DatosGenerales:cuadro56789.html.twig', array(
                    'entities' => $entities,
        ));
    }
    public function reportecuadro101112131415Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = "";
        return $this->render('FocalAppBundle:DatosGenerales:cuadro101112131415.html.twig', array(
                    'entities' => $entities,
        ));
    }

    public function crearReporteCuadro1Action($fin, $fin2, $fin3, $fin4) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $fini = new \DateTime($fin);
        $ffin = new \DateTime($fin2);


        $strFini = $fini->format('Y-m-d');
        $strFFin = $ffin->format('Y-m-d');
        $departamento= $fin3;
        $municipio = $fin4;

        $em = $this->getDoctrine()->getManager();
        
       // $eEstado = $em->getRepository('AdminBundle:AdEstado')->findOneBy(array('idEstado' => $fin2));
        //$fin2 = $eEstado->getDescripcion();

        //$query = "select count(id) as mesas 
        //          from res_orden 
        //          where fecha ='" . $strFin . "'";
        //$stmt = $em->getConnection()->prepare($query);
        //$stmt->execute();
        //$mesas = $stmt->fetchAll();
        // $val = 0;
        //$AdConstantes = new AdConstantes();
        //$desTipoMenu = $AdConstantes->tipoMenu($em, $fin3);

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'departamento'=> $departamento,
            'municipio' => $municipio
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reportecuadro1.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    public function crearReporteCuadro234Action($fin, $fin2, $fin3, $fin4) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $fini = new \DateTime($fin);
        $ffin = new \DateTime($fin2);


        $strFini = $fini->format('Y-m-d');
        $strFFin = $ffin->format('Y-m-d');
        $departamento= $fin3;
        $municipio = $fin4;

        $em = $this->getDoctrine()->getManager();
        
       // $eEstado = $em->getRepository('AdminBundle:AdEstado')->findOneBy(array('idEstado' => $fin2));
        //$fin2 = $eEstado->getDescripcion();

        //$query = "select count(id) as mesas 
        //          from res_orden 
        //          where fecha ='" . $strFin . "'";
        //$stmt = $em->getConnection()->prepare($query);
        //$stmt->execute();
        //$mesas = $stmt->fetchAll();
        // $val = 0;
        //$AdConstantes = new AdConstantes();
        //$desTipoMenu = $AdConstantes->tipoMenu($em, $fin3);

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'departamento'=> $departamento,
            'municipio' => $municipio
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reportecuadro234.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    
    public function crearReporteCuadro56789Action($fin, $fin2, $fin3, $fin4) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $fini = new \DateTime($fin);
        $ffin = new \DateTime($fin2);


        $strFini = $fini->format('Y-m-d');
        $strFFin = $ffin->format('Y-m-d');
        $departamento= $fin3;
        $municipio = $fin4;

        $em = $this->getDoctrine()->getManager();
        
       // $eEstado = $em->getRepository('AdminBundle:AdEstado')->findOneBy(array('idEstado' => $fin2));
        //$fin2 = $eEstado->getDescripcion();

        //$query = "select count(id) as mesas 
        //          from res_orden 
        //          where fecha ='" . $strFin . "'";
        //$stmt = $em->getConnection()->prepare($query);
        //$stmt->execute();
        //$mesas = $stmt->fetchAll();
        // $val = 0;
        //$AdConstantes = new AdConstantes();
        //$desTipoMenu = $AdConstantes->tipoMenu($em, $fin3);

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'departamento'=> $departamento,
            'municipio' => $municipio
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reportecuadro56789.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    public function crearReporteCuadro101112131415Action($fin, $fin2, $fin3, $fin4) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $fini = new \DateTime($fin);
        $ffin = new \DateTime($fin2);


        $strFini = $fini->format('Y-m-d');
        $strFFin = $ffin->format('Y-m-d');
        $departamento= $fin3;
        $municipio = $fin4;

        $em = $this->getDoctrine()->getManager();
        
       // $eEstado = $em->getRepository('AdminBundle:AdEstado')->findOneBy(array('idEstado' => $fin2));
        //$fin2 = $eEstado->getDescripcion();

        //$query = "select count(id) as mesas 
        //          from res_orden 
        //          where fecha ='" . $strFin . "'";
        //$stmt = $em->getConnection()->prepare($query);
        //$stmt->execute();
        //$mesas = $stmt->fetchAll();
        // $val = 0;
        //$AdConstantes = new AdConstantes();
        //$desTipoMenu = $AdConstantes->tipoMenu($em, $fin3);

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'departamento'=> $departamento,
            'municipio' => $municipio
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reportecuadro101112131415.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }    
    
public function reportecuadro161718192021Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = "";
        return $this->render('FocalAppBundle:DatosGenerales:cuadro161718192021.html.twig', array(
                    'entities' => $entities,
        ));
    }

 public function crearReporteCuadro161718192021Action($fin, $fin2, $fin3, $fin4) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $fini = new \DateTime($fin);
        $ffin = new \DateTime($fin2);


        $strFini = $fini->format('Y-m-d');
        $strFFin = $ffin->format('Y-m-d');
        $departamento= $fin3;
        $municipio = $fin4;

        $em = $this->getDoctrine()->getManager();
        
       // $eEstado = $em->getRepository('AdminBundle:AdEstado')->findOneBy(array('idEstado' => $fin2));
        //$fin2 = $eEstado->getDescripcion();

        //$query = "select count(id) as mesas 
        //          from res_orden 
        //          where fecha ='" . $strFin . "'";
        //$stmt = $em->getConnection()->prepare($query);
        //$stmt->execute();
        //$mesas = $stmt->fetchAll();
        // $val = 0;
        //$AdConstantes = new AdConstantes();
        //$desTipoMenu = $AdConstantes->tipoMenu($em, $fin3);

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'departamento'=> $departamento,
            'municipio' => $municipio
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reportecuadro161718192021.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }    
    
    public function buscarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $session->set('_modo_buscar', 1);
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        $numboleta = $request->get('numboleta');
        
        $dsql ="select dt.idEnc, dt.numBoleta, mu.nombre, ba.nombre as nomBarrio, dt.codBarrio, co.nombre as nomColonia, dt.codColonia, al.nombre as nomAldea, dt.codAldea, ca.nombre as nomCaserio, dt.codCaserio, en.nombre as nomEncuestador, dt.idEncuestador, dt.nombreEntrevistado, dt.telefonoCel, dt.usuarioCreacion "
                . "from FocalAppBundle:DatosGenerales dt "
                . "left join FocalAppBundle:AdComunidades co with (co.codComunidad = dt.codColonia)"
                . "left join FocalAppBundle:AdComunidades ba with (ba.codComunidad = dt.codBarrio)"
                . "left join FocalAppBundle:AdComunidades al with (al.codComunidad = dt.codAldea)"
                . "left join FocalAppBundle:AdComunidades ca with (ca.codComunidad = dt.codCaserio) "
                . "left join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = dt.codMunicipio) "
                . "left join FocalAppBundle:AdEncuestadores en with (en.id = dt.idEncuestador)"
                . "where dt.estado = :estado and dt.codDepartamento = :departamento and dt.codMunicipio = :municipio and dt.periodo = :per "
                . "and dt.numBoleta = :numboleta "
                . "order by dt.fechaCreacion DESC";
            $query = $em->createQuery($dsql);
            $query ->setParameter('estado', AppConst::ESTADO_ENC_VALIDADA);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('departamento', $codDept);
            $query ->setParameter('numboleta', $numboleta);
            $query ->setParameter('per', $periodo);
            $entities = $query->getResult();
        
        /* Cambio el estado de la encuesta para que el usuario tenga que validarla de nuevo */  
        if($entities) {
            $id = $entities[0]['idEnc'];
            $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni, 'codDepartamento' => $codDept, 'periodo' => $periodo));
            
            $entity[0]->setEstado(AppConst::ESTADO_ENC_CREADA);
            $em->flush();
        }    
        return $this->render('FocalAppBundle:DatosGenerales:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Lists all DatosGenerales entities.
     *
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $session->set('_modo_buscar', 0);
        $codMuni = $session->get('_cod_municipio');
        
        $periodo = $session->get('_periodo');
        
        
        $dsql ="select dt.idEnc, dt.numBoleta, mu.nombre, ba.nombre as nomBarrio, dt.codBarrio, co.nombre as nomColonia, dt.codColonia, al.nombre as nomAldea, dt.codAldea, ca.nombre as nomCaserio, dt.codCaserio, en.nombre as nomEncuestador, dt.idEncuestador, dt.nombreEntrevistado, dt.telefonoCel, dt.usuarioCreacion "
                . "from FocalAppBundle:DatosGenerales dt "
                . "left join FocalAppBundle:AdComunidades co with (co.codComunidad = dt.codColonia)"
                . "left join FocalAppBundle:AdComunidades ba with (ba.codComunidad = dt.codBarrio)"
                . "left join FocalAppBundle:AdComunidades al with (al.codComunidad = dt.codAldea)"
                . "left join FocalAppBundle:AdComunidades ca with (ca.codComunidad = dt.codCaserio) "
                . "left join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = dt.codMunicipio) "
                . "left join FocalAppBundle:AdEncuestadores en with (en.id = dt.idEncuestador)"
                . "where dt.estado = :estado and dt.codMunicipio = :municipio and dt.periodo = :per order by dt.fechaCreacion DESC";
            $query = $em->createQuery($dsql);
            $query ->setParameter('estado', AppConst::ESTADO_ENC_CREADA);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('per', $periodo);
            $entities = $query->getResult();
        
        return $this->render('FocalAppBundle:DatosGenerales:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatosGenerales entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatosGenerales();
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        
        $form = $this->createCreateForm($entity, $codMuni);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('numBoleta' => $entity->getNumBoleta(), 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

        if ($ent) {
            $this->addFlash('warning','Número de boleta ya existe');
            
            $dsql ="select distinct co.codComunidad, co.nombre "
                . "from FocalAppBundle:AdComunidades co "
                . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
                . "order by co.nombre";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $comunidades = $query->getResult();
            
            return $this->render('FocalAppBundle:DatosGenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'comunidades' => $comunidades,    
            ));
        }        
        
        if ($form->isValid()) {
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);
            $entity->setEstado(AppConst::ESTADO_ENC_CREADA); /* 1=Creada, 2=Validada, 3=Cancelada */
            
            $session = $request->getSession();
            $codMuni = $session->get('_cod_municipio');
            $codDept = $session->get('_cod_departamento');
            $periodo = $session->get('_periodo');
            $entity->setcodMunicipio($codMuni);  
            $entity->setcodDepartamento($codDept);
            $entity->setperiodo($periodo);
            
            $codComunidad = $request->get('comunidad');
            $entity->setCodColonia($codComunidad);
            
            /*if ($entity->getcodColonia() !== null) {
                $codCol = $entity->getcodColonia()->getcodComunidad();
                $entity->setCodColonia($codCol);
            }
            if ($entity->getcodBarrio() !== null) {
                $codBar = $entity->getcodBarrio()->getcodComunidad();
                $entity->setCodBarrio($codBar);
            }
            
            if ($entity->getCodAldea() !== null) {
                $codAld = $entity->getCodAldea()->getcodComunidad();
                $entity->setCodAldea($codAld);
            }
            
            if ($entity->getCodCaserio() !== null) {
                $codCas = $entity->getCodCaserio()->getcodComunidad();
                $entity->setCodCaserio($codCas);
            }*/
            if ($entity->getIdEncuestador() !== null) {
                $idEnc = $entity->getIdEncuestador()->getId();
                $entity->setIdEncuestador($idEnc);
            }
            
            $sequenceName = 'datos_generales_id_enc_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            
            $codCom = $codMuni . $newId;
            $entity->setIdEnc($codCom);

            $em->persist($entity);
            $em->flush();
            
            return $this->redirect($this->generateUrl('datosgenerales_show', array('id' => $entity->getIdEnc())));
        }

        return $this->render('FocalAppBundle:DatosGenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a DatosGenerales entity.
     *
     * @param DatosGenerales $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosGenerales $entity, $codMuni)
    {
        $form = $this->createForm(new DatosGeneralesType(), $entity, array(
            'action' => $this->generateUrl('datosgenerales_create'),
            'method' => 'POST',
            'codMuni' => $codMuni,
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosGenerales entity.
     *
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        $dsql ="select distinct co.codComunidad, co.nombre "
                . "from FocalAppBundle:AdComunidades co "
                . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
                . "order by co.nombre";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $comunidades = $query->getResult();
            
        $entity = new DatosGenerales();
        $form   = $this->createCreateForm($entity, $codMuni);

        return $this->render('FocalAppBundle:DatosGenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'comunidades' => $comunidades
        ));
    }

    /**
     * Finds and displays a DatosGenerales entity.
     *
     */
    public function showAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $periodo = $session->get('_periodo');
        $modob = $session->get('_modo_buscar');
        
        $dsql ="select dt.idEnc, dt.numBoleta, dt.numUbicacion, dt.cantPersonasvivienda, dt.cantPersonas523, dt.cantPersonasm10, dt.codMunicipio, mu.nombre as nomMunicipio, ba.nombre as nomBarrio, dt.codBarrio, co.nombre as nomColonia, dt.codColonia, al.nombre as nomAldea, dt.codAldea, ca.nombre as nomCaserio, dt.codCaserio, en.nombre as nomEncuestador, dt.idEncuestador, dt.nombreEntrevistado, dt.telefonoCel "
                . "from FocalAppBundle:DatosGenerales dt "
                . "left join FocalAppBundle:AdComunidades co with (co.codComunidad = dt.codColonia)"
                . "left join FocalAppBundle:AdComunidades ba with (ba.codComunidad = dt.codBarrio)"
                . "left join FocalAppBundle:AdComunidades al with (al.codComunidad = dt.codAldea)"
                . "left join FocalAppBundle:AdComunidades ca with (ca.codComunidad = dt.codCaserio) "
                . "left join FocalAppBundle:AdMunicipios mu with (mu.codMunicipio = dt.codMunicipio) "
                . "left join FocalAppBundle:AdEncuestadores en with (en.id = dt.idEncuestador)"
                . "where dt.estado = :estado and dt.codMunicipio = :municipio and dt.idEnc = :idenc and dt.periodo = :per ";
            $query = $em->createQuery($dsql);
            $query ->setParameter('estado', ($modob == '0')?AppConst::ESTADO_ENC_CREADA:AppConst::ESTADO_ENC_VALIDADA);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('idenc', $id);
            $query ->setParameter('per', $periodo);
            $entity = $query->getResult();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosGenerales entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosGenerales:show.html.twig', array(
            'entity'      => $entity[0],
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function validarAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $modob = $session->get('_modo_buscar');
        
        $enccompleta = 'enabled';
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni, 'estado'=> ($modob == 0 ) ? AppConst::ESTADO_ENC_CREADA : AppConst::ESTADO_ENC_VALIDADA ));
        if (!$entity) {
            $this->addFlash('warning_dg','Datos Generales no estan ingresados');
            $enccompleta = 'disabled';
        } else {
            $numboleta = $entity[0]->getNumBoleta();
        }
        $entity = $em->getRepository('FocalAppBundle:DatosdFamilia')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_df','Datos Demograficos no estan ingresados completamente (Familiares)');
            $enccompleta = 'disabled';
        }
        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_dr','Datos Demograficos no estan ingresados completamente (Ragos de Edades)');
            $enccompleta = 'disabled';
        }        
        $entity = $em->getRepository('FocalAppBundle:DatosdOtros')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_do','Datos Demograficos no estan ingresados completamente (Otros)');
            $enccompleta = 'disabled';
        }   
        $entity = $em->getRepository('FocalAppBundle:DatossGeneral')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sg','Datos de Salud no estan ingresados completamente (Generales)');
            $enccompleta = 'disabled';
        }
        /*$entity = $em->getRepository('FocalAppBundle:DatossEnfermedades')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_se','Datos de Salud no estan ingresados completamente (Enfermedades)');
            $enccompleta = 'disabled';
        }*/
        $entity = $em->getRepository('FocalAppBundle:DatossEdadvacunas')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sv','Datos de Salud no estan ingresados completamente (Vacunas)');
            $enccompleta = 'disabled';
        }
        $entity = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sp','Datos de Seguridad y Participación no estan ingresados completamente');
            $enccompleta = 'disabled';
        } 
        $entity = $em->getRepository('FocalAppBundle:DatosServiciospub')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_spu','Datos de Servicios Publicos no estan ingresados completamente');
            $enccompleta = 'disabled';
        }   
        $entity = $em->getRepository('FocalAppBundle:DatosSegAlimentaria')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sa','Datos de Seguridad Alimentaria no estan ingresados completamente');
            $enccompleta = 'disabled';
        } 
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        $cantp523 = $entity[0]->getCantPersonas523();
        if(!empty($cantp523)) {
            $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
            if (!$entity) {
                $this->addFlash('warning_sed','Datos de Educación no estan ingresados completamente');
                $enccompleta = 'disabled';
            } 
            /* validad la cantidad ingresada con los datos de educación */
            $dsql ="select count(dt.id) suma "
                . "from FocalAppBundle:DatosEducacion dt "
                . "where dt.codMunicipio = :municipio "
                    . "and dt.codDepartamento = :departamento "
                    . "and dt.idEnc = :idenc ";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $query ->setParameter('departamento', $codDept);
            $query ->setParameter('idenc', $id);
            $datos = $query->getResult();
            $sumareg = $datos[0]['suma'];
            if($sumareg != $cantp523) {
                $this->addFlash('warning_ced','Las cantidades de personas en edad de estudiar es diferente a las ingresadas, cambie las cantidades, ver pregunta no.10');
                $enccompleta = 'disabled';
            }
        }
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        $cantpMayor10 = $entity[0]->getCantPersonasm10();  
        /* validad la cantidad ingresada con los datos de laboral */
        $dsql ="select count(dt.id) suma "
            . "from FocalAppBundle:DatosFuerzaIngresos dt "
            . "where dt.codMunicipio = :municipio "
                . "and dt.codDepartamento = :departamento "
                . "and dt.idEnc = :idenc ";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $query ->setParameter('departamento', $codDept);
        $query ->setParameter('idenc', $id);
        $datos = $query->getResult();
        $sumareg = $datos[0]['suma'];
        
        if($cantpMayor10 != $sumareg) {
            $this->addFlash('warning_cfl','Las cantidades de personas en edad de trabajar es diferente a las ingresadas en Fuerza Laboral e Ingreso, cambie las cantidades, ver pregunta no.11');
                $enccompleta = 'disabled';
        }
        /* Validar que la cantidad de personas sea igual a las ingresadas */
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        $cantp = $entity[0]->getCantPersonasvivienda();
        $dsql ="select count(dt.id) suma "
            . "from FocalAppBundle:DatosdFamilia dt "
            . "where dt.codMunicipio = :municipio "
                . "and dt.codDepartamento = :departamento "
                . "and dt.idEnc = :idenc ";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $query ->setParameter('departamento', $codDept);
        $query ->setParameter('idenc', $id);
        $datos = $query->getResult();
        $sumareg = $datos[0]['suma'];
        
        if($cantp != $sumareg) {
            $this->addFlash('warning_cpf','Las cantidades de personas en la vivienda es diferente a las ingresadas en datos familiares, cambie las cantidades, ver pregunta no.9');
                $enccompleta = 'disabled';
        }
        
        /* Validar que las cantidades en rangos de edad sean iguales a los rangos ingresados en familiares */
        $dsql ="select sum(dr.cantPersonas) totrango "
                . "from FocalAppBundle:DatosdRangos dr "
                . "where dr.codMunicipio = :municipio "
                . "and dr.codDepartamento = :departamento "
                . "and dr.idEnc = :idenc ";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $query ->setParameter('departamento', $codDept);
        $query ->setParameter('idenc', $id);
        $datos = $query->getResult();
        $totrango = $datos[0]['totrango'];
        
        if($cantp != $totrango) {
            $this->addFlash('warning_crng','Las cantidades de familiares es diferente a las ingresadas por rango, ver preguntas 12 y 13');
                $enccompleta = 'disabled';
        }
        
        $dsql ="select 
                sum(case when (dt.edad <1) and (dt.sexo = 1) then 1 else 0 end) as rh1,
                sum(case when (dt.edad <1) and (dt.sexo = 2) then 1 else 0 end) as rm1, 
                sum(case when (dt.edad >=1 and dt.edad <=4) and (dt.sexo = 1) then 1 else 0 end) as rh2,
                sum(case when (dt.edad >=1 and dt.edad <=4) and (dt.sexo = 2) then 1 else 0 end) as rm2, 
                sum(case when (dt.edad >=5 and dt.edad <=6) and (dt.sexo = 1) then 1 else 0 end) as rh3,
                sum(case when (dt.edad >=5 and dt.edad <=6) and (dt.sexo = 2) then 1 else 0 end) as rm3,
                sum(case when (dt.edad >=7 and dt.edad <=12) and (dt.sexo = 1) then 1 else 0 end) as rh4,
                sum(case when (dt.edad >=7 and dt.edad <=12) and (dt.sexo = 2) then 1 else 0 end) as rm4,
                sum(case when (dt.edad >=13 and dt.edad <=15) and (dt.sexo = 1) then 1 else 0 end) as rh5,
                sum(case when (dt.edad >=13 and dt.edad <=15) and (dt.sexo = 2) then 1 else 0 end) as rm5,
                sum(case when (dt.edad >=16 and dt.edad <=18) and (dt.sexo = 1) then 1 else 0 end) as rh6,
                sum(case when (dt.edad >=16 and dt.edad <=18) and (dt.sexo = 2) then 1 else 0 end) as rm6,
                sum(case when (dt.edad >=19 and dt.edad <=23) and (dt.sexo = 1) then 1 else 0 end) as rh7,
                sum(case when (dt.edad >=19 and dt.edad <=23) and (dt.sexo = 2) then 1 else 0 end) as rm7,
                sum(case when (dt.edad >=24 and dt.edad <=30) and (dt.sexo = 1) then 1 else 0 end) as rh8,
                sum(case when (dt.edad >=24 and dt.edad <=30) and (dt.sexo = 2) then 1 else 0 end) as rm8,
                sum(case when (dt.edad >=31 and dt.edad <=40) and (dt.sexo = 1) then 1 else 0 end) as rh9,
                sum(case when (dt.edad >=31 and dt.edad <=40) and (dt.sexo = 2) then 1 else 0 end) as rm9,
                sum(case when (dt.edad >=41 and dt.edad <=50) and (dt.sexo = 1) then 1 else 0 end) as rh10,
                sum(case when (dt.edad >=41 and dt.edad <=50) and (dt.sexo = 2) then 1 else 0 end) as rm10,
                sum(case when (dt.edad >=51 and dt.edad <=64) and (dt.sexo = 1) then 1 else 0 end) as rh11,
                sum(case when (dt.edad >=51 and dt.edad <=64) and (dt.sexo = 2) then 1 else 0 end) as rm11,
                sum(case when (dt.edad >=65 ) and (dt.sexo = 1) then 1 else 0 end) as rh12,
                sum(case when (dt.edad >=65 ) and (dt.sexo = 2) then 1 else 0 end) as rm12 "
            . "from FocalAppBundle:DatosdFamilia dt "
            . "where dt.codMunicipio = :municipio "
                . "and dt.codDepartamento = :departamento "
                . "and dt.idEnc = :idenc ";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $query ->setParameter('departamento', $codDept);
        $query ->setParameter('idenc', $id);
        $datos = $query->getResult();
        
        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        
        $rngok = true;
        foreach($entity as $ent) {
            $rng = $ent->getRango();
            
            $cantrngh = $ent->getCantHombres();
            $cantrngm = $ent->getCantMujeres();
            $rngfamh = $datos[0]['rh'.$rng];
            $rngfamm = $datos[0]['rm'.$rng];
            if(($cantrngh != $rngfamh)||($cantrngm != $rngfamm)) {
                $rngok = false;
            }
        }
        if (!$rngok) {
            $this->addFlash('warning_rng','Las cantidades ingresadas en los rangos edad no son iguales a las edades en datos de la familia');
            $enccompleta = 'disabled';
        }
        /* Validar la cantidad de personas ingresasa en familia sea igual a la cantidad de rangos ingresados */
        
                
        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaIngresos')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sfi','Datos de Fuerza de Ingresos no estan ingresados completamente (Fuerza laboral)');
            $enccompleta = 'disabled';
        }
        $entity = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_sfo','Datos de Fuerza de Ingresos no estan ingresados completamente (Otros)');
            $enccompleta = 'disabled';
        }
        $entity = $em->getRepository('FocalAppBundle:DatosVivienda')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if (!$entity) {
            $this->addFlash('warning_dv','Datos de Vivienda no estan ingresados completamente');
            $enccompleta = 'disabled';
        }
        return $this->render('FocalAppBundle:DatosGenerales:validar.html.twig', array(
            //'entity' => $entity[0],
            'idenc' => $id,
            'numboleta' => $numboleta,
            'enccompleta' => $enccompleta,
        ));
    }
    
public function guardarAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $modob = $session->get('_modo_buscar');
        $periodo = $session->get('_periodo');
        
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni, 'estado'=> ($modob == '0')?AppConst::ESTADO_ENC_CREADA:AppConst::ESTADO_ENC_VALIDADA));
        if (!$entity) {
            $this->addFlash('warning','Error al guardar los datos validados, la encuenta no se guardo');

        } else {
            $entity[0]->setEstado(AppConst::ESTADO_ENC_VALIDADA);
            $entity[0]->setPeriodo($periodo);
            $em->flush();
            $this->addFlash('success','Cambios realizados con exito');
        }
        $enccompleta = 'disabled';
        $numboleta = $entity[0]->getNumBoleta();
        return $this->render('FocalAppBundle:DatosGenerales:validar.html.twig', array(
            'entity' => $entity[0],
            'idenc' => $id,
            'numboleta' => $numboleta,
            'enccompleta' => $enccompleta,
        ));
    }    
    /**
     * Displays a form to edit an existing DatosGenerales entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->find($id);
        $codMuni = $entity->getCodMunicipio();  
        $encsel = $entity->getIdEncuestador();
        $comsel = $entity->getCodColonia();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosGenerales entity.');
        }
        
        $dsql ="select distinct co.codComunidad, co.nombre "
                . "from FocalAppBundle:AdComunidades co "
                . "where co.codMunicipio = :municipio "
                . "order by co.nombre";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $comunidades = $query->getResult();
            
        $editForm = $this->createEditForm($entity, $entity->getCodMunicipio());
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosGenerales:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'idenc' => $entity->getIdEnc(),
            'comunidades' => $comunidades,
            'encsel' => $encsel,
            'comsel' => $comsel,
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosGenerales entity.
    *
    * @param DatosGenerales $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosGenerales $entity, $codMuni)
    {
        $form = $this->createForm(new DatosGeneralesType(), $entity, array(
            'action' => $this->generateUrl('datosgenerales_update', array('id' => $entity->getIdEnc())),
            'method' => 'PUT',
            'codMuni' => $codMuni,
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosGenerales entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosGenerales entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity, $entity->getCodMunicipio());
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($entity->getIdEncuestador() !== null) {
                $idEncuestador = $entity->getIdEncuestador()->getId();
                $entity->setIdEncuestador($idEncuestador);
            }
            $codComunidad = $request->get('comunidad');
            $entity->setCodColonia($codComunidad);
            
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            
            $em->flush();

            return $this->redirect($this->generateUrl('datosgenerales_edit', array('id' => $id)));
        }

        return $this->render('FocalAppBundle:DatosGenerales:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatosGenerales entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=> $id, 'codMunicipio' => $codMuni));
        if ($entity) {
            $query = $em->getConnection()->prepare('select * from SP_Borrarencuesta(:idenc)');
            $query->bindValue('idenc', $id);
            $query->execute();
            $resultado = $query->fetchall();
        }
        
        return $this->redirect($this->generateUrl('datosgenerales'));
    }

    /**
     * Creates a form to delete a DatosGenerales entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosgenerales_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
