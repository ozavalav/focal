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
class ReporteFSSegAController extends Controller
{

    public function crearReporteFSSegAAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        
/* ========== SEGURIDAD ALIMENTARIA ============  */      

/* Trabajan la tierra */
        $query = "select 
 case 
  when trabajo_tierra = 1 then 'SI' 
  when trabajo_tierra = 2 then 'NO'
 end trabajatierra, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by trabajo_tierra
order by trabajo_tierra
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datostrat = $stmt->fetchAll();         

/* trabajan tierra por sexo */        
$query = "select 
 sum(cant_hombres) canth,
 sum(cant_mujeres) cantm,
 round(sum(cant_hombres)::decimal * 100 / nullif((select sum(coalesce(cant_hombres,0) + coalesce(cant_mujeres,0)) from datos_seg_alimentaria where trabajo_tierra = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porhom,   
 round(sum(cant_mujeres)::decimal * 100 / nullif((select sum(coalesce(cant_hombres,0) + coalesce(cant_mujeres,0)) from datos_seg_alimentaria where trabajo_tierra = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) pormuj   
 from datos_seg_alimentaria 
where trabajo_tierra=1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosttx = $stmt->fetchAll();
        
/* Agrupado por cantidad de persoanas */
$query = "select coalesce(cant_hombres,0) + coalesce(cant_mujeres,0) personas,
count(*) cantidad,
round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where trabajo_tierra = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
from datos_seg_alimentaria 
where trabajo_tierra=1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
coalesce(cant_hombres,0) + coalesce(cant_mujeres,0)
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datospertt = $stmt->fetchAll(); 
        
/* Tipo tenencia de la tierra */        
$query = "select 
 case 
  when tipo_tenencia = 1 then 'Propia pagada' 
  when tipo_tenencia = 2 then 'Propia pagando' 
  when tipo_tenencia = 3 then 'Alquilada' 
  when tipo_tenencia = 4 then 'Prestada' 
  when tipo_tenencia = 5 then 'En litigio' 
  when tipo_tenencia = 6 then 'Comunal' 
  when tipo_tenencia = 7 then 'No tiene' 
 end tipotenencia, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by tipo_tenencia
order by tipo_tenencia
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datostipott = $stmt->fetchAll();
        
        
/* Tipo de dominio */        
$query = "select 
 case 
  when dominio = 1 then 'Pleno' 
  when dominio = 2 then 'Util' 
 end tipodominio, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where tipo_tenencia = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where tipo_tenencia = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by dominio
order by dominio
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosttdom = $stmt->fetchAll();
        
       
/* Produce alimento la familia */        
$query = "select 
 case 
  when produce_alimento = 1 then 'SI' 
  when produce_alimento = 2 then 'NO'
 end producealimento, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by produce_alimento
order by produce_alimento
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosproa = $stmt->fetchAll();        
        
/* Produce alimento para autocomsuno */        
$query = "select 
 case 
  when produce_suficiente = 1 then 'SI' 
  when produce_suficiente = 2 then 'NO'
 end producesuficiente,
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where produce_alimento = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where  produce_alimento = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by produce_suficiente
order by produce_suficiente
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datospaauto = $stmt->fetchAll();
        
        
/* Produce excedente para venta */        
$query = "select 
 case 
  when excedente = 1 then 'SI' 
  when excedente = 2 then 'NO'
 end produceexcedente, 
 count(*) cantidad,
 round(count(*)::decimal * 100 / nullif((select count(*) from datos_seg_alimentaria where produce_alimento = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) portot   
 from datos_seg_alimentaria 
where  produce_alimento = 1 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by excedente
order by excedente
";   
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosobte = $stmt->fetchAll();        
        
/* Cantidades de siembros de la tierra*/
$query = "select 
	sum(cant_maiz) cmaiz, sum(area_maiz) amaiz,
	sum(cant_frijol) cfrijol, sum(area_frijol) afrijol,
	sum(cant_maicillo) cmaicillo, sum(area_maicillo) amaicillo,
	sum(cant_cafe) ccafe, sum(area_cafe) acafe,
	sum(cant_cana) ccana, sum(area_cana) acana,
	sum(cant_otro) cotro, sum(area_otro) aotro,
	sum(area_goteo) agoteo, sum(area_aspersion) aaspersion, 
	sum(area_ninguno) aninguno, sum(area_riego) ariego,
	sum(case when tiene_huerto = 1 then 1 else 0 end) huertosi,
	round(sum(case when tiene_huerto = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porhsi,
	sum(case when tiene_huerto = 2 then 1 else 0 end) huertono,
	round(sum(case when tiene_huerto = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porhno,
	sum(case when tiene_animales = 1 then 1 else 0 end) animalessi,
	round(sum(case when tiene_animales = 1 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porasi,
	sum(case when tiene_animales = 2 then 1 else 0 end) animalesno,
	round(sum(case when tiene_animales = 2 then 1 else 0 end)::decimal*100 / nullif(count(*),0),2) porano,
	sum(coalesce(cant_aves)) caves,
	sum(case when cant_aves > 0 then 1 else 0 end) ccaves,
	round(sum(case when cant_aves > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcaves,
	sum(coalesce(cant_bovino,0)) cbovino,
	sum(case when cant_bovino > 0 then 1 else 0 end) ccbovino,
	round(sum(case when cant_bovino > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcbovino,
	sum(coalesce(cant_caprino,0)) ccaprino, 
	sum(case when cant_caprino > 0 then 1 else 0 end) cccaprino,
	round(sum(case when cant_caprino > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pccaprino,
        sum(coalesce(cant_equino,0)) cequino, 
	sum(case when cant_equino > 0 then 1 else 0 end) ccequino,
	round(sum(case when cant_equino > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcequino,
	sum(coalesce(cant_porcino,0)) cporcino, 
	sum(case when cant_porcino > 0 then 1 else 0 end) ccporcino,
	round(sum(case when cant_porcino > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcporcino,
	sum(coalesce(cant_piscicultura,0)) cpiscicultura,
	sum(case when cant_piscicultura > 0 then 1 else 0 end) ccpiscicultura, 
	round(sum(case when cant_piscicultura > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcpiscicultura, 
	sum(coalesce(cant_apicultura,0)) capicultura,
	sum(case when cant_apicultura > 0 then 1 else 0 end) ccapicultura,  
	round(sum(case when cant_apicultura > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcapicultura,
	sum(coalesce(cant_domesticos,0)) cdomesticos,
	sum(case when cant_domesticos > 0 then 1 else 0 end) ccdomesticos,
	round(sum(case when cant_domesticos > 0 then 1 else 0 end)::decimal * 100 / nullif(sum(case when tiene_animales = 1 then 1 else 0 end),0),2) pcdomesticos
from datos_seg_alimentaria 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
";   
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datostie = $stmt->fetchAll();          

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
            'datostrat' => $datostrat,
            'datosttx' => $datosttx[0],
            'datospertt' => $datospertt,
            'datostipott' => $datostipott,
            'datosttdom' => $datosttdom,
            'datosproa' => $datosproa,
            'datospaauto' => $datospaauto,
            'datosobte' => $datosobte,
            'datostie' => $datostie[0],
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSSegA.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosSAlimentaria.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
