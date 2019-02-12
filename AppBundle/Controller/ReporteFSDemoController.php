<?php

namespace Focal\AppBundle\Controller;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Form\DatosGeneralesType;
use Symfony\Component\HttpFoundation\Response;

if (!defined('__ROOT__')) { 
    define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once (__ROOT__ . '/jpgraph/src/jpgraph.php');
require_once (__ROOT__ . '/jpgraph/src/jpgraph_bar.php');
/**
 * ReporteFSSrvP controller.
 *
 */
class ReporteFSDemoController extends Controller
{

    public function crearReporteFSDemoAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        $rngfechag = "";
        $rngfechar = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechag = "and g.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechar = "and r.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComr = "";
        $codColg = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComr = " and r.cod_comunidad in ('" . $strcom ."')";
            $codColg = " and g.cod_colonia in ('" . $strcom. "')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        $query = "select 
	a.id orden, 
	a.descripcion,
	coalesce(sum(r.cant_personas),0) cantp, 
	coalesce(round((sum(r.cant_personas)::decimal*100) / nullif((select sum(cant_personas) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porp,
	coalesce(sum(r.cant_hombres),0) canth,
	coalesce(round((sum(r.cant_hombres)::decimal*100) / nullif((select sum(cant_hombres) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porh, 
	coalesce(sum(r.cant_mujeres),0) cantm, 
	coalesce(round((sum(r.cant_mujeres)::decimal*100) / nullif((select sum(cant_mujeres) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porm, 
	coalesce(sum(r.cant_hombres_leen),0) canthl, 
	coalesce(round((sum(r.cant_hombres_leen)::decimal*100) / nullif((select sum(cant_hombres_leen) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porhl, 
	coalesce(sum(r.cant_hombres -r.cant_hombres_leen),0) canthnl,
        coalesce(round(sum(r.cant_hombres - r.cant_hombres_leen)::decimal*100 / nullif((select sum(cant_hombres - cant_hombres_leen) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porhnl,
        coalesce(sum(r.cant_mujeres_leen),0) cantml,
	coalesce(round((sum(r.cant_mujeres_leen)::decimal*100) / nullif((select sum(cant_mujeres_leen) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) porml,
        coalesce(sum(r.cant_mujeres - r.cant_mujeres_leen),0) cantmnl,
        coalesce(round(sum(r.cant_mujeres - r.cant_mujeres_leen)::decimal*100 / nullif((select sum(cant_mujeres - cant_mujeres_leen) from datosd_rangos where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2),0) pormnl
        from datos_generales g join datosd_rangos r on (g.id_enc = r.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s) right join ad_rangosedad a on (a.id = r.rango and r.cod_departamento = :dep and r.cod_municipio = :mun %5\$s %6\$s)
        group by a.id
        order by a.id";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag, $codComr, $rngfechar);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Cantidad personas por edad */
        $query = "select trunc(edad) edad, sum(case when sexo = 1 then 1 else 0 end) canth, sum(case when sexo = 2 then 1 else 0 end) cantm,
            count(sexo) total
from datos_generales g join datosd_familia r on (g.id_enc = r.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s )
group by trunc(edad)
order by edad";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datospxe = $stmt->fetchAll();
        
        /* Personas por quinquenios de edad */
        $query = "select
sum(case when edad < 5 and sexo = 1 then 1 else 0 end) e0a5h,
sum(case when edad < 5 and sexo = 2 then 1 else 0 end) e0a5m,
sum(case when edad >=  5 and edad < 10 and sexo = 1 then 1 else 0 end) e5a10h,
sum(case when edad >=  5 and edad < 10 and sexo = 2 then 1 else 0 end) e5a10m,
sum(case when edad >= 10 and edad < 15 and sexo = 1 then 1 else 0 end) e10a15h,
sum(case when edad >= 10 and edad < 15 and sexo = 2 then 1 else 0 end) e10a15m,
sum(case when edad >= 15 and edad < 20 and sexo = 1 then 1 else 0 end) e15a20h,
sum(case when edad >= 15 and edad < 20 and sexo = 2 then 1 else 0 end) e15a20m,
sum(case when edad >= 20 and edad < 25 and sexo = 1 then 1 else 0 end) e20a25h,
sum(case when edad >= 20 and edad < 25 and sexo = 2 then 1 else 0 end) e20a25m,
sum(case when edad >= 25 and edad < 30 and sexo = 1 then 1 else 0 end) e25a30h,
sum(case when edad >= 25 and edad < 30 and sexo = 2 then 1 else 0 end) e25a30m,
sum(case when edad >= 30 and edad < 35 and sexo = 1 then 1 else 0 end) e30a35h,
sum(case when edad >= 30 and edad < 35 and sexo = 2 then 1 else 0 end) e30a35m,
sum(case when edad >= 35 and edad < 40 and sexo = 1 then 1 else 0 end) e35a40h,
sum(case when edad >= 35 and edad < 40 and sexo = 2 then 1 else 0 end) e35a40m,
sum(case when edad >= 40 and edad < 45 and sexo = 1 then 1 else 0 end) e40a45h,
sum(case when edad >= 40 and edad < 45 and sexo = 2 then 1 else 0 end) e40a45m,
sum(case when edad >= 45 and edad < 50 and sexo = 1 then 1 else 0 end) e45a50h,
sum(case when edad >= 45 and edad < 50 and sexo = 2 then 1 else 0 end) e45a50m,
sum(case when edad >= 50 and edad < 55 and sexo = 1 then 1 else 0 end) e50a55h,
sum(case when edad >= 50 and edad < 55 and sexo = 2 then 1 else 0 end) e50a55m,
sum(case when edad >= 55 and edad < 60 and sexo = 1 then 1 else 0 end) e55a60h,
sum(case when edad >= 55 and edad < 60 and sexo = 2 then 1 else 0 end) e55a60m,
sum(case when edad >= 60 and edad < 65 and sexo = 1 then 1 else 0 end) e60a65h,
sum(case when edad >= 60 and edad < 65 and sexo = 2 then 1 else 0 end) e60a65m,
sum(case when edad >= 65 and edad < 70 and sexo = 1 then 1 else 0 end) e65a70h,
sum(case when edad >= 65 and edad < 70 and sexo = 2 then 1 else 0 end) e65a70m,
sum(case when edad >= 70 and edad < 75 and sexo = 1 then 1 else 0 end) e70a75h,
sum(case when edad >= 70 and edad < 75 and sexo = 2 then 1 else 0 end) e70a75m,
sum(case when edad >= 75 and edad < 80 and sexo = 1 then 1 else 0 end) e75a80h,
sum(case when edad >= 75 and edad < 80 and sexo = 2 then 1 else 0 end) e75a80m,
sum(case when edad >= 80 and edad < 85 and sexo = 1 then 1 else 0 end) e80a85h,
sum(case when edad >= 80 and edad < 85 and sexo = 2 then 1 else 0 end) e80a85m,
sum(case when edad >= 85 and edad < 90 and sexo = 1 then 1 else 0 end) e85a90h,
sum(case when edad >= 85 and edad < 90 and sexo = 2 then 1 else 0 end) e85a90m,
sum(case when edad >= 90 and edad < 95 and sexo = 1 then 1 else 0 end) e90a95h,
sum(case when edad >= 90 and edad < 95 and sexo = 2 then 1 else 0 end) e90a95m,
sum(case when edad >= 95 and edad < 100 and sexo = 1 then 1 else 0 end) e95a100h,
sum(case when edad >= 95 and edad < 100 and sexo = 2 then 1 else 0 end) e95a100m,
sum(case when edad >= 100 and edad < 105 and sexo = 1 then 1 else 0 end) e100a105h,
sum(case when edad >= 100 and edad < 105 and sexo = 2 then 1 else 0 end) e100a105m,
sum(case when edad >= 105 and edad < 110 and sexo = 1 then 1 else 0 end) e105a110h,
sum(case when edad >= 105 and edad < 110 and sexo = 2 then 1 else 0 end) e105a110m,
sum(case when edad >= 110 and edad < 115 and sexo = 1 then 1 else 0 end) e110a115h,
sum(case when edad >= 110 and edad < 115 and sexo = 2 then 1 else 0 end) e110a115m,
sum(case when edad >= 115 and edad < 120 and sexo = 1 then 1 else 0 end) e115a120h,
sum(case when edad >= 115 and edad < 120 and sexo = 2 then 1 else 0 end) e115a120m,
sum(case when edad >= 120 and sexo = 1 then 1 else 0 end) e120mash,
sum(case when edad >= 120 and sexo = 2 then 1 else 0 end) e120masm                                            
from datos_generales g join datosd_familia r on (g.id_enc = r.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s )";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datospxq = $stmt->fetchAll();
        
$query = "select sum(cant_solteras) cantmsol, sum(cant_solteros) cantpsol 
    from datos_generales g join datosd_otros r on (g.id_enc = r.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s )";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datossol = $stmt->fetchAll();    
        
$query = "select sum(cantidad) totnac, sum(cant_ninos) totninos, sum(cant_ninas) totninas, avg(edad) proedad
                  from datos_generales g join datosd_otrosnac r on (g.id_enc = r.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s )";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosnac = $stmt->fetchAll();   
        
        $graph = new \Graph(600,400);
        $graph->SetScale('intint');
        $graph->SetMargin(50,10,10,110);
        $graph->title->Set('Prueba de grafica');
        $graph->title->SetFont(FF_DV_SANSSERIF, FS_BOLD,10);
        $graph->xaxis->title->Set('Desripcion');
        $graph->yaxis->title->Set('# personas');
        
        $datay = [];
        $dtdes = [];
        foreach($datosd as $dta ) {
            $datay[] = $dta['cantp'];
            $dtdes[] = $dta['descripcion'];
        }
        
        $graph->xaxis->SetTickLabels($dtdes);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->SetFont(FF_DV_SERIFCOND,FS_NORMAL,8);
        $graph->yaxis->SetFont(FF_DV_SERIFCOND,FS_NORMAL,8);
        
        
        $bplot = new \BarPlot($datay);
        $bplot->SetFillColor('green');
        
        
        
        $path = '/var/www/FocalAB/web/bundles/app/expofiles/imagen.png'; 
        $graph->Add($bplot);
        $graph->Stroke($path);
        //$imgh = $graph->Stroke(_IMG_HANDLER);
        //$imgh = $graph->Stroke($path);
        /*ob_start();
        $graph->img->Stream();
        $image_data = ob_get_contents();
        ob_end_clean();
        $image = base64_decode($image_data);*/
        
        
        

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
            'datosd' => $datosd,
            'datospxe' => $datospxe,
            'datospxq' => $datospxq[0],
            'datossol' => $datossol[0],
            'datosnac' => $datosnac[0],
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSDemo.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosDemograficos.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
