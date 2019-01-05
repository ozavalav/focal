<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Form\DatosGeneralesType;
use Symfony\Component\HttpFoundation\Response;


/**
 * ReporteFSSeg controller.
 *
 */
class ReporteFSSegController extends Controller
{

    public function crearReporteFSSegAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        
        
/* ================ Seguridad =============== */
/* Casos de violencia */        
$query = "select 
 case 
  when casos_violencia = 1 then 'SI' 
  when casos_violencia = 2 then 'NO'
 end casosviolencia, 
 count(*) cantidad,
 round(count(casos_violencia)::decimal * 100 / nullif((select count(*) from datos_seguridad_participacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot,   
 sum( case when casos_violencia = 1 then cant_casos_violencia else 0 end) cantcasos
 from datos_generales g join datos_seguridad_participacion s
on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
group by casos_violencia
order by casos_violencia";  
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datoscasv = $stmt->fetchAll();  
        
/* Participación por rangos */        
       
$query = "select 
case
	when cant_hombres_miembros = 1 then '1 Un hombre'
	when cant_hombres_miembros = 2 then '2 Dos hombres'
	when cant_hombres_miembros > 2 then '3 Mas de dos hombres'
end miembros,
sum(case
	when cant_hombres_miembros = 1 then cant_hombres_miembros
	when cant_hombres_miembros = 2 then cant_hombres_miembros
	when cant_hombres_miembros > 2 then cant_hombres_miembros
end) cantidad
from datos_generales g join datos_seguridad_participacion s
on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)
group by
case
	when cant_hombres_miembros = 1 then '1 Un hombre'
	when cant_hombres_miembros = 2 then '2 Dos hombres'
	when cant_hombres_miembros > 2 then '3 Mas de dos hombres'
end
union all
select 
case
	when cant_mujeres_miembros = 1 then '1 Una mujer'
	when cant_mujeres_miembros = 2 then '2 Dos mujeres'
	when cant_mujeres_miembros > 2 then '3 Mas de dos mujeres'
end miembros,
sum(case
	when cant_mujeres_miembros = 1 then cant_mujeres_miembros
	when cant_mujeres_miembros = 2 then cant_mujeres_miembros
	when cant_mujeres_miembros > 2 then cant_mujeres_miembros
end) cantidad
from datos_generales g join datos_seguridad_participacion s
on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s)
group by
case
	when cant_mujeres_miembros = 1 then '1 Una mujer'
	when cant_mujeres_miembros = 2 then '2 Dos mujeres'
	when cant_mujeres_miembros > 2 then '3 Mas de dos mujeres'
end
order by 1";  
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosparxr = $stmt->fetchAll();          
        
/* Victimas de violencia */
$query = "select
case 
  when victima_violencia = 1 then 'SI' 
  when victima_violencia = 2 then 'NO'
 end victimavio,
 count(*) cantidad,
 round(count(victima_violencia)::decimal * 100 / nullif((select count(*) from datos_seguridad_participacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porviv,   
 sum( case when victima_violencia = 1 then cant_victima_violencia else 0 end) cantcasosv
 from datos_generales g join datos_seguridad_participacion s
on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
group by victima_violencia
order by victima_violencia
"; 
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosvicv = $stmt->fetchAll();        
        
/* Estan seguros */
        $query = "select
 case 
  when considera_seguro = 1 then 'Estan seguros' 
  when considera_seguro = 2 then 'NO estan seguros'
 end seguros,
 count(*) cantidad,
 round(count(considera_seguro)::decimal * 100 / nullif((select count(*) from datos_seguridad_participacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porseg   
 from datos_generales g join datos_seguridad_participacion s
on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
group by considera_seguro
order by considera_seguro
"; 
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosests = $stmt->fetchAll();        

/* Estan Participación */
        $query = "select 
 sum(cant_hombres_miembros) canth,
 sum(cant_mujeres_miembros) cantm,
 round(sum(cant_hombres_miembros)::decimal*100 / nullif((select sum(coalesce(cant_hombres_miembros,0) + coalesce(cant_mujeres_miembros,0)) from datos_seguridad_participacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porph,
 round(sum(cant_mujeres_miembros)::decimal*100 / nullif((select sum(coalesce(cant_hombres_miembros,0) + coalesce(cant_mujeres_miembros,0)) from datos_seguridad_participacion where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porpm
from datos_generales g join datos_seguridad_participacion s
on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)";  
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datospar = $stmt->fetchAll();        

/* Orden de inseguridad */
        $query = "select o.num, coalesce(r.crobo,0) crobo, coalesce(m.cmaras,0) cmaras, coalesce(d.cdrogas,0) cdrogas, coalesce(c.ccantinas,0) ccantinas, coalesce(p.cpeleas,0) cpeleas, coalesce(v.cviolaciones,0) cviolaciones
from (select 1 num union select 2 union select 3 union  select 4 union select 5 union select 6 ) o 
left join (select orden_robo, count(*) crobo from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_robo is not null) group by orden_robo) r on (o.num = r.orden_robo)
left join (select orden_maras, count(*) cmaras from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_maras is not null) group by orden_maras) m on (o.num = m.orden_maras)
left join (select orden_drogas, count(*) cdrogas from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_drogas is not null) group by orden_drogas) d on (o.num = d.orden_drogas)
left join (select orden_cantinas, count(*) ccantinas from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_cantinas is not null) group by orden_cantinas) c on (o.num = c.orden_cantinas)
left join (select orden_peleas, count(*) cpeleas from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_peleas is not null) group by orden_peleas) p on (o.num = p.orden_peleas)
left join (select orden_violaciones, count(*) cviolaciones from datos_generales g join datos_seguridad_participacion s on ( g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s and orden_violaciones is not null) group by orden_violaciones) v on (o.num = v.orden_violaciones)
order by num
";    
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosords = $stmt->fetchAll();

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
            'datoscasv' => $datoscasv,
            'datosvicv' => $datosvicv,
            'datosests' => $datosests,
            'datospar' => $datospar[0],
            'datosords' => $datosords,
            'datosparxr' => $datosparxr,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSSeg.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosSeguridad.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
