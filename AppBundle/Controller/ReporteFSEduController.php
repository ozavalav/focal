<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Form\DatosGeneralesType;
use Symfony\Component\HttpFoundation\Response;


/**
 * ReporteFSSrvP controller.
 *
 */
class ReporteFSEduController extends Controller
{

    public function crearReporteFSEduAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        
/*** =========== DATOS EDUCACION ============ ****/

/*por niveles de educación si estudia o no y sexo*/          
    $query = "select
  case 
   when e.grado = 1 then 'PE'
   when e.grado = 2 then '1'
   when e.grado = 3 then '2'
   when e.grado = 4 then '3'
   when e.grado = 5 then '4'
   when e.grado = 6 then '5'
   when e.grado = 7 then '6'
   when e.grado = 8 then 'B'
   when e.grado = 9 then 'D'
   when e.grado = 10 then 'U'
   else 'NC'
  end nivele,
  count(*) canttot,
  round(count(*)::decimal * 100 / nullif((select count(*) from datos_educacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot,
  sum(case when f.sexo = 1 then 1 else 0 end) canth,
  round(sum(case when f.sexo = 1 then 1 else 0 end)::decimal*100 /
       nullif((select count(*) 
       from datos_educacion e inner join 
       datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 ) 
       where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) porhom,
  sum(case when f.sexo = 2 then 1 else 0 end) cantm,
  round(sum(case when f.sexo = 2 then 1 else 0 end)::decimal*100 /
       nullif((select count(*) 
       from datos_educacion e inner join 
       datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 ) 
       where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) pormuj,  	
  sum(case when e.estudia = 1 then 1 else 0 end) siestudia,
  round(sum(case when e.estudia = 1 then 1 else 0 end)::decimal * 100 / 
     nullif((select count(*) from datos_educacion where estudia = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsiest, 
  sum(case when e.estudia = 1 and f.sexo = 1 then 1 else 0 end) siestudiah,
  sum(case when e.estudia = 1 and f.sexo = 2 then 1 else 0 end) siestudiam,
  sum(case when e.estudia = 2 or e.estudia is null then 1 else 0 end) noestudia,
  round(sum(case when e.estudia = 2 or e.estudia is null then 1 else 0 end)::decimal*100 / 
     nullif((select count(*) from datos_educacion where estudia = 2 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) pornoest,
  sum(case when (e.estudia = 2 or e.estudia is null) and f.sexo = 1 then 1 else 0 end) noestudiah,
  sum(case when (e.estudia = 2 or e.estudia is null) and f.sexo = 2 then 1 else 0 end) noestudiam   
from datos_generales g join datos_educacion e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %5\$s %6\$s ) 
inner join datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun )   
where
 e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by e.grado
order by e.grado
";
        $query = sprintf($query,$codCom, $rngfecha,$codCome, $rngfechae,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosedu = $stmt->fetchAll();   
        
/*Educacion por rangos de edad y sexo */          
    $query = "select 
 case
   when (f.edad >=1 and f.edad <=4) then '1. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then '2. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then '3. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then '4. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then '5. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then '6. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then '7. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then '8. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then '9. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then '10. de 51 a 64 años'
   when (f.edad >=65 ) then '11. de 65 años y mas'
 end rangoedad, 
 count(*) cantrane,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_educacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot,
 sum(case when f.sexo = 1 then 1 else 0 end) canth,
 round(sum(case when f.sexo = 1 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 and f.edad > 4 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) porhom,
 sum(case when f.sexo = 2 then 1 else 0 end) cantm,
 round(sum(case when f.sexo = 2 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 and f.edad > 4 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) pormuj,
 /*Estudian si/no */
 sum(case when f.sexo = 1 and e.estudia = 1 then 1 else 0 end) canteh,
 round(sum(case when f.sexo = 1 and e.estudia = 1 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 and f.edad > 4 and e.estudia = 1 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) poreh,
 sum(case when f.sexo = 1 and e.estudia = 2 then 1 else 0 end) cantneh,
 round(sum(case when f.sexo = 1 and e.estudia = 2 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 and f.edad > 4 and e.estudia = 2 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) porneh,
 sum(case when f.sexo = 2 and e.estudia = 1 then 1 else 0 end) cantem,
 round(sum(case when f.sexo = 2 and e.estudia = 1 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 and f.edad > 4 and e.estudia = 1 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) porem,
 sum(case when f.sexo = 2 and e.estudia = 2 then 1 else 0 end) cantnem,
 round(sum(case when f.sexo = 2 and e.estudia = 2 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_educacion e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 and f.edad > 4 and e.estudia = 2 %3\$s %4\$s) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) pornem
from 
datos_generales g join datos_educacion e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %7\$s %8\$s)
inner join datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.edad > 4 %3\$s %4\$s)
where f.edad > 4
and e.cod_departamento = :dep and e.cod_municipio = :mun %5\$s %6\$s
group by --f.edad, 
case
   when (f.edad >=1 and f.edad <=4) then '1. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then '2. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then '3. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then '4. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then '5. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then '6. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then '7. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then '8. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then '9. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then '10. de 51 a 64 años'
   when (f.edad >=65 ) then '11. de 65 años y mas'
 end
order by 1";
        $query = sprintf($query,$codCom, $rngfecha, $codComf, $rngfechaf, $codCome, $rngfechae, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosestr = $stmt->fetchAll();
        
/* Estudiantes fuera de edad para estudiar segun el grado */
        
$query = "select
grado,sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end) cantidad,
        sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 1 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 1 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 1 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 1 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 1 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 1 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 1 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 1 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 1 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 1 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 1 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 1 then 1            
	else 0 end) canth,
	sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 2 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 2 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 2 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 2 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 2 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 2 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 2 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 2 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 2 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 2 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 2 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 2 then 1            
	else 0 end) cantm,
        round(sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end)::decimal *100 / nullif((select
sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end)
from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %3\$s %4\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) portot
from datos_generales g join datos_educacion e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %5\$s %6\$s) 
join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %3\$s %4\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by grado
having sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end) > 0
order by 1
";
        $query = sprintf($query,$codCom, $rngfecha, $codCome, $rngfechae, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosefe = $stmt->fetchAll();        

        
/* Estudiantes fuera de edad para estudiar por rango edad */
        
$query = "select
case
   when (f.edad >=1 and f.edad <=4) then '1. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then '2. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then '3. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then '4. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then '5. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then '6. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then '7. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then '8. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then '9. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then '10. de 51 a 64 años'
   when (f.edad >=65 ) then '11. de 65 años y mas'
 end rangoedad,
 sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end) cantidad,
	sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 1 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 1 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 1 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 1 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 1 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 1 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 1 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 1 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 1 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 1 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 1 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 1 then 1            
	else 0 end) canth,
	round(sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 1 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 1 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 1 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 1 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 1 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 1 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 1 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 1 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 1 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 1 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 1 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 1 then 1            
	else 0 end)::decimal* 100 / nullif( ((select
sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 1 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 1 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 1 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 1 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 1 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 1 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 1 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 1 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 1 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 1 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 1 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 1 then 1            
	else 0 end)
from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s) ) ,0),2) porh,
	sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 2 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 2 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 2 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 2 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 2 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 2 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 2 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 2 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 2 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 2 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 2 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 2 then 1            
	else 0 end) cantm,
	round(sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 2 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 2 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 2 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 2 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 2 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 2 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 2 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 2 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 2 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 2 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 2 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 2 then 1            
	else 0 end)::decimal* 100 / nullif( ((select
sum(case 
	when estudia = 1 and grado = 1 and edad > 7 and sexo = 2 then 1
	when estudia = 1 and grado = 2 and edad > 8 and sexo = 2 then 1
	when estudia = 1 and grado = 3 and edad > 9 and sexo = 2 then 1  
	when estudia = 1 and grado = 4 and edad > 10 and sexo = 2 then 1  
	when estudia = 1 and grado = 5 and edad > 11 and sexo = 2 then 1
	when estudia = 1 and grado = 6 and edad > 12 and sexo = 2 then 1
	when estudia = 1 and grado = 7 and edad > 13 and sexo = 2 then 1
	when estudia = 1 and grado = 8 and edad > 14 and sexo = 2 then 1
	when estudia = 1 and grado = 9 and edad > 15 and sexo = 2 then 1 
	when estudia = 1 and grado = 10 and edad > 16 and sexo = 2 then 1
	when estudia = 1 and grado = 11 and edad > 17 and sexo = 2 then 1
	when estudia = 1 and grado = 12 and edad > 18 and sexo = 2 then 1            
	else 0 end)
from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s) ) ,0),2) porm,
	round(sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end)::decimal *100 / nullif((select
sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end)
from datos_educacion e join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun),0),2) portot
from datos_generales g join datos_educacion e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %5\$s %6\$s) 
join datosd_familia f on (f.id = e.id_familia and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by case
   when (f.edad >=1 and f.edad <=4) then '1. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then '2. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then '3. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then '4. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then '5. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then '6. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then '7. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then '8. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then '9. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then '10. de 51 a 64 años'
   when (f.edad >=65 ) then '11. de 65 años y mas'
 end
having sum(case 
	when estudia = 1 and grado = 1 and edad > 7 then 1
	when estudia = 1 and grado = 2 and edad > 8 then 1
	when estudia = 1 and grado = 3 and edad > 9 then 1  
	when estudia = 1 and grado = 4 and edad > 10 then 1  
	when estudia = 1 and grado = 5 and edad > 11 then 1
	when estudia = 1 and grado = 6 and edad > 12 then 1
	when estudia = 1 and grado = 7 and edad > 13 then 1
	when estudia = 1 and grado = 8 and edad > 14 then 1
	when estudia = 1 and grado = 9 and edad > 15 then 1 
	when estudia = 1 and grado = 10 and edad > 16 then 1
	when estudia = 1 and grado = 11 and edad > 17 then 1
	when estudia = 1 and grado = 12 and edad > 18 then 1            
	else 0 end) > 0
order by 1";
        $query = sprintf($query,$codComf, $rngfechaf, $codCome, $rngfechae, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosefrxr = $stmt->fetchAll(); 
        

        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'nomcom' =>$nomCom,
            'dtnomcom' => $dtnomcom,
            'cantcom' => $cantcom,
            'datosedu' => $datosedu,
            'datosestr' => $datosestr,
            'datosefe' => $datosefe,
            'datosefrxr' => $datosefrxr,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSEdu.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosEducacion.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
