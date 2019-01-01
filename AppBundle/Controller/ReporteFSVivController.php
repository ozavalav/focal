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
class ReporteFSVivController extends Controller
{

    public function crearReporteFSVivAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        
/* ============= datos de vivienda ================== */        
        $query = "select 
            sum(case when d.tipo_vivienda = 1 then 1 else 0 end) cantcasa, 
            sum(case when d.tipo_vivienda = 2 then 1 else 0 end) cantapar,
            sum(case when d.tipo_vivienda = 3 then 1 else 0 end) cantlocal,
            sum(case when d.tipo_vivienda = 4 then 1 else 0 end) cantcuarto,
            sum(case when d.tipo_vivienda = 5 then 1 else 0 end) cantcimpro,
            sum(case when d.tipo_tenencia = 1 then 1 else 0 end) cantpropia,
            sum(case when d.tipo_tenencia = 2 then 1 else 0 end) cantpagando,
            sum(case when d.tipo_tenencia = 3 then 1 else 0 end) cantalquila,
            sum(case when d.tipo_tenencia = 4 then 1 else 0 end) cantprestada,
            sum(case when d.tipo_dominio = 1 then 1 else 0 end) cantpleno,
            sum(case when d.tipo_dominio = 2 then 1 else 0 end) cantutil,
            sum(case when d.tipo_dominio = 3 then 1 else 0 end) cantocupacion,
            sum(case when d.tipo_dominio = 4 then 1 else 0 end) cantposesion,
            sum(case when d.sexo_tenencia = 1 then 1 else 0 end) cantth,
            sum(case when d.sexo_tenencia = 2 then 1 else 0 end) canttm,
            sum(case when d.sexo_tenencia = 3 then 1 else 0 end) cantta,
            sum(case when d.problema_repello = 1 then 1 else 0 end) cantri,
            sum(case when d.problema_repello = 2 then 1 else 0 end) cantre,
            sum(case when d.problema_repello = 3 then 1 else 0 end) cantra,
            sum(case when d.material_vivienda = 1 then 1 else 0 end) cantmado,
            sum(case when d.material_vivienda = 2 then 1 else 0 end) cantmblo,
            sum(case when d.material_vivienda = 3 then 1 else 0 end) cantmbah,
            sum(case when d.material_vivienda = 4 then 1 else 0 end) cantmmad,
            sum(case when d.material_vivienda = 5 then 1 else 0 end) cantmdes,
            sum(case when d.material_vivienda = 6 then 1 else 0 end) cantmlad,
            sum(case when d.material_vivienda = 7 then 1 else 0 end) cantmyug,
            sum(case when d.material_techo = 1 then 1 else 0 end) canttdes,
            sum(case when d.material_techo = 2 then 1 else 0 end) canttpaj,
            sum(case when d.material_techo = 3 then 1 else 0 end) canttbar,
            sum(case when d.material_techo = 4 then 1 else 0 end) canttmet,
            sum(case when d.material_techo = 5 then 1 else 0 end) canttasb,
            sum(case when d.material_techo = 6 then 1 else 0 end) canttcon,
            sum(case when d.material_techo = 7 then 1 else 0 end) canttshi,
            sum(case when d.material_piso = 1 then 1 else 0 end) cantptie,
            sum(case when d.material_piso = 2 then 1 else 0 end) cantppla,
            sum(case when d.material_piso = 3 then 1 else 0 end) cantpmad,
            sum(case when d.material_piso = 4 then 1 else 0 end) cantplad,
            sum(case when d.material_piso = 5 then 1 else 0 end) cantpgra,
            sum(case when d.material_piso = 6 then 1 else 0 end) cantpcer,
            sum(case when d.material_piso = 7 then 1 else 0 end) cantpmos, 
            sum(case when d.condicion_vivienda = 1 then 1 else 0 end) cantcbue,
            sum(case when d.condicion_vivienda = 2 then 1 else 0 end) cantcreg,
            sum(case when d.condicion_vivienda = 3 then 1 else 0 end) cantcmal,
            sum(case when d.tiene_cocina = 1 then 1 else 0 end) cocinasi,
            sum(case when d.tiene_cocina = 2 then 1 else 0 end) cocinano,
            sum(case when d.ubicacion_cocina = 1 then 1 else 0 end) ubicdentro,
            sum(case when d.ubicacion_cocina = 2 then 1 else 0 end) ubicfuera,
            sum(case when d.ubicacion_cocina = 3 then 1 else 0 end) ubiccorre,
            sum(case when d.razon_migracion = 1 then 1 else 0 end) razonmeco,
            sum(case when d.razon_migracion = 2 then 1 else 0 end) razonmvio,
            sum(case when d.razon_migracion = 3 then 1 else 0 end) razonmfam,
            sum(case when d.miembro_emigrado = 1 then 1 else 0 end) emigradosi,
            sum(case when d.miembro_emigrado = 2 then 1 else 0 end) emigradono,
            sum(pv_sinrepello) pvsinrep,
            sum(pv_pisotierra) pvpisotie,
            sum(pv_cielofalso) pvcielofal,
            sum(pv_estructural) pvestruct,
            sum(pv_techomalo) pvtechmalo,
            sum(pv_ninguno) pvninguno,
            sum(concinacon_elec) cocinacele,
            sum(concinacon_chimbo) cocinacchi,
            sum(concinacon_kerosen) cocinacker,
            sum(concinacon_lena) cocinaclena,
            sum(concinacon_eco) cocinaceco,
            sum(consumoagua_notratada) consumoanot,
            sum(consumoagua_botellon) consumoabot,
            sum(consumoagua_hervida) consumoaher,
            sum(consumoagua_filtrada) consumoafil,
            sum(consumoagua_clorada) consumoaclo,
            count(*) total
           from datos_vivienda d
           where d.cod_departamento = :dep and d.cod_municipio = :mun %1\$s %2\$s";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosv = $stmt->fetchAll();
        
/* Piezan por vivienda */   
        
$query = "select
case
  when piezas_vivienda = 0 then 'a. Ninguna' 
  when piezas_vivienda = 1 then 'b. Una pieza'
  when piezas_vivienda = 2 then 'c. Dos piezas'
  when piezas_vivienda = 3 then 'd. Tres piezas'
  when piezas_vivienda > 3 then 'e. Cuatro o mas'
  else 'NC'
 end rango,
 count(*) cantidad,
 round(count(*)::decimal*100 / nullif((select count(*) from datos_vivienda where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porpiezas
from datos_vivienda 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
case
  when piezas_vivienda = 0 then 'a. Ninguna' 
  when piezas_vivienda = 1 then 'b. Una pieza'
  when piezas_vivienda = 2 then 'c. Dos piezas'
  when piezas_vivienda = 3 then 'd. Tres piezas'
  when piezas_vivienda > 3 then 'e. Cuatro o mas'
  else 'NC'
 end 
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datospxv = $stmt->fetchAll();

/* Baños  por vivienda */        
$query = "select
case
  when banos_vivienda = 0 then 'a. Sin baño' 
  when banos_vivienda = 1 then 'b. Un baño'
  when banos_vivienda = 2 then 'c. Dos baños'
  when banos_vivienda > 2 then 'd. Tres o mas baños'
  else 'NC'
 end rango,
 count(*) cantidad,
 round(count(*)::decimal*100 / nullif((select count(*) from datos_vivienda where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porbxviv
from datos_vivienda 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
case
  when banos_vivienda = 0 then 'a. Sin baño' 
  when banos_vivienda = 1 then 'b. Un baño'
  when banos_vivienda = 2 then 'c. Dos baños'
  when banos_vivienda > 2 then 'd. Tres o mas baños'
  else 'NC'
 end 
order by 1
";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosbxv = $stmt->fetchAll();
        
/* Dormitorios por vivienda */        
$query = "select
case
  when dormitorios_vivienda = 0 then 'a. Sin dormitorios' 
  when dormitorios_vivienda = 1 then 'b. Un dormintorio'
  when dormitorios_vivienda = 2 then 'c. Dos dormitorios'
  when dormitorios_vivienda > 2 then 'd. Tres o mas dormitorios'
  else 'NC'
 end rango,
 count(*) cantidad,
 round(count(*)::decimal*100 / nullif((select count(*) from datos_vivienda where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) pordxviv
from datos_vivienda 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
case
  when dormitorios_vivienda = 0 then 'a. Sin dormitorios' 
  when dormitorios_vivienda = 1 then 'b. Un dormintorio'
  when dormitorios_vivienda = 2 then 'c. Dos dormitorios'
  when dormitorios_vivienda > 2 then 'd. Tres o mas dormitorios'
  else 'NC'
 end 
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosdxv = $stmt->fetchAll();        
        
/* Personas por dormitorio*/
      $query = "select
case
  when personasx_dormitorio = 0 then 'a. Ninguna' 
  when personasx_dormitorio = 1 then 'b. Una persona'
  when personasx_dormitorio = 2 then 'c. Dos personas'
  when personasx_dormitorio > 2 then 'd. Tres o mas personas'
  else 'NC'
 end rango,
 count(*) cantidad,
 round(count(*)::decimal*100 / nullif((select count(*) from datos_vivienda where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porpxdor
from datos_vivienda 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
case
  when personasx_dormitorio = 0 then 'a. Ninguna' 
  when personasx_dormitorio = 1 then 'b. Una persona'
  when personasx_dormitorio = 2 then 'c. Dos personas'
  when personasx_dormitorio > 2 then 'd. Tres o mas personas'
  else 'NC'
 end 
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datospxd = $stmt->fetchAll();  
        
/* Familias por vivienda */        
      $query = "select
case
  when familias_casa = 0 then 'a. Ninguna' 
  when familias_casa = 1 then 'b. Una familia'
  when familias_casa = 2 then 'c. Dos familias'
  when familias_casa > 2 then 'd. Tres o mas familias'
  else 'NC'
 end rango,
 count(*) cantidad,
 round(count(*)::decimal*100 / nullif((select count(*) from datos_vivienda where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s),0),2) porfxviv
from datos_vivienda 
where cod_departamento = :dep and cod_municipio = :mun %1\$s %2\$s
group by
case
  when familias_casa = 0 then 'a. Ninguna' 
  when familias_casa = 1 then 'b. Una familia'
  when familias_casa = 2 then 'c. Dos familias'
  when familias_casa > 2 then 'd. Tres o mas familias'
  else 'NC'
 end 
order by 1";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosfxv = $stmt->fetchAll();         

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
            'datosv' => $datosv[0],
            'datospxv' => $datospxv,
            'datosbxv' => $datosbxv,
            'datosdxv' => $datosdxv,
            'datospxd' => $datospxd,
            'datosfxv' => $datosfxv,
        );
        $array3 = $array4;
        
        $html = $this->renderView('FocalAppBundle:Reportes:reporteFSViv.html.twig', $array3);
        $html2pdf = new \Html2Pdf_Html2Pdf('L', $format = 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($html);
        $html2pdf->Output('DatosVivienda.pdf');
        $response = new Response();
        $response->setContent($html);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
