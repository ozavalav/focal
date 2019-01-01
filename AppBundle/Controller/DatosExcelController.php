<?php
namespace Focal\AppBundle\Controller;
define('__ROOT__', dirname(dirname(__FILE__))); 

require_once (__ROOT__ . '/spout-2.7.3/src/Spout/Autoloader/autoload.php');



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

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;


/**
 * DatosGenerales controller.
 *
 */
class DatosExcelController extends Controller
{

    public function buscarComunidadAction(Request $request, $param)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $codMuni = $param;
        

    //IDENTITY
        $dql = 'SELECT co.codComunidad, co.nombre
        FROM FocalAppBundle:AdComunidades co 
        WHERE co.codMunicipio = ?1 
        order by co.nombre';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $codMuni);
        $comunidades = $query->getResult();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($comunidades, 'json');
        $response->setData($comunidades);
        return $response;
    }
    
    public function reporte1Action() {
        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findAll(array('id' => 'ASC'));

        } else {
            $request = $this->getRequest();
            $session = $request->getSession();
            $codDep = $session->get('_cod_departamento');
            $entities = $em->getRepository('FocalAppBundle:AdDepartamentos')->findBy(array('codDepartamento' => $codDep));
        }
        
        return $this->render('FocalAppBundle:DatosGenerales:producciondiaria.html.twig', array(
                    'departamentos' => $entities,
        ));
    }

    public function exportarDatosExcelAction($fin, $fin2, $fin3, $fin4, $fin5) {
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
        
        $query = "select 
        dg.num_boleta boleta, dg.id_enc enc, dg.cant_personasvivienda cantp, dg.cant_personas523 peest, dg.cant_personasm10 petra,
        dto.cant_solteras,
        dto.cant_solteros,
        ds.embarazos, ds.cant_casa, ds.cant_centros, ds.cant_materno, ds.cant_hospital, ds.cant_clinica, ds.cant_otros, ds.cant_muertem,
        case when ds.planifican = 1 then 'si' when ds.planifican = 2 then 'no' else '-' end planifican, 
        case 
         when ds.metodo = 1 then 'Ritmo'
         when ds.metodo = 2 then 'DIU'
         when ds.metodo = 3 then 'Pastilla'
         when ds.metodo = 4 then 'Preservativo'
         when ds.metodo = 5 then 'Operacion'
         when ds.metodo = 6 then 'Implante'
         when ds.metodo = 7 then 'Inyeccion'       
        end metodo, 
        ds.cant_muerte_ninos, ds.cant_muerte_ninas,
        dsp.cant_casos_violencia,
        dsp.cant_victima_violencia,
        case when dsp.considera_seguro = 1 then 'si' when dsp.considera_seguro = 2 then 'no' else '-' end con_seguro, 
        dsp.cant_hombres_miembros,
        dsp.cant_mujeres_miembros,
        case when dsa.trabajo_tierra = 1 then 'si' when dsa.trabajo_tierra = 2 then 'no' else '-' end trabaja_tierra,
        dsa.cant_hombres,
        dsa.cant_mujeres,
        case 
         when dsa.tipo_tenencia = 1 then 'Propia(pagada)'
         when dsa.tipo_tenencia = 2 then 'Propia(pagando)'
         when dsa.tipo_tenencia = 3 then 'Alquilada'
         when dsa.tipo_tenencia = 4 then 'Prestada'
         when dsa.tipo_tenencia = 5 then 'En litigio'
         when dsa.tipo_tenencia = 6 then 'Comunal'
         when dsa.tipo_tenencia = 1 then 'No tiene'
        end tipo_tenencia,
        case when dsa.produce_alimento = 1 then 'si' when dsa.produce_alimento = 2 then 'no' else '-' end produce_alimento,
        case when dsa.produce_suficiente = 1 then 'si' when dsa.produce_suficiente = 2 then 'no' else '-' end produce_suficiente,
        dsa.cant_hombres,
        case when dsa.excedente = 1 then 'si' when dsa.excedente = 2 then 'no' else '-' end produce_excedente,
        case when dsa.tiene_huerto = 1 then 'si' when dsa.tiene_huerto = 2 then 'no' else '-' end tiene_huerto,
        case when dsa.tiene_animales = 1 then 'si' when dsa.tiene_animales = 2 then 'no' else '-' end tiene_animales,
        case when dfo.ocupacion = 1 then 'Empleado' when dfo.ocupacion = 2 then 'Cuenta Propia' when dfo.ocupacion = 2 then 'No aplica' else '-' end ocupacion_actual,
        case when dfo.sectore = 1 then 'Comercial' when dfo.sectore = 2 then 'Industrial' when dfo.sectore = 3 then 'Servicio' end sector_empleado,
        case when dfo.sectorp = 1 then 'Primario' when dfo.sectorp = 2 then 'Secundario' when dfo.sectorp = 3 then 'Terciario' end sector_cpropia,
        case when dfo.prestamofam = 1 then 'si' when dfo.prestamofam = 2 then 'no' else '-' end prestamo_fam,
        case when dfo.prestamosexo = 1 then 'H' when dfo.prestamosexo = 2 then 'M' else '-' end prestamo_sexo,
        case when dfo.remesas = 1 then 'si' when dfo.remesas = 2 then 'no' else '-' end recibe_remesas,
        dfo.cant_remesas,
        dfo.cant_ingresofam,
        case when dfo.ingreso_ajusta = 1 then '3 Tiempos' when dfo.ingreso_ajusta = 2 then '2 Tiempos' when dfo.ingreso_ajusta = 3 then '1 Tiempo' else '-' end ingreso_ajusta,
        case 
         when dv.tipo_vivienda = 1 then 'Casa'
         when dv.tipo_vivienda = 2 then 'Apartamento'
         when dv.tipo_vivienda = 3 then 'Local no habitacional'
         when dv.tipo_vivienda = 4 then 'Cuarteria'
         when dv.tipo_vivienda = 5 then 'Casa improvisada'
        end tipo_vivienda,
        case 
         when dv.tipo_tenencia = 1 then 'Propia pagada'
         when dv.tipo_tenencia = 2 then 'Propia pagando'
         when dv.tipo_tenencia = 3 then 'Alquilada'
         when dv.tipo_tenencia = 4 then 'Prestada'
        end tipo_tenencia,
        case 
         when dv.material_vivienda = 1 then 'Adobe'
         when dv.material_vivienda = 2 then 'Bloque'
         when dv.material_vivienda = 3 then 'Bahareque'
         when dv.material_vivienda = 4 then 'Madera'
         when dv.material_vivienda = 5 then 'Desperdicios'
         when dv.material_vivienda = 6 then 'Ladrillo'
         when dv.material_vivienda = 7 then 'Yugua'
        end material_vivienda,
        case 
         when dv.material_techo = 1 then 'Material de desecho'
         when dv.material_techo = 2 then 'Paja o similar'
         when dv.material_techo = 3 then 'Teja o barro'
         when dv.material_techo = 4 then 'Lamina metalica'
         when dv.material_techo = 5 then 'Lamina asbesto'
         when dv.material_techo = 6 then 'Concreto'
         when dv.material_techo = 7 then 'Shingle'
        end material_techo,
        case 
         when dv.material_piso = 1 then 'Tierra'
         when dv.material_piso = 2 then 'Plancha de cemento'
         when dv.material_piso = 3 then 'Madera rustica'
         when dv.material_piso = 4 then 'Ladrillo barro'
         when dv.material_piso = 5 then 'Granito'
         when dv.material_piso = 6 then 'Ceramica'
         when dv.material_piso = 7 then 'Mosaico'
        end material_piso,
        case when dv.condicion_vivienda = 1 then 'Buena' when dv.condicion_vivienda = 2 then 'Regular' when dv.condicion_vivienda = 3 then 'Mala' else '-' end condicion_vivienda,
        case when dv.tiene_cocina = 1 then 'si' when dv.tiene_cocina = 2 then 'no' else '-' end tiene_cocina,
        dv.piezas_vivienda,
        dv.banos_vivienda,
        dv.dormitorios_vivienda,
        dv.personasx_dormitorio,
        dv.familias_casa,
        case when dv.miembro_emigrado = 1 then 'si' when dv.miembro_emigrado = 2 then 'no' else '-' end miembro_emigrado,
        dv.razon_migracion
        from datos_generales dg join datosd_otros dto on (dto.id_enc = dg.id_enc and dto.cod_departamento = :dep and dto.cod_municipio = :mun %1\$s %2\$s)
        join datoss_general ds on (ds.id_enc = dg.id_enc and ds.cod_departamento = :dep and ds.cod_municipio = :mun %1\$s %2\$s)
        left join datos_seguridad_participacion dsp on (dsp.id_enc = dg.id_enc and dsp.cod_departamento = :dep and dsp.cod_municipio = :mun %1\$s %2\$s)
        left join datos_seg_alimentaria dsa on (dsa.id_enc = dg.id_enc and dsa.cod_departamento = :dep and dsa.cod_municipio = :mun %1\$s %2\$s)
        left join datos_fuerza_otros dfo on (dfo.id_enc = dg.id_enc and dfo.cod_departamento = :dep and dfo.cod_municipio = :mun %1\$s %2\$s)
        left join datos_vivienda dv on (dv.id_enc = dg.id_enc and dv.cod_departamento = :dep and dv.cod_municipio = :mun %1\$s %2\$s)
        where dg.cod_departamento = :dep and dg.cod_municipio = :mun %1\$s %2\$s
        order by dg.num_boleta limit 500";
        $query = sprintf($query,$codCom, $rngfecha);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Oscar Zavala')
            ->setLastModifiedBy('Oscar Zavala')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3' ,'boleta')
            ->setCellValue('B3' ,'enc')
            ->setCellValue('C3' ,'personas vivienda')
            ->setCellValue('D3' ,'personas edad estudiar')
            ->setCellValue('E3' ,'personas edad trabajar')
            ->setCellValue('F3' ,'cant solteras')
            ->setCellValue('G3' ,'cant solteros')
            ->setCellValue('H3' ,'cant embarazos')
            ->setCellValue('I3' ,'atendidos en casa')
            ->setCellValue('J3' ,'atendidos en centros')
            ->setCellValue('K3' ,'atendidos en materno')
            ->setCellValue('L3' ,'atendidos en hospital')
            ->setCellValue('M3' ,'atendidos en clinica')
            ->setCellValue('N3' ,'atendidos en otros')
            ->setCellValue('O3' ,'cant muerte materna')
            ->setCellValue('P3' ,'planifican')
            ->setCellValue('Q3' ,'metodo')
            ->setCellValue('R3' ,'cant_muerte_ninos')
            ->setCellValue('S3' ,'cant_muerte_ninas')
            ->setCellValue('T3' ,'cant_casos_violencia')
            ->setCellValue('U3' ,'cant_victima_violencia')
            ->setCellValue('V3' ,'considera seguro')
            ->setCellValue('W3' ,'cant_hombres_miembros')
            ->setCellValue('X3' ,'cant_mujeres_miembros')
            ->setCellValue('Y3' ,'trabaja_tierra')
            ->setCellValue('Z3' ,'cant_hombres')
            ->setCellValue('AA3' ,'cant_mujeres')
            ->setCellValue('AB3' ,'tipo_tenencia')
            ->setCellValue('AC3' ,'produce_alimento')
            ->setCellValue('AD3' ,'produce_suficiente')
            ->setCellValue('AE3' ,'cant_hombres')
            ->setCellValue('AF3' ,'produce_excedente')
            ->setCellValue('AG3' ,'tiene_huerto')
            ->setCellValue('AH3' ,'tiene_animales')
            ->setCellValue('AI3' ,'ocupacion_actual')
            ->setCellValue('AJ3' ,'sector_empleado')
            ->setCellValue('AK3' ,'sector_cpropia')
            ->setCellValue('AL3' ,'prestamo_fam')
            ->setCellValue('AM3' ,'prestamo_sexo')
            ->setCellValue('AN3' ,'recibe_remesas')
            ->setCellValue('AO3' ,'cant_remesas')
            ->setCellValue('AP3' ,'cant_ingresofam')
            ->setCellValue('AQ3' ,'ingreso_ajusta')
            ->setCellValue('AR3' ,'tipo_vivienda')
            ->setCellValue('AS3' ,'tipo_tenencia')
            ->setCellValue('AT3' ,'material_vivienda')
            ->setCellValue('AU3' ,'material_techo')
            ->setCellValue('AV3' ,'material_piso')
            ->setCellValue('AW3' ,'condicion_vivienda')
            ->setCellValue('AX3' ,'tiene_cocina')
            ->setCellValue('AY3' ,'piezas_vivienda')
            ->setCellValue('AZ3' ,'banos_vivienda')
            ->setCellValue('BA3' ,'dormitorios_vivienda')
            ->setCellValue('BB3' ,'personasx_dormitorio')
            ->setCellValue('BC3' ,'familias_casa')
            ->setCellValue('BD3' ,'miembro_emigrado')
            ->setCellValue('BE3' ,'razon_migracion');
        
        $i = 4;
        foreach($datosd as $dt) {
            $spreadsheet->getActiveSheet()->setCellValue('A' . $i, $dt['boleta'])
            ->setCellValue('B' . $i, $dt['enc'])
            ->setCellValue('C' . $i, $dt['cantp'])
            ->setCellValue('D' . $i, $dt['peest'])
            ->setCellValue('E' . $i, $dt['petra'])
            ->setCellValue('F' . $i, $dt['cant_solteras'])
            ->setCellValue('G' . $i, $dt['cant_solteros'])
            ->setCellValue('H' . $i, $dt['embarazos'])
            ->setCellValue('I' . $i, $dt['cant_casa'])
            ->setCellValue('J' . $i, $dt['cant_centros'])
            ->setCellValue('K' . $i, $dt['cant_materno'])
            ->setCellValue('L' . $i, $dt['cant_hospital'])
            ->setCellValue('M' . $i, $dt['cant_clinica'])
            ->setCellValue('N' . $i, $dt['cant_otros'])
            ->setCellValue('O' . $i, $dt['cant_muertem'])
            ->setCellValue('P' . $i, $dt['planifican'])
            ->setCellValue('Q' . $i, $dt['metodo'])
            ->setCellValue('R' . $i, $dt['cant_muerte_ninos'])
            ->setCellValue('S' . $i, $dt['cant_muerte_ninas'])
            ->setCellValue('T' . $i, $dt['cant_casos_violencia'])
            ->setCellValue('U' . $i, $dt['cant_victima_violencia'])
            ->setCellValue('V' . $i, $dt['con_seguro'])
            ->setCellValue('W' . $i, $dt['cant_hombres_miembros'])
            ->setCellValue('X' . $i, $dt['cant_mujeres_miembros'])
            ->setCellValue('Y' . $i, $dt['trabaja_tierra'])
            ->setCellValue('Z' . $i, $dt['cant_hombres'])
            ->setCellValue('AA' . $i, $dt['cant_mujeres'])
            ->setCellValue('AB' . $i, $dt['tipo_tenencia'])
            ->setCellValue('AC' . $i, $dt['produce_alimento'])
            ->setCellValue('AD' . $i, $dt['produce_suficiente'])
            ->setCellValue('AE' . $i, $dt['cant_hombres'])
            ->setCellValue('AF' . $i, $dt['produce_excedente'])
            ->setCellValue('AG' . $i, $dt['tiene_huerto'])
            ->setCellValue('AH' . $i, $dt['tiene_animales'])
            ->setCellValue('AI' . $i, $dt['ocupacion_actual'])
            ->setCellValue('AJ' . $i, $dt['sector_empleado'])
            ->setCellValue('AK' . $i, $dt['sector_cpropia'])
            ->setCellValue('AL' . $i, $dt['prestamo_fam'])
            ->setCellValue('AM' . $i, $dt['prestamo_sexo'])
            ->setCellValue('AN' . $i, $dt['recibe_remesas'])
            ->setCellValue('AO' . $i, $dt['cant_remesas'])
            ->setCellValue('AP' . $i, $dt['cant_ingresofam'])
            ->setCellValue('AQ' . $i, $dt['ingreso_ajusta'])
            ->setCellValue('AR' . $i, $dt['tipo_vivienda'])
            ->setCellValue('AS' . $i, $dt['tipo_tenencia'])
            ->setCellValue('AT' . $i, $dt['material_vivienda'])
            ->setCellValue('AU' . $i, $dt['material_techo'])
            ->setCellValue('AV' . $i, $dt['material_piso'])
            ->setCellValue('AW' . $i, $dt['condicion_vivienda'])
            ->setCellValue('AX' . $i, $dt['tiene_cocina'])
            ->setCellValue('AY' . $i, $dt['piezas_vivienda'])
            ->setCellValue('AZ' . $i, $dt['banos_vivienda'])
            ->setCellValue('BA' . $i, $dt['dormitorios_vivienda'])
            ->setCellValue('BB' . $i, $dt['personasx_dormitorio'])
            ->setCellValue('BC' . $i, $dt['familias_casa'])
            ->setCellValue('BD' . $i, $dt['miembro_emigrado'])
            ->setCellValue('BE' . $i, $dt['razon_migracion'])
            ;
            $i++;
        }
        
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Simple');
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);
        
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
    
public function sacarExcelAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechads = "";
        $rngfechadsp = "";
        $rngfechadsa = "";
        $rngfechadfo = "";
        $rngfechadv = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechads = "and ds.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadsp = "and dsp.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadsa = "and dsa.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadfo = "and dfo.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadv = "and dv.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codCol = "";
        $codComds = "";
        $codComdsp = "";
        $codComdsa = "";
        $codComdfo = "";
        $codComdv = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCol = " and dg.cod_colonia in ('" . $strcom. "')";
            $codComds = " and ds.cod_comunidad in ('" . $strcom ."')";
            $codComdsp = " and dsp.cod_comunidad in ('" . $strcom ."')";
            $codComdsa = " and dsa.cod_comunidad in ('" . $strcom ."')";
            $codComdfo = " and dfo.cod_comunidad in ('" . $strcom ."')";
            $codComdv = " and dv.cod_comunidad in ('" . $strcom ."')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
        dg.num_boleta boleta, dg.id_enc enc, dg.cant_personasvivienda cantp, dg.cant_personas523 peest, dg.cant_personasm10 petra,
        dto.cant_solteras,
        dto.cant_solteros,
        ds.embarazos, ds.cant_casa, ds.cant_centros, ds.cant_materno, ds.cant_hospital, ds.cant_clinica, ds.cant_otros, ds.cant_muertem,
        case when ds.planifican = 1 then 'si' when ds.planifican = 2 then 'no' else '-' end planifican, 
        case 
         when ds.metodo = 1 then 'Ritmo'
         when ds.metodo = 2 then 'DIU'
         when ds.metodo = 3 then 'Pastilla'
         when ds.metodo = 4 then 'Preservativo'
         when ds.metodo = 5 then 'Operacion'
         when ds.metodo = 6 then 'Implante'
         when ds.metodo = 7 then 'Inyeccion'       
        end metodo, 
        ds.cant_muerte_ninos, ds.cant_muerte_ninas,
        dsp.cant_casos_violencia,
        dsp.cant_victima_violencia,
        case when dsp.considera_seguro = 1 then 'si' when dsp.considera_seguro = 2 then 'no' else '-' end con_seguro, 
        dsp.cant_hombres_miembros,
        dsp.cant_mujeres_miembros,
        case when dsa.trabajo_tierra = 1 then 'si' when dsa.trabajo_tierra = 2 then 'no' else '-' end trabaja_tierra,
        dsa.cant_hombres,
        dsa.cant_mujeres,
        case 
         when dsa.tipo_tenencia = 1 then 'Propia(pagada)'
         when dsa.tipo_tenencia = 2 then 'Propia(pagando)'
         when dsa.tipo_tenencia = 3 then 'Alquilada'
         when dsa.tipo_tenencia = 4 then 'Prestada'
         when dsa.tipo_tenencia = 5 then 'En litigio'
         when dsa.tipo_tenencia = 6 then 'Comunal'
         when dsa.tipo_tenencia = 7 then 'No tiene'
        end tipo_tenencia,
        case when dsa.produce_alimento = 1 then 'si' when dsa.produce_alimento = 2 then 'no' else '-' end produce_alimento,
        case when dsa.produce_suficiente = 1 then 'si' when dsa.produce_suficiente = 2 then 'no' else '-' end produce_suficiente,
        dsa.cant_hombres,
        case when dsa.excedente = 1 then 'si' when dsa.excedente = 2 then 'no' else '-' end produce_excedente,
        case when dsa.tiene_huerto = 1 then 'si' when dsa.tiene_huerto = 2 then 'no' else '-' end tiene_huerto,
        case when dsa.tiene_animales = 1 then 'si' when dsa.tiene_animales = 2 then 'no' else '-' end tiene_animales,
        case when dfo.ocupacion = 1 then 'Empleado' when dfo.ocupacion = 2 then 'Cuenta Propia' when dfo.ocupacion = 3 then 'No aplica' else '-' end ocupacion_actual,
        case when dfo.sectore = 1 then 'Comercial' when dfo.sectore = 2 then 'Industrial' when dfo.sectore = 3 then 'Servicio' end sector_empleado,
        case when dfo.sectorp = 1 then 'Primario' when dfo.sectorp = 2 then 'Secundario' when dfo.sectorp = 3 then 'Terciario' end sector_cpropia,
        case when dfo.prestamofam = 1 then 'si' when dfo.prestamofam = 2 then 'no' else '-' end prestamo_fam,
        case when dfo.prestamosexo = 1 then 'H' when dfo.prestamosexo = 2 then 'M' else '-' end prestamo_sexo,
        case when dfo.remesas = 1 then 'si' when dfo.remesas = 2 then 'no' else '-' end recibe_remesas,
        dfo.cant_remesas,
        dfo.cant_ingresofam,
        case when dfo.ingreso_ajusta = 1 then '3 Tiempos' when dfo.ingreso_ajusta = 2 then '2 Tiempos' when dfo.ingreso_ajusta = 3 then '1 Tiempo' else '-' end ingreso_ajusta,
        case 
         when dv.tipo_vivienda = 1 then 'Casa'
         when dv.tipo_vivienda = 2 then 'Apartamento'
         when dv.tipo_vivienda = 3 then 'Local no habitacional'
         when dv.tipo_vivienda = 4 then 'Cuarteria'
         when dv.tipo_vivienda = 5 then 'Casa improvisada'
        end tipo_vivienda,
        case 
         when dv.tipo_tenencia = 1 then 'Propia pagada'
         when dv.tipo_tenencia = 2 then 'Propia pagando'
         when dv.tipo_tenencia = 3 then 'Alquilada'
         when dv.tipo_tenencia = 4 then 'Prestada'
        end tipo_tenencia,
        case 
         when dv.material_vivienda = 1 then 'Adobe'
         when dv.material_vivienda = 2 then 'Bloque'
         when dv.material_vivienda = 3 then 'Bahareque'
         when dv.material_vivienda = 4 then 'Madera'
         when dv.material_vivienda = 5 then 'Desperdicios'
         when dv.material_vivienda = 6 then 'Ladrillo'
         when dv.material_vivienda = 7 then 'Yugua'
        end material_vivienda,
        case 
         when dv.material_techo = 1 then 'Material de desecho'
         when dv.material_techo = 2 then 'Paja o similar'
         when dv.material_techo = 3 then 'Teja o barro'
         when dv.material_techo = 4 then 'Lamina metalica'
         when dv.material_techo = 5 then 'Lamina asbesto'
         when dv.material_techo = 6 then 'Concreto'
         when dv.material_techo = 7 then 'Shingle'
        end material_techo,
        case 
         when dv.material_piso = 1 then 'Tierra'
         when dv.material_piso = 2 then 'Plancha de cemento'
         when dv.material_piso = 3 then 'Madera rustica'
         when dv.material_piso = 4 then 'Ladrillo barro'
         when dv.material_piso = 5 then 'Granito'
         when dv.material_piso = 6 then 'Ceramica'
         when dv.material_piso = 7 then 'Mosaico'
        end material_piso,
        case when dv.condicion_vivienda = 1 then 'Buena' when dv.condicion_vivienda = 2 then 'Regular' when dv.condicion_vivienda = 3 then 'Mala' else '-' end condicion_vivienda,
        case when dv.tiene_cocina = 1 then 'si' when dv.tiene_cocina = 2 then 'no' else '-' end tiene_cocina,
        dv.piezas_vivienda,
        dv.banos_vivienda,
        dv.dormitorios_vivienda,
        dv.personasx_dormitorio,
        dv.familias_casa,
        case when dv.miembro_emigrado = 1 then 'si' when dv.miembro_emigrado = 2 then 'no' else '-' end miembro_emigrado,
        case when dv.razon_migracion = 1 then 'Economicos' when dv.razon_migracion = 2 then 'Violencia' when dv.razon_migracion = 1 then 'Familia' else '-' end razon_migracion
        from datos_generales dg join datosd_otros dto on (dto.id_enc = dg.id_enc and dg.periodo = :per and dg.cod_departamento = :dep and dg.cod_municipio = :mun and dto.cod_departamento = :dep and dto.cod_municipio = :mun %1\$s %2\$s)
        join datoss_general ds on (ds.id_enc = dg.id_enc and ds.cod_departamento = :dep and ds.cod_municipio = :mun %3\$s %4\$s)
        left join datos_seguridad_participacion dsp on (dsp.id_enc = dg.id_enc and dsp.cod_departamento = :dep and dsp.cod_municipio = :mun %5\$s %6\$s)
        left join datos_seg_alimentaria dsa on (dsa.id_enc = dg.id_enc and dsa.cod_departamento = :dep and dsa.cod_municipio = :mun %7\$s %8\$s)
        left join datos_fuerza_otros dfo on (dfo.id_enc = dg.id_enc and dfo.cod_departamento = :dep and dfo.cod_municipio = :mun %9\$s %10\$s)
        left join datos_vivienda dv on (dv.id_enc = dg.id_enc and dv.cod_departamento = :dep and dv.cod_municipio = :mun %11\$s %12\$s)
        order by dg.num_boleta"; 
        $query = sprintf($query,$codCol, $rngfechadg, $codComds, $rngfechads, $codComdsp, $rngfechadsp, $codComdsa, $rngfechadsa, $codComdfo, $rngfechadfo, $codComdv, $rngfechadv );
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
}
// Redirect output to a client’s web browser (Xlsx)
        
      
//$writer = WriterFactory::create(Type::XLSX); // for XLSX files
//$writer = WriterFactory::create(Type::CSV); // for CSV files
//$writer = WriterFactory::create(Type::ODS); // for ODS files

//$path = '/var/www/FocalAB/src/Focal/AppBundle/Resources/public/archex.csv'; 

$filename = 'datosgenerales'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
//$writer->openToBrowser('excelfile.xlsx'); // stream data directly to the browser

//$writer->openToFile('php://output');
//$writer->openToBrowser('php://output');        

$writer->addRow(['Boleta', 'Encuesta'
,'personas vivienda'
            ,'personas edad estudiar'
            ,'personas edad trabajar'
            ,'cant solteras'
            ,'cant solteros'
            ,'cant embarazos'
            ,'atendidos en casa'
            ,'atendidos en centros'
            ,'atendidos en materno'
            ,'atendidos en hospital'
            ,'atendidos en clinica'
            ,'atendidos en otros'
            ,'cant muerte materna'
            ,'planifican'
            ,'metodo'
            ,'cant_muerte_ninos'
            ,'cant_muerte_ninas'
            ,'cant_casos_violencia'
            ,'cant_victima_violencia'
            ,'considera seguro'
            ,'cant_hombres_miembros'
            ,'cant_mujeres_miembros'
            ,'trabaja_tierra'
            ,'cant_hombres'
            ,'cant_mujeres'
            ,'tipo_tenencia'
            ,'produce_alimento'
            ,'produce_suficiente'
            ,'cant_hombres'
            ,'produce_excedente'
            ,'tiene_huerto'
            ,'tiene_animales'
            ,'ocupacion_actual'
            ,'sector_empleado'
            ,'sector_cpropia'
            ,'prestamo_fam'
            ,'prestamo_sexo'
            ,'recibe_remesas'
            ,'cant_remesas'
            ,'cant_ingresofam'
            ,'ingreso_ajusta'
            ,'tipo_vivienda'
            ,'tipo_tenencia'
            ,'material_vivienda'
            ,'material_techo'
            ,'material_piso'
            ,'condicion_vivienda'
            ,'tiene_cocina'
            ,'piezas_vivienda'
            ,'banos_vivienda'
            ,'dormitorios_vivienda'
            ,'personasx_dormitorio'
            ,'familias_casa'
            ,'miembro_emigrado'
            ,'razon_migracion']);


foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['cantp']
            , $dt['peest']
            , $dt['petra']
            , $dt['cant_solteras']
            , $dt['cant_solteros']
            , $dt['embarazos']
            , $dt['cant_casa']
            , $dt['cant_centros']
            , $dt['cant_materno']
            , $dt['cant_hospital']
            , $dt['cant_clinica']
            , $dt['cant_otros']
            , $dt['cant_muertem']
            , $dt['planifican']
            , $dt['metodo']
            , $dt['cant_muerte_ninos']
            , $dt['cant_muerte_ninas']
            , $dt['cant_casos_violencia']
            , $dt['cant_victima_violencia']
            , $dt['con_seguro']
            , $dt['cant_hombres_miembros']
            , $dt['cant_mujeres_miembros']
            , $dt['trabaja_tierra']
            , $dt['cant_hombres']
            , $dt['cant_mujeres']
            , $dt['tipo_tenencia']
            , $dt['produce_alimento']
            , $dt['produce_suficiente']
            , $dt['cant_hombres']
            , $dt['produce_excedente']
            , $dt['tiene_huerto']
            , $dt['tiene_animales']
            , $dt['ocupacion_actual']
            , $dt['sector_empleado']
            , $dt['sector_cpropia']
            , $dt['prestamo_fam']
            , $dt['prestamo_sexo']
            , $dt['recibe_remesas']
            , $dt['cant_remesas']
            , $dt['cant_ingresofam']
            , $dt['ingreso_ajusta']
            , $dt['tipo_vivienda']
            , $dt['tipo_tenencia']
            , $dt['material_vivienda']
            , $dt['material_techo']
            , $dt['material_piso']
            , $dt['condicion_vivienda']
            , $dt['tiene_cocina']
            , $dt['piezas_vivienda']
            , $dt['banos_vivienda']
            , $dt['dormitorios_vivienda']
            , $dt['personasx_dormitorio']
            , $dt['familias_casa']
            , $dt['miembro_emigrado']
            , $dt['razon_migracion']]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));

    }       

public function sacarExcelFamAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechadf = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadf = "and df.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComdf = "";
        $codColdg = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codColdg = " and dg.cod_colonia in ('" . $strcom. "')";
            $codComdf = " and df.cod_comunidad in ('" . $strcom ."')";
            

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
dg.num_boleta boleta,
dg.id_enc enc,
case when df.sexo = 1 then 'M' else 'F' end 
sexo, df.edad,
pr.descripcion parentesco,
et.descripcion etnia,
case when df.tiene_pn = 1 then 'Si' when df.tiene_pn = 2 then 'No' else '-' end con_documento 
from datos_generales dg join datosd_familia df on (df.id_enc = dg.id_enc and dg.periodo = :per and dg.cod_departamento = :dep and dg.cod_municipio = :mun %1\$s %2\$s and df.cod_departamento = :dep and df.cod_municipio = :mun %3\$s %4\$s)
left join ad_parentesco pr on (df.id_parentesco = pr.id)
left join ad_etnia et on (df.id_etnia = et.id) 
order by dg.num_boleta";
        $query = sprintf($query,$codColdg, $rngfechadg, $codComdf, $rngfechadg);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
}

$filename = 'datosfamilia'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
    
$writer->addRow(['Boleta', 'Encuesta'
            ,'Sexo'
            ,'Edad'
            ,'Parentesco'
            ,'Etnia'
            ,'Partida Nac.']);

foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['sexo']
            , $dt['edad']
            , $dt['parentesco']
            , $dt['etnia']
            , $dt['con_documento']]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));
    }
    
public function sacarExcelEnferAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechade = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechade = "and de.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codCol = "";
        $codComde = "";
        
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codCol = " and dg.cod_colonia in ('" . $strcom. "')";
            $codComde = " and de.cod_comunidad in ('" . $strcom ."')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
dg.num_boleta boleta,
dg.id_enc enc,
ef.descripcion,
de.cant_manifestaron manifestaron,
de.cant_hombres hombres,
de.cant_mujeres mujeres,
de.cant_publica publica,
de.cant_privada privada
from datos_generales dg join datoss_enfermedades de on (de.id_enc = dg.id_enc and dg.periodo = :per and dg.cod_departamento = :dep and dg.cod_municipio = :mun %1\$s %2\$s and de.cod_departamento = :dep and de.cod_municipio = :mun %3\$s %4\$s )
join ad_enfermedades ef on (ef.id = de.id_enfermedad)
order by dg.num_boleta";
        $query = sprintf($query,$codCol, $rngfechadg, $codComde, $rngfechade);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
}

$filename = 'datosefermedades'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
    
$writer->addRow(['Boleta', 'Encuesta'
            ,'Enfermedad'
            ,'Cant_Manifestaron'
            ,'Cant_Hombres'
            ,'Cant_Mujeres'
            ,'Cant_Publica'
            ,'Cant_Privada']);

foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['descripcion']
            , $dt['manifestaron']
            , $dt['hombres']
            , $dt['mujeres']
            , $dt['publica']
            , $dt['privada']]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));
    } 
    
public function sacarExcelSrvPAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechadsp = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadsp = "and dsp.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComdsp = "";
        $codColdg = "";
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComdsp = " and dsp.cod_comunidad in ('" . $strcom ."')";
            $codColdg = " and dg.cod_colonia in ('" . $strcom. "')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
dg.num_boleta boleta,
dg.id_enc enc,
sp.descripcion servicio,
case when dsp.reciben = 1 then 'Si' when dsp.reciben = 2 then 'No' else '-' end reciben,
case 
when dsp.estado = 1 then 'Bueno'
when dsp.estado = 2 then 'Regular'
when dsp.estado = 3 then 'Malo'
end estado,
dsp.cant_dias dias
from datos_generales dg left join datos_serviciospub dsp on (dsp.id_enc = dg.id_enc and dg.cod_departamento = :dep and dg.cod_municipio = :mun and dg.periodo = :per %1\$s %2\$s and dsp.cod_departamento = :dep and dsp.cod_municipio = :mun %3\$s %4\$s)
join ad_servicios_publicos sp on (sp.id = dsp.id_servicio)  
order by dg.num_boleta";
        $query = sprintf($query,$codColdg, $rngfechadg, $codComdsp, $rngfechadsp);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
} 

$filename = 'datossrvpub'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
    
$writer->addRow(['Boleta', 'Encuesta'
            ,'Servicio'
            ,'Reciben'
            ,'Estado'
            ,'Días']);

foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['servicio']
            , $dt['reciben']
            , $dt['estado']
            , $dt['dias']]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));
    }     
    
public function sacarExcelEduAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechade = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechade = "and de.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComde = "";
        $codColdg = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComde = " and de.cod_comunidad in ('" . $strcom ."')";
            $codColdg = " and dg.cod_colonia in ('" . $strcom. "')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
dg.num_boleta boleta,
dg.id_enc enc,
de.grado,
case when de.estudia = 1 then 'Si' when de.estudia = 2 then 'No' else '-' end estudia,
case when df.sexo = 1 then 'M' when df.sexo = 2 then 'F' else '-' end sexo,
df.edad
from datos_generales dg join datos_educacion de on (de.id_enc = dg.id_enc and dg.periodo = :per and dg.cod_departamento = :dep and dg.cod_municipio = :mun %1\$s %2\$s and de.cod_departamento = :dep and de.cod_municipio = :mun %3\$s %4\$s)
join datosd_familia df on (df.id_enc = de.id_enc and df.id = de.id_familia and df.cod_departamento = :dep and df.cod_municipio = :mun %1\$s %2\$s)
order by dg.num_boleta";
        $query = sprintf($query,$codColdg, $rngfechadg, $codComde, $rngfechade);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
} 

$filename = 'datoseducacion'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
    
$writer->addRow(['Boleta', 'Encuesta'
            ,'Grado'
            ,'Estudia'
            ,'Sexo'
            ,'Edad']);

foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['grado']
            , $dt['estudia']
            , $dt['sexo']
            , $dt['edad']]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));
    }      
    
public function sacarExcelFIngAction($fin, $fin2, $fin3, $fin4, $fin6, $fin5) {
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
        $rngfechadg = "";
        $rngfechadfi = "";
        $rngfechadf = "";
        if($strIni != '' && $strFin != '') {
            $rngfecha = "and fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadg = "and dg.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadfi = "and dfi.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
            $rngfechadf = "and dfi.fecha_creacion between '".$strIni."'::date and '".$strFin."'::date ";
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
        $codComdfi = "";
        $codComdf = "";
        $codColdg = "";
        
        if($fin5 != '000000000000') {
            
            $codCom = " and cod_comunidad in ('" . $strcom . "')";
            $codComdfi = " and dfi.cod_comunidad in ('" . $strcom ."')";
            $codComdf = " and df.cod_comunidad in ('" . $strcom ."')";
            $codColdg = " and dg.cod_colonia in ('" . $strcom. "')";

        }
        $codComsa = " cod_comunidad in ('" . $strcom . "')";
        $query = "select cod_comunidad codcom, nombre from ad_comunidades where %1\$s ";
        $query = sprintf($query,$codComsa);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $dtnomcom = $stmt->fetchAll();
        
        
        //$strFfi = $ffi2->format('Y-m-d');

        $fin2o = $fin2;
        
        $query = "select 
dg.num_boleta boleta,
dg.id_enc enc,
df.edad,
case when df.sexo = 1 then 'M' when df.sexo = 2 then 'F' else '-' end sexo,
case 
 when dfi.estado_civil = 1 then 'Soltero' 
 when dfi.estado_civil = 2 then 'Casado' 
 when dfi.estado_civil = 3 then 'Divorsiado'
 when dfi.estado_civil = 4 then 'Union Libre'  
 else '-' end ecivil,
pf.descripcion profesion,
oc.descripcion ocupacion,
case when dfi.trabaja = 1 then 'Si' when dfi.trabaja = 2 then 'No' else '-' end trabaja,
dfi.ingresos,
dfi.nivele
from datos_generales dg join datos_fuerza_ingresos dfi on (dfi.id_enc = dg.id_enc and dg.periodo = :per and dg.cod_departamento = :dep and dg.cod_municipio = :mun %1\$s %2\$s and dfi.cod_departamento = :dep and dfi.cod_municipio = :mun %3\$s %4\$s)
left join datosd_familia df on (df.id_enc = dfi.id_enc and df.id = dfi.id_familia and df.cod_departamento = :dep and df.cod_municipio = :mun %5\$s %6\$s)
left join ad_profesiones pf on (pf.id = dfi.profesion)
left join ad_ocupaciones oc on (oc.id = dfi.ocupacion)
order by dg.num_boleta";
        $query = sprintf($query,$codColdg, $rngfechadg, $codComdfi, $rngfechadfi, $codComdf, $rngfechadf);
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('dep',$codDep);
        $stmt->bindValue('mun',$codMun);
        $stmt->bindValue('per',$periodo);
        $stmt->execute();
        $datosd = $stmt->fetchAll();
        
        /* Exportar Datos a Excel */
        
switch ($fin6) {
    case "1":
        $writer = WriterFactory::create(Type::CSV);
        $extension = '.csv';
        break;
    case "2":
        $writer = WriterFactory::create(Type::XLSX);
        $extension = '.xlsx';
        break;
} 

$filename = 'datosfuerzaing'.$extension;
$path = '/var/www/FocalAB/web/bundles/app/expofiles/'.$filename; 
$writer->openToFile($path); // write data to a file or to a PHP stream
    
$writer->addRow(['Boleta', 'Encuesta'
            ,'Edad'
            ,'Sexo'
            ,'Estado_Civil'
            ,'Profesion', 'Ocupacion', 'Trabaja', 'Ingresos', 'Nivel_Educativo']);

foreach($datosd as $dt) {
    
    $writer->addRow([$dt['boleta'],$dt['enc']
            , $dt['edad']
            , $dt['sexo']
            , $dt['ecivil']
            , $dt['profesion'], $dt['ocupacion'], $dt['trabaja'], $dt['ingresos'], $dt['nivele'] ]);    
}

$writer->close();

return $this->render('FocalAppBundle:DatosGenerales:resExpo.html.twig', array(
            'archivo' => $filename, 
        ));
    }  
    
}
