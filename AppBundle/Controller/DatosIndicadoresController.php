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
class DatosIndicadoresController extends Controller
{

    public function reporte1Action() {
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AdminBundle:ResOrden')->findAll(array('id' => 'DESC'));
        $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));
        return $this->render('FocalAppBundle:DatosGenerales:producciondiaria.html.twig', array(
                    'departamentos' => $entities,
        ));
    }

    public function crearReporteProduccionDiariaAction($fin, $fin2, $fin3, $fin4, $fin5) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();
        
        $strIni = '';
        $strFin = '';
        $nomCom = '';
        
        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }
        
        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }
        
        $rngfecha = "";
        $rngfechae = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }
        
        $codCom = "";
        $codCome = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');
        
        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }
        
        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);
            
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        
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
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'nomcom' =>$nomCom,
            'datosd' => $datosd,
            'datose' => $datose,
            'datossvac' => $datossvac,
            'datossol' => $datossol[0],
            'datosnac' => $datosnac[0],
            'datosmmp' => $datosmmp[0],
            'datosmp' => $datosmp,
            'datosso' => $datosso[0],
            'datosv' => $datosv[0],
            'datossrv' => $datossrv,
            'datossrvd' => $datossrvd,
            'datosrnge' => $datosrnge,
            'datossinr' => $datossinr,
            'datospref' => $datospref,
            'datosprefx' => $datosprefx,
            'datosrecr' => $datosrecr,
            'datosrngir' => $datosrngir,
            'datosrngifam' => $datosrngifam,
            'datosingat' => $datosingat,
            'datosfuetx' => $datosfuetx,
            'datosfuett' => $datosfuett,
            'datosfuete' => $datosfuete,
            'datosedu' => $datosedu,
            'datosestr' => $datosestr,
            'datostrat' => $datostrat,
            'datosttx' => $datosttx[0],
            'datospertt' => $datospertt,
            'datostipott' => $datostipott,
            'datosttdom' => $datosttdom,
            'datosproa' => $datosproa,
            'datospaauto' => $datospaauto,
            'datosobte' => $datosobte,
            'datoscasv' => $datoscasv,
            'datosvicv' => $datosvicv,
            'datosests' => $datosests,
            'datospar' => $datospar[0],
            'datosords' => $datosords,
            'datospxe' => $datospxe,
            'datospxq' => $datospxq[0],
            'datosembxe' => $datosembxe,
            'datosnacxl' => $datosnacxl,
            'datosmmxt' => $datosmmxt,
            'datosparxr' => $datosparxr,
            'datostie' => $datostie[0],
            'datosefe' => $datosefe,
            'datoslstp' => $datoslstp,
            'datoslsto' => $datoslsto,
            'datosptxs' => $datosptxs,
            'datosfte' => $datosfte[0],
            'datosocu' => $datosocu[0],
            'datospxv' => $datospxv,
            'datosbxv' => $datosbxv,
            'datosdxv' => $datosdxv,
            'datospxd' => $datospxd,
            'datosfxv' => $datosfxv,
            'dtnomcom' => $dtnomcom,
            'cantcom' => $cantcom,
            'datosinst' => $datosinst,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:DatosGenerales:reporteproducciondiaria.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('GestoresVariable.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    
    public function reportecuadro1Action() {
        
        
        $em = $this->getDoctrine()->getManager();
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        
        return $this->render('FocalAppBundle:DatosIndicadores:cuadro1.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    public function reportecuadro234Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:DatosIndicadores:cuadro234.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    public function reportecuadro56789Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:DatosIndicadores:cuadro56789.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    public function reportecuadro101112131415Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:DatosIndicadores:cuadro101112131415.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    
    public function reportecuadro161718192021Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:DatosIndicadores:cuadro161718192021.html.twig', array(
                    'departamentos' => $entities,
        ));
    }

    public function crearReporteCuadro1Action($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin6 = '0', $fin5 = '000000000000') {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();

        $strIni = '';
        $strFin = '';
        $nomCom = '';

        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }

        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }

        $rngfecha = "";
        $rngfechae = "";
        $rngfechaf = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }

        $codCom = "";
        $codCome = "";
        $codComf = "";
        $codCol = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');

        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }

        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);

        if($fin5 != '000000000000') {

            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }  
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total
            from datos_generales f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $totviv = $dtviv[0]['total'];
            
        /* Numero de personas ingresadas de la comunidad */
            $query = "select count(*) totalper from datosd_familia
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtfamilia = $stmt->fetchAll();
        
        /* Hogaras Fuerza Ingresos */
            $query = "select 
            sum(case when ingreso_ajusta = 1  then 1 else 0 end) tiempos3,
            sum(case when cant_ingresofam >=0 and cant_ingresofam <=(1.25 * %3\$d) then 1 else 0 end) rangoi1
            from datos_fuerza_otros
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha, $fin6);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtingreso = $stmt->fetchAll();
            
        /* Hogares seguridad alimentaria */
            $query = "select
            sum(case when produce_alimento = 1 then 1 else 0 end) tproducen, 
            sum(case when produce_alimento = 1 and produce_suficiente = 1 then 1 else 0 end) tpconsumo,
            sum(case when tiene_huerto = 1 then 1 else 0 end) thuerto
            from datos_seg_alimentaria 
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtalimento = $stmt->fetchAll();
            
        /* Enfermedades */
            $query = "select 
            sum(case when id_enfermedad = 1 then cant_manifestaron else 0 end) ira,
            sum(case when id_enfermedad = 2 then cant_manifestaron else 0 end) denguec,
            sum(case when id_enfermedad = 3 then cant_manifestaron else 0 end) paludismo,
            sum(case when id_enfermedad = 4 then cant_manifestaron else 0 end) dengueh,
            sum(case when id_enfermedad = 15 then cant_manifestaron else 0 end) tuber
            from datoss_enfermedades
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtenfermedad = $stmt->fetchAll();
        
        /* Salud */
            $query = "select
            sum(case when planifican = 1 then 1 else 0 end) planifican,    
            sum(case when planifican = 1 and metodo = 1 then 1 else 0 end) ritmo,
            sum(case when planifican = 1 and metodo = 2 then 1 else 0 end) diu,
            sum(case when planifican = 1 and metodo = 3 then 1 else 0 end) pastilla,
            sum(case when planifican = 1 and metodo = 4 then 1 else 0 end) condon,
            sum(case when planifican = 1 and metodo = 6 then 1 else 0 end) operacion,
            sum(case when planifican = 1 and metodo = 7 then 1 else 0 end) implante,
            sum(case when planifican = 1 and metodo = 5 then 1 else 0 end) inyeccion
            from datoss_general
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtsalud = $stmt->fetchAll();
            
        /* Educación */
            $query = "select 
count(*) total_escolar,  
sum(case when estudia = 1 then 1 else 0 end) escolare, 
sum(case when f.sexo = 1 then 1 else 0 end) escolarm,
sum(case when f.sexo = 2 then 1 else 0 end) escolarf,
sum(case when estudia = 1 and f.sexo = 1 then 1 else 0 end) escolarem,
sum(case when estudia = 1 and f.sexo = 2 then 1 else 0 end) escolaref,
sum(case when (e.grado >= 2 and e.grado <= 7) and (f.edad >= 7 and f.edad <= 12) and estudia = 1 then 1 else 0 end) primaria_e, 
sum(case when (e.grado > 7 ) and (f.edad >=13 and f.edad <= 18) then 1 else 0 end) emergente_cp,
sum(case when (e.grado >= 2 and e.grado <= 7) and (f.edad >= 7 and f.edad <= 12) and estudia = 1 and f.sexo = 1 then 1 else 0 end) primaria_m,
sum(case when (e.grado >= 2 and e.grado <= 7) and (f.edad >= 7 and f.edad <= 12) and estudia = 1 and f.sexo = 1 then 2 else 0 end) primaria_f, 
sum(case when (e.grado = 8 ) and (f.edad >= 13 and f.edad <= 15) and estudia = 1 and f.sexo = 1 then 1 else 0 end) secundaria_m,
sum(case when (e.grado = 8 ) and (f.edad >= 13 and f.edad <= 15) and estudia = 1 and f.sexo = 2 then 1 else 0 end) secundaria_f
from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s )
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s ";
            $query = sprintf($query,$codComf, $rngfechaf, $codCome, $rngfechae);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtedu = $stmt->fetchAll();    
            
        /* Vivienda */
            $query = "select 
            sum(case when concinacon_lena = 1 then 1 else 0 end) cocinalena,
            sum(case when condicion_vivienda > 1 then 1 else 0 end) conregmal,
            sum(case when coalesce(banos_vivienda,0) = 0 then 1 else 0 end) nobanos,
            sum(case when dormitorios_vivienda = 1 then 1 else 0 end) undormitorio
            from datos_vivienda
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtvivienda = $stmt->fetchAll();

        /* Servicios Publicos */
            $query = "select 
            sum(case when id_servicio = 4 and reciben = 1 then 1 else 0 end) recagua,
            sum(case when (id_servicio >= 7 and id_servicio <= 9) and reciben = 1 then 1 else 0 end) recexcreta,
            sum(case when id_servicio = 11 and coalesce(reciben,2) = 2 then 1 else 0 end) basurano,
            sum(case when id_servicio = 7 and coalesce(reciben,2) = 2 then 1 else 0 end) serviciono,
            sum(case when id_servicio = 13 and reciben = 1 then 1 else 0 end) energiasi
            from datos_serviciospub
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtsrvpub = $stmt->fetchAll();
            
        /* Fuerza de trabajo */
            $query = "select 
            sum(case when ocupacion = 2 then 1 else 0 end) propia,
            sum(case when ocupacion = 2 and sectorp = 1 then 1 else 0 end) primario,
            sum(case when ocupacion = 2 and sectorp = 2 then 1 else 0 end) secundario,
            sum(case when ocupacion = 2 and sectorp = 3 then 1 else 0 end) terciario,
            sum(case when ocupacion = 2 and generaemp = 1 then 1 else 0 end) generaemp
            from datos_fuerza_otros
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtfuerzat = $stmt->fetchAll();  
         
        /* Seguridad  */
            $query = "select
            sum(case when coalesce(casos_violencia,0) = 1 then 1 else 0 end) casosvio,
            sum(case when coalesce(victima_violencia,0) = 1 then 1 else 0 end) victimavio,
            sum(case when coalesce(considera_seguro,0) = 1 then 1 else 0 end) tsiseguro
            from datos_seguridad_participacion 
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtseg = $stmt->fetchAll();      
            
            
        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'tasa' => $fin6,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'cantcom' =>$cantcom,    
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'totviv' => $totviv,
            'dtingreso' => $dtingreso[0], 
            'dtalimento' => $dtalimento[0],
            'dtfamilia' => $dtfamilia[0],
            'dtenfermedad' => $dtenfermedad[0],
            'dtsalud' => $dtsalud[0],
            'dtedu' => $dtedu[0],
            'dtvivienda' => $dtvivienda[0],
            'dtsrvpub' => $dtsrvpub[0],
            'dtfuerzat' => $dtfuerzat[0],
            'dtseg' => $dtseg[0],
            
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosIndicadores:reportecuadro1.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('Cuadro1.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    public function crearReporteCuadro234Action($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin5 = '000000000000') {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();

        $strIni = '';
        $strFin = '';
        $nomCom = '';

        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }

        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }

        $rngfecha = "";
        $rngfechae = "";
        $rngfechaf = "";
        $rngfechar = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechar = "and r.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }

        $codCom = "";
        $codComr = "";
        $codCome = "";
        $codComf = "";
        $codCol = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');

        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }

        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);

        if($fin5 != '000000000000') {

            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codComr = " and r.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }  
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total
            from datos_generales f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $totviv = $dtviv[0]['total'];
            
        /* Numero de personas ingresadas de la comunidad */
            $query = "select count(*) totalper from datosd_familia
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtfamilia = $stmt->fetchAll();
            
        /* Rango de Edades */
        $query = "select 
	a.id orden, 
	a.descripcion,
	coalesce(sum(r.cant_personas),0) cantp, 
	coalesce(round((sum(r.cant_personas)::decimal*100) / nullif((select sum(cant_personas) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porp,
	coalesce(sum(r.cant_hombres),0) canth,
	coalesce(round((sum(r.cant_hombres)::decimal*100) / nullif((select sum(cant_hombres) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porh, 
	coalesce(sum(r.cant_mujeres),0) cantm, 
	coalesce(round((sum(r.cant_mujeres)::decimal*100) / nullif((select sum(cant_mujeres) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porm 
        from datosd_rangos r right join ad_rangosedad a on (a.id = r.rango and r.cod_departamento = :dep and cod_municipio = :mun %3\$s %4\$s)
        group by a.id
        order by a.id";
        $query = sprintf($query,$codCom, $rngfecha,$codComr, $rngfechar);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosrng = $stmt->fetchAll();
        
        /* Enfermedades */
        $query = "select 
	a.id orden, 
        a.descripcion,
        sum(case when e.cant_manifestaron > 0 then 1 else 0 end) cantmani,
        coalesce(sum(e.cant_manifestaron),0) cantper
        from datoss_enfermedades e right join ad_enfermedades a on (a.id = e.id_enfermedad and e.cod_departamento = :dep and e.cod_municipio = :mun %1\$s %2\$s)
        group by a.id
        order by a.id";
        $query = sprintf($query,$codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtenfe = $stmt->fetchAll();
        
        /* Vacunas */
        $query = "select rango, 
        sum(cant_personas) cantp,
        coalesce(round(sum(cant_personas)::decimal*100/nullif((select sum(cant_personas) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) port, 
        sum(cant_hombres) canth,
        coalesce(round(sum(cant_hombres)::decimal*100/nullif((select sum(cant_hombres) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porh, 
        sum(cant_mujeres) cantm, 
        coalesce(round(sum(cant_mujeres)::decimal*100/nullif((select sum(cant_mujeres) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porm, 
        sum(cant_completa) vcom,
        coalesce(round(sum(cant_completa)::decimal*100/nullif((select sum(cant_completa) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porvc,  
        sum(cant_incompleta) vinc,
        coalesce(round(sum(cant_incompleta)::decimal*100/nullif((select sum(cant_incompleta) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ) ,0),2),0) porvi
        from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
        group by rango";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtvac = $stmt->fetchAll();

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'cantcom' =>$cantcom,    
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'datosrng' => $datosrng,
            'totviv' => $totviv,
            'dtfamilia' => $dtfamilia[0],
            'dtenfe' => $dtenfe,
            'dtvac' => $dtvac
            
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosIndicadores:reportecuadro234.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('Cuadro234.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    
    public function crearReporteCuadro56789Action($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin5 = '000000000000') {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();

        $strIni = '';
        $strFin = '';
        $nomCom = '';

        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }

        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }

        $rngfecha = "";
        $rngfechae = "";
        $rngfechaf = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }

        $codCom = "";
        $codCome = "";
        $codComf = "";
        $codCol = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');

        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }

        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);

        if($fin5 != '000000000000') {

            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }  
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total
            from datos_generales f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $totviv = $dtviv[0]['total'];
        
        /* Servicios Publicos */
        $query = "select
        f.id_servicio, s.descripcion, 
        sum(case when f.reciben = 1 then 1 else 0 end ) treciben,
        sum(case when f.reciben = 1 then cant_dias else 0 end ) tdias
        from datos_serviciospub f join ad_servicios_publicos s on (s.id = f.id_servicio)
        where f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s
        group by id_servicio, s.descripcion
        order by 1";
        $query = sprintf($query,$codComf, $rngfechaf);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtsrvp = $stmt->fetchAll();
        
        /* Vivienda Piezas */
        $query = "select
        case 
          when piezas_vivienda = 1 then '1 una pieza' 
          when piezas_vivienda = 2 then '2 dos piezas'
          when piezas_vivienda = 3 then '3 tres piezas' 
          when piezas_vivienda = 4 then '4 cuatro piezas'
          when piezas_vivienda = 5 then '5 cinco piezas'
          when piezas_vivienda = 6 then '6 seis piezas'
          when piezas_vivienda > 6 then 'Mas de 6 piezas'
        end piezas,
        count(*) cantp 
        from datos_vivienda
        where piezas_vivienda > 0 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
        group by
        case 
            when piezas_vivienda = 1 then '1 una pieza' 
            when piezas_vivienda = 2 then '2 dos piezas'
            when piezas_vivienda = 3 then '3 tres piezas' 
            when piezas_vivienda = 4 then '4 cuatro piezas'
            when piezas_vivienda = 5 then '5 cinco piezas'
            when piezas_vivienda = 6 then '6 seis piezas'
            when piezas_vivienda > 6 then 'Mas de 6 piezas'
        end
        order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtpiviv = $stmt->fetchAll();

        /* Vivienda Dormitorios */
        $query = "select
        case 
          when dormitorios_vivienda = 1 then '1 una pieza' 
          when dormitorios_vivienda = 2 then '2 dos piezas'
          when dormitorios_vivienda = 3 then '3 tres piezas' 
          when dormitorios_vivienda = 4 then '4 cuatro piezas'
          when dormitorios_vivienda = 5 then '5 cinco piezas'
          when dormitorios_vivienda = 6 then '6 seis piezas'
          when dormitorios_vivienda > 6 then 'Mas de seis piezas'
        end dorm,
        count(*) cantdorm 
        from datos_vivienda
        where dormitorios_vivienda > 0 and cod_departamento = :dep and cod_municipio = :mun  %1\$s %2\$s
        group by 
        case 
          when dormitorios_vivienda = 1 then '1 una pieza' 
          when dormitorios_vivienda = 2 then '2 dos piezas'
          when dormitorios_vivienda = 3 then '3 tres piezas' 
          when dormitorios_vivienda = 4 then '4 cuatro piezas'
          when dormitorios_vivienda = 5 then '5 cinco piezas'
          when dormitorios_vivienda = 6 then '6 seis piezas'
          when dormitorios_vivienda > 6 then 'Mas de seis piezas'
        end
        order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtdoviv = $stmt->fetchAll();
        
        /* Familias Vivienda */
        $query = "select
        case 
          when familias_casa = 1 then '1 Viviendas con una familia' 
          when familias_casa = 2 then '2 Viviendas con dos familias'
          when familias_casa = 3 then '3 Viviendas con tres familias' 
          when familias_casa > 3 then '4 Viviendas con cuatro y mas familias'
        end fam,
        count(*) cantfam 
        from datos_vivienda
        where coalesce(familias_casa,0) > 0 and cod_departamento = :dep and cod_municipio = :mun  %1\$s %2\$s
        group by 
        case 
          when familias_casa = 1 then '1 Viviendas con una familia' 
          when familias_casa = 2 then '2 Viviendas con dos familias'
          when familias_casa = 3 then '3 Viviendas con tres familias' 
          when familias_casa > 3 then '4 Viviendas con cuatro y mas familias'
        end
        order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtfamviv = $stmt->fetchAll();
        
        
        /* Baños por Vivienda */
        $query = "select
        case 
          when coalesce(banos_vivienda,0) = 0 then '0 Viviendas sin baños' 
          when banos_vivienda = 1 then '1 Viviendas con un baño'
          when banos_vivienda = 2 then '2 Viviendas con dos baños' 
          when banos_vivienda = 3 then '3 Viviendas con tres baños'
          when banos_vivienda > 3 then '4 Viviendas con cuatro y mas baños'
        end banos,
        count(*) cantbanos 
        from datos_vivienda
        where cod_departamento = :dep and cod_municipio = :mun  %1\$s %2\$s
        group by 
        case 
          when coalesce(banos_vivienda,0) = 0 then '0 Viviendas sin baños' 
          when banos_vivienda = 1 then '1 Viviendas con un baño'
          when banos_vivienda = 2 then '2 Viviendas con dos baños' 
          when banos_vivienda = 3 then '3 Viviendas con tres baños'
          when banos_vivienda > 3 then '4 Viviendas con cuatro y mas baños'
        end
        order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtbanviv = $stmt->fetchAll();
        
        /* Combustible para cocinar Vivienda */
        $query = "select
        sum(case when concinacon_elec = 1 then 1 else 0 end) tcocele,
        sum(case when concinacon_chimbo = 1 then 1 else 0 end) tcocchi,
        sum(case when concinacon_kerosen = 1 then 1 else 0 end) tcocker,
        sum(case when concinacon_lena = 1 then 1 else 0 end) tcoclen,
        sum(case when concinacon_eco = 1 then 1 else 0 end) tcoceco
        from datos_vivienda
        where cod_departamento = :dep and cod_municipio = :mun  %1\$s %2\$s";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $dtcomcoc = $stmt->fetchAll();
        
        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'cantcom' =>$cantcom,    
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'totviv' => $totviv,
            'dtsrvp' => $dtsrvp,
            'dtpiviv' => $dtpiviv,
            'dtdoviv' => $dtdoviv,
            'dtfamviv' => $dtfamviv,
            'dtbanviv' => $dtbanviv,
            'dtcomcoc' => $dtcomcoc[0],
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosIndicadores:reportecuadro56789.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('Cuadros56789.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }

    public function crearReporteCuadro101112131415Action($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin5 = '000000000000') {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();

        $strIni = '';
        $strFin = '';
        $nomCom = '';

        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }

        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }

        $rngfecha = "";
        $rngfechar = "";
        $rngfechae = "";
        $rngfechaf = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechar = "and r.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }

        $codCom = "";
        $codComr = "";
        $codCome = "";
        $codComf = "";
        $codCol = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');

        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }

        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);

        if($fin5 != '000000000000') {

            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComr = " and r.cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }  
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total
            from datos_generales f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $totviv = $dtviv[0]['total'];
            
        /* Distribución escolar  */
            $query = "select count(*) totaleest,
            sum(case when f.edad >=5 and f.edad <= 23 and f.sexo = 1 then 1 else 0 end) tem,
            sum(case when f.edad >=5 and f.edad <= 23 and f.sexo = 2 then 1 else 0 end) tef,
            sum(case when f.sexo = 1 then 1 else 0 end) totm,
            sum(case when f.sexo = 2 then 1 else 0 end) totf, 
            sum(case when e.estudia = 1 and f.sexo = 1 then 1 else 0 end) toem,
            sum(case when e.estudia = 1 and f.sexo = 2 then 1 else 0 end) toef,
            sum(case when e.estudia = 2 and f.sexo = 1 then 1 else 0 end) toenm,
            sum(case when e.estudia = 2 and f.sexo = 2 then 1 else 0 end) toenf,
            --Prekinder
            sum(case when f.edad >=5 and f.edad <= 6 and f.sexo = 1 then 1 else 0 end) pem,
            sum(case when f.edad >=5 and f.edad <= 6 and f.sexo = 2 then 1 else 0 end) pef, 
            sum(case when e.grado = 1 and f.sexo = 1 then 1 else 0 end) petm,
            sum(case when e.grado = 1 and f.sexo = 2 then 1 else 0 end) petf,
            sum(case when e.grado = 1 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) peem,
            sum(case when e.grado = 1 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) peef,
            sum(case when e.grado = 1 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) peenm,
            sum(case when e.grado = 1 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) peenf,
            --Primero
            sum(case when f.edad = 7 and f.sexo = 1 then 1 else 0 end) pgm,
            sum(case when f.edad = 7 and f.sexo = 2 then 1 else 0 end) pgf, 
            sum(case when e.grado = 2 and f.sexo = 1 then 1 else 0 end) pgtm,
            sum(case when e.grado = 2 and f.sexo = 2 then 1 else 0 end) pgtf,
            sum(case when e.grado = 2 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) pgem,
            sum(case when e.grado = 2 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) pgef,
            sum(case when e.grado = 2 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) pgenm,
            sum(case when e.grado = 2 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) pgenf,
            --Segundo
            sum(case when f.edad = 8 and f.sexo = 1 then 1 else 0 end) sgm,
            sum(case when f.edad = 8 and f.sexo = 2 then 1 else 0 end) sgf, 
            sum(case when e.grado = 3 and f.sexo = 1 then 1 else 0 end) sgtm,
            sum(case when e.grado = 3 and f.sexo = 2 then 1 else 0 end) sgtf,
            sum(case when e.grado = 3 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) sgem,
            sum(case when e.grado = 3 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) sgef,
            sum(case when e.grado = 3 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) sgenm,
            sum(case when e.grado = 3 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) sgenf,
            --Tercero
            sum(case when f.edad = 9 and f.sexo = 1 then 1 else 0 end) tgm,
            sum(case when f.edad = 9 and f.sexo = 2 then 1 else 0 end) tgf, 
            sum(case when e.grado = 4 and f.sexo = 1 then 1 else 0 end) tgtm,
            sum(case when e.grado = 4 and f.sexo = 2 then 1 else 0 end) tgtf,
            sum(case when e.grado = 4 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) tgem,
            sum(case when e.grado = 4 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) tgef,
            sum(case when e.grado = 4 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) tgenm,
            sum(case when e.grado = 4 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) tgenf,
            --Cuarto
            sum(case when f.edad = 10 and f.sexo = 1 then 1 else 0 end) cgm,
            sum(case when f.edad = 10 and f.sexo = 2 then 1 else 0 end) cgf, 
            sum(case when e.grado = 5 and f.sexo = 1 then 1 else 0 end) cgtm,
            sum(case when e.grado = 5 and f.sexo = 2 then 1 else 0 end) cgtf,
            sum(case when e.grado = 5 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) cgem,
            sum(case when e.grado = 5 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) cgef,
            sum(case when e.grado = 5 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) cgenm,
            sum(case when e.grado = 5 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) cgenf,
            --Quinto
            sum(case when f.edad = 11 and f.sexo = 1 then 1 else 0 end) qgm,
            sum(case when f.edad = 11 and f.sexo = 2 then 1 else 0 end) qgf, 
            sum(case when e.grado = 6 and f.sexo = 1 then 1 else 0 end) qgtm,
            sum(case when e.grado = 6 and f.sexo = 2 then 1 else 0 end) qgtf,
            sum(case when e.grado = 6 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) qgem,
            sum(case when e.grado = 6 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) qgef,
            sum(case when e.grado = 6 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) qgenm,
            sum(case when e.grado = 6 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) qgenf,
            --Sexo
            sum(case when f.edad = 12 and f.sexo = 1 then 1 else 0 end) xgm,
            sum(case when f.edad = 12 and f.sexo = 2 then 1 else 0 end) xgf, 
            sum(case when e.grado = 7 and f.sexo = 1 then 1 else 0 end) xgtm,
            sum(case when e.grado = 7 and f.sexo = 2 then 1 else 0 end) xgtf,
            sum(case when e.grado = 7 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) xgem,
            sum(case when e.grado = 7 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) xgef,
            sum(case when e.grado = 7 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) xgenm,
            sum(case when e.grado = 7 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) xgenf,
            --plan basico
            sum(case when f.edad >= 13 and f.edad <= 15 and f.sexo = 1 then 1 else 0 end) pbm,
            sum(case when f.edad >= 13 and f.edad <= 15 and f.sexo = 2 then 1 else 0 end) pbf,
            sum(case when e.grado = 8 and f.sexo = 1 then 1 else 0 end) pbtm,
            sum(case when e.grado = 8 and f.sexo = 2 then 1 else 0 end) pbtf,
            sum(case when e.grado = 8 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) pbem,
            sum(case when e.grado = 8 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) pbef,
            sum(case when e.grado = 8 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) pbenm,
            sum(case when e.grado = 8 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) pbenf,
            --diversificado
            sum(case when f.edad >= 16 and f.edad <= 18 and f.sexo = 1 then 1 else 0 end) dim,
            sum(case when f.edad >= 16 and f.edad <= 18 and f.sexo = 2 then 1 else 0 end) dif,
            sum(case when e.grado = 9 and f.sexo = 1 then 1 else 0 end) ditm,
            sum(case when e.grado = 9 and f.sexo = 2 then 1 else 0 end) ditf,
            sum(case when e.grado = 9 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) diem,
            sum(case when e.grado = 9 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) dief,
            sum(case when e.grado = 9 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) dienm,
            sum(case when e.grado = 9 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) dienf,
            --universidad
            sum(case when f.edad >= 19 and f.edad <= 23 and f.sexo = 1 then 1 else 0 end) unm,
            sum(case when f.edad >= 19 and f.edad <= 23 and f.sexo = 2 then 1 else 0 end) unf,
            sum(case when e.grado = 10 and f.sexo = 1 then 1 else 0 end) untm,
            sum(case when e.grado = 10 and f.sexo = 2 then 1 else 0 end) untf,
            sum(case when e.grado = 10 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) unem,
            sum(case when e.grado = 10 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) unef,
            sum(case when e.grado = 10 and e.estudia = 2 and f.sexo = 1 then 1 else 0 end) unenm,
            sum(case when e.grado = 10 and e.estudia = 2 and f.sexo = 2 then 1 else 0 end) unenf
            from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s )
            where e.cod_departamento = :dep and e.cod_municipio = :mun  %3\$s %4\$s";
            $query = sprintf($query,$codComf, $rngfechaf, $codCome, $rngfechae);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtdie = $stmt->fetchAll();   

            
        /* Cobertura Escolar  */
            $query = "select
            sum(case when f.edad >=5 and f.edad <= 23 then 1 else 0 end) totes,
            sum(case when f.edad >=5 and f.edad <= 23 and e.estudia = 1 then 1 else 0 end) totaes,
            sum(case when f.edad >=5 and f.edad <= 6 then 1 else 0 end) d56a,
            sum(case when f.edad >=5 and f.edad <= 6 and e.estudia = 1 then 1 else 0 end) d56e,
            sum(case when f.edad >=7 and f.edad <= 12 then 1 else 0 end) d712a,
            sum(case when f.edad >=7 and f.edad <= 12 and e.estudia = 1 then 1 else 0 end) d712e,
            sum(case when f.edad >=13 and f.edad <= 15 then 1 else 0 end) d1315a,
            sum(case when f.edad >=13 and f.edad <= 15 and e.estudia = 1 then 1 else 0 end) d1315e,
            sum(case when f.edad >=16 and f.edad <= 18 then 1 else 0 end) d1618a,
            sum(case when f.edad >=16 and f.edad <= 18 and e.estudia = 1 then 1 else 0 end) d1618e,
            sum(case when f.edad >=19 and f.edad <= 23 then 1 else 0 end) d1923a,
            sum(case when f.edad >=19 and f.edad <= 23 and e.estudia = 1 then 1 else 0 end) d1923e,
            sum(case when f.edad >=7 and f.edad <= 12 and f.sexo = 1 then 1 else 0 end) teprim,
            sum(case when f.edad >=7 and f.edad <= 12 and f.sexo = 2 then 1 else 0 end) teprif,
            sum(case when f.edad >=7 and f.edad <= 12 then 1 else 0 end) tepri,
            sum(case when e.grado >= 2 and e.grado <= 7 and f.sexo = 1 and e.estudia = 1 then 1 else 0 end) tepriem,
            sum(case when e.grado >= 2 and e.grado <= 7 and f.sexo = 2 and e.estudia = 1 then 1 else 0 end) teprief,
            sum(case when e.grado >= 2 and e.grado <= 7 and e.estudia = 1 then 1 else 0 end) teprie,
            sum(case when f.sexo = 1 and e.estudia = 1 and e.grado >= 2 and e.grado <= 7 and f.edad >=7 and f.edad <= 12 then 1 else 0 end) tgepriem,
            sum(case when f.sexo = 2 and e.estudia = 1 and e.grado >= 2 and e.grado <= 7 and f.edad >=7 and f.edad <= 12 then 1 else 0 end) tgeprief,
            sum(case when e.estudia = 1 and e.grado >= 2 and e.grado <= 7 and f.edad >=7 and f.edad <= 12then 1 else 0 end) tgeprie,
            --Secundaria
            sum(case when f.edad >=13 and f.edad <= 15 and f.sexo = 1 then 1 else 0 end) tesecm,
            sum(case when f.edad >=13 and f.edad <= 15 and f.sexo = 2 then 1 else 0 end) tesecf,
            sum(case when f.edad >=13 and f.edad <= 15 then 1 else 0 end) tesec,
            sum(case when e.grado = 8 and f.sexo = 1 and e.estudia = 1 then 1 else 0 end) tesecem,
            sum(case when e.grado = 8 and f.sexo = 2 and e.estudia = 1 then 1 else 0 end) tesecef,
            sum(case when e.grado = 8 and e.estudia = 1 then 1 else 0 end) tesece,
            sum(case when f.sexo = 1 and e.estudia = 1 and e.grado = 8 and f.edad >=13 and f.edad <= 15 then 1 else 0 end) tgesecem,
            sum(case when f.sexo = 2 and e.estudia = 1 and e.grado = 8 and f.edad >=13 and f.edad <= 15 then 1 else 0 end) tgesecef,
            sum(case when e.estudia = 1 and e.grado = 8 and f.edad >=13 and f.edad <= 15 then 1 else 0 end) tgesece,
            --Equida en el acceso a la educacion
            sum(case when f.edad >=5 and f.edad <= 23 and f.sexo = 1 then 1 else 0 end) te5a23m,
            sum(case when f.edad >=5 and f.edad <= 23 and f.sexo = 2 then 1 else 0 end) te5a23f,
            sum(case when f.sexo = 1 and e.grado >= 1 and e.grado <=10 then 1 else 0 end) tg5a23m,
            sum(case when f.sexo = 2 and e.grado >= 1 and e.grado <=10 then 1 else 0 end) tg5a23f,
            sum(case when e.grado >= 1 and e.grado <=10 then 1 else 0 end) tg5a23,
            sum(case when e.estudia = 1 and f.sexo = 1 then 1 else 0 end) te5a23em,
            sum(case when e.estudia = 1 and f.sexo = 2 then 1 else 0 end) te5a23ef,
            sum(case when e.estudia = 1 then 1 else 0 end) te5a23e
            from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s )
            where e.cod_departamento = :dep and e.cod_municipio = :mun  %3\$s %4\$s";
            $query = sprintf($query,$codComf, $rngfechaf, $codCome, $rngfechae);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtcober = $stmt->fetchAll(); 
            
        /* Poblacion saben leer y esctibir */
            $query = "select 
            a.id orden, 
            a.descripcion,
            coalesce(sum(r.cant_personas),0) cantp, 
            coalesce(round((sum(r.cant_personas)::decimal*100) / (select sum(cant_personas) from datosd_rangos where rango > 4 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),2),0) porp,
            coalesce(sum(r.cant_hombres_leen + r.cant_mujeres_leen),0) pobleen,
            coalesce(round((sum(r.cant_hombres_leen + r.cant_mujeres_leen)::decimal*100) / (select sum(cant_hombres_leen + cant_mujeres_leen) from datosd_rangos where rango > 4 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),2),0) porpobleen, 
            coalesce(sum((r.cant_hombres + r.cant_mujeres) - (r.cant_hombres_leen + r.cant_mujeres_leen)),0) pobnleen, 
            coalesce(round(sum((r.cant_hombres + cant_mujeres) - (r.cant_hombres_leen + r.cant_mujeres_leen))::decimal*100 / (select sum((cant_hombres + cant_mujeres) - (cant_hombres_leen + cant_mujeres_leen)) from datosd_rangos where rango > 4 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ) ,2),0) porpobnleen
            from datosd_rangos r right join ad_rangosedad a on (a.id = r.rango and r.cod_departamento = :dep and cod_municipio = :mun %3\$s %4\$s)
            where r.rango > 4
            group by a.id
            order by a.id;";
            $query = sprintf($query,$codCom, $rngfecha, $codComr, $rngfechar);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtrng = $stmt->fetchAll(); 
            
        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'cantcom' =>$cantcom,    
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'totviv' => $totviv,
            'dtdie' => $dtdie[0],
            'dtcober' => $dtcober[0],
            'dtrng' => $dtrng,
        );
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosIndicadores:reportecuadro101112131415.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DistribucionEscolar.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }    
    
 public function crearReporteCuadro161718192021Action($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin6 = '0', $fin5 = '000000000000') {
        $request = $this->getRequest();
        $session = $request->getSession();
        $usuario = $session->get('Usuario');
        $em = $this->getDoctrine()->getManager();

        $strIni = '';
        $strFin = '';
        $nomCom = '';

        if($fin != '00') {
            $fin = new \DateTime($fin);
            $strIni = $fin->format('Y-m-d');
        }

        if($fin2 != '00') {
            $fin2 = new \DateTime($fin2);
            $strFin = $fin2->format('Y-m-d');
        }

        $rngfecha = "";
        $rngfechar = "";
        $rngfechae = "";
        $rngfechaf = "";
        $rngfechai = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechai = "and i.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechar = "and r.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }

        $codCom = "";
        $codComr = "";
        $codCome = "";
        $codComf = "";
        $codComi = "";
        $codCol = "";

        $codDep = $session->get('_cod_departamento');
        $codMun = $session->get('_cod_municipio');
        $nomDep = $session->get('_nombre_departamento');
        $nomMun = $session->get('_nombre_municipio');

        if($fin3 != '00') {
            $codDep = $fin3;
            $entDepto = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
            $nomDep = $entDepto[0]->getNombre();
        }
        if($fin4 != '0000') {
            $codMun = $fin4;
            $entMuni = $em->getRepository('FocalAppBundle:AdMunicipios')->findBy(array('codMunicipio' => $codMun));
            $nomMun = $entMuni[0]->getNombre();
        }

        $comtmp = explode(",", $fin5);
        $cantcom = count($comtmp);
        $strcom = implode("','", $comtmp);

        if($fin5 != '000000000000') {

            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComi = " and i.cod_comunidad in ('" . $strcom . "')";
            $codComr = " and r.cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codCol = " and f.cod_colonia in ('" . $strcom. "')";

        }  
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total
            from datos_generales f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $totviv = $dtviv[0]['total'];
            
        /* Total de personas ingresadas */
            $query = "select 
            count(*) total,    
            sum(case when sexo = 1 then 1 else 0 end ) thom,
            sum(case when sexo = 2 then 1 else 0 end ) tmuj
            from datosd_familia
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtper = $stmt->fetchAll();
            $totper = $dtper[0]['total'];
            
        /* Agrupacion de profesiones */
            $query = "select
            o.categoria ncate, 
            case 
             when o.categoria = 0 then 'No trabaja / No definido'
             when o.categoria = 1 then 'Profesionales universitarios'
             when o.categoria = 2 then 'Profesionales secundaria / Técnico'
             when o.categoria = 3 then 'Obreros'
             when o.categoria = 4 then 'Agricultores y Ganaderos'
             when o.categoria = 5 then 'Artesanos'
             when o.categoria = 6 then 'Jornaleros'
             when o.categoria = 7 then 'Empresarios - Comerciantes - Vendedores'
             when o.categoria = 8 then 'Oficios domésticos'
             when o.categoria = 9 then 'Estudiantes'
             when o.categoria = 10 then 'Discapacitados - Jubilados - Pensionados - Rentistas'
            end categoria,
            count(*) cantidad,
            round(count(*)::decimal * 100 / (select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),2) portot,
            sum(case when trabaja = 1 and ocupacion not in (21, 28, 63, 64, 83) then 1 else 0 end) peao,
            sum(case when trabaja = 2 and ocupacion not in (21, 28, 63, 64, 83) then 1 else 0 end) pead,
            sum(case when ocupacion in (21, 28, 63, 64, 83) then 1 else 0 end) pei,
            round(sum(case when ocupacion in (21, 28, 63, 64, 83) then 1 else 0 end)::decimal * 100 / (select count(*) from datos_fuerza_ingresos where ocupacion in (21, 28, 63, 64, 83) and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),2) porpei
            from datos_fuerza_ingresos i join ad_ocupaciones o on i.ocupacion = o.id
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
            group by 
            o.categoria,
            case 
             when o.categoria = 0 then 'No trabaja / No definido'
             when o.categoria = 1 then 'Profesionales universitarios'
             when o.categoria = 2 then 'Profesionales secundaria / Técnico'
             when o.categoria = 3 then 'Obreros'
             when o.categoria = 4 then 'Agricultores y Ganaderos'
             when o.categoria = 5 then 'Artesanos'
             when o.categoria = 6 then 'Jornaleros'
             when o.categoria = 7 then 'Empresarios - Comerciantes - Vendedores'
             when o.categoria = 8 then 'Oficios domésticos'
             when o.categoria = 9 then 'Estudiantes'
             when o.categoria = 10 then 'Discapacitados - Jubilados - Pensionados - Rentistas'
            end
            order by 1";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtocu = $stmt->fetchAll();        
            
        /* Hogares por rango de ingresos */    
            $query = "select 
            case
              when (f.cant_ingresofam >=0 and f.cant_ingresofam <=1000) then '1. Ingresos menores a < 1000'
              when (f.cant_ingresofam >=1001 and f.cant_ingresofam <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=12000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.cant_ingresofam >=12001 and f.cant_ingresofam <=20000) then '6. Ingresos entre Lps. 12001 - 20000'
              when (f.cant_ingresofam >=20001 and f.cant_ingresofam <=30000) then '7. Ingresos entre Lps. 20001 - 30000'
              when (f.cant_ingresofam >=30001 and f.cant_ingresofam <=50000) then '8. Ingresos entre Lps. 30001 - 50000'
              when (f.cant_ingresofam >=50001 ) then '9. Ingresos arriba de Lps. 50001 - y mas'
            end rangoingreso, 
            count(*) cantidad,
            round(count(*)::decimal * 100 / (select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),2) portot
           from 
            datos_fuerza_otros f 
           where f.cod_departamento = :dep and f.cod_municipio = :mun %3\$s %4\$s
           group by
           case
              when (f.cant_ingresofam >=0 and f.cant_ingresofam <=1000) then '1. Ingresos menores a < 1000'
              when (f.cant_ingresofam >=1001 and f.cant_ingresofam <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=12000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.cant_ingresofam >=12001 and f.cant_ingresofam <=20000) then '6. Ingresos entre Lps. 12001 - 20000'
              when (f.cant_ingresofam >=20001 and f.cant_ingresofam <=30000) then '7. Ingresos entre Lps. 20001 - 30000'
              when (f.cant_ingresofam >=30001 and f.cant_ingresofam <=50000) then '8. Ingresos entre Lps. 30001 - 50000'
              when (f.cant_ingresofam >=50001 ) then '9. Ingresos arriba de Lps. 50001 - y mas'
            end
           order by 1";
            $query = sprintf($query,$codCom, $rngfecha, $codComf, $rngfechaf );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtrifam = $stmt->fetchAll();
            
        /* Personas por rango de ingresos */    
            $query = "select 
            case
              when (f.ingresos >=0 and f.ingresos <=1000) then '1. Ingresos menores a < 1000'
              when (f.ingresos >=1001 and f.ingresos <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.ingresos >=2001 and f.ingresos <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.ingresos >=4001 and f.ingresos <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.ingresos >=8001 and f.ingresos <=12000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.ingresos >=12001 and f.ingresos <=20000) then '6. Ingresos entre Lps. 12001 - 20000'
              when (f.ingresos >=20001 and f.ingresos <=30000) then '7. Ingresos entre Lps. 20001 - 30000'
              when (f.ingresos >=30001 and f.ingresos <=50000) then '8. Ingresos entre Lps. 30001 - 50000'
              when (f.ingresos >50000 ) then '9. Ingresos arriba de Lps. 50001 y mas'
            end rangoi, 
            count(*) cantper,
            sum(case when i.sexo = 1 then 1 else 0 end) canth,
            sum(case when i.sexo = 2 then 1 else 0 end) cantm
           from 
            datos_fuerza_ingresos f inner join datosd_familia i on (f.id_familia = i.id and i.cod_departamento = :dep and i.cod_municipio = :mun %1\$s %2\$s )
           where f.trabaja = 1 and f.cod_departamento = :dep and f.cod_municipio = :mun %3\$s %4\$s
           group by
           case
              when (f.ingresos >=0 and f.ingresos <=1000) then '1. Ingresos menores a < 1000'
              when (f.ingresos >=1001 and f.ingresos <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.ingresos >=2001 and f.ingresos <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.ingresos >=4001 and f.ingresos <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.ingresos >=8001 and f.ingresos <=12000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.ingresos >=12001 and f.ingresos <=20000) then '6. Ingresos entre Lps. 12001 - 20000'
              when (f.ingresos >=20001 and f.ingresos <=30000) then '7. Ingresos entre Lps. 20001 - 30000'
              when (f.ingresos >=30001 and f.ingresos <=50000) then '8. Ingresos entre Lps. 30001 - 50000'
              when (f.ingresos >50000 ) then '9. Ingresos arriba de Lps. 50001 y mas'
            end
           order by 1";
            $query = sprintf($query,$codComi, $rngfechai, $codComf, $rngfechaf );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtriper = $stmt->fetchAll();    
            
        /* Totales - Personas por rango de ingresos */    
            $query = "select
count(*) totalp,                
sum(case when f.sexo = 1 then 1 else 0 end) canth,
sum(case when f.sexo = 2 then 1 else 0 end) cantm
from datos_fuerza_ingresos e inner join 
datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s) 
where e.trabaja = 1 and e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s";
            $query = sprintf($query,$codComf, $rngfechaf, $codCome, $rngfechae );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtript = $stmt->fetchAll();      

        /* Cuadro #20 ingresos promedios a nivel de hogares */
            $query = "select 
            case
              when (f.cant_ingresofam >=0 and f.cant_ingresofam <=2000) then '1. Ingresos 0 a 2000'
              when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then '2. Ingresos entre Lps. 2001 a 4000'
              when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then '3. Ingresos entre Lps. 4001 - 8000'
              when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=20000) then '4. Ingresos entre Lps. 8001 - 20000'
              when (f.cant_ingresofam >20000 ) then '5. Ingresos de Lps. 20001 en adelante'
            end rangoingreso, 
            count(*) cantidad,
            round(count(*)::decimal * 100 / (select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),2) portot
           from 
            datos_fuerza_otros f 
           where f.cod_departamento = :dep and f.cod_municipio = :mun %3\$s %4\$s
           group by
           case
              when (f.cant_ingresofam >=0 and f.cant_ingresofam <=2000) then '1. Ingresos 0 a 2000'
              when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then '2. Ingresos entre Lps. 2001 a 4000'
              when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then '3. Ingresos entre Lps. 4001 - 8000'
              when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=20000) then '4. Ingresos entre Lps. 8001 - 20000'
              when (f.cant_ingresofam >20000 ) then '5. Ingresos de Lps. 20001 en adelante'
            end
           order by 1";
            $query = sprintf($query,$codCom, $rngfecha, $codComf, $rngfechaf );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtipxh = $stmt->fetchAll();  
            
         /* Cuadro #21 ingresos promedio diario individual */
            $query = "select 
            case
              when (f.ingresos >=0 and f.ingresos <=1000) then '1. Ingresos menores a < 1000'
              when (f.ingresos >=1001 and f.ingresos <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.ingresos >=2001 and f.ingresos <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.ingresos >=4001 and f.ingresos <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.ingresos >=8001 and f.ingresos <=20000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.ingresos > 20001) then '6. Ingresos 20001 y mas'
            end rangoi, 
            count(*) cantper,
            round(count(*)::decimal * 100 / (select count(*) from datos_fuerza_ingresos where trabaja = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),2) portot,
            sum(case when i.sexo = 1 then 1 else 0 end) canth,
            sum(case when i.sexo = 2 then 1 else 0 end) cantm
           from 
            datos_fuerza_ingresos f inner join datosd_familia i on (f.id_familia = i.id and i.cod_departamento = :dep and i.cod_municipio = :mun %3\$s %4\$s )
           where f.trabaja = 1 and f.cod_departamento = :dep and f.cod_municipio = :mun %5\$s %6\$s
           group by
           case
              when (f.ingresos >=0 and f.ingresos <=1000) then '1. Ingresos menores a < 1000'
              when (f.ingresos >=1001 and f.ingresos <=2000) then '2. Ingresos entre Lps. 1001 a 2000'
              when (f.ingresos >=2001 and f.ingresos <=4000) then '3. Ingresos entre Lps. 2001 - 4000'
              when (f.ingresos >=4001 and f.ingresos <=8000) then '4. Ingresos entre Lps. 4001 - 8000'
              when (f.ingresos >=8001 and f.ingresos <=20000) then '5. Ingresos entre Lps. 8001 - 12000'
              when (f.ingresos > 20001) then '6. Ingresos 20001 y mas'
            end
           order by 1
           ";
            $query = sprintf($query,$codCom, $rngfecha, $codComi, $rngfechai, $codComf, $rngfechaf );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtipxp = $stmt->fetchAll();    
            
            $query = "select 
            count(*) pet,
            sum(case when f.trabaja = 1 then 1 else 0 end) peao,
            sum(case when f.trabaja = 2 then 1 else 0 end) pead
            from datos_fuerza_ingresos f
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s 
            and f.ocupacion not in (21, 28, 63, 64, 83) -- se excluyen las ocupaciones: los estudiantes, jubilados, rentarios, amas de casa, incapacitados ";
            $query = sprintf($query,$codCom, $rngfecha );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtpea = $stmt->fetchAll();      

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'tasa' => $fin6,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'cantcom' =>$cantcom,    
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'totviv' => $totviv,
            'totper' >= $totper,
            'dtrifam' => $dtrifam,
            'dtriper' => $dtriper,
            'dtript' => $dtript[0],
            'dtipxh' => $dtipxh,
            'dtipxp' => $dtipxp,
            'dtpea' => $dtpea[0],
            'dtocu' => $dtocu,
        );
        
        $array3 = $array4;
        $html = $this->renderView('FocalAppBundle:DatosIndicadores:reportecuadro161718192021.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosFuerzaTrabajo.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }    
    
}
