<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Entity\DatosVivienda;
use Focal\AppBundle\Entity\DatosSeguridadParticipacion;
use Focal\AppBundle\Entity\DatosServiciospub;
use Focal\AppBundle\Entity\DatossEnfermedades;
use Focal\AppBundle\Entity\DatosSegAlimentaria;
use Focal\AppBundle\Entity\DatosGrupoFamiliar;
use Focal\AppBundle\Entity\DatosdOtros;
use Focal\AppBundle\Entity\DatosdRangos;
use Focal\AppBundle\Entity\DatosFuerzaOtros;
use Focal\AppBundle\Entity\DatossGeneral;
use Focal\AppBundle\Form\DatosGeneralesType;
use Focal\AppBundle\Entity\AppConst;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * DatosGenerales controller.
 *
 */
class DatosGeneralesABController extends Controller
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
        
        return $this->render('FocalAppBundle:DatosGenerales:indexAB.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatosGenerales entity.
     *
     */
    public function createAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        

        $em = $this->getDoctrine()->getManager();
        
        /* Listado de comunidades */
        $dsql ="select distinct co.codComunidad, co.nombre "
        . "from FocalAppBundle:AdComunidades co "
        . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
        . "order by co.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $comunidades = $query->getResult();

        /* Listado de encuestadores */
        $dsql ="select en.id, en.nombre "
        . "from FocalAppBundle:AdEncuestadores en "
        . "where en.codMunicipio = :municipio and en.estado = 1" // Estado activo
        . "order by en.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $encuestadores = $query->getResult();        
        
        /* Revisar numero de boleta si ya existe */
        $qst_07 = $request->get('qst_07');
        try {
            $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('numBoleta' => $qst_07, 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

            if ($ent) { 
                $this->addFlash('warning','Número de boleta ya existe');

                return $this->render('FocalAppBundle:DatosGenerales:newAB.html.twig', array(
                //'entity' => $entity,
                //'form'   => $form->createView(),
                'comunidades' => $comunidades, 
                'encuestadores' => $encuestadores,    
                ));
            } 
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            throw new NotFoundHttpException('Número de boleta ya existe o esta vacia');
            //throw $this->createAccessDeniedException();
        }
        /****************** Datos Comunes **********************/
        $codComunidad = $request->get('qst_03');
        
        /* Generar el numero de secuencia para el id_enc */
        try {
        $sequenceName = 'datos_generales_id_enc_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

        $id_encuesta = $codMuni . $newId;
        
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            //throw $e;
        }
        
/* =================== DATOS GENERALES ===========================*/
        $entity = new DatosGenerales();
        
        /* Verifica y valida los valores de la tabla datos_generales */
        
        // s******* Nombre del entrevistado
        $qst_01 = $request->get('qst_01');
        if (empty($qst_01)) {
            throw new NotFoundHttpException('Nombre de entrevistado no puede estar vacio');
        }
        $entity->setNombreEntrevistado($qst_01);
        
        // ******* Verificar id_encuestador
        $qst_01_01 = $request->get('qst_01_01');
        if (empty($qst_01_01)) {
            throw new NotFoundHttpException('Nombre de encuestador no puede estar vacio');
        }
        $entity->setIdEncuestador($qst_01_01);
        
        $entity->setIdEnc($id_encuesta);
        $telCelular = $request->get('qst_02');
        $numUbicacion = $request->get('qst_06');
        $numBoleta = $request->get('qst_07');
        
        $entity->setCodColonia($codComunidad);
        $entity->setTelefonoCel($telCelular);
        $entity->setNumUbicacion($numUbicacion);
        $entity->setNumBoleta($numBoleta);
        
        /* Guarda los valores por defecto */
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        $entity->setUsuarioCreacion($usuario);
        $entity->setUsuarioUltimaModificacion($usuario);
        $entity->setFechaCreacion($fecha);
        $entity->setFechaUltimaModificacion($fecha);
        $entity->setEstado(AppConst::ESTADO_ENC_CREADA); /* 1=Creada, 2=Validada, 3=Cancelada */

       
        $entity->setcodMunicipio($codMuni);  
        $entity->setcodDepartamento($codDept);
        $entity->setperiodo($periodo);
        
/*================ DATOS VIVIENDA ================*/
        $entviv = new DatosVivienda();
        
        /* Generar el id unico de la tabla vivienda */
        $sequenceName = 'datos_vivienda_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entviv->setId((int)$codCom);
        $entviv->setIdEnc($id_encuesta);
        $entviv->setCodComunidad($codComunidad);
        $entviv->setcodMunicipio($codMuni);  
        $entviv->setcodDepartamento($codDept);
        
        
        $mat_viv = $request->get('qst_12');
        $mat_techo = $request->get('qst_13');
        $mat_piso = $request->get('qst_14');
        $pro_viv = $request->get('qst_15');
        $con_viv = $request->get('qst_16');
        $tenencia = $request->get('qst_20_01');
        $ten_sexo = $request->get('qst_20_02');
        $tiene_coc = $request->get('qst_21');
        $ubi_cocina = $request->get('qst_22');
        $con_ele = $request->get('qst_23_1');
        $con_gas = $request->get('qst_23_2');
        $con_ker = $request->get('qst_23_3');
        $con_len = $request->get('qst_23_4');
        $con_eco = $request->get('qst_23_5');
        $con_nin = $request->get('qst_23_6');
        $con_otro = $request->get('qst_23_7');
        $con_nsnr = $request->get('qst_23_8');
        
        $agu_not = $request->get('qst_24_1');
        $agu_bot = $request->get('qst_24_2');
        $agu_fil = $request->get('qst_24_3');
        $agu_her = $request->get('qst_24_4');
        $agu_clo = $request->get('qst_24_5');
        $agu_otro = $request->get('qst_24_6');
        $agu_nsnr = $request->get('qst_24_7');
        
        $piezas_viv = $request->get('qst_25');
        $banos_viv = $request->get('qst_26');
        $dor_viv = $request->get('qst_27');
        $per_xdor = $request->get('qst_28');
        $fam_xviv = $request->get('qst_29');
        $per_xviv = $request->get('qst_30');
        
        $miembro_mig = $request->get('qst_31_01');
        $mig_hom = $request->get('qst_31_02');
        $mig_muj = $request->get('qst_31_03');
        
        
        
        $mig_ca = $request->get('qst_32_01');
        $mig_na = $request->get('qst_32_02');
        $mig_sa = $request->get('qst_32_03');
        $mig_eu = $request->get('qst_32_04');
        $mig_in = $request->get('qst_32_05');
        $mig_ot = $request->get('qst_32_06');
        
        
        $entviv->setCantCentroa($mig_ca);
        $entviv->setCantNortea($mig_na);
        $entviv->setCantSura($mig_sa);
        $entviv->setCantEuropa($mig_eu);
        $entviv->setCantDentropais($mig_in);
        $entviv->setCantOtros($mig_ot);
        
        
        $entviv->setCantHombres($mig_hom);
        $entviv->setCantMujeres($mig_muj);
        $entviv->setMiembroEmigrado($miembro_mig);
        
        
        $entity->setCantPersonasvivienda($per_xviv); /* Campo de datos generales */
        
        $entviv->setFamiliasCasa($fam_xviv);
        $entviv->setPersonasxDormitorio($per_xdor);
        $entviv->setDormitoriosVivienda($dor_viv);
        $entviv->setBanosVivienda($banos_viv);
        $entviv->setPiezasVivienda($piezas_viv);
        
        
        $entviv->setConsumoaguaNotratada($agu_not);
        $entviv->setConsumoaguaBotellon($agu_bot);
        $entviv->setConsumoaguaFiltrada($agu_fil);
        $entviv->setConsumoaguaHervida($agu_her);
        $entviv->setConsumoaguaClorada($agu_clo);
        
        $entviv->setConcinaconElec($con_ele);
        $entviv->setConcinaconChimbo($con_gas);
        $entviv->setConcinaconKerosen($con_ker);
        $entviv->setConcinaconLena($con_len);
        $entviv->setConcinaconEco($con_eco);
        
        
        $entviv->setUbicacionCocina($ubi_cocina);
        $entviv->setTieneCocina($tiene_coc);
        $entviv->setTipoTenencia($tenencia);
        $entviv->setSexoTenencia($ten_sexo);
        $entviv->setMaterialVivienda($mat_viv);
        $entviv->setMaterialTecho($mat_techo);
        $entviv->setMaterialPiso($mat_piso);
        switch ($pro_viv) {
            case 1: 
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                break;
            case 2:
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                break;
            case 3: 
                $entviv->setPvSinrepello(1);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                break;
            case 4: 
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(1);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                break;
            case 5: 
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(1);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                break;
            case 6:
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(1);
                $entviv->setPvNinguno(0);
                break;
            case 7:
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvNinguno(0);
                break;
            case 8: 
                $entviv->setPvSinrepello(0);
                $entviv->setPvPisotierra(0);
                $entviv->setPvCielofalso(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvTechomalo(0);
                $entviv->setPvNinguno(1);
                break;
                
        }
        $entviv->setCondicionVivienda($con_viv);
        
        
        /* Guarda los valores por defecto */
        $entviv->setUsuarioCreacion($usuario);
        $entviv->setUsuarioUltimaModificacion($usuario);
        $entviv->setFechaCreacion($fecha);
        $entviv->setFechaUltimaModificacion($fecha);
        
        

/* ================ SEGURIDAD Y PARTICIPACION ==================*/
        $entsep = new DatosSeguridadParticipacion;
        
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datos_seguridad_participacion_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entsep->setId((int)$codCom);
        $entsep->setIdEnc($id_encuesta);
        $entsep->setCodComunidad($codComunidad);
        $entsep->setcodMunicipio($codMuni);  
        $entsep->setcodDepartamento($codDept);
        
        $cas_vio = $request->get('qst_34_01');
        $can_cvi = $request->get('qst_34_02');
        $vic_cvi = $request->get('qst_35_01');
        $can_vcv = $request->get('qst_35_02');
        $con_seg = $request->get('qst_36');
        $rin_rob = $request->get('qst_37_01');
        $rin_mar = $request->get('qst_37_02');
        $rin_dro = $request->get('qst_37_03');
        $rin_can = $request->get('qst_37_04');
        $rin_rin = $request->get('qst_37_05');
        $rin_vio = $request->get('qst_37_06');
        $rin_otr = $request->get('qst_37_07');
        
        $entsep->setOrdenViolaciones($rin_vio);
        $entsep->setOrdenPeleas($rin_rin);
        $entsep->setOrdenCantinas($rin_can);
        $entsep->setOrdenDrogas($rin_dro);
        $entsep->setOrdenMaras($rin_mar);
        $entsep->setOrdenRobo($rin_rob);
        $entsep->setConsideraSeguro($con_seg);
        $entsep->setConsideraSeguro($con_seg);
        $entsep->setCantVictimaViolencia($can_vcv);
        $entsep->setVictimaViolencia($vic_cvi);
        $entsep->setCantCasosViolencia($can_cvi);
        $entsep->setCasosViolencia($cas_vio);
        
        /* Guarda los valores por defecto */
        $entsep->setUsuarioCreacion($usuario);
        $entsep->setUsuarioUltimaModificacion($usuario);
        $entsep->setFechaCreacion($fecha);
        $entsep->setFechaUltimaModificacion($fecha);

/* ================ SERVICIOS PUBLICOS ==================*/

        for($i = 1; $i <= 17; $i++) {
            $entspu = new DatosServiciospub;
            
            /* Generar el id unico de la tabla servicios publicos */
            $sequenceName = 'datos_serviciospub_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            $codCom = (int)$codMuni . (int)$newId;

            $entspu->setId($codCom);
            $entspu->setIdEnc($id_encuesta);
            $entspu->setCodComunidad($codComunidad);
            $entspu->setcodMunicipio($codMuni);  
            $entspu->setcodDepartamento($codDept);
            $entspu->setIdServicio($i);
            
            $srv_rec = (int)$request->get('qst_38_'.$i.'_01');
            $srv_est = (int)$request->get('qst_38_'.$i.'_02');
            $srv_dia = (int)$request->get('qst_38_'.$i.'_03');
            
            $entspu->setReciben($srv_rec);
            $entspu->setEstado($srv_est);
            $entspu->setCantDias($srv_dia);
            
            /* Guarda los valores por defecto */
            $entspu->setUsuarioCreacion($usuario);
            $entspu->setUsuarioUltimaModificacion($usuario);
            $entspu->setFechaCreacion($fecha);
            $entspu->setFechaUltimaModificacion($fecha);
            
            $em->persist($entspu);
            
        }

/* ================ ENFERMEDADES ==================*/
        
        for($i = 1; $i <= 20; $i++) {
            $entenf = new DatossEnfermedades;
            
            /* Generar el id unico de la tabla servicios publicos */
            $sequenceName = 'datoss_enfermedades_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            $codCom = (int)$codMuni . $newId;

            $entenf->setId((int)$codCom);
            $entenf->setIdEnc($id_encuesta);
            $entenf->setCodComunidad($codComunidad);
            $entenf->setcodMunicipio($codMuni);  
            $entenf->setcodDepartamento($codDept);
            $entenf->setIdEnfermedad($i);
            
            $enf_hom = (int)$request->get('qst_39_'.$i.'_01');
            $enf_muj = (int)$request->get('qst_39_'.$i.'_02');
            $enf_pub = (int)$request->get('qst_39_'.$i.'_03');
            $enf_pri = (int)$request->get('qst_39_'.$i.'_04');
            $enf_pro = (int)$request->get('qst_39_'.$i.'_05');
            
            $entenf->setCantHombres($enf_hom);
            $entenf->setCantMujeres($enf_muj);
            $entenf->setCantPublica($enf_pub);
            $entenf->setCantPrivada($enf_pri);
            
            /* Guarda los valores por defecto */
            
            $entenf->setUsuarioCreacion($usuario);
            $entenf->setUsuarioUltimaModificacion($usuario);
            $entenf->setFechaCreacion($fecha);
            $entenf->setFechaUltimaModificacion($fecha);
            
            $em->persist($entenf);
            
        }        

/* ================ SEGURIDAD ALIMENTARIA ==================*/
        $entsal = new DatosSegAlimentaria;
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datos_seg_alimentaria_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entsal->setId((int)$codCom);
        $entsal->setIdEnc($id_encuesta);
        $entsal->setCodComunidad($codComunidad);
        $entsal->setcodMunicipio($codMuni);  
        $entsal->setcodDepartamento($codDept);
        
        $hur_fam = (int)$request->get('qst_40');
        $tra_tie = (int)$request->get('qst_41_01');
        $tti_hom = (int)$request->get('qst_41_02');
        $tti_muj = (int)$request->get('qst_41_03');
        $tte_tie = (int)$request->get('qst_42');
        $pro_ali = (int)$request->get('qst_43');
        $pro_suf = (int)$request->get('qst_44');
        
        $rie_got = (int)$request->get('qst_45_01');
        $rie_asp = (int)$request->get('qst_45_02');
        $rie_nin = (int)$request->get('qst_45_03');
        $rie_otr = (int)$request->get('qst_45_04');
        
        $pro_exd = (int)$request->get('qst_47');
        
        $ado_ave = (int)$request->get('qst_48_01');
        $ado_bov = (int)$request->get('qst_48_02');
        $ado_cap = (int)$request->get('qst_48_03');
        $ado_equ = (int)$request->get('qst_48_04');
        $ado_por = (int)$request->get('qst_48_05');
        $ado_pis = (int)$request->get('qst_48_06');
        $ado_api = (int)$request->get('qst_48_07');
        $ado_dom = (int)$request->get('qst_48_08');
        
       
        $entsal->setTieneHuerto($hur_fam);
        $entsal->setTrabajoTierra($tra_tie);
        $entsal->setCantHombres($tti_hom);
        $entsal->setCantHombres($tti_muj);
        $entsal->setTipoTenencia($tte_tie);
        $entsal->setProduceAlimento($pro_ali);
        $entsal->setProduceSuficiente($pro_suf);
        $entsal->setAreaGoteo($rie_got);
        $entsal->setAreaAspersion($rie_asp);
        $entsal->setAreaNinguno($rie_nin);
        $entsal->setAreaOtro($rie_otr);
        $entsal->setExcedente($pro_exd);
        $entsal->setCantAves($ado_ave);
        $entsal->setCantBovino($ado_bov);
        $entsal->setCantCaprino($ado_cap);
        $entsal->setCantEquino($ado_equ);
        $entsal->setCantPorcino($ado_por);
        $entsal->setCantPiscicultura($ado_pis);
        $entsal->setCantApicultura($ado_api);
        $entsal->setCantDomesticos($ado_dom);      
        
        /* Guarda los valores por defecto */
        $entsal->setUsuarioCreacion($usuario);
        $entsal->setUsuarioUltimaModificacion($usuario);
        $entsal->setFechaCreacion($fecha);
        $entsal->setFechaUltimaModificacion($fecha);

        
        $em->persist($entity);
        $em->persist($entviv);
        $em->persist($entsep);
        $em->persist($entsal);
        $em->flush();

        
        /* Listado de parentescos */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdParentesco p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $parentescos = $query->getResult();
        
        /* Listado de etnias */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdEtnia e "
        . "order by e.id";
        $query = $em->createQuery($dsql);
        $etnias = $query->getResult();
        
        /* Listado de ocupaciones */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdOcupaciones p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $ocupaciones = $query->getResult();
        
        /* Listado de profesiones */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdProfesiones e "
        . "order by e.id";
        $query = $em->createQuery($dsql);
        $profesiones = $query->getResult();
        
        $this->addFlash('success','Datos guardados con exito');
        return $this->render('FocalAppBundle:DatosGenerales:newABr.html.twig', array(
            //'entity' => $entity,
            //'form'   => $form->createView(),
            'idEnc' => $id_encuesta,
            'etnias' => $etnias, 
            'parentescos' => $parentescos,
            'ocupaciones' => $ocupaciones,
            'profesiones' => $profesiones,
        ));
    }

/**
     * Creates a new DatosGenerales entity.
     *
     */
    public function createABrAction(Request $request)
    {
        /* Verifica si el usuario esta autenticado */
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        
        $codComunidad = $request->get('codComunidad');
        $idEnc = $request->get('idEnc');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();

        $em = $this->getDoctrine()->getManager();
        
/* =================== DATOS GRUPO FAMILIAR ===========================*/
        
        
    /* Guarda cada registro ingresado */
        $cantfila = $request->get('contres');
        $numero = 1;
        $tingfam = 0;
        $per5a23 = 0;
        $perm10 = 0;
        $totalper = 0;
        
        $rangos = array(
            array("rng" =>  1, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  2, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  3, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  4, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  5, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  6, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  7, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  8, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  9, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 10, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 11, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
        );
        
        for($i = 1; $i <= $cantfila; $i++){
           $efi = $request->get('tblest_'.$i);
           if($efi == 1) {
                $entdgf = new DatosGrupoFamiliar();
                
                /* Generar el id unico de la tabla vivienda */
                $sequenceName = 'datos_grupo_familiar_id_seq';
                $dbConnection = $em->getConnection();
                $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                $codCom = (int)$codMuni . $newId;
                
                /* Guarda valores de referencia */
                $entdgf->setId((int)$codCom);
                $entdgf->setCodComunidad($codComunidad);
                $entdgf->setcodMunicipio($codMuni);  
                $entdgf->setcodDepartamento($codDept);
                $entdgf->setIdEnc($idEnc);
                
                $nom = (int)$request->get('qst_50_'.$i);
                $par = (int)$request->get('qst_51_'.$i);
                $sex = (int)$request->get('qst_52_'.$i);
                $eda = (int)$request->get('qst_53_'.$i);
                $etn = (int)$request->get('qst_54_'.$i);
                $rnp = (int)$request->get('qst_55_'.$i);
                $vac = (int)$request->get('qst_56_'.$i);
                $lye = (int)$request->get('qst_57_'.$i);
                $est = (int)$request->get('qst_58_'.$i);
                $acu = (int)$request->get('qst_59_'.$i);
                $aes = (int)$request->get('qst_60_'.$i);
                $eci = (int)$request->get('qst_61_'.$i);
                $tra = (int)$request->get('qst_62_'.$i);
                $ocu = (int)$request->get('qst_63_'.$i);
                $pro = (int)$request->get('qst_64_'.$i);
                $ing = (int)$request->get('qst_65_'.$i);
                $aec = (int)$request->get('qst_66_'.$i);
                $sco = (int)$request->get('qst_67_'.$i);
                $sde = (int)$request->get('qst_68_'.$i);
                $gee = (int)$request->get('qst_69_'.$i);
                $nor = (int)$request->get('qst_70_'.$i);
                $ia1 = $request->get('qst_71_1_'.$i);
                $ia2 = $request->get('qst_71_2_'.$i);
                $pao = (int)$request->get('qst_72_'.$i);
                $pre = (int)$request->get('qst_73_'.$i);
                $rem = (int)$request->get('qst_74_'.$i);
                $pla = (int)$request->get('qst_75_'.$i);
                
                $entdgf->setNumero($numero++);
                $entdgf->setNombre($nom);
                $entdgf->setIdParentesco($par);
                $entdgf->setSexo($sex);
                $entdgf->setEdad($eda);
                $entdgf->setIdEtnia($etn);
                $entdgf->setTienePn((empty($rnp))?0:$rnp);
                $entdgf->setVacunas((empty($vac))?0:$vac);
                $entdgf->setLeerEscribir((empty($lye))?0:$lye);
                $entdgf->setEstudia((empty($est))?0:$est);
                $entdgf->setAnoCursando((empty($acu))?0:$acu);
                $entdgf->setAnoEstudio((empty($aes))?:$aes);
                $entdgf->setEstadoCivil((empty($eci))?0:$eci);
                $entdgf->setTrabaja((empty($tra))?0:$tra);
                $entdgf->setOcupacion((empty($ocu))?0:$ocu);
                $entdgf->setProfesion((empty($pro))?0:$pro);
                $entdgf->setIngresos((empty($ing))?0:$ing);
                $entdgf->setActividadEco((empty($aec))?0:$aec);
                $entdgf->setSectorContratado((empty($sco))?0:$sco);
                $entdgf->setSectorDedica((empty($sde))?0:$sde);
                $entdgf->setGeneraEmpleo((empty($gee))?0:$gee);
                $entdgf->setNombreOrg((empty($nor))?null:$nor);
                $entdgf->setInstApoyo1((empty($ia1))?null:$ia1);
                $entdgf->setInstApoyo2((empty($ia2))?null:$ia2);
                $entdgf->setParticipaOrg($pao);
                $entdgf->setPrestamo((empty($pre))?0:$pre);
                $entdgf->setRemesas((empty($rem))?0:$rem);
                $entdgf->setMetodoPlanifica((empty($pla))?0:$pla);
                
                /* Guarda los valores por defecto */
                $fecha = new \DateTime("now");
                $entdgf->setUsuarioCreacion($usuario);
                $entdgf->setUsuarioUltimaModificacion($usuario);
                $entdgf->setFechaCreacion($fecha);
                $entdgf->setFechaUltimaModificacion($fecha);
                
                /* Variable que guarda el total de ingreso familiar */
                $tingfam = $tingfam + $ing;
                
                /* Calcula cantidad de personas por rango de edad y las que saben leer y escribir */
                if($eda <= 4) {
                    $rangos[0]["h"]  = $rangos[0]["h"]  + ($sex==1)?1:0;
                    $rangos[0]["m"]  = $rangos[0]["m"]  + ($sex==2)?1:0;
                    $rangos[0]["hl"] = $rangos[0]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[0]["ml"] = $rangos[0]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>4 && $eda<=6) {
                    $rangos[1]["h"]  = $rangos[1]["h"]  + ($sex==1)?1:0;
                    $rangos[1]["m"]  = $rangos[1]["m"]  + ($sex==2)?1:0;
                    $rangos[1]["hl"] = $rangos[1]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[1]["ml"] = $rangos[1]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[1]["t"]  = $rangos[1]["t"] + 1;
                } else if ($eda>6 && $eda<=12) {
                    $rangos[2]["h"]  = $rangos[2]["h"]  + ($sex==1)?1:0;
                    $rangos[2]["m"]  = $rangos[2]["m"]  + ($sex==2)?1:0;
                    $rangos[2]["hl"] = $rangos[2]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[2]["ml"] = $rangos[2]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>12 && $eda<=15) {
                    $rangos[3]["h"]  = $rangos[3]["h"]  + ($sex==1)?1:0;
                    $rangos[3]["m"]  = $rangos[3]["m"]  + ($sex==2)?1:0;
                    $rangos[3]["hl"] = $rangos[3]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[3]["ml"] = $rangos[3]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } if ($eda>15 && $eda<=18) {
                    $rangos[4]["h"]  = $rangos[4]["h"]  + ($sex==1)?1:0;
                    $rangos[4]["m"]  = $rangos[4]["m"]  + ($sex==2)?1:0;
                    $rangos[4]["hl"] = $rangos[4]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[4]["ml"] = $rangos[4]["ml"] + ($lye==1 && $sex==2)?1:0;
                } if ($eda>18 && $eda<=23) {
                    $rangos[5]["h"]  = $rangos[5]["h"]  + ($sex==1)?1:0;
                    $rangos[5]["m"]  = $rangos[5]["m"]  + ($sex==2)?1:0;
                    $rangos[5]["hl"] = $rangos[5]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[5]["ml"] = $rangos[5]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[5]["t"]  = $rangos[5]["t"] + 1;
                } if ($eda>23 && $eda<=30) {
                    $rangos[6]["h"]  = $rangos[6]["h"]  + ($sex==1)?1:0;
                    $rangos[6]["m"]  = $rangos[6]["m"]  + ($sex==2)?1:0;
                    $rangos[6]["hl"] = $rangos[6]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[6]["ml"] = $rangos[6]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[6]["t"]  = $rangos[6]["t"] + 1;
                } if ($eda>30 && $eda<=40) {
                    $rangos[7]["h"]  = $rangos[7]["h"]  + ($sex==1)?1:0;
                    $rangos[7]["m"]  = $rangos[7]["m"]  + ($sex==2)?1:0;
                    $rangos[7]["hl"] = $rangos[7]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[7]["ml"] = $rangos[7]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[7]["t"]  = $rangos[7]["t"] + 1;
                } if ($eda>40 && $eda<=50) {
                    $rangos[8]["h"]  = $rangos[8]["h"]  + ($sex==1)?1:0;
                    $rangos[8]["m"]  = $rangos[8]["m"]  + ($sex==2)?1:0;
                    $rangos[8]["hl"] = $rangos[8]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[8]["ml"] = $rangos[8]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[8]["t"]  = $rangos[8]["t"] + 1;
                } if ($eda>50 && $eda<=64) {
                    $rangos[9]["h"]  = $rangos[9]["h"]  + ($sex==1)?1:0;
                    $rangos[9]["m"]  = $rangos[9]["m"]  + ($sex==2)?1:0;
                    $rangos[9]["hl"] = $rangos[9]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[9]["ml"] = $rangos[9]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[9]["t"]  = $rangos[9]["t"] + 1;
                } if ($eda>64) {
                    $rangos[10]["h"]  = $rangos[10]["h"]  + ($sex==1)?1:0;
                    $rangos[10]["m"]  = $rangos[10]["m"]  + ($sex==2)?1:0;
                    $rangos[10]["hl"] = $rangos[10]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[10]["ml"] = $rangos[10]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[10]["t"]  = $rangos[10]["t"] + 1;
                }
                
                /* Calcula los valores de las personas que ocupan la vivienda para actualizar tabla: Datos Generales */
                if($eda >4 && $eda <=13) {
                    $per5a23 = $per5a23 + 1;
                }
                if($eda > 9) {
                    $perm10 = $perm10 + 1;
                }
                
                $totalper = $totalper + 1;
                
                $em->persist($entdgf);
            }        
        }
        
/* =================== DATOS DEMOGRAFICOS OTROS ===========================*/
        $entddo = new DatosdOtros;
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datosd_otros_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entddo->setId((int)$codCom);
        $entddo->setIdEnc($idEnc);
        $entddo->setCodComunidad($codComunidad);
        $entddo->setcodMunicipio($codMuni);  
        $entddo->setcodDepartamento($codDept);
        
        $msol = (int)$request->get('qst_76');
        $psol = (int)$request->get('qst_77');
        $cnac = (int)$request->get('qst_79_1_1');
        $cnho = (int)$request->get('qst_79_1_2');
        $cnmu = (int)$request->get('qst_79_1_3');
        $ema1 = (int)$request->get('qst_79_1_4');
        $ema2 = (int)$request->get('qst_79_1_4');
        $ema3 = (int)$request->get('qst_79_1_5');
        
        
        $entddo->setCantSolteros($psol) ;
        $entddo->setCantSolteras($msol) ;
        $entddo->setCantNacimientos($cnac);
        $entddo->setCantNacNinos($cnho);
        $entddo->setCantNacNinas($cnmu);
        $entddo->setEdadMadre($ema1);
        
        
        /* Guarda los valores por defecto */
        $entddo->setUsuarioCreacion($usuario);
        $entddo->setUsuarioUltimaModificacion($usuario);
        $entddo->setFechaCreacion($fecha);
        $entddo->setFechaUltimaModificacion($fecha);
        
        $em->persist($entddo);
        
        
/* =================== DATOS SALUD GENERAL ===========================*/
        $entsag = new DatossGeneral();
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datoss_general_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entsag->setId((int)$codCom);
        $entsag->setIdEnc($idEnc);
        $entsag->setCodComunidad($codComunidad);
        $entsag->setcodMunicipio($codMuni);  
        $entsag->setcodDepartamento($codDept);
        
        $embr = (int)$request->get('qst_78');
        $cemb = (int)$request->get('qst_78_1');
        $eem1 = (int)$request->get('qst_78_2');
        $eem2 = (int)$request->get('qst_78_3');
        $eem3 = (int)$request->get('qst_78_4');
        $dnun = (int)$request->get('qst_80');
        
        $muem = (int)$request->get('qst_81');
        $cmum = (int)$request->get('qst_81_1');
        $momm = (int)$request->get('qst_81_2');
        $caum = $request->get('qst_81_3');
        
        $muen = (int)$request->get('qst_82');
        $cmun = (int)$request->get('qst_82_1');
        $cmuh1 = (int)$request->get('qst_82_2_1');
        $cmum1 = (int)$request->get('qst_82_2_2');
        $caum1 = $request->get('qst_82_2_3');
        $cmuh2 = (int)$request->get('qst_82_3_1');
        $cmum2 = (int)$request->get('qst_82_3_2');
        $caum2 = (int)$request->get('qst_82_3_3');
        
       
        
        $entsag->setEmbarazos($embr);
        $entsag->setCantEmbarazos($cemb);
        $entsag->setEdad1($eem1);
        $entsag->setEdad2($eem2);
        $entsag->setEdad3($eem3);
        
        
        $entsag->setLugarCasa(false);
        $entsag->setCantCasa(0);
        $entsag->setLugarCentros(false);
        $entsag->setCantCentros(0);
        $entsag->setLugarMaterno(false);
        $entsag->setCantMaterno(0);
        $entsag->setLugarHospital(false);
        $entsag->setCantHospital(0);
        $entsag->setLugarClinica(false);
        $entsag->setCantClinica(0);
        $entsag->setLugarOtros(false);
        $entsag->setCantOtros(0);
        
        switch($dnun) {
            case 1:
                $entsag->setLugarCasa(true);
                break;
            case 2:
                $entsag->setLugarCentros(true);
                break;
            case 3:
                $entsag->setLugarClinica(true);
                break;
            case 4:
                $entsag->setLugarHospital(true);
                break;
            case 5:
                $entsag->setLugarClinica(true);
                break;
            case 6:
                $entsag->setLugarOtros(true);
                break;
            case 7:
                /* Falta este campo en la base de datos*/
                break;
        }
        
        $entsag->setSemurionino($muen);
        $entsag->setCantMuerteNinos($cmuh1);
        $entsag->setCantMuerteNinas($cmum1);
        $entsag->setCausaMuerte($caum1);
        
        $entsag->setMuerteMat($muem);
        $entsag->setCantMuertem($cmum);
        $entsag->setMomentoMuertem($momm);
        $entsag->setCausaMuertem($caum);
        
        /* Guarda los valores por defecto */
        $entsag->setUsuarioCreacion($usuario);
        $entsag->setUsuarioUltimaModificacion($usuario);
        $entsag->setFechaCreacion($fecha);
        $entsag->setFechaUltimaModificacion($fecha);
        
        $em->persist($entsag);        
        
/* =================== DATOS FUERZA OTROS ===========================*/
        $entdfo = new DatosFuerzaOtros();
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datos_fuerza_otros_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entdfo->setId((int)$codCom);
        $entdfo->setIdEnc($idEnc);
        $entdfo->setCodComunidad($codComunidad);
        $entdfo->setcodMunicipio($codMuni);  
        $entdfo->setcodDepartamento($codDept);
        
        $iaju = (int)$request->get('qst_83');
        
        $entdfo->setIngresoAjusta($iaju); /* 1 = un tiempo, 2 = 2 tiempos y 3 = 3 tiempos */
        
        /* Calcula el rango de ingresos familiar */
        
/***** FALTA INCLUIR EN EL CALCULO LAS REMESAS ******/
        
        $ringfam = 0;
        if($tingfam < 1000 ) {
            $ringfam = 1;
        } else if ($tingfam > 1001 && $tingfam <= 2000) {
            $ringfam = 2;
        } else if ($tingfam > 2001 && $tingfam <= 4000) {
            $ringfam = 3;
        } else if ($tingfam > 4001 && $tingfam <= 8000) {
            $ringfam = 4;
        } else if ($tingfam > 8001 && $tingfam <= 12000) {
            $ringfam = 5;
        } else if ($tingfam > 12001 && $tingfam <= 20000) {
            $ringfam = 6;
        } else if ($tingfam > 20001 && $tingfam <= 30000) {
            $ringfam = 7;
        } else if ($tingfam > 30001 && $tingfam <= 50000) {
            $ringfam = 8;
        } else if ($tingfam > 50001 ) {
            $ringfam = 9;
        } 
        
        $entdfo->setCantIngresofam($tingfam);
        $entdfo->setRangoIngresofam($ringfam);
        
        /* Guarda los valores por defecto */
        $entdfo->setUsuarioCreacion($usuario);
        $entdfo->setUsuarioUltimaModificacion($usuario);
        $entdfo->setFechaCreacion($fecha);
        $entdfo->setFechaUltimaModificacion($fecha);
        
        $em->persist($entdfo);
        
/* =================== DATOS DEMOGRAFICOS POR RANGOS EDAD ===========================*/
        
        foreach($rangos as $item) {
            if($item["t"] > 0) {
                $entddr = new DatosdRangos;
                /* Generar el id unico de la tabla seguridad y participacion */
                $sequenceName = 'datosd_rangos_id_seq';
                $dbConnection = $em->getConnection();
                $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                $codCom = (int)$codMuni . $newId;

                $entddr->setId((int)$codCom);
                $entddr->setIdEnc($idEnc);
                $entddr->setCodComunidad($codComunidad);
                $entddr->setcodMunicipio($codMuni);  
                $entddr->setcodDepartamento($codDept);

                $entddr->setRango($item["rng"]);
                $entddr->setCantPersonas($item["t"]);
                $entddr->setCantHombres($item["h"]);
                $entddr->setCantMujeres($item["m"]);
                $entddr->setCantHombresLeen($item["hl"]);
                $entddr->setCantMujeresLeen($item["ml"]);

                /* Guarda los valores por defecto */
                $entddr->setUsuarioCreacion($usuario);
                $entddr->setUsuarioUltimaModificacion($usuario);
                $entddr->setFechaCreacion($fecha);
                $entddr->setFechaUltimaModificacion($fecha);

                $em->persist($entddr);
            }
        }
        
/* =================== ACTUALIZAR DATOS GENERALES ===========================*/
        
        try {
            $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idEnc, 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

            if ($ent) { 
                $ent[0]->setCantPersonas523($per5a23);
                $ent[0]->setCantPersonasm10($perm10);
                $ent[0]->setCantPersonasvivienda($totalper);
                /* Guarda los valores por defecto */
                $ent[0]->setUsuarioUltimaModificacion($usuario);
                $ent[0]->setFechaUltimaModificacion($fecha);
            } 
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            $this->addFlash('warning_df','No se pudieron encontrar los Datos Generales algunos datos no se actulizaron en la table Datos Generales');
            //throw new NotFoundHttpException('Número de boleta ya existe o esta vacia');
            //throw $this->createAccessDeniedException();
        }
        
        $em->flush();
        
        /* Listado de parentescos */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdParentesco p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $parentescos = $query->getResult();
        
        /* Listado de etnias */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdEtnia e "
        . "order by e.id";
        $query = $em->createQuery($dsql);
        $etnias = $query->getResult();
        
        /* Listado de ocupaciones */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdOcupaciones p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $ocupaciones = $query->getResult();
        
        /* Listado de profesiones */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdProfesiones e "
        . "order by e.id";
        $query = $em->createQuery($dsql);
        $profesiones = $query->getResult();
        
        $this->addFlash('success','Datos guardados con exito');
        return $this->render('FocalAppBundle:DatosGenerales:newABr.html.twig', array(
            //'entity' => $entity,
            //'form'   => $form->createView(),
            'idEnc' => 000,
            'codComunidad' => '000000000',
            'etnias' => $etnias, 
            'parentescos' => $parentescos,
            'ocupaciones' => $ocupaciones,
            'profesiones' => $profesiones,
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        /* Listado de comunidades */
        $dsql ="select distinct co.codComunidad, co.nombre "
                . "from FocalAppBundle:AdComunidades co "
                . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
                . "order by co.nombre";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $comunidades = $query->getResult();
        
        /* Listado de encuestadores  */
        $dsql ="select en.id, en.nombre "
                . "from FocalAppBundle:AdEncuestadores en "
                . "where en.codMunicipio = :municipio and en.estado = 1" // Estado activo
                . "order by en.nombre";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $codMuni);
            $encuestadores = $query->getResult();    
            
        //$entity = new DatosGenerales();
        //$form   = $this->createCreateForm($entity, $codMuni);

        return $this->render('FocalAppBundle:DatosGenerales:newAB.html.twig', array(
        //    'entity' => $entity,
        //    'form'   => $form->createView(),
            'comunidades' => $comunidades,
            'encuestadores' => $encuestadores,
        ));
    }
    
    /**
     * Displays a form to create a new DatosGenerales entity.
     *
     */
    public function newABrAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        /* Listado de parentescos */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdParentesco p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $parentescos = $query->getResult();
        
        /* Listado de etnias */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdEtnia e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $etnias = $query->getResult();
        
        /* Listado de ocupaciones */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdOcupaciones p "
        . "order by p.descripcion";
        $query = $em->createQuery($dsql);
        $ocupaciones = $query->getResult();
        
        /* Listado de profesiones */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdProfesiones e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $profesiones = $query->getResult();
        
        return $this->render('FocalAppBundle:DatosGenerales:newABr.html.twig', array(
            'idEnc' => 1620147695,
            'codComunidad' => '162030001000',
            'etnias' => $etnias, 
            'parentescos' => $parentescos,
            'ocupaciones' => $ocupaciones,
            'profesiones' => $profesiones,
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
     * Displays a form to editAB an existing DatosGenerales entity.
     *
     */
    public function editAction($id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->find($id);
        $entviv = $em->getRepository('FocalAppBundle:DatosVivienda')->findBy(array('idEnc' => $id));
        $entsep = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->findBy(array('idEnc' => $id));
        $entenf = $em->getRepository('FocalAppBundle:DatossEnfermedades')->findBy(array('idEnc' => $id), array('idEnfermedad' => 'ASC'));
        $entspu = $em->getRepository('FocalAppBundle:DatosServiciospub')->findBy(array('idEnc' => $id), array('idServicio' => 'ASC'));
        $entsal = $em->getRepository('FocalAppBundle:DatosSegAlimentaria')->findBy(array('idEnc' => $id));
        
        $codMuni = $entity->getCodMunicipio();  
        $encsel = $entity->getIdEncuestador();
        $comsel = $entity->getCodColonia();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosGenerales entity.');
        }
        
        /* Listado de comunidades */
        $dsql ="select distinct co.codComunidad, co.nombre "
        . "from FocalAppBundle:AdComunidades co "
        . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
        . "order by co.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $comunidades = $query->getResult();

        /* Listado de encuestadores */
        $dsql ="select en.id, en.nombre "
        . "from FocalAppBundle:AdEncuestadores en "
        . "where en.codMunicipio = :municipio and en.estado = 1" // Estado activo
        . "order by en.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $encuestadores = $query->getResult();     
            

        return $this->render('FocalAppBundle:DatosGenerales:editAB.html.twig', array(
            'entity'      => $entity,
            'entviv'      => $entviv[0],
            'entsep'      => $entsep[0],
            'entenf'      => $entenf,
            'entspu'      => $entspu,
            'entsal'      => $entsal[0],
            'idenc' => $entity->getIdEnc(),
            'comunidades' => $comunidades,
            'encuestadores' => $encuestadores,
            'encsel' => $encsel,
            'comsel' => $comsel,
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('FocalAppBundle:DatosGenerales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosGenerales entity.');
        }
        $codMuni = $entity->getCodMunicipio();  
        $codDept = $entity->getCodDepartamento();
        $periodo = $entity->getPeriodo();
        
        /* Listado de comunidades */
        $dsql ="select distinct co.codComunidad, co.nombre "
        . "from FocalAppBundle:AdComunidades co "
        . "where co.codMunicipio = :municipio and co.tipoComunidad <> 99"
        . "order by co.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $comunidades = $query->getResult();

        /* Listado de encuestadores */
        $dsql ="select en.id, en.nombre "
        . "from FocalAppBundle:AdEncuestadores en "
        . "where en.codMunicipio = :municipio and en.estado = 1" // Estado activo
        . "order by en.nombre";
        $query = $em->createQuery($dsql);
        $query ->setParameter('municipio', $codMuni);
        $encuestadores = $query->getResult();  

               
        /* Revisar numero de boleta si ya existe. Solamente si se modifico el numero */      
        $num_bola = $entity->getNumBoleta();
        $num_bol = $request->get('qst_07');
        if ($num_bol != $num_bola) {
            try {
                $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('numBoleta' => $num_bol, 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

                if ($ent) { 
                    $this->addFlash('warning','Número de boleta ya existe');
                    throw new NotFoundHttpException('Número de boleta ya existe o esta vacia');
                } 
            } catch (\Exception $e){
                //error_log($e->getMessage());
                $this->addFlash('error',$e->getMessage());
                throw new NotFoundHttpException('Problema al recuperar información: ' . $e->getMessage());
                //throw $this->createAccessDeniedException();
            }
        }
        
        try {
            $entviv = $em->getRepository('FocalAppBundle:DatosVivienda')->findBy(array('idEnc' => $id));
            $entsep = $em->getRepository('FocalAppBundle:DatosSeguridadParticipacion')->findBy(array('idEnc' => $id));
            $entenf = $em->getRepository('FocalAppBundle:DatossEnfermedades')->findBy(array('idEnc' => $id), array('idEnfermedad' => 'ASC'));
            $entspu = $em->getRepository('FocalAppBundle:DatosServiciospub')->findBy(array('idEnc' => $id), array('idServicio' => 'ASC'));
            $entsal = $em->getRepository('FocalAppBundle:DatosSegAlimentaria')->findBy(array('idEnc' => $id));
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            throw new NotFoundHttpException('No se puede recuperar registro: '. $e->getMessage() );
            //throw $this->createAccessDeniedException();
        }

/* =================== DATOS GENERALES ===========================*/
        
        /* Verifica y valida los valores de la tabla datos_generales */
        
        // s******* Nombre del entrevistado
        $qst_01 = $request->get('qst_01');
        if (empty($qst_01)) {
            throw new NotFoundHttpException('Nombre de entrevistado no puede estar vacio');
        }
        $entity->setNombreEntrevistado($qst_01);
        
        // ******* Verificar id_encuestador
        $qst_01_01 = $request->get('qst_01_01');
        if (empty($qst_01_01)) {
            throw new NotFoundHttpException('Nombre de encuestador no puede estar vacio');
        }
        $entity->setIdEncuestador($qst_01_01);
        
        $telCelular = $request->get('qst_02');
        $numUbicacion = $request->get('qst_06');
        
        $entity->setTelefonoCel($telCelular);
        $entity->setNumUbicacion($numUbicacion);
        $entity->setNumBoleta($num_bol);
        
        /* Guarda los valores por defecto */
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        $entity->setUsuarioUltimaModificacion($usuario);
        $entity->setFechaUltimaModificacion($fecha);        
       

/*================ DATOS VIVIENDA ================*/
                
        $mat_viv = $request->get('qst_12');
        $mat_techo = $request->get('qst_13');
        $mat_piso = $request->get('qst_14');
        $pro_viv = $request->get('qst_15');
        $con_viv = $request->get('qst_16');
        $tenencia = $request->get('qst_20_01');
        $ten_sexo = $request->get('qst_20_02');
        $tiene_coc = $request->get('qst_21');
        $ubi_cocina = $request->get('qst_22');
        $con_ele = $request->get('qst_23_1');
        $con_gas = $request->get('qst_23_2');
        $con_ker = $request->get('qst_23_3');
        $con_len = $request->get('qst_23_4');
        $con_eco = $request->get('qst_23_5');
        $con_nin = $request->get('qst_23_6');
        $con_otro = $request->get('qst_23_7');
        $con_nsnr = $request->get('qst_23_8');
        
        $agu_not = $request->get('qst_24_1');
        $agu_bot = $request->get('qst_24_2');
        $agu_fil = $request->get('qst_24_3');
        $agu_her = $request->get('qst_24_4');
        $agu_clo = $request->get('qst_24_5');
        $agu_otro = $request->get('qst_24_6');
        $agu_nsnr = $request->get('qst_24_7');
        
        $piezas_viv = $request->get('qst_25');
        $banos_viv = $request->get('qst_26');
        $dor_viv = $request->get('qst_27');
        $per_xdor = $request->get('qst_28');
        $fam_xviv = $request->get('qst_29');
        $per_xviv = $request->get('qst_30');
        
        $miembro_mig = $request->get('qst_31_01');
        $mig_hom = $request->get('qst_31_02');
        $mig_muj = $request->get('qst_31_03');
              
        $mig_ca = $request->get('qst_32_01');
        $mig_na = $request->get('qst_32_02');
        $mig_sa = $request->get('qst_32_03');
        $mig_eu = $request->get('qst_32_04');
        $mig_in = $request->get('qst_32_05');
        $mig_ot = $request->get('qst_32_06');
        
        $entviv[0]->setCantCentroa($mig_ca);
        $entviv[0]->setCantNortea($mig_na);
        $entviv[0]->setCantSura($mig_sa);
        $entviv[0]->setCantEuropa($mig_eu);
        $entviv[0]->setCantDentropais($mig_in);
        $entviv[0]->setCantOtros($mig_ot);
        
        $entviv[0]->setCantHombres($mig_hom);
        $entviv[0]->setCantMujeres($mig_muj);
        $entviv[0]->setMiembroEmigrado($miembro_mig);
        
        
        $entity->setCantPersonasvivienda($per_xviv); /* Campo de datos generales */
        
        $entviv[0]->setFamiliasCasa($fam_xviv);
        $entviv[0]->setPersonasxDormitorio($per_xdor);
        $entviv[0]->setDormitoriosVivienda($dor_viv);
        $entviv[0]->setBanosVivienda($banos_viv);
        $entviv[0]->setPiezasVivienda($piezas_viv);
        
        $entviv[0]->setConsumoaguaNotratada($agu_not);
        $entviv[0]->setConsumoaguaBotellon($agu_bot);
        $entviv[0]->setConsumoaguaFiltrada($agu_fil);
        $entviv[0]->setConsumoaguaHervida($agu_her);
        $entviv[0]->setConsumoaguaClorada($agu_clo);
        
        $entviv[0]->setConcinaconElec($con_ele);
        $entviv[0]->setConcinaconChimbo($con_gas);
        $entviv[0]->setConcinaconKerosen($con_ker);
        $entviv[0]->setConcinaconLena($con_len);
        $entviv[0]->setConcinaconEco($con_eco);
        
        $entviv[0]->setUbicacionCocina($ubi_cocina);
        $entviv[0]->setTieneCocina($tiene_coc);
        $entviv[0]->setTipoTenencia($tenencia);
        $entviv[0]->setSexoTenencia($ten_sexo);
        $entviv[0]->setMaterialVivienda($mat_viv);
        $entviv[0]->setMaterialTecho($mat_techo);
        $entviv[0]->setMaterialPiso($mat_piso);
        switch ($pro_viv) {
            case 1: 
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                break;
            case 2:
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                break;
            case 3: 
                $entviv[0]->setPvSinrepello(1);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                break;
            case 4: 
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(1);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                break;
            case 5: 
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(1);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                break;
            case 6:
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(1);
                $entviv[0]->setPvNinguno(0);
                break;
            case 7:
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvNinguno(0);
                break;
            case 8: 
                $entviv[0]->setPvSinrepello(0);
                $entviv[0]->setPvPisotierra(0);
                $entviv[0]->setPvCielofalso(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvTechomalo(0);
                $entviv[0]->setPvNinguno(1);
                break;
                
        }
        $entviv[0]->setCondicionVivienda($con_viv);
        
        /* Guarda los valores por defecto */
        $entviv[0]->setUsuarioUltimaModificacion($usuario);
        $entviv[0]->setFechaUltimaModificacion($fecha);    
        
/* ================ SEGURIDAD Y PARTICIPACION ==================*/
        
        $cas_vio = $request->get('qst_34_01');
        $can_cvi = $request->get('qst_34_02');
        $vic_cvi = $request->get('qst_35_01');
        $can_vcv = $request->get('qst_35_02');
        $con_seg = $request->get('qst_36');
        $rin_rob = $request->get('qst_37_01');
        $rin_mar = $request->get('qst_37_02');
        $rin_dro = $request->get('qst_37_03');
        $rin_can = $request->get('qst_37_04');
        $rin_rin = $request->get('qst_37_05');
        $rin_vio = $request->get('qst_37_06');
        $rin_otr = $request->get('qst_37_07');
        
        $entsep[0]->setOrdenViolaciones($rin_vio);
        $entsep[0]->setOrdenPeleas($rin_rin);
        $entsep[0]->setOrdenCantinas($rin_can);
        $entsep[0]->setOrdenDrogas($rin_dro);
        $entsep[0]->setOrdenMaras($rin_mar);
        $entsep[0]->setOrdenRobo($rin_rob);
        $entsep[0]->setConsideraSeguro($con_seg);
        $entsep[0]->setConsideraSeguro($con_seg);
        $entsep[0]->setCantVictimaViolencia($can_vcv);
        $entsep[0]->setVictimaViolencia($vic_cvi);
        $entsep[0]->setCantCasosViolencia($can_cvi);
        $entsep[0]->setCasosViolencia($cas_vio);
        
        /* Guarda los valores por defecto */
        $entsep[0]->setUsuarioUltimaModificacion($usuario);
        $entsep[0]->setFechaUltimaModificacion($fecha);
        
/* ================ SERVICIOS PUBLICOS ==================*/

        for($i = 1; $i <= 17; $i++) {
            
            $x = $i -1;
            $srv_rec = (int)$request->get('qst_38_'.$i.'_01');
            $srv_est = (int)$request->get('qst_38_'.$i.'_02');
            $srv_dia = (int)$request->get('qst_38_'.$i.'_03');
            
            $entspu[$x]->setReciben($srv_rec);
            $entspu[$x]->setEstado($srv_est);
            $entspu[$x]->setCantDias($srv_dia);
            
            /* Guarda los valores por defecto */
            $entspu[$x]->setUsuarioUltimaModificacion($usuario);
            $entspu[$x]->setFechaUltimaModificacion($fecha);
 
        }
        
/* ================ ENFERMEDADES ==================*/
        
        for($i = 1; $i <= 20; $i++) {
            
            $enf_hom = (int)$request->get('qst_39_'.$i.'_01');
            $enf_muj = (int)$request->get('qst_39_'.$i.'_02');
            $enf_pub = (int)$request->get('qst_39_'.$i.'_03');
            $enf_pri = (int)$request->get('qst_39_'.$i.'_04');
            $enf_pro = (int)$request->get('qst_39_'.$i.'_05');
            
            $x = $i - 1;
            $entenf[$x]->setCantHombres($enf_hom);
            $entenf[$x]->setCantMujeres($enf_muj);
            $entenf[$x]->setCantPublica($enf_pub);
            $entenf[$x]->setCantPrivada($enf_pri);
            
            /* Guarda los valores por defecto */
            $entenf[$x]->setUsuarioUltimaModificacion($usuario);
            $entenf[$x]->setFechaUltimaModificacion($fecha);
            
        } 
        
/* ================ SEGURIDAD ALIMENTARIA ================== */
        
        $hur_fam = (int)$request->get('qst_40');
        $tra_tie = (int)$request->get('qst_41_01');
        $tti_hom = (int)$request->get('qst_41_02');
        $tti_muj = (int)$request->get('qst_41_03');
        $tte_tie = (int)$request->get('qst_42');
        $pro_ali = (int)$request->get('qst_43');
        $pro_suf = (int)$request->get('qst_44');
        
        $rie_got = (int)$request->get('qst_45_01');
        $rie_asp = (int)$request->get('qst_45_02');
        $rie_nin = (int)$request->get('qst_45_03');
        $rie_otr = (int)$request->get('qst_45_04');
        
        $pro_exd = (int)$request->get('qst_47');
        
        $ado_ave = (int)$request->get('qst_48_01');
        $ado_bov = (int)$request->get('qst_48_02');
        $ado_cap = (int)$request->get('qst_48_03');
        $ado_equ = (int)$request->get('qst_48_04');
        $ado_por = (int)$request->get('qst_48_05');
        $ado_pis = (int)$request->get('qst_48_06');
        $ado_api = (int)$request->get('qst_48_07');
        $ado_dom = (int)$request->get('qst_48_08');
        
        $entsal[0]->setTieneHuerto($hur_fam);
        $entsal[0]->setTrabajoTierra($tra_tie);
        $entsal[0]->setCantHombres($tti_hom);
        $entsal[0]->setCantMujeres($tti_muj);
        $entsal[0]->setTipoTenencia($tte_tie);
        $entsal[0]->setProduceAlimento($pro_ali);
        $entsal[0]->setProduceSuficiente($pro_suf);
        $entsal[0]->setAreaGoteo($rie_got);
        $entsal[0]->setAreaAspersion($rie_asp);
        $entsal[0]->setAreaNinguno($rie_nin);
        $entsal[0]->setAreaOtro($rie_otr);
        $entsal[0]->setExcedente($pro_exd);
        $entsal[0]->setCantAves($ado_ave);
        $entsal[0]->setCantBovino($ado_bov);
        $entsal[0]->setCantCaprino($ado_cap);
        $entsal[0]->setCantEquino($ado_equ);
        $entsal[0]->setCantPorcino($ado_por);
        $entsal[0]->setCantPiscicultura($ado_pis);
        $entsal[0]->setCantApicultura($ado_api);
        $entsal[0]->setCantDomesticos($ado_dom);      
        
        /* Guarda los valores por defecto */

        $entsal[0]->setUsuarioUltimaModificacion($usuario);
        $entsal[0]->setFechaUltimaModificacion($fecha);        
        
        $em->flush();
        
        return $this->render('FocalAppBundle:DatosGenerales:editAB.html.twig', array(
            'entity'      => $entity,
            'entviv'      => $entviv[0],
            'entsep'      => $entsep[0],
            'entenf'      => $entenf,
            'entspu'      => $entspu,
            'entsal'      => $entsal[0],
            'idenc' => $entity->getIdEnc(),
            'comunidades' => $comunidades,
            'encuestadores' => $encuestadores,
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
