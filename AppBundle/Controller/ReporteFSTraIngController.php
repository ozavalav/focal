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
class ReporteFSTraIngController extends Controller
{

    public function crearReporteFSTraIngAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
        }
        
        $codCom = "";
        $codCome = "";
        $codComf = "";
        $codCol = "";

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
        
/*** =========== FUERZA TRABAJO IGRESOS ============ ****/
        
$query = "select 
 case
   when (f.edad >=1 and f.edad <=4) then 'a. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then 'b. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then 'c. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then 'd. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then 'e. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then 'f. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then 'g. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then 'h. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then 'i. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then 'j. de 51 a 64 años'
   when (f.edad >=65 ) then 'k. de 65 años y mas'
 end rangoedad, 
 count(*) cantrane,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot,
 sum(case when f.sexo = 1 then 1 else 0 end) canth,
 round(sum(case when f.sexo = 1 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_fuerza_ingresos e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 ) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) porhom,
 sum(case when f.sexo = 2 then 1 else 0 end) cantm,
 round(sum(case when f.sexo = 2 then 1 else 0 end)::decimal*100 /nullif((select count(*) 
 from datos_fuerza_ingresos e inner join 
 datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 ) 
 where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) pormuj
from 
 datos_fuerza_ingresos e inner join datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun )
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by --f.edad, 
case
   when (f.edad >=1 and f.edad <=4) then 'a. de 1 a 4 años'
   when (f.edad >=5 and f.edad <=6) then 'b. de 5 a 6 años'
   when (f.edad >=7 and f.edad <=12) then 'c. de 7 a 12 años'
   when (f.edad >=13 and f.edad <=15) then 'd. de 13 a 15 años'
   when (f.edad >=16 and f.edad <=18) then 'e. de 16 a 18 años'
   when (f.edad >=19 and f.edad <=23) then 'f. de 19 a 23 años'
   when (f.edad >=24 and f.edad <=30) then 'g. de 24 a 30 años'
   when (f.edad >=31 and f.edad <=40) then 'h. de 31 a 40 años'
   when (f.edad >=41 and f.edad <=50) then 'i. de 41 a 50 años'
   when (f.edad >=51 and f.edad <=64) then 'j. de 51 a 64 años'
   when (f.edad >=65 ) then 'k. de 65 años y mas'
 end
order by 1";
        $query = sprintf($query,$codCom, $rngfecha, $codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosrnge = $stmt->fetchAll();
                
/* Fuerza ingreso SIN REMESAS */        
                
$query = "select 
 case
   when (e.ingresos >=0 and e.ingresos <=1000) then 'a. < 1000'
   when (e.ingresos >=1001 and e.ingresos <=2000) then 'b. 1001 - 2000'
   when (e.ingresos >=2001 and e.ingresos <=4000) then 'c. 2001 - 4000'
   when (e.ingresos >=4001 and e.ingresos <=8000) then 'd. 4001 - 8000'
   when (e.ingresos >=8001 and e.ingresos <=12000) then 'e. 8001 - 12000'
   when (e.ingresos >=12001 and e.ingresos <=20000) then 'f. 12001 - 20000'
   when (e.ingresos >=20001 and e.ingresos <=30000) then 'g. 20001 - 30000'
   when (e.ingresos >=30001 and e.ingresos <=50000) then 'h. 30001 - 50000'
   when (e.ingresos >50000 ) then 'i. 50001 - y mas'
 end rangoingreso, 
 count(*) cantper,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where trabaja = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot,
 sum(case when i.sexo = 1 then 1 else 0 end) canth,
 round(sum(case when i.sexo = 1 then 1 else 0 end)::decimal *100 /
       nullif((select count(*) 
       from datos_fuerza_ingresos e inner join 
       datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 1 ) 
       where e.trabaja = 1 and e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) porhom,
 sum(case when i.sexo = 2 then 1 else 0 end) cantm,
 round(sum(case when i.sexo = 2 then 1 else 0 end)::decimal *100 /
       nullif((select count(*) 
       from datos_fuerza_ingresos e inner join 
       datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun and f.sexo = 2 ) 
       where e.trabaja = 1 and e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s),0),2) pormuj
from 
 datos_fuerza_ingresos e inner join datosd_familia i on (e.id_familia = i.id and i.cod_departamento = :dep and i.cod_municipio = :mun )
where e.trabaja = 1 and e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by
case
   when (e.ingresos >=0 and e.ingresos <=1000) then 'a. < 1000'
   when (e.ingresos >=1001 and e.ingresos <=2000) then 'b. 1001 - 2000'
   when (e.ingresos >=2001 and e.ingresos <=4000) then 'c. 2001 - 4000'
   when (e.ingresos >=4001 and e.ingresos <=8000) then 'd. 4001 - 8000'
   when (e.ingresos >=8001 and e.ingresos <=12000) then 'e. 8001 - 12000'
   when (e.ingresos >=12001 and e.ingresos <=20000) then 'f. 12001 - 20000'
   when (e.ingresos >=20001 and e.ingresos <=30000) then 'g. 20001 - 30000'
   when (e.ingresos >=30001 and e.ingresos <=50000) then 'h. 30001 - 50000'
   when (e.ingresos >50000 ) then 'i. 50001 - y mas'
 end
order by 1";
        $query = sprintf($query,$codCom, $rngfecha,$codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datossinr = $stmt->fetchAll();        
        

/* Prestamos en la familia*/
        
        $query = "select 
 case 
  when prestamofam = 1 then 'SI' 
  when prestamofam = 2 then 'NO'
 end prestamo, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_otros 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by prestamofam
order by prestamofam";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datospref = $stmt->fetchAll();        
        
/* Prestamos familiar por sexo */        
$query = "select 
 case 
  when prestamosexo = 1 then 'H' 
  when prestamosexo = 2 then 'M'
 end sexoprestamo, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where prestamofam = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_otros 
where prestamofam = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by prestamosexo
order by prestamosexo
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosprefx = $stmt->fetchAll();   
        
/*Reciben remesas*/        

        $query = "select 
 case 
  when remesas = 1 then 'SI' 
  when remesas = 2 then 'NO'
 end remesas, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_otros 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by remesas
order by remesas";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosrecr = $stmt->fetchAll(); 
        
/*Rango ingresos remesas*/        

        $query = "select 
 case
   when (f.cant_remesas <= 0) then 'd. sin ingreso'
   when (f.cant_remesas > 0 and f.cant_remesas <=1000) then 'a. 0.01 - 1000.00'
   when (f.cant_remesas >1000 and f.cant_remesas <=2000) then 'b. 1001 - 2000'
   when (f.cant_remesas >2000) then 'c. 2000.01 y mas'
 end rangoremesas, 
 count(*) cantremesas,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where remesas = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot
from 
 datos_fuerza_otros f 
where remesas = 1 and f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s
group by
case
   when (f.cant_remesas <= 0) then 'd. sin ingreso'
   when (f.cant_remesas > 0 and f.cant_remesas <=1000) then 'a. 0.01 - 1000.00'
   when (f.cant_remesas >1000 and f.cant_remesas <=2000) then 'b. 1001 - 2000'
   when (f.cant_remesas >2000) then 'c. 2000.01 y mas'
 end
order by 1
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosrngir = $stmt->fetchAll(); 
        
        
/*Rango ingresos familiar*/        

        $query = "select 
 case
   when (coalesce(f.cant_ingresofam,0) >=0 and coalesce(f.cant_ingresofam,0) <=1000) then 'a. < 1000'
   when (f.cant_ingresofam >=1001 and f.cant_ingresofam <=2000) then 'b. 1001 - 2000'
   when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then 'c. 2001 - 4000'
   when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then 'd. 4001 - 8000'
   when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=12000) then 'e. 8001 - 12000'
   when (f.cant_ingresofam >=12001 and f.cant_ingresofam <=20000) then 'f. 12001 - 20000'
   when (f.cant_ingresofam >=20001 and f.cant_ingresofam <=30000) then 'g. 20001 - 30000'
   when (f.cant_ingresofam >=30001 and f.cant_ingresofam <=50000) then 'h. 30001 - 50000'
   when (f.cant_ingresofam >=50001 ) then 'i. 50001 - y mas'
 end rangoingreso, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot
from 
 datos_fuerza_otros f 
where f.cod_departamento = :dep and f.cod_municipio = :mun %1\$s %2\$s
group by
case
   when (coalesce(f.cant_ingresofam,0) >=0 and coalesce(f.cant_ingresofam,0) <=1000) then 'a. < 1000'
   when (f.cant_ingresofam >=1001 and f.cant_ingresofam <=2000) then 'b. 1001 - 2000'
   when (f.cant_ingresofam >=2001 and f.cant_ingresofam <=4000) then 'c. 2001 - 4000'
   when (f.cant_ingresofam >=4001 and f.cant_ingresofam <=8000) then 'd. 4001 - 8000'
   when (f.cant_ingresofam >=8001 and f.cant_ingresofam <=12000) then 'e. 8001 - 12000'
   when (f.cant_ingresofam >=12001 and f.cant_ingresofam <=20000) then 'f. 12001 - 20000'
   when (f.cant_ingresofam >=20001 and f.cant_ingresofam <=30000) then 'g. 20001 - 30000'
   when (f.cant_ingresofam >=30001 and f.cant_ingresofam <=50000) then 'h. 30001 - 50000'
   when (f.cant_ingresofam >=50001 ) then 'i. 50001 - y mas'
 end
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosrngifam = $stmt->fetchAll();
        
/*Ingresos familiar ajusta para...*/        

        $query = "select 
 case 
  when ingreso_ajusta = 1 then 'Tres tiempos'
  when ingreso_ajusta = 2 then 'Dos tiempos'
  when ingreso_ajusta = 3 then 'Un tiempo' 
 end tiempos, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_otros where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_otros 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by ingreso_ajusta
order by ingreso_ajusta";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosingat = $stmt->fetchAll();        
        

/*Fueza de trabajo por sexo*/        
$query = "select 
 case when f.sexo = 1 then 'Masculino' else 'Femenino' end sexo, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot  
 from datos_fuerza_ingresos e inner join datosd_familia f on (e.id_familia = f.id and f.cod_departamento = :dep and f.cod_municipio = :mun )
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by f.sexo
";
        $query = sprintf($query,$codCom, $rngfecha,$codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosfuetx = $stmt->fetchAll();          
        
        
/*Fueza de trabajo trabajan*/          
        $query = "select 
 case 
  when trabaja = 1 then 'SI' 
  when trabaja = 2 then 'NO'
 end trabaja, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_ingresos 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by trabaja
order by trabaja
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosfuett = $stmt->fetchAll();          
        
/*Fueza de trabajo estado civil*/          
        $query = "select 
 case 
  when estado_civil = 1 then 'Soltero' 
  when estado_civil = 2 then 'Casado'
  when estado_civil = 3 then 'Divorsiado' 
  when estado_civil = 4 then 'Union Libre'
  else 'Sin respuesta'  
 end estadocivil, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_fuerza_ingresos 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by estado_civil
order by estado_civil
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosfuete = $stmt->fetchAll();          

/* Listado de Profesiones y Ocupaciones */
$query = "select p.descripcion profesion, count(*) cantidad,
round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot       
from datos_fuerza_ingresos e join ad_profesiones p on (p.id = e.profesion)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by p.descripcion
order by 2 desc
";
        $query = sprintf($query,$codCom, $rngfecha, $codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datoslstp = $stmt->fetchAll();                  
        
$query = "select case when p.descripcion is null then 'N/D' else p.descripcion end ocupacion, count(*) cantidad,
round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot           
from datos_fuerza_ingresos e left join ad_ocupaciones p on (p.id = e.ocupacion)
where e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s
group by p.descripcion
order by 2 desc
";
        $query = sprintf($query,$codCom, $rngfecha, $codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datoslsto = $stmt->fetchAll();
        
/* Personas con ingreso por genero */
        $query = "select 
case 
   when f.sexo = 1 then 'Hombres'
   when f.sexo = 2 then 'Mujeres'
   else 'ND'
end genero,
count(*) cantidad,
round(count(*)::decimal * 100 / nullif((select count(*) from datos_fuerza_ingresos where cod_departamento = :dep and cod_municipio = :mun and trabaja = 1 %1\$s %2\$s),0),2) portot, 
round(avg(ingresos),2) promedio  
from datos_fuerza_ingresos e join datosd_familia f on (f.id = e.id_familia)
where e.cod_departamento = :dep and e.cod_municipio = :mun and e.trabaja = 1 %3\$s %4\$s
group by
case 
   when f.sexo = 1 then 'Hombres'
   when f.sexo = 2 then 'Mujeres'
   else 'ND'
end";
        $query = sprintf($query,$codCom, $rngfecha, $codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosptxs = $stmt->fetchAll();   
        
/* Fuerza de trabajo Emergente */        
$query = "select
sum(case when e.sexo = 1 then 1 else 0 end) hombres,
sum(case when e.sexo = 2 then 1 else 0 end) mujeres,
count(*) total
from datosd_familia e join datos_educacion d on (d.id_familia = e.id)
where e.cod_departamento = :dep and e.cod_municipio = :mun and e.edad >= 13 and e.edad <= 18 and d.estudia = 2 and d.grado = 9 %1\$s %2\$s";
        $query = sprintf($query,$codCome, $rngfechae);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosfte = $stmt->fetchAll();           

/* Ocupacion fuerza laboral */
$query = "select 
sum(case when ocupacion = 1 then 1 else 0 end) oempleado,
sum(case when ocupacion = 2 then 1 else 0 end) ocuentap,
sum(case when ocupacion is null or ocupacion = 3 then 1 else 0 end) oninguna,
round(sum(case when ocupacion = 1 then 1 else 0 end)::decimal * 100 / nullif(count(*),0),2) porempleado,
round(sum(case when ocupacion = 2 then 1 else 0 end)::decimal * 100 / nullif(count(*),0),2) porcuentap,
round(sum(case when ocupacion is null or ocupacion = 3 then 1 else 0 end)::decimal * 100 / nullif(count(*),0),2) poroninguna,
sum(case when sectore = 1 then 1 else 0 end) scomercial,
sum(case when sectore = 2 then 1 else 0 end) sindustrial,
sum(case when sectore = 3 then 1 else 0 end) sservicio,
sum(case when sectore is null then 1 else 0 end) sninguna,
round(sum(case when sectore = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porcomercial,
round(sum(case when sectore = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porindustrial,
round(sum(case when sectore = 3 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porservicio,
round(sum(case when sectore is null then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porsninguna,
sum(case when sectorp = 1 then 1 else 0 end) spprimario,
sum(case when sectorp = 2 then 1 else 0 end) spsecundario,
sum(case when sectorp = 3 then 1 else 0 end) spterciario,
sum(case when sectorp is null then 1 else 0 end) spninguna,
round(sum(case when sectorp = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porprimario,
round(sum(case when sectorp = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porsecundario,
round(sum(case when sectorp = 3 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porterciario,
round(sum(case when sectorp is null then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porspninguna,
sum(case when generaemp = 1 then 1 else 0 end) generaesi,
sum(case when generaemp = 2 then 1 else 0 end) generaeno,
sum(case when generaemp is null then 1 else 0 end) generaen,
round(sum(case when generaemp = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porgeneraesi,
round(sum(case when generaemp = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porgeneraeno,
round(sum(case when generaemp is null then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porgeneraen,
sum(cant_15) e1a5,
sum(cant_610) e6a10,
sum(cant_1115) e11a15,
sum(cant_1620) e16a20,
round(sum(cant_15)::decimal*100 / nullif(count(*),0),2) pore1a5,
round(sum(cant_610)::decimal*100 / nullif(count(*),0),2) pore6a10,
round(sum(cant_1115)::decimal*100 / nullif(count(*),0),2) pore11a15,
round(sum(cant_1620)::decimal*100 / nullif(count(*),0),2) pore16a20,
sum(case when miembroorg = 1 then 1 else 0 end) miembrosi,
sum(case when miembroorg = 2 then 1 else 0 end) miembrono,
sum(case when miembroorg is null then 1 else 0 end) miembrosin,
round(sum(case when miembroorg = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) pormiembrosi,
round(sum(case when miembroorg = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) pormiembrono,
round(sum(case when miembroorg is null then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) pormiembrosin
from datos_fuerza_otros  
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosocu = $stmt->fetchAll();

/* Instituciones de apoyo al sector */        
$query = "select distinct inst1 inst from datos_fuerza_otros where inst1 is not null and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
union
select distinct inst2 inst from datos_fuerza_otros where inst2 is not null and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
union
select distinct inst3 inst from datos_fuerza_otros where inst3 is not null and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosinst = $stmt->fetchAll(); 
        

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
            'datoslstp' => $datoslstp,
            'datoslsto' => $datoslsto,
            'datosptxs' => $datosptxs,
            'datosfte' => $datosfte[0],
            'datosocu' => $datosocu[0],
            'datosinst' => $datosinst,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSTraIng.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosTrabajoIng.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
