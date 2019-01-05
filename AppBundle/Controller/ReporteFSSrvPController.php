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
class ReporteFSSrvPController extends Controller
{

    public function crearReporteFSSrvPAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        $rngfechag = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        
/*============== SERVICIOS PUBLICOS =================*/
        $query = "select
id_servicio idsrv,
c.descripcion,
sum(case when reciben = 1 then 1 else 0 end) rsi,
sum(case when reciben = 2 then 1 else 0 end) rno,
sum(case when reciben = 1 then 1 else 0 end)::decimal * 100 / nullif((sum(case when reciben = 1 then 1 else 0 end) + sum(case when reciben = 2 then 1 else 0 end)),0) porsi,
sum(case when reciben = 2 then 1 else 0 end)::decimal * 100 / nullif((sum(case when reciben = 1 then 1 else 0 end) + sum(case when reciben = 2 then 1 else 0 end)),0) porno,
sum(case when reciben = 1 then 1 else 0 end) + sum(case when reciben = 2 then 1 else 0 end) total,
nullif(sum(case when reciben = 1 and s.estado = 1 then 1 else 0 end),0) ebueno,
nullif(sum(case when reciben = 1 and s.estado = 2 then 1 else 0 end),0) eregular,
nullif(sum(case when reciben = 1 and s.estado = 3 then 1 else 0 end),0) emalo,
sum(case when reciben = 1 and s.estado is null then 1 else 0 end) enulo
from 
datos_generales g join datos_serviciospub s on (g.id_enc = s.id_enc and g.periodo = :per and g.cod_departamento = :dep and g.cod_municipio = :mun %1\$s %2\$s) 
inner join ad_servicios_publicos c on (s.id_servicio = c.id )
group by id_servicio, descripcion
order by id_servicio
";
        $query = sprintf($query,$codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datossrv = $stmt->fetchAll();
        
        
        $query = "select cant_dias cantdias, 
sum(case when id_servicio = 1 then 1 else 0 end) srv1,
round(sum(case when id_servicio = 1 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 1 and cod_departamento = :dep and cod_municipio = :mun %2\$s),0),2) porsrv1,   
sum(case when id_servicio = 2 then 1 else 0 end) srv2,
round(sum(case when id_servicio = 2 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 2 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv2,
sum(case when id_servicio = 3 then 1 else 0 end) srv3,
round(sum(case when id_servicio = 3 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 3 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv3,
sum(case when id_servicio = 4 then 1 else 0 end) srv4,
round(sum(case when id_servicio = 4 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 4 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv4,
sum(case when id_servicio = 5 then 1 else 0 end) srv5,
round(sum(case when id_servicio = 5 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 5 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv5,
sum(case when id_servicio = 6 then 1 else 0 end) srv6,
round(sum(case when id_servicio = 6 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 6 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv6,
sum(case when id_servicio = 11 then 1 else 0 end) srv11,
round(sum(case when id_servicio = 11 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 11 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv11,
sum(case when id_servicio = 12 then 1 else 0 end) srv12,
round(sum(case when id_servicio = 12 then 1 else 0 end)::decimal * 100 / 
  nullif((select count(*) 
  from datos_serviciospub 
  where reciben = 1 and id_servicio = 12 and cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porsrv12
from datos_generales g join datos_serviciospub s
on(g.id_enc = s.id_enc and g.periodo = :per and id_servicio in (1,2,3,4,5,6,11,12) and reciben = 1 and g.cod_departamento = :dep and g.cod_municipio = :mun %3\$s %4\$s)
group by cant_dias
order by cant_dias";
        $query = sprintf($query,$codCom, $rngfecha, $codColg, $rngfechag);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datossrvd = $stmt->fetchAll();        

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
            'datossrv' => $datossrv,
            'datossrvd' => $datossrvd,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSSrvP.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosSrvPublicos.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
