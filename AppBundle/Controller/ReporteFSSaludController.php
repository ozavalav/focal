<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Form\DatosGeneralesType;
use Symfony\Component\HttpFoundation\Response;


/**
 * ReporteFSSalud controller.
 *
 */
class ReporteFSSaludController extends Controller
{

    public function crearReporteFSSaludAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        $rngfechad = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechae = "and e.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechaf = "and f.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechag = "and g.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechad = "and d.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComd = "";
        $codCome = "";
        $codComf = "";
        $codColg = "";
        $codCold = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCome = " and e.cod_comunidad in ('" . $strcom ."')";
            $codComf = " and f.cod_comunidad in ('" . $strcom ."')";
            $codColg = " and g.cod_colonia in ('" . $strcom. "')";
            $codComd = " and d.cod_comunidad in ('" . $strcom ."')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
/* ==================== DATOS DE SALUD =================*/        
        
        $query = "select 
	a.id orden, 
	a.descripcion,
	coalesce(sum(e.cant_manifestaron),0) cantmani, 
	coalesce(round((sum(e.cant_manifestaron)::decimal*100) / nullif((select sum(cant_manifestaron) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) pormani,
	coalesce(sum(e.cant_hombres),0) canth,
	coalesce(round((sum(e.cant_hombres)::decimal*100) / nullif((select sum(cant_hombres) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porh, 
	coalesce(sum(e.cant_mujeres),0) cantm, 
	coalesce(round((sum(e.cant_mujeres)::decimal*100) / nullif((select sum(cant_mujeres) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porm, 
	coalesce(sum(e.cant_publica),0) cantpub, 
	coalesce(round((sum(e.cant_publica)::decimal*100) / nullif((select sum(cant_publica) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porpub, 
	coalesce(sum(e.cant_privada),0) cantpri,
	coalesce(round((sum(e.cant_privada)::decimal*100) / nullif((select sum(cant_privada) from datoss_enfermedades where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porpri
        from datos_generales g join datoss_enfermedades e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %5\$s %6\$s) right join ad_enfermedades a on (a.id = e.id_enfermedad and e.cod_departamento = :dep and e.cod_municipio = :mun %3\$s %4\$s)
        group by a.id
        order by a.id";
        $query = sprintf($query,$codCom, $rngfecha,$codCome, $rngfechae, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datose = $stmt->fetchAll();
        
        /* Vacunas */
        $query = "select rango, 
sum(cant_personas) cantp,
coalesce(round(sum(cant_personas)::decimal*100/nullif((select sum(cant_personas) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) port, 
sum(cant_hombres) canth,
coalesce(round(sum(cant_hombres)::decimal*100/nullif((select sum(cant_hombres) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porh, 
sum(cant_mujeres) cantm, 
coalesce(round(sum(cant_mujeres)::decimal*100/nullif((select sum(cant_mujeres) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0),2),0) porm, 
sum(cant_completa) vcom,
coalesce(round(sum(cant_completa)::decimal*100/nullif((select sum(cant_completa) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porvc,  
sum(cant_incompleta) vinc,
coalesce(round(sum(cant_incompleta)::decimal*100/nullif((select sum(cant_incompleta) from datoss_edadvacunas where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s ),0) ,2),0) porvi
from datos_generales g join datoss_edadvacunas v on (g.id_enc = v.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
where v.cod_departamento = :dep and v.cod_municipio = :mun
group by rango";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datossvac = $stmt->fetchAll();
        
        
        $query = "select 
            sum(case when d.muerte_mat = 1 then 1 else 0 end) cantmsi, 
            sum(case when d.muerte_mat = 2 then 1 else 0 end) cantmno,
            sum(case when d.planifican = 1 then 1 else 0 end) plansi,
            sum(case when d.planifican = 2 then 1 else 0 end) planno,
            sum(case when d.embarazos = 1 then 1 else 0 end) totemb,
            count(d.muerte_mat) total
           from datos_generales g join datoss_general d on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosmmp = $stmt->fetchAll();
        
        $query = "select 
            a.id orden, 
            a.descripcion,
            coalesce(count(e.metodo),0) cantmetodo, 
            coalesce(round((count(e.metodo)::decimal*100) / nullif((select count(metodo) from datoss_general where planifican = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) pormetodo	
          from datos_generales g join datoss_general e on (g.id_enc = e.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s) 
          right join ad_metodo_plan a on (a.id = e.metodo and e.planifican = 1)
          group by a.id
          order by a.id";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosmp = $stmt->fetchAll();
        
        $query = "select 
            sum(case when d.sexo_operacion = 1 then 1 else 0 end) operh, 
            sum(case when d.sexo_operacion = 2 then 1 else 0 end) operm,
            count(d.sexo_operacion) total
           from datos_generales g join datoss_general d on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
           where d.planifican = 1 and d.metodo = 6 and d.cod_departamento = :dep and d.cod_municipio = :mun %1\$s %2\$s";
        $query = sprintf($query,$codComd, $rngfechad, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosso = $stmt->fetchAll();
        
        /* Cantidad de mujeres embarazadas por rangos de edad  */
        
        $query = "select 
 case 
 when edad < 5 then 'a menores de 5' 
 when edad >= 5 and edad < 10 then 'b de 5 a 9'
 when edad >= 10 and edad < 15 then 'c de 10 a 14'
 when edad >= 15 and edad < 20 then 'd de 15 a 19'
 when edad >= 20 and edad < 25 then 'f de 20 a 24' 
 when edad >= 25 and edad < 30 then 'g de 25 a 29'
 when edad >= 30 and edad < 35 then 'h de 30 a 34'
 when edad >= 35 and edad < 40 then 'i de 35 a 39'
 when edad >= 40 and edad < 45 then 'j de 40 a 44'
 when edad >= 45 and edad < 50 then 'k de 45 a 49'
 when edad >= 50 and edad < 55 then 'l de 50 a 54'
 when edad >= 55 and edad < 60 then 'm de 55 a 59'
 when edad >= 60 and edad < 65 then 'n de 60 a 64'
 when edad >= 65 and edad < 70 then 'o de 65 a 69'    
 end rango, 
 count(*) cantemb,
 round(count(*)::decimal * 100 / nullif((select 
(select count(*) from datoss_general where cod_departamento = :dep and cod_municipio = :mun and embarazos = 1 and edad1 > 12 %1\$s %2\$s ) +
(select count(*) from datoss_general where cod_departamento = :dep and cod_municipio = :mun and embarazos = 1 and edad2 > 12 %1\$s %2\$s ) +
(select count(*) from datoss_general where cod_departamento = :dep and cod_municipio = :mun and embarazos = 1 and edad3 > 12 %1\$s %2\$s )),0),2) port
from (
select edad1 edad 
from datos_generales g join datoss_general d
on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun and d.embarazos = 1 and d.edad1 > 12 %3\$s %4\$s)
union all
select edad2 edad 
from datos_generales g join datoss_general d
on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun and d.embarazos = 1 and d.edad2 > 12 %3\$s %4\$s)
union all
select edad3 edad 
from datos_generales g join datoss_general d
on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun and d.embarazos = 1 and d.edad3 > 12 %3\$s %4\$s)) emb 
group by 
case 
 when edad < 5 then 'a menores de 5' 
 when edad >= 5 and edad < 10 then 'b de 5 a 9'
 when edad >= 10 and edad < 15 then 'c de 10 a 14'
 when edad >= 15 and edad < 20 then 'd de 15 a 19'
 when edad >= 20 and edad < 25 then 'f de 20 a 24' 
 when edad >= 25 and edad < 30 then 'g de 25 a 29'
 when edad >= 30 and edad < 35 then 'h de 30 a 34'
 when edad >= 35 and edad < 40 then 'i de 35 a 39'
 when edad >= 40 and edad < 45 then 'j de 40 a 44'
 when edad >= 45 and edad < 50 then 'k de 45 a 49'
 when edad >= 50 and edad < 55 then 'l de 50 a 54'
 when edad >= 55 and edad < 60 then 'm de 55 a 59'
 when edad >= 60 and edad < 65 then 'n de 60 a 64'
 when edad >= 65 and edad < 70 then 'o de 65 a 69'  
 end
order by 1";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosembxe = $stmt->fetchAll();
        
/* Cantidad de nacimientos por lugar */
        
        $query = "select 
		case
			when lugar_casa then 'Casa'
			when lugar_centros then 'Centro de Salud'
			when lugar_materno then 'Clinica materno infantil'
			when lugar_hospital then 'Hospital'
			when lugar_clinica then 'Clinica Privada'
			when lugar_otros then 'Otros'
		end lugar,
		sum(case
			when lugar_casa then cant_casa
			when lugar_centros then cant_centros
			when lugar_materno then cant_materno
			when lugar_hospital then cant_hospital
			when lugar_clinica then cant_clinica
			when lugar_otros then cant_otros
		end) cantidad,
                sum(case
			when lugar_casa then cant_casa
			when lugar_centros then cant_centros
			when lugar_materno then cant_materno
			when lugar_hospital then cant_hospital
			when lugar_clinica then cant_clinica
			when lugar_otros then cant_otros
		end)::decimal*100 /
		(select sum(coalesce(cant_casa,0) + coalesce(cant_centros,0) + coalesce(cant_materno,0) + coalesce(cant_hospital,0) + coalesce(cant_clinica,0) + coalesce(cant_otros,0)) from datoss_general where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s) port
	from datos_generales g join datoss_general d 
	on (g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun and 
(case
	when d.lugar_casa then 'Casa'
	when d.lugar_centros then 'Centro de Salud'
	when d.lugar_materno then 'Clínica materno infantil'
	when d.lugar_hospital then 'Hospital'
	when d.lugar_clinica then 'Clínica Privada'
	when d.lugar_otros then 'Otros'
end) is not null %3\$s %4\$s ) 
	group by 
		case
			when lugar_casa then 'Casa'
			when lugar_centros then 'Centro de Salud'
			when lugar_materno then 'Clinica materno infantil'
			when lugar_hospital then 'Hospital'
			when lugar_clinica then 'Clinica Privada'
			when lugar_otros then 'Otros'
		end
	order by 1";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosnacxl = $stmt->fetchAll();    
        
/* Cantidad de muertes maternas por el tiempo de ocurrencia */
        
        $query = "select
case
	when momento_muertem = 1 then 'Durante embarazo'
	when momento_muertem = 2 then 'En el parto'
	when momento_muertem = 3 then 'Después del parto'
end tiempo,
sum(case
	when momento_muertem = 1 then cant_muertem
	when momento_muertem = 2 then cant_muertem
	when momento_muertem = 3 then cant_muertem
end) cantidad
from datos_generales g join datoss_general d 
on ( g.id_enc = d.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun and d.muerte_mat = 1 %1\$s %2\$s )
group by 
case
	when momento_muertem = 1 then 'Durante embarazo'
	when momento_muertem = 2 then 'En el parto'
	when momento_muertem = 3 then 'Después del parto'
end
order by 1";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosmmxt = $stmt->fetchAll();   
        
        
/* Cantidad niños y niñas muertos */
        
        $query = "select 
        sum(cant_muerte_ninos) ninos, 
        sum(cant_muerte_ninas) ninas, 
        sum(cant_muerte_ninos+cant_muerte_ninas) total
        from datos_generales g join datoss_general d 
        on ( g.id_enc = d.id_enc and g.periodo = :per and g.cod_municipio = :mun and g.cod_departamento = :dep %1\$s %2\$s
        and d.semurionino = 1)";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosminf = $stmt->fetchAll();         
        


        $array4 = array('fecha' => date('d-m-Y'), 
            'fin' => $fin,
            'fin2' => $fin2,
            'coddep' => $codDep,
            'nomdep' => $nomDep,
            'codmun' => $codMun,
            'nommun' => $nomMun,
            'fin5' => $fin5,
            'nomcom' =>$nomCom,
            'datose' => $datose,
            'datossvac' => $datossvac,
            'datosmmp' => $datosmmp[0],
            'datosmp' => $datosmp,
            'datosso' => $datosso[0],
            'datosembxe' => $datosembxe,
            'datosnacxl' => $datosnacxl,
            'datosmmxt' => $datosmmxt,
            'dtnomcom' => $dtnomcom,
            'cantcom' => $cantcom,
            'datosminf' => $datosminf[0],
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSSalud.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosSalud.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
