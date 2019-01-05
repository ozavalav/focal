<?php
namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\AppConst;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\Response;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reporteAnexosController
 *
 * @author mfusuario
 */
class reporteAnexosController extends Controller {
    
    public function reporteIndexAction() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:Reportes:reporteAnexos.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    
    public function reporteGeneralAction() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        return $this->render('FocalAppBundle:Reportes:reporteGeneral.html.twig', array(
                    'departamentos' => $entities,
        ));
    }
    
    public function crearReporteAnexosAction($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin6 = '0', $fin5 = '000000000000' ) {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }
            
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
            $rngfechag = "";
            
            if($strIni != '' && $strFin != '') {
                $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechag = "and g.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            }
            
            $codDep = $session->get('_cod_departamento');
            $codMun = $session->get('_cod_municipio');
            $nomDep = $session->get('_nombre_departamento');
            $nomMun = $session->get('_nombre_municipio');
            $periodo = $session->get('_periodo');

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
            
            $codCom = "";
            $codCome = "";
            $codComf = "";
            $codColg = "";
            
            if($fin5 != '000000000000') {
                
                $codCom = " and cod_comunidad in ('" . $strcom . "')";
                $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
                $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
                $codColg = " and g.cod_colonia in ('" . $strcom. "')";
                
            }  
            $codComsa = " cod_comunidad in ('" . $strcom . "')";
            $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
            $query = sprintf($query,$codComsa);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->execute();
            $dtnomcom = $stmt->fetchAll();
            
            /* Numero de viviendas encuestadas */
            $query = "select 
            count(*) as total,
            sum(g.cant_personasvivienda) totper
            from datos_generales g
            where g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            $total = $dtviv[0]['total'];
            
            
                        
            /* Poblacion maculina, femenina y poblacion total */
            $query = "select 
            count(*) totper,    
            sum(case when sexo = 1 then 1 else 0 end ) thom,
            sum(case when sexo = 2 then 1 else 0 end ) tmuj
            from datos_generales g join datosd_familia f
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtxsex = $stmt->fetchAll();
            $totper = $dtxsex[0]['totper'];
            
            $query = "select r.descripcion rango, sum(cant_personas) rtotal 
            from datos_generales g join datosd_rangos f
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)
            join ad_rangosedad r on (r.id = f.rango)
            group by r.id
            order by r.id";
            $query = sprintf($query, $codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtredad = $stmt->fetchAll();      
            
            $query = "select 
            sum(case when sexo = 1 then 1 else 0 end) homm18,
            sum(case when sexo = 2 then 1 else 0 end) mujm18
            from 
            datos_generales g join datosd_familia f
            on (g.id_enc = f.id_enc and g.periodo = :per and f.edad > 18 and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dthym18 = $stmt->fetchAll(); 
            
            
            $query = "select
            sum(case when f.trabaja = 1 then 1 else 0 end) ttra, 
            sum(case when f.trabaja = 1 and a.sexo = 1 then 1 else 0 end) ttrah,
            sum(case when f.trabaja = 1 and a.sexo = 2 then 1 else 0 end) ttram, 
            sum(case when f.ocupacion not in (21, 28, 63, 64, 83) then 1 else 0 end) pet,
            sum(case when f.trabaja = 1 and f.ocupacion not in (21, 28, 63, 64, 83) then 1 else 0 end) peao,
            sum(case when f.trabaja = 2 and f.ocupacion not in (21, 28, 63, 64, 83) then 1 else 0 end) pead
            from datos_generales g join datos_fuerza_ingresos f 
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)
            join datosd_familia a on (a.id = f.id_familia)"; // se excluyen las ocupaciones: los estudiantes, jubilados, rentarios, amas de casa, incapacitados 
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtpet = $stmt->fetchAll();
            
            $query = "select 
            count(*) pei
            from datos_generales g join datos_fuerza_ingresos f 
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)
            where f.ocupacion in (21, 28, 63, 64, 83) "; // se excluyen las ocupaciones: los estudiantes, jubilados, rentarios, amas de casa, incapacitados 
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtpei = $stmt->fetchAll();
            
            $dolar2 = $fin6 * 2;
            
            $query = "select 
            sum(case when ocupacion = 1  then 1 else 0 end) empleado,
            sum(case when ocupacion = 2 then 1 else 0 end) propia,
            sum(case when ocupacion = 1 and sectore = 1 then 1 else 0 end) comercial,
            sum(case when ocupacion = 1 and sectore = 2 then 1 else 0 end) industrial,
            sum(case when ocupacion = 1 and sectore = 3 then 1 else 0 end) servicios,
            sum(case when ocupacion = 2 and sectorp = 1 then 1 else 0 end) primario,
            sum(case when ocupacion = 2 and sectorp = 2 then 1 else 0 end) secundario,
            sum(case when ocupacion = 2 and sectorp = 3 then 1 else 0 end) terciario,
            sum(case when ocupacion = 2 then cant_15 + cant_610 + cant_1115 + cant_1620 + cant_21mas else 0 end) empxsec,
            sum(case when ingreso_ajusta = 1  then 1 else 0 end) tiempos3,
            sum(case when ingreso_ajusta = 2  then 1 else 0 end) tiempos2,
            sum(case when ingreso_ajusta = 3  then 1 else 0 end) tiempos1,
            sum(case when remesas = 1  then 1 else 0 end) remesas,
            sum(case when cant_remesas >0 and cant_remesas <= 1000 then 1 else 0 end) rangor1,
            sum(case when cant_remesas >1000 and cant_remesas <= 2000 then 1 else 0 end) rangor2,
            sum(case when cant_remesas >2000 then 1 else 0 end) rangor3,
            sum(case when cant_ingresofam < %3\$f then 1 else 0 end) hingm1,
            sum(case when cant_ingresofam >= %3\$f and cant_ingresofam < %4\$f then 1 else 0 end) hing1a2,
            sum(case when cant_ingresofam >= %4\$f then 1 else 0 end) hingm2,
            sum(case when cant_ingresofam >=0 and cant_ingresofam <=1000 then 1 else 0 end) rangoi1,
            sum(case when cant_ingresofam >=1001 and cant_ingresofam <=2000 then 1 else 0 end) rangoi2,
            sum(case when cant_ingresofam >=2001 and cant_ingresofam <=4000 then 1 else 0 end) rangoi3,
            sum(case when cant_ingresofam >=4001 and cant_ingresofam <=8000 then 1 else 0 end) rangoi4,
            sum(case when cant_ingresofam >=8001 and cant_ingresofam <=12000 then 1 else 0 end) rangoi5,
            sum(case when cant_ingresofam >=12001 and cant_ingresofam <=20000 then 1 else 0 end) rangoi6,
            sum(case when cant_ingresofam >=20001 and cant_ingresofam <=30000 then 1 else 0 end) rangoi7,
            sum(case when cant_ingresofam >=30001 and cant_ingresofam <=50000 then 1 else 0 end) rangoi8,
            sum(case when cant_ingresofam >=50001 then 1 else 0 end) rangoi9
            from datos_generales g join datos_fuerza_otros f 
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag, $fin6, $fin6*2);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            //$stmt->bindValue('tasa',floatval($fin6) );
            //$stmt->bindValue('tasa2',$dolar2);
            $stmt->execute();
            $dtfuerzal = $stmt->fetchAll();
            
            
            $query = "select count(*) totaleest, 
            sum(case when estudia = 1 then 1 else 0 end) estudian,
            sum(case when estudia = 2 or estudia is null then 1 else 0 end) noestudian,
            sum(case when f.sexo = 1 then 1 else 0 end) sexom, 
            sum(case when f.sexo = 2 then 1 else 0 end) sexof,
            sum(case when e.grado = 1 then 1 else 0 end) preesc,
            sum(case when e.grado = 1 and e.estudia = 1 then 1 else 0 end) preesce,
            sum(case when e.grado = 1 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) preescem,
            sum(case when e.grado = 1 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) preescef,
            sum(case when e.grado >= 2 and e.grado <= 7 then 1 else 0 end) primaria,
            sum(case when e.grado >= 2 and e.grado <= 7 and e.estudia = 1 then 1 else 0 end) primariae,
            sum(case when e.grado >= 2 and e.grado <= 7 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) primariaem,
            sum(case when e.grado >= 2 and e.grado <= 7 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) primariaef,
            sum(case when e.grado = 8 then 1 else 0 end) planb,
            sum(case when e.grado = 8 and e.estudia = 1 then 1 else 0 end) planbe,
            sum(case when e.grado = 8 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) planbem,
            sum(case when e.grado = 8 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) planbef,
            sum(case when e.grado = 9 then 1 else 0 end) diver,
            sum(case when e.grado = 9 and e.estudia = 1 then 1 else 0 end) divere,
            sum(case when e.grado = 9 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) diverem,
            sum(case when e.grado = 9 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) diveref,
            sum(case when e.grado = 10 then 1 else 0 end) univ,
            sum(case when e.grado = 10 and e.estudia = 1 then 1 else 0 end) unive,
            sum(case when e.grado = 10 and e.estudia = 1 and f.sexo = 1 then 1 else 0 end) univem,
            sum(case when e.grado = 10 and e.estudia = 1 and f.sexo = 2 then 1 else 0 end) univef,
            sum(case 
            when e.estudia = 1 and e.grado = 1 and f.edad > 7 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 2 and f.edad > 8 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 3 and f.edad > 9 and f.sexo = 1 then 1  
            when e.estudia = 1 and e.grado = 4 and f.edad > 10 and f.sexo = 1 then 1  
            when e.estudia = 1 and e.grado = 5 and f.edad > 11 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 6 and f.edad > 12 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 7 and f.edad > 13 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 8 and f.edad > 14 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 9 and f.edad > 15 and f.sexo = 1 then 1 
            when e.estudia = 1 and e.grado = 10 and f.edad > 16 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 11 and f.edad > 17 and f.sexo = 1 then 1
            when e.estudia = 1 and e.grado = 12 and f.edad > 18 and f.sexo = 1 then 1            
            else 0 end) neseh,
            sum(case 
            when e.estudia = 1 and e.grado = 1 and f.edad > 7 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 2 and f.edad > 8 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 3 and f.edad > 9 and f.sexo = 2 then 1  
            when e.estudia = 1 and e.grado = 4 and f.edad > 10 and f.sexo = 2 then 1  
            when e.estudia = 1 and e.grado = 5 and f.edad > 11 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 6 and f.edad > 12 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 7 and f.edad > 13 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 8 and f.edad > 14 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 9 and f.edad > 15 and f.sexo = 2 then 1 
            when e.estudia = 1 and e.grado = 10 and f.edad > 16 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 11 and f.edad > 17 and f.sexo = 2 then 1
            when e.estudia = 1 and e.grado = 12 and f.edad > 18 and f.sexo = 2 then 1            
            else 0 end) nesem,
            sum(case when f.edad >=13 and f.edad <= 18 then 1 else 0 end) emergente,
            sum(case when (f.edad >=13 and f.edad <= 18) and e.estudia = 1 and (e.grado >= 8 and e.grado <=9) then 1 else 0 end) emergentees,
            sum(case when f.edad >=13 and f.edad <= 18 and f.sexo = 1 and e.estudia = 1 and (e.grado >= 8 and e.grado <=9) then 1 else 0 end) emergenteesm,
            sum(case when f.edad >=13 and f.edad <= 18 and f.sexo = 2 and e.estudia = 1 and (e.grado >= 8 and e.grado <=9) then 1 else 0 end) emergenteesf
            from datos_generales g join datos_educacion e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
            join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codComf, $rngfechaf, $codColg, $rngfechag );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtedu = $stmt->fetchAll();
            
            $query = "select 
            count(*) tfuerzal,
            sum(case when f.sexo = 1 then 1 else 0 end) tfuerzalm,
            sum(case when f.sexo = 2 then 1 else 0 end) tfuerzalf
            from datos_generales g join datos_fuerza_ingresos i 
            on (g.id_enc = i.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
            join datosd_familia f on (f.id = i.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codComf, $rngfechaf, $codColg, $rngfechag );
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtfuerzal2 = $stmt->fetchAll();
            
            $query = "select sum(cant_solteras) tmasol, sum(cant_solteros) tpasol
            from datos_generales g join datosd_otros f 
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtdemoo = $stmt->fetchAll();
            
            $query = "select count(*) totalm5 
                from datos_generales g join datosd_familia f 
                on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s) 
            where edad <= 5 and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s";
            $query = sprintf($query,$codComf, $rngfechaf,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtcantm5 = $stmt->fetchAll();
            
            $query = "select sum(case when semurionino = 1 then cant_muerte_ninos + cant_muerte_ninas else 0 end) muertesninos
            from datos_generales g join datoss_general f
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtmuertesn = $stmt->fetchAll();
            
            $query = "select 
            sum(coalesce(cant_casa,0) + coalesce(cant_centros,0) + coalesce(cant_materno,0) + coalesce(cant_hospital,0) + coalesce(cant_clinica,0) + coalesce(cant_otros,0)) tpartos,
            sum(cant_casa) tcasa,
            sum(cant_centros) tcentros,
            sum(cant_materno) tmaterno,
            sum(cant_hospital) thospital,
            sum(cant_clinica) tclinica,
            sum(cant_otros) totros,
            sum(case when (muerte_mat = 1 and momento_muertem = 1 ) then cant_muertem else 0 end) mantes,
            sum(case when (muerte_mat = 1 and momento_muertem = 2 ) then cant_muertem else 0 end) mdurante,
            sum(case when (muerte_mat = 1 and momento_muertem = 3 ) then cant_muertem else 0 end) mdespues,
            sum(case when muerte_mat = 1 then cant_muertem else 0 end) tmuertem
            from datos_generales g join datoss_general f 
            on( g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtpartos = $stmt->fetchAll();
            
            $query = "select sum(coalesce(cant_ninos,0) + coalesce(cant_ninas,0)) tnnac
            from datos_generales g join datosd_otrosnac f 
            on( g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtnnac = $stmt->fetchAll();
            
            $query = "select f.id_enfermedad, sum(f.cant_manifestaron) tmanif, a.descripcion enfermedad,
            round(sum(f.cant_manifestaron)::decimal * 100/nullif((select sum(cant_manifestaron) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun ),0),2) pormanif
            from datos_generales g join datoss_enfermedades f on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
            join ad_enfermedades a on (a.id = f.id_enfermedad) 
            where f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s
            group by f.id_enfermedad, a.descripcion
            order by f.id_enfermedad";
            $query = sprintf($query,$codComf, $rngfechaf,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtenfer = $stmt->fetchAll();
            
            $query = "select count(*) total
            from datos_generales g join datos_serviciospub f
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
            where f.cod_departamento = :dep and f.cod_municipio = :mun and f.reciben = 1 %1\$s %2\$s";
            $query = sprintf($query,$codComf, $rngfechaf,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dttotsrvp = $stmt->fetchAll();
            $totalsrvp = $dttotsrvp[0]['total'];
            
            $query = "select
            f.id_servicio servp, s.descripcion nomservp, sum(case when f.reciben = 1 then 1 else 0 end ) cantservp
            from datos_generales g join datos_serviciospub f 
            on(g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
            join ad_servicios_publicos s on (s.id = f.id_servicio)
            where f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s
            group by id_servicio, s.descripcion
            order by 1";
            $query = sprintf($query,$codComf, $rngfechaf, $codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtservp = $stmt->fetchAll();
            
            $query = "select count(*) tviv,
            sum(case when material_vivienda =1 then 1 else 0 end) tmatado,
            sum(case when material_vivienda =2 then 1 else 0 end) tmatblo,
            sum(case when material_vivienda =3 then 1 else 0 end) tmatbah,
            sum(case when material_vivienda =4 then 1 else 0 end) tmatmad,
            sum(case when material_vivienda =5 then 1 else 0 end) tmatdes,
            sum(case when material_vivienda =6 then 1 else 0 end) tmatlad,
            sum(case when material_vivienda =7 then 1 else 0 end) tmatyug,
            sum(case when tipo_tenencia = 1 then 1 else 0 end) ttenpro,
            sum(case when tipo_tenencia = 2 then 1 else 0 end) ttenpag,
            sum(case when tipo_tenencia = 3 then 1 else 0 end) ttenalq,
            sum(case when tipo_tenencia = 4 then 1 else 0 end) ttenpre,
            sum(case when tipo_dominio = 1 then 1 else 0 end) tdomple,
            sum(case when tipo_dominio = 2 then 1 else 0 end) tdomuti,
            sum(case when tipo_dominio = 3 then 1 else 0 end) tdomocu,
            sum(case when tipo_dominio = 4 then 1 else 0 end) tdompos,
            sum(case when pv_sinrepello = 1 then 1 else 0 end) tprorep,
            sum(case when problema_repello = 1 then 1 else 0 end) trepint,
            sum(case when problema_repello = 2 then 1 else 0 end) trepext,
            sum(case when problema_repello = 3 then 1 else 0 end) trepamb,
            sum(case when pv_pisotierra = 1 then 1 else 0 end) tpropis,
            sum(case when pv_cielofalso = 1 then 1 else 0 end) tprocie,
            sum(case when pv_estructural = 1 then 1 else 0 end) tproest,
            sum(case when pv_techomalo = 1 then 1 else 0 end) tprotec,
            sum(case when pv_ninguno = 1 then 1 else 0 end) tpronin,
            sum(case when condicion_vivienda = 1 then 1 else 0 end) tconbue,
            sum(case when condicion_vivienda = 2 then 1 else 0 end) tconreg,
            sum(case when condicion_vivienda = 3 then 1 else 0 end) tconmal,
            sum(case when concinacon_elec = 1 then 1 else 0 end) tcocele,
            sum(case when concinacon_chimbo = 1 then 1 else 0 end) tcocchi,
            sum(case when concinacon_kerosen = 1 then 1 else 0 end) tcocker,
            sum(case when concinacon_lena = 1 then 1 else 0 end) tcoclen,
            sum(case when concinacon_eco = 1 then 1 else 0 end) tcoceco,
            sum(case when coalesce(piezas_vivienda,0) = 0 then 1 else 0 end) tviv0pi,
            sum(case when piezas_vivienda = 1 then 1 else 0 end) tviv1pi,
            sum(case when piezas_vivienda = 2 then 1 else 0 end) tviv2pi,
            sum(case when piezas_vivienda > 2 then 1 else 0 end) tviv3mp,
            sum(case when coalesce(banos_vivienda,0) = 0 then 1 else 0 end) tbansin,
            sum(case when banos_vivienda = 1 then 1 else 0 end) tban1ba,
            sum(case when banos_vivienda > 1 then 1 else 0 end) tban2mb,
            sum(case when coalesce(dormitorios_vivienda,0) = 0 then 1 else 0 end) tdor0do,
            sum(case when dormitorios_vivienda = 1 then 1 else 0 end) tdor1do,
            sum(case when dormitorios_vivienda = 2 then 1 else 0 end) tdor2do,
            sum(case when dormitorios_vivienda > 2 then 1 else 0 end) tdor3md,
            sum(case when coalesce(familias_casa,0) = 0 then 1 else 0 end) tfam0ca,
            sum(case when familias_casa = 1 then 1 else 0 end) tfam1ca,
            sum(case when familias_casa = 2 then 1 else 0 end) tfam2ca,
            sum(case when familias_casa > 2 then 1 else 0 end) tfam3mc,
            sum(case when miembro_emigrado = 1 then 1 else 0 end) tmieemi,
            sum(case when miembro_emigrado = 2 or coalesce(miembro_emigrado,0) = 0 then 1 else 0 end) tmienoemi,
            sum(case when miembro_emigrado = 1 and coalesce(cant_hombres,0) > 0 then 1 else 0 end) tmieemh,
            sum(case when miembro_emigrado = 1 and coalesce(cant_mujeres,0) > 0 then 1 else 0 end) tmieemm
            from datos_generales g join datos_vivienda f
            on(g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtviv = $stmt->fetchAll();
            
            $query = "select 
            sum(case when prestamofam = 1 then 1 else 0 end) tprestamo,
            sum(case when prestamofam = 2 then 1 else 0 end) tnoprestamo,
            sum(case when prestamofam = 1 and prestamosexo = 2 then 1 else 0 end) tpmujer,
            sum(case when prestamofam = 1 and prestamosexo = 1 then 1 else 0 end) tphombre
            from datos_generales g join datos_fuerza_otros f
            on(g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtcre = $stmt->fetchAll();
            
            $query = "select
            count(*) tenc,
            sum(case when produce_alimento = 1 then 1 else 0 end) tproducen, 
            sum(case when coalesce(produce_alimento,2) = 2 then 1 else 0 end) tnoproducen,
            sum(case when produce_alimento = 1 and produce_suficiente = 1 then 1 else 0 end) tpconsumo,
            sum(case when produce_alimento = 1 and coalesce(produce_suficiente,2) = 2 then 1 else 0 end) tpnoconsumo,
            sum(case when tiene_huerto = 1 then 1 else 0 end) thuerto,
            sum(case when coalesce(tiene_huerto,2) = 2 then 1 else 0 end) tnohuerto,
            sum(case when tiene_animales = 1 then 1 else 0 end) tanimales,
            sum(case when coalesce(tiene_animales,2) = 2 then 1 else 0 end) tnoanimales,
            sum(case when tipo_tenencia = 1 then 1 else 0 end) ttpropia,
            sum(case when tipo_tenencia = 2 then 1 else 0 end) ttpagando,
            sum(case when tipo_tenencia = 3 then 1 else 0 end) ttalquilada,
            sum(case when tipo_tenencia = 4 then 1 else 0 end) ttprestada,
            sum(case when tipo_tenencia = 5 then 1 else 0 end) ttenlitigio,
            sum(case when tipo_tenencia = 6 then 1 else 0 end) ttcomunal,
            sum(case when tipo_tenencia = 7 then 1 else 0 end) ttnotiene,
            sum(case when (coalesce(area_goteo,0) > 0) or (coalesce(area_aspersion,0) > 0) or (coalesce(area_riego,0) > 0) then 1 else 0 end) triego
            from datos_generales g join datos_seg_alimentaria f
            on(g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query, $codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtsega = $stmt->fetchAll();
            
            $query = "select
            count(*) tenc,
            sum(case when coalesce(casos_violencia,0) = 1 then 1 else 0 end) tcasosvio,
            sum(case when coalesce(victima_violencia,0) = 1 then coalesce(cant_victima_violencia,0) else 0 end) tcasosvfam,
            sum(case when coalesce(considera_seguro,0) = 1 then 1 else 0 end) tsiseguro,
            sum(coalesce(cant_hombres_miembros,0)) thmiembros,
            sum(coalesce(cant_mujeres_miembros,0)) tmmiembros,
            sum(case when coalesce(cant_hombres_miembros,0) = 1 then 1 else 0 end) thsolouno,
            sum(case when coalesce(cant_mujeres_miembros,0) >= 2 then 1 else 0 end) tmmayor2
            from datos_generales g join datos_seguridad_participacion f
            on (g.id_enc = f.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
            $query = sprintf($query, $codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtsegp = $stmt->fetchAll();
            
            
            $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'tasa' => $fin6,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'nomcom' =>$nomCom,
            'dttotal' => $total,
            'dttotper' => $totper,
            'dtxsex' => $dtxsex[0],
            'dtredad' => $dtredad,
            'dtpet' => $dtpet[0],
            'dtpei' => $dtpei[0], 
            'dtfuerzal' => $dtfuerzal[0], 
            'dtedu' => $dtedu[0],
            'dtfuerzal2' => $dtfuerzal2[0],
            'dtdemoo' => $dtdemoo[0],
            'dtcantm5' => $dtcantm5[0],
            'dtmuertesn' => $dtmuertesn[0],
            'dtpartos' => $dtpartos[0],
            'dtnnac' => $dtnnac[0],
            'dtenfer' => $dtenfer,
            'totalsrvp' => $totalsrvp,
            'dtservp' => $dtservp,
            'dtviv' => $dtviv[0],
            'dtcre' => $dtcre[0],
            'dtsega' => $dtsega[0],
            'dtsegp' => $dtsegp[0],
            'cantcom' => $cantcom,
            'dtnomcom' => $dtnomcom,
            'dthym18' => $dthym18[0],
                
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:crearReporteAnexos.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('ReporteAnexis.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
    
public function crearReporteGeneralAction($fin = '00', $fin2 = '00', $fin3 = '00', $fin4 = '0000', $fin5 = '000000000000') {
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
            $rngfechag = "";
            if($strIni != '' && $strFin != '') {
                $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechar = "and r.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
                $rngfechag = "and g.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            }
            
            $codDep = $session->get('_cod_departamento');
            $codMun = $session->get('_cod_municipio');
            $nomDep = $session->get('_nombre_departamento');
            $nomMun = $session->get('_nombre_municipio');
            $periodo = $session->get('_periodo');

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
            
            $codCom = "";
            $codCome = "";
            $codComf = "";
            $codComr = "";
            $codColg = "";
            if($fin5 != '000000000000') {
                
                $codCom = " and cod_comunidad in ('" . $strcom . "')";
                $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
                $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
                $codComr = " and r.cod_comunidad in ('" . $strcom ."')";
                $codColg = " and g.cod_colonia in ('" . $strcom. "')";
                
            }  
            $codComsa = " cod_comunidad in ('" . $strcom . "')";
            $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
            $query = sprintf($query,$codComsa);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->execute();
            $dtnomcom = $stmt->fetchAll();
                
            /* Numero encuestas por municipio */
            $query = "select g.cod_colonia comunidad, c.nombre, count(*) cantidad
            from datos_generales g left join ad_comunidades c on (c.cod_comunidad = g.cod_colonia and c.cod_municipio = g.cod_municipio)
            where g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s
            group by g.cod_colonia, c.nombre
            order by 2 desc";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtencxmuni = $stmt->fetchAll();
            
            /* Numero de encuestas por usuario */
            $query = "select g.usuario_ultima_modificacion usuario, count(*) cantidad from datos_generales g 
            where g.estado = 2 and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s
            group by g.usuario_ultima_modificacion
            order by 2 desc";
            $query = sprintf($query,$codColg, $rngfechag);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtencxuser = $stmt->fetchAll();
            
            /* Correlativo por numero de boletas */
            /*$query = "select num_boleta 
            from datos_generales where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
            order by 1";
            $query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtxnumb = $stmt->fetchAll();*/
            
            /* Discrepancias cantidades de personas sección Familiares y Rangos de Edad */
            $query = "select f.id_enc num_enc, f.fcant familia, r.rcant rango, g.num_boleta 
            from (
            select id_enc, count(*) fcant
            from datosd_familia
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
            group by id_enc ) f join ( 
            select id_enc, sum(cant_personas) rcant
            from datosd_rangos
            where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
            group by id_enc) r on (f.id_enc = r.id_enc)
            join datos_generales g on (g.id_enc = f.id_enc) 
            where g.periodo = :per and f.fcant <> r.rcant";
            $query = sprintf($query,$codCom, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('dep',$codDep);
            $stmt->bindValue('mun',$codMun);
            $stmt->bindValue('per',$periodo);
            $stmt->execute();
            $dtdisper = $stmt->fetchAll();
            
            /* Total de encuestas por departamento y municipio */
            $query = "select d.cod_departamento cod, d.nombre departamento, m.nombre municipio, 
sum(case when estado = 2 then 1 else 0 end) validadas,
sum(case when estado = 1 then 1 else 0 end) pendientes,
count(*) total
from datos_generales e join ad_departamentos d on (d.cod_departamento = e.cod_departamento)
join ad_municipios m on (m.cod_municipio = e.cod_municipio)
where e.periodo = :per
group by d.cod_departamento,d.nombre, m.nombre
order by 1";
            //$query = sprintf($query,$codCol, $rngfecha);
            $stmt = $em->getConnection()->prepare($query);
            $stmt->bindValue('per',$periodo);
            //$stmt->bindValue('dep',$codDep);
            //$stmt->bindValue('mun',$codMun);
            $stmt->execute();
            $dtencxdym = $stmt->fetchAll();
                        
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
            'dtencxmuni' => $dtencxmuni,
            'dtencxuser' => $dtencxuser,
            'dtencxdym' => $dtencxdym,
            'dtnomcom' => $dtnomcom,
            //'dtxnumb' => $dtxnumb,
            'dtdisper' => $dtdisper,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:crearReporteGeneral.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('P', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('ReporteGeneral.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }    
}
