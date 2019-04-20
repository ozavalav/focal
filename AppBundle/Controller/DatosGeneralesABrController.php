<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Focal\AppBundle\Entity\DatosGenerales;
use Focal\AppBundle\Entity\DatosGrupoFamiliar;
use Focal\AppBundle\Entity\DatosdOtros;
use Focal\AppBundle\Entity\DatosdRangos;
use Focal\AppBundle\Entity\DatosFuerzaOtros;
use Focal\AppBundle\Entity\DatossGeneral;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * DatosGeneralesABr controller.
 *
 */
class DatosGeneralesABrController extends Controller
{

    /**
     * Creates a new DatosGenerales entity.
     *
     */
    public function createABrAction(Request $request)
    {
        /* Verifica si el usuario esta autenticado */
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        
        $codComunidad = $request->get('codComunidad');
        $idEnc = $request->get('idEnc');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();

        $em = $this->getDoctrine()->getManager();
        
/* =================== DATOS GRUPO FAMILIAR ===========================*/
        
        
    /* Guarda cada registro ingresado */
        $cantfila = $request->get('contres');
        $numero = 1;
        $tingfam = 0;
        $per5a23 = 0;
        $perm10 = 0;
        $totalper = 0;
        
        $rangos = array(
            array("rng" =>  1, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  2, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  3, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  4, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  5, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  6, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  7, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  8, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  9, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 10, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 11, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
        );
        
        for($i = 1; $i <= $cantfila; $i++){
           $efi = $request->get('tblest_'.$i);
           if($efi == 1) {
                $entdgf = new DatosGrupoFamiliar();
                
                /* Generar el id unico de la tabla vivienda */
                $sequenceName = 'datos_grupo_familiar_id_seq';
                $dbConnection = $em->getConnection();
                $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                $codCom = (int)$codMuni . $newId;
                
                /* Guarda valores de referencia */
                $entdgf->setId((int)$codCom);
                $entdgf->setCodComunidad($codComunidad);
                $entdgf->setcodMunicipio($codMuni);  
                $entdgf->setcodDepartamento($codDept);
                $entdgf->setIdEnc($idEnc);
                
                $nom = $request->get('qst_50_'.$i);
                $par = (int)$request->get('qst_51_'.$i);
                $sex = (int)$request->get('qst_52_'.$i);
                $eda = (int)$request->get('qst_53_'.$i);
                $etn = (int)$request->get('qst_54_'.$i);
                $rnp = (int)$request->get('qst_55_'.$i);
                $vac = (int)$request->get('qst_56_'.$i);
                $lye = (int)$request->get('qst_57_'.$i);
                $est = (int)$request->get('qst_58_'.$i);
                $acu = (int)$request->get('qst_59_'.$i);
                $aes = (int)$request->get('qst_60_'.$i);
                $eci = (int)$request->get('qst_61_'.$i);
                $tra = (int)$request->get('qst_62_'.$i);
                $ocu = (int)$request->get('qst_63_'.$i);
                $pro = (int)$request->get('qst_64_'.$i);
                $ing = (int)$request->get('qst_65_'.$i);
                $aec = (int)$request->get('qst_66_'.$i);
                $sco = (int)$request->get('qst_67_'.$i);
                $sde = (int)$request->get('qst_68_'.$i);
                $gee = (int)$request->get('qst_69_'.$i);
                $nor = $request->get('qst_70_'.$i);
                $ia1 = $request->get('qst_71_1_'.$i);
                $ia2 = $request->get('qst_71_2_'.$i);
                $pao = (int)$request->get('qst_72_'.$i);
                $pre = (int)$request->get('qst_73_'.$i);
                $rem = (int)$request->get('qst_74_'.$i);
                $pla = (int)$request->get('qst_75_'.$i);
                
                $entdgf->setNumero($numero++);
                $entdgf->setNombre($nom);
                $entdgf->setIdParentesco($par);
                $entdgf->setSexo($sex);
                $entdgf->setEdad($eda);
                $entdgf->setIdEtnia($etn);
                $entdgf->setTienePn((empty($rnp))?0:$rnp);
                $entdgf->setVacunas((empty($vac))?0:$vac);
                $entdgf->setLeerEscribir((empty($lye))?0:$lye);
                $entdgf->setEstudia((empty($est))?0:$est);
                $entdgf->setAnoCursando((empty($acu))?0:$acu);
                $entdgf->setAnoEstudio((empty($aes))?:$aes);
                $entdgf->setEstadoCivil((empty($eci))?0:$eci);
                $entdgf->setTrabaja((empty($tra))?0:$tra);
                $entdgf->setOcupacion((empty($ocu))?0:$ocu);
                $entdgf->setProfesion((empty($pro))?0:$pro);
                $entdgf->setIngresos((empty($ing))?0:$ing);
                $entdgf->setActividadEco((empty($aec))?0:$aec);
                $entdgf->setSectorContratado((empty($sco))?0:$sco);
                $entdgf->setSectorDedica((empty($sde))?0:$sde);
                $entdgf->setGeneraEmpleo((empty($gee))?0:$gee);
                $entdgf->setNombreOrg((empty($nor))?null:$nor);
                $entdgf->setInstApoyo1((empty($ia1))?null:$ia1);
                $entdgf->setInstApoyo2((empty($ia2))?null:$ia2);
                $entdgf->setParticipaOrg($pao);
                $entdgf->setPrestamo((empty($pre))?0:$pre);
                $entdgf->setRemesas((empty($rem))?0:$rem);
                $entdgf->setMetodoPlanifica((empty($pla))?0:$pla);
                
                /* Guarda los valores por defecto */
                $fecha = new \DateTime("now");
                $entdgf->setUsuarioCreacion($usuario);
                $entdgf->setUsuarioUltimaModificacion($usuario);
                $entdgf->setFechaCreacion($fecha);
                $entdgf->setFechaUltimaModificacion($fecha);
                
                /* Variable que guarda el total de ingreso familiar */
                $tingfam = $tingfam + $ing;
                
                /* Calcula cantidad de personas por rango de edad y las que saben leer y escribir */
                if($eda <= 4) {
                    $rangos[0]["h"]  = $rangos[0]["h"]  + ($sex==1)?1:0;
                    $rangos[0]["m"]  = $rangos[0]["m"]  + ($sex==2)?1:0;
                    $rangos[0]["hl"] = $rangos[0]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[0]["ml"] = $rangos[0]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>4 && $eda<=6) {
                    $rangos[1]["h"]  = $rangos[1]["h"]  + ($sex==1)?1:0;
                    $rangos[1]["m"]  = $rangos[1]["m"]  + ($sex==2)?1:0;
                    $rangos[1]["hl"] = $rangos[1]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[1]["ml"] = $rangos[1]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[1]["t"]  = $rangos[1]["t"] + 1;
                } else if ($eda>6 && $eda<=12) {
                    $rangos[2]["h"]  = $rangos[2]["h"]  + ($sex==1)?1:0;
                    $rangos[2]["m"]  = $rangos[2]["m"]  + ($sex==2)?1:0;
                    $rangos[2]["hl"] = $rangos[2]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[2]["ml"] = $rangos[2]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>12 && $eda<=15) {
                    $rangos[3]["h"]  = $rangos[3]["h"]  + ($sex==1)?1:0;
                    $rangos[3]["m"]  = $rangos[3]["m"]  + ($sex==2)?1:0;
                    $rangos[3]["hl"] = $rangos[3]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[3]["ml"] = $rangos[3]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>15 && $eda<=18) {
                    $rangos[4]["h"]  = $rangos[4]["h"]  + ($sex==1)?1:0;
                    $rangos[4]["m"]  = $rangos[4]["m"]  + ($sex==2)?1:0;
                    $rangos[4]["hl"] = $rangos[4]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[4]["ml"] = $rangos[4]["ml"] + ($lye==1 && $sex==2)?1:0;
                } else if ($eda>18 && $eda<=23) {
                    $rangos[5]["h"]  = $rangos[5]["h"]  + ($sex==1)?1:0;
                    $rangos[5]["m"]  = $rangos[5]["m"]  + ($sex==2)?1:0;
                    $rangos[5]["hl"] = $rangos[5]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[5]["ml"] = $rangos[5]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[5]["t"]  = $rangos[5]["t"] + 1;
                } else if ($eda>23 && $eda<=30) {
                    $rangos[6]["h"]  = $rangos[6]["h"]  + ($sex==1)?1:0;
                    $rangos[6]["m"]  = $rangos[6]["m"]  + ($sex==2)?1:0;
                    $rangos[6]["hl"] = $rangos[6]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[6]["ml"] = $rangos[6]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[6]["t"]  = $rangos[6]["t"] + 1;
                } else if ($eda>30 && $eda<=40) {
                    $rangos[7]["h"]  = $rangos[7]["h"]  + ($sex==1)?1:0;
                    $rangos[7]["m"]  = $rangos[7]["m"]  + ($sex==2)?1:0;
                    $rangos[7]["hl"] = $rangos[7]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[7]["ml"] = $rangos[7]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[7]["t"]  = $rangos[7]["t"] + 1;
                } else if ($eda>40 && $eda<=50) {
                    $rangos[8]["h"]  = $rangos[8]["h"]  + ($sex==1)?1:0;
                    $rangos[8]["m"]  = $rangos[8]["m"]  + ($sex==2)?1:0;
                    $rangos[8]["hl"] = $rangos[8]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[8]["ml"] = $rangos[8]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[8]["t"]  = $rangos[8]["t"] + 1;
                } else if ($eda>50 && $eda<=64) {
                    $rangos[9]["h"]  = $rangos[9]["h"]  + ($sex==1)?1:0;
                    $rangos[9]["m"]  = $rangos[9]["m"]  + ($sex==2)?1:0;
                    $rangos[9]["hl"] = $rangos[9]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[9]["ml"] = $rangos[9]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[9]["t"]  = $rangos[9]["t"] + 1;
                } else if ($eda>64) {
                    $rangos[10]["h"]  = $rangos[10]["h"]  + ($sex==1)?1:0;
                    $rangos[10]["m"]  = $rangos[10]["m"]  + ($sex==2)?1:0;
                    $rangos[10]["hl"] = $rangos[10]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[10]["ml"] = $rangos[10]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[10]["t"]  = $rangos[10]["t"] + 1;
                }
                
                /* Calcula los valores de las personas que ocupan la vivienda para actualizar tabla: Datos Generales */
                if($eda >4 && $eda <=13) {
                    $per5a23 = $per5a23 + 1;
                }
                if($eda > 9) {
                    $perm10 = $perm10 + 1;
                }
                
                $totalper = $totalper + 1;
                
                $em->persist($entdgf);
            }        
        }
        
/* =================== DATOS DEMOGRAFICOS OTROS ===========================*/
        $entddo = new DatosdOtros;
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datosd_otros_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entddo->setId((int)$codCom);
        $entddo->setIdEnc($idEnc);
        $entddo->setCodComunidad($codComunidad);
        $entddo->setcodMunicipio($codMuni);  
        $entddo->setcodDepartamento($codDept);
        
        $msol = (int)$request->get('qst_76');
        $psol = (int)$request->get('qst_77');
        $cnac = (int)$request->get('qst_79_1_1');
        $cnho = (int)$request->get('qst_79_1_2');
        $cnmu = (int)$request->get('qst_79_1_3');
        $ema1 = (int)$request->get('qst_79_1_4');
        $ema2 = (int)$request->get('qst_79_1_4');
        $ema3 = (int)$request->get('qst_79_1_5');
        
        
        $entddo->setCantSolteros($psol) ;
        $entddo->setCantSolteras($msol) ;
        $entddo->setCantNacimientos($cnac);
        $entddo->setCantNacNinos($cnho);
        $entddo->setCantNacNinas($cnmu);
        $entddo->setEdadMadre($ema1);
        
        
        /* Guarda los valores por defecto */
        $entddo->setUsuarioCreacion($usuario);
        $entddo->setUsuarioUltimaModificacion($usuario);
        $entddo->setFechaCreacion($fecha);
        $entddo->setFechaUltimaModificacion($fecha);
        
        $em->persist($entddo);
        
        
/* =================== DATOS SALUD GENERAL ===========================*/
        $entsag = new DatossGeneral();
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datoss_general_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entsag->setId((int)$codCom);
        $entsag->setIdEnc($idEnc);
        $entsag->setCodComunidad($codComunidad);
        $entsag->setcodMunicipio($codMuni);  
        $entsag->setcodDepartamento($codDept);
        
        $embr = (int)$request->get('qst_78');
        $cemb = (int)$request->get('qst_78_1');
        $eem1 = (int)$request->get('qst_78_2');
        $eem2 = (int)$request->get('qst_78_3');
        $eem3 = (int)$request->get('qst_78_4');
        $dnun = (int)$request->get('qst_80');
        
        $muem = (int)$request->get('qst_81');
        $cmum = (int)$request->get('qst_81_1');
        $momm = (int)$request->get('qst_81_2');
        $caum = $request->get('qst_81_3');
        
        $muen = (int)$request->get('qst_82');
        $cmun = (int)$request->get('qst_82_1');
        $cmuh1 = (int)$request->get('qst_82_2_1');
        $cmum1 = (int)$request->get('qst_82_2_2');
        $caum1 = $request->get('qst_82_2_3');
        $cmuh2 = (int)$request->get('qst_82_3_1');
        $cmum2 = (int)$request->get('qst_82_3_2');
        $caum2 = (int)$request->get('qst_82_3_3');
        
       
        
        $entsag->setEmbarazos($embr);
        $entsag->setCantEmbarazos($cemb);
        $entsag->setEdad1($eem1);
        $entsag->setEdad2($eem2);
        $entsag->setEdad3($eem3);
        
        
        $entsag->setLugarCasa(false);
        $entsag->setCantCasa(0);
        $entsag->setLugarCentros(false);
        $entsag->setCantCentros(0);
        $entsag->setLugarMaterno(false);
        $entsag->setCantMaterno(0);
        $entsag->setLugarHospital(false);
        $entsag->setCantHospital(0);
        $entsag->setLugarClinica(false);
        $entsag->setCantClinica(0);
        $entsag->setLugarOtros(false);
        $entsag->setCantOtros(0);
        
        switch($dnun) {
            case 1:
                $entsag->setLugarCasa(true);
                break;
            case 2:
                $entsag->setLugarCentros(true);
                break;
            case 3:
                $entsag->setLugarClinica(true);
                break;
            case 4:
                $entsag->setLugarHospital(true);
                break;
            case 5:
                $entsag->setLugarClinica(true);
                break;
            case 6:
                $entsag->setLugarOtros(true);
                break;
            case 7:
                /* Falta este campo en la base de datos*/
                break;
        }
        
        $entsag->setSemurionino($muen);
        $entsag->setCantMuerteNinos($cmuh1);
        $entsag->setCantMuerteNinas($cmum1);
        $entsag->setCausaMuerte($caum1);
        
        $entsag->setMuerteMat($muem);
        $entsag->setCantMuertem($cmum);
        $entsag->setMomentoMuertem($momm);
        $entsag->setCausaMuertem($caum);
        
        /* Guarda los valores por defecto */
        $entsag->setUsuarioCreacion($usuario);
        $entsag->setUsuarioUltimaModificacion($usuario);
        $entsag->setFechaCreacion($fecha);
        $entsag->setFechaUltimaModificacion($fecha);
        
        $em->persist($entsag);        
        
/* =================== DATOS FUERZA OTROS ===========================*/
        $entdfo = new DatosFuerzaOtros();
        
        /* Generar el id unico de la tabla seguridad y participacion */
        $sequenceName = 'datos_fuerza_otros_id_seq';
        $dbConnection = $em->getConnection();
        $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
        $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
        $codCom = (int)$codMuni . $newId;
        
        $entdfo->setId((int)$codCom);
        $entdfo->setIdEnc($idEnc);
        $entdfo->setCodComunidad($codComunidad);
        $entdfo->setcodMunicipio($codMuni);  
        $entdfo->setcodDepartamento($codDept);
        
        $iaju = (int)$request->get('qst_83');
        
        $entdfo->setIngresoAjusta($iaju); /* 1 = un tiempo, 2 = 2 tiempos y 3 = 3 tiempos */
        
        /* Calcula el rango de ingresos familiar */
        
/***** FALTA INCLUIR EN EL CALCULO LAS REMESAS ******/
        
        $ringfam = 0;
        if($tingfam < 1000 ) {
            $ringfam = 1;
        } else if ($tingfam > 1001 && $tingfam <= 2000) {
            $ringfam = 2;
        } else if ($tingfam > 2001 && $tingfam <= 4000) {
            $ringfam = 3;
        } else if ($tingfam > 4001 && $tingfam <= 8000) {
            $ringfam = 4;
        } else if ($tingfam > 8001 && $tingfam <= 12000) {
            $ringfam = 5;
        } else if ($tingfam > 12001 && $tingfam <= 20000) {
            $ringfam = 6;
        } else if ($tingfam > 20001 && $tingfam <= 30000) {
            $ringfam = 7;
        } else if ($tingfam > 30001 && $tingfam <= 50000) {
            $ringfam = 8;
        } else if ($tingfam > 50001 ) {
            $ringfam = 9;
        } 
        
        $entdfo->setCantIngresofam($tingfam);
        $entdfo->setRangoIngresofam($ringfam);
        
        /* Guarda los valores por defecto */
        $entdfo->setUsuarioCreacion($usuario);
        $entdfo->setUsuarioUltimaModificacion($usuario);
        $entdfo->setFechaCreacion($fecha);
        $entdfo->setFechaUltimaModificacion($fecha);
        
        $em->persist($entdfo);
        
/* =================== DATOS DEMOGRAFICOS POR RANGOS EDAD ===========================*/
        
        foreach($rangos as $item) {
            if($item["t"] > 0) {
                $entddr = new DatosdRangos;
                /* Generar el id unico de la tabla seguridad y participacion */
                $sequenceName = 'datosd_rangos_id_seq';
                $dbConnection = $em->getConnection();
                $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                $codCom = (int)$codMuni . $newId;

                $entddr->setId((int)$codCom);
                $entddr->setIdEnc($idEnc);
                $entddr->setCodComunidad($codComunidad);
                $entddr->setcodMunicipio($codMuni);  
                $entddr->setcodDepartamento($codDept);

                $entddr->setRango($item["rng"]);
                $entddr->setCantPersonas($item["t"]);
                $entddr->setCantHombres($item["h"]);
                $entddr->setCantMujeres($item["m"]);
                $entddr->setCantHombresLeen($item["hl"]);
                $entddr->setCantMujeresLeen($item["ml"]);

                /* Guarda los valores por defecto */
                $entddr->setUsuarioCreacion($usuario);
                $entddr->setUsuarioUltimaModificacion($usuario);
                $entddr->setFechaCreacion($fecha);
                $entddr->setFechaUltimaModificacion($fecha);

                $em->persist($entddr);
            }
        }
        
/* =================== ACTUALIZAR DATOS GENERALES ===========================*/
        
        try {
            $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idEnc, 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

            if ($ent) { 
                $ent[0]->setCantPersonas523($per5a23);
                $ent[0]->setCantPersonasm10($perm10);
                $ent[0]->setCantPersonasvivienda($totalper);
                /* Guarda los valores por defecto */
                $ent[0]->setUsuarioUltimaModificacion($usuario);
                $ent[0]->setFechaUltimaModificacion($fecha);
            } 
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            $this->addFlash('warning_df','No se pudieron encontrar los Datos Generales algunos datos no se actulizarón en la table Datos Generales');
            //throw new NotFoundHttpException('Número de boleta ya existe o esta vacia');
            //throw $this->createAccessDeniedException();
        }
        
        $em->flush();
        
        return $this->redirect($this->generateUrl('datosgeneralesAB'));
    }    
    
    /**
     * Creates a new DatosGenerales entity.
     *
     */
    public function updateABrAction(Request $request)
    {
        /* Verifica si el usuario esta autenticado */
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        $periodo = $session->get('_periodo');
        
        $codComunidad = $request->get('codComunidad');
        $idEnc = $request->get('idEnc');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();

        $em = $this->getDoctrine()->getManager();
        
        try {
            $entdgf = $em->getRepository('FocalAppBundle:DatosGrupoFamiliar')->findBy(array('idEnc' => $idEnc));
            $entddo = $em->getRepository('FocalAppBundle:DatosdOtros')->findBy(array('idEnc' => $idEnc));
            $entdsg = $em->getRepository('FocalAppBundle:DatossGeneral')->findBy(array('idEnc' => $idEnc));
            $entdfo = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc' => $idEnc));
            $entddr = $em->getRepository('FocalAppBundle:DatosdRangos')->findBy(array('idEnc' => $idEnc));
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            throw new NotFoundHttpException('No se puede recuperar registro: '. $e->getMessage() );
            //throw $this->createAccessDeniedException();
        }
        
/* =================== DATOS GRUPO FAMILIAR ===========================*/
        
        
    /* Guarda cada registro ingresado */
        $cantfila = $request->get('contres');
        $numero = 1;
        $tingfam = 0;
        $per5a23 = 0;
        $perm10 = 0;
        $totalper = 0;
       
        $fecha = new \DateTime("now"); //Fecha que se captura para usarla toda la transaccion 
        
        /* inicializa el arraglo de rangos */
        $rangos = array(
            array("rng" =>  1, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  2, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  3, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  4, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  5, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  6, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  7, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  8, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" =>  9, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 10, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
            array("rng" => 11, "t" => 0, "h" => 0, "m" => 0, "hl" => 0, "ml" => 0),
        );
        
        for($i = 1; $i <= $cantfila; $i++){
           $efi = (int)$request->get('tblest_'.$i);
           $idr = $request->get('tblidr_'.$i); // guarda el id del registro guardado en la tabla datos_grupo_familiar
           
           /* leer los datos que vienen en el request provenientes del fontend */
            $nom = $request->get('qst_50_'.$i);
            $par = (int)$request->get('qst_51_'.$i);
            $sex = (int)$request->get('qst_52_'.$i);
            $eda = (int)$request->get('qst_53_'.$i);
            $etn = (int)$request->get('qst_54_'.$i);
            $rnp = (int)$request->get('qst_55_'.$i);
            $vac = (int)$request->get('qst_56_'.$i);
            $lye = (int)$request->get('qst_57_'.$i);
            $est = (int)$request->get('qst_58_'.$i);
            $acu = (int)$request->get('qst_59_'.$i);
            $aes = (int)$request->get('qst_60_'.$i);
            $eci = (int)$request->get('qst_61_'.$i);
            $tra = (int)$request->get('qst_62_'.$i);
            $ocu = (int)$request->get('qst_63_'.$i);
            $pro = (int)$request->get('qst_64_'.$i);
            $ing = (int)$request->get('qst_65_'.$i);
            $aec = (int)$request->get('qst_66_'.$i);
            $sco = (int)$request->get('qst_67_'.$i);
            $sde = (int)$request->get('qst_68_'.$i);
            $gee = (int)$request->get('qst_69_'.$i);
            $nor = $request->get('qst_70_'.$i);
            $ia1 = $request->get('qst_71_1_'.$i);
            $ia2 = $request->get('qst_71_2_'.$i);
            $pao = (int)$request->get('qst_72_'.$i);
            $pre = (int)$request->get('qst_73_'.$i);
            $rem = (int)$request->get('qst_74_'.$i);
            $pla = (int)$request->get('qst_75_'.$i);
           
           /* Se agrega un nuevo registro en el frontend */ 
           if($efi == 1) {
                $entdgf = new DatosGrupoFamiliar();
                
                /* Generar el id unico de la tabla vivienda */
                $sequenceName = 'datos_grupo_familiar_id_seq';
                $dbConnection = $em->getConnection();
                $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                $codCom = (int)$codMuni . $newId;
                
                /* Guarda valores de referencia */
                $entdgf->setId((int)$codCom);
                $entdgf->setCodComunidad($codComunidad);
                $entdgf->setcodMunicipio($codMuni);  
                $entdgf->setcodDepartamento($codDept);
                $entdgf->setIdEnc($idEnc);
                
                $entdgf->setNumero($numero++);
                $entdgf->setNombre($nom);
                $entdgf->setIdParentesco($par);
                $entdgf->setSexo($sex);
                $entdgf->setEdad($eda);
                $entdgf->setIdEtnia($etn);
                $entdgf->setTienePn((empty($rnp))?0:$rnp);
                $entdgf->setVacunas((empty($vac))?0:$vac);
                $entdgf->setLeerEscribir((empty($lye))?0:$lye);
                $entdgf->setEstudia((empty($est))?0:$est);
                $entdgf->setAnoCursando((empty($acu))?0:$acu);
                $entdgf->setAnoEstudio((empty($aes))?:$aes);
                $entdgf->setEstadoCivil((empty($eci))?0:$eci);
                $entdgf->setTrabaja((empty($tra))?0:$tra);
                $entdgf->setOcupacion((empty($ocu))?0:$ocu);
                $entdgf->setProfesion((empty($pro))?0:$pro);
                $entdgf->setIngresos((empty($ing))?0:$ing);
                $entdgf->setActividadEco((empty($aec))?0:$aec);
                $entdgf->setSectorContratado((empty($sco))?0:$sco);
                $entdgf->setSectorDedica((empty($sde))?0:$sde);
                $entdgf->setGeneraEmpleo((empty($gee))?0:$gee);
                $entdgf->setNombreOrg((empty($nor))?null:$nor);
                $entdgf->setInstApoyo1((empty($ia1))?null:$ia1);
                $entdgf->setInstApoyo2((empty($ia2))?null:$ia2);
                $entdgf->setParticipaOrg($pao);
                $entdgf->setPrestamo((empty($pre))?0:$pre);
                $entdgf->setRemesas((empty($rem))?0:$rem);
                $entdgf->setMetodoPlanifica((empty($pla))?0:$pla);
                
                /* Guarda los valores por defecto */
                
                $entdgf->setUsuarioCreacion($usuario);
                $entdgf->setUsuarioUltimaModificacion($usuario);
                $entdgf->setFechaCreacion($fecha);
                $entdgf->setFechaUltimaModificacion($fecha);
                
                $em->persist($entdgf);
             } /* fin del if == 1 */          
            
            /* El registro en el fontend ha sido actualizado */ 
            if($efi == 2) {
                /* Buscar el registro segun el id de la tabla datos_grupo_familiar */
                $entdgf = $em->getRepository('FocalAppBundle:DatosGrupoFamiliar')->find($idr);
                
                /* si lo encontro actualiza la entidad con los datos */
                if($entdgf) {
                    /* Guarda los datos en la entidad */
                    $entdgf->setNumero($numero++);
                    $entdgf->setNombre($nom);
                    $entdgf->setIdParentesco($par);
                    $entdgf->setSexo($sex);
                    $entdgf->setEdad($eda);
                    $entdgf->setIdEtnia($etn);
                    $entdgf->setTienePn((empty($rnp))?0:$rnp);
                    $entdgf->setVacunas((empty($vac))?0:$vac);
                    $entdgf->setLeerEscribir((empty($lye))?0:$lye);
                    $entdgf->setEstudia((empty($est))?0:$est);
                    $entdgf->setAnoCursando((empty($acu))?0:$acu);
                    $entdgf->setAnoEstudio((empty($aes))?:$aes);
                    $entdgf->setEstadoCivil((empty($eci))?0:$eci);
                    $entdgf->setTrabaja((empty($tra))?0:$tra);
                    $entdgf->setOcupacion((empty($ocu))?0:$ocu);
                    $entdgf->setProfesion((empty($pro))?0:$pro);
                    $entdgf->setIngresos((empty($ing))?0:$ing);
                    $entdgf->setActividadEco((empty($aec))?0:$aec);
                    $entdgf->setSectorContratado((empty($sco))?0:$sco);
                    $entdgf->setSectorDedica((empty($sde))?0:$sde);
                    $entdgf->setGeneraEmpleo((empty($gee))?0:$gee);
                    $entdgf->setNombreOrg((empty($nor))?null:$nor);
                    $entdgf->setInstApoyo1((empty($ia1))?null:$ia1);
                    $entdgf->setInstApoyo2((empty($ia2))?null:$ia2);
                    $entdgf->setParticipaOrg($pao);
                    $entdgf->setPrestamo((empty($pre))?0:$pre);
                    $entdgf->setRemesas((empty($rem))?0:$rem);
                    $entdgf->setMetodoPlanifica((empty($pla))?0:$pla);
                    /* Guarda los valores por defecto */
                    $entdgf->setUsuarioUltimaModificacion($usuario);
                    $entdgf->setFechaUltimaModificacion($fecha);
                }
            } 
            
            /* El registro en el frontend ha sido borrado, se busca el registro y se borra inmediatamente */
            if($efi === 0) {
                $entdgf = $em->getRepository('FocalAppBundle:DatosGrupoFamiliar')->find($idr);
                $em->remove($entdgf);
                //$em->flush();
            }
            
            /* Actualizo la entidad para aquellos registros con estado en el frontend de 1, 2 y 3
             * El registro borrado no se considera en el calculo de los rangos de edad y tampoco del ingreso familiar  
             */
            if($efi == 1 || $efi == 2 || $efi == 3 ) {   
                /* Variable que guarda el total de ingreso familiar */
                $tingfam = $tingfam + $ing;

                /* Calcula cantidad de personas por rango de edad y las que saben leer y escribir */
                if($eda <= 4) {
                    $rangos[0]["h"]  = $rangos[0]["h"]  + ($sex==1)?1:0;
                    $rangos[0]["m"]  = $rangos[0]["m"]  + ($sex==2)?1:0;
                    $rangos[0]["hl"] = $rangos[0]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[0]["ml"] = $rangos[0]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[0]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>4 && $eda<=6) {
                    $rangos[1]["h"]  = $rangos[1]["h"]  + ($sex==1)?1:0;
                    $rangos[1]["m"]  = $rangos[1]["m"]  + ($sex==2)?1:0;
                    $rangos[1]["hl"] = $rangos[1]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[1]["ml"] = $rangos[1]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[1]["t"]  = $rangos[1]["t"] + 1;
                } else if ($eda>6 && $eda<=12) {
                    $rangos[2]["h"]  = $rangos[2]["h"]  + ($sex==1)?1:0;
                    $rangos[2]["m"]  = $rangos[2]["m"]  + ($sex==2)?1:0;
                    $rangos[2]["hl"] = $rangos[2]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[2]["ml"] = $rangos[2]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[2]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>12 && $eda<=15) {
                    $rangos[3]["h"]  = $rangos[3]["h"]  + ($sex==1)?1:0;
                    $rangos[3]["m"]  = $rangos[3]["m"]  + ($sex==2)?1:0;
                    $rangos[3]["hl"] = $rangos[3]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[3]["ml"] = $rangos[3]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[3]["t"]  = $rangos[0]["t"] + 1;
                } else if ($eda>15 && $eda<=18) {
                    $rangos[4]["h"]  = $rangos[4]["h"]  + ($sex==1)?1:0;
                    $rangos[4]["m"]  = $rangos[4]["m"]  + ($sex==2)?1:0;
                    $rangos[4]["hl"] = $rangos[4]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[4]["ml"] = $rangos[4]["ml"] + ($lye==1 && $sex==2)?1:0;
                } else if ($eda>18 && $eda<=23) {
                    $rangos[5]["h"]  = $rangos[5]["h"]  + ($sex==1)?1:0;
                    $rangos[5]["m"]  = $rangos[5]["m"]  + ($sex==2)?1:0;
                    $rangos[5]["hl"] = $rangos[5]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[5]["ml"] = $rangos[5]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[5]["t"]  = $rangos[5]["t"] + 1;
                } else if ($eda>23 && $eda<=30) {
                    $rangos[6]["h"]  = $rangos[6]["h"]  + ($sex==1)?1:0;
                    $rangos[6]["m"]  = $rangos[6]["m"]  + ($sex==2)?1:0;
                    $rangos[6]["hl"] = $rangos[6]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[6]["ml"] = $rangos[6]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[6]["t"]  = $rangos[6]["t"] + 1;
                } else if ($eda>30 && $eda<=40) {
                    $rangos[7]["h"]  = $rangos[7]["h"]  + ($sex==1)?1:0;
                    $rangos[7]["m"]  = $rangos[7]["m"]  + ($sex==2)?1:0;
                    $rangos[7]["hl"] = $rangos[7]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[7]["ml"] = $rangos[7]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[7]["t"]  = $rangos[7]["t"] + 1;
                } else if ($eda>40 && $eda<=50) {
                    $rangos[8]["h"]  = $rangos[8]["h"]  + ($sex==1)?1:0;
                    $rangos[8]["m"]  = $rangos[8]["m"]  + ($sex==2)?1:0;
                    $rangos[8]["hl"] = $rangos[8]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[8]["ml"] = $rangos[8]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[8]["t"]  = $rangos[8]["t"] + 1;
                } else if ($eda>50 && $eda<=64) {
                    $rangos[9]["h"]  = $rangos[9]["h"]  + ($sex==1)?1:0;
                    $rangos[9]["m"]  = $rangos[9]["m"]  + ($sex==2)?1:0;
                    $rangos[9]["hl"] = $rangos[9]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[9]["ml"] = $rangos[9]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[9]["t"]  = $rangos[9]["t"] + 1;
                } else if ($eda>64) {
                    $rangos[10]["h"]  = $rangos[10]["h"]  + ($sex==1)?1:0;
                    $rangos[10]["m"]  = $rangos[10]["m"]  + ($sex==2)?1:0;
                    $rangos[10]["hl"] = $rangos[10]["hl"] + ($lye==1 && $sex==1)?1:0;
                    $rangos[10]["ml"] = $rangos[10]["ml"] + ($lye==1 && $sex==2)?1:0;
                    $rangos[10]["t"]  = $rangos[10]["t"] + 1;
                }

                /* Calcula los valores de las personas que ocupan la vivienda para actualizar tabla: Datos Generales */
                if($eda >4 && $eda <=13) {
                    $per5a23 = $per5a23 + 1;
                }
                if($eda > 9) {
                    $perm10 = $perm10 + 1;
                }

                $totalper = $totalper + 1;
                
                
            } // fin del if $efi <> 0
        } /* fin del for */
        
/* =================== DATOS DEMOGRAFICOS OTROS ===========================*/
        /* Capturo los datos que viene del frontend por medio del request */
        $msol = (int)$request->get('qst_76');
        $psol = (int)$request->get('qst_77');
        $cnac = (int)$request->get('qst_79_1_1');
        $cnho = (int)$request->get('qst_79_1_2');
        $cnmu = (int)$request->get('qst_79_1_3');
        $ema1 = (int)$request->get('qst_79_1_4');
        $ema2 = (int)$request->get('qst_79_1_4');
        $ema3 = (int)$request->get('qst_79_1_5');
        
        /* verifico que la entidad se encuentra si no la encuentra la agrega un nuevo registro */
        if (!$entddo) {
            $entddo = new DatosdOtros();
            /* Generar el id unico de la tabla seguridad y participacion */
            $sequenceName = 'datosd_otros_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            $codCom = (int)$codMuni . $newId;

            $entddo->setId((int)$codCom);
            $entddo->setIdEnc($idEnc);
            $entddo->setCodComunidad($codComunidad);
            $entddo->setcodMunicipio($codMuni);  
            $entddo->setcodDepartamento($codDept);

            $entddo->setCantSolteros($psol) ;
            $entddo->setCantSolteras($msol) ;
            $entddo->setCantNacimientos($cnac);
            $entddo->setCantNacNinos($cnho);
            $entddo->setCantNacNinas($cnmu);
            $entddo->setEdadMadre($ema1);


            /* Guarda los valores por defecto */
            $entddo->setUsuarioCreacion($usuario);
            $entddo->setUsuarioUltimaModificacion($usuario);
            $entddo->setFechaCreacion($fecha);
            $entddo->setFechaUltimaModificacion($fecha);

            $em->persist($entddo);
        } else {
            $entddo[0]->setCantSolteras($msol);
            $entddo[0]->setCantSolteros($psol);
            $entddo[0]->setCantNacimientos($cnac);
            $entddo[0]->setCantNacNinos($cnho);
            $entddo[0]->setCantNacNinas($cnmu);
            $entddo[0]->setEdadMadre($ema1);
            
            /* Guarda los valores por defecto */
            $entddo[0]->setUsuarioUltimaModificacion($usuario);
            $entddo[0]->setFechaUltimaModificacion($fecha);
        }
        
        
/* =================== DATOS SALUD GENERAL ===========================*/
        /* Capturo los datos que viene del frontend por medio del request */
        $embr = (int)$request->get('qst_78');
        $cemb = (int)$request->get('qst_78_1');
        $eem1 = (int)$request->get('qst_78_2');
        $eem2 = (int)$request->get('qst_78_3');
        $eem3 = (int)$request->get('qst_78_4');
        $dnun = (int)$request->get('qst_80');
        
        $muem = (int)$request->get('qst_81');
        $cmum = (int)$request->get('qst_81_1');
        $momm = (int)$request->get('qst_81_2');
        $caum = $request->get('qst_81_3');
        
        $muen = (int)$request->get('qst_82');
        $cmun = (int)$request->get('qst_82_1');
        $cmuh1 = (int)$request->get('qst_82_2_1');
        $cmum1 = (int)$request->get('qst_82_2_2');
        $caum1 = $request->get('qst_82_2_3');
        $cmuh2 = (int)$request->get('qst_82_3_1');
        $cmum2 = (int)$request->get('qst_82_3_2');
        $caum2 = (int)$request->get('qst_82_3_3');
        
        /* verifico que la entidad se encuentra si no la encuentra la agrega un nuevo registro */
        if(!$entdsg) {
            $entdsg = new DatossGeneral();

            /* Generar el id unico de la tabla seguridad y participacion */
            $sequenceName = 'datoss_general_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            $codCom = (int)$codMuni . $newId;

            $entdsg->setId((int)$codCom);
            $entdsg->setIdEnc($idEnc);
            $entdsg->setCodComunidad($codComunidad);
            $entdsg->setcodMunicipio($codMuni);  
            $entdsg->setcodDepartamento($codDept);
            
            $entdsg->setEmbarazos($embr);
            $entdsg->setCantEmbarazos($cemb);
            $entdsg->setEdad1($eem1);
            $entdsg->setEdad2($eem2);
            $entdsg->setEdad3($eem3);

            $entdsg->setLugarCasa(false);
            $entdsg->setCantCasa(0);
            $entdsg->setLugarCentros(false);
            $entdsg->setCantCentros(0);
            $entdsg->setLugarMaterno(false);
            $entdsg->setCantMaterno(0);
            $entdsg->setLugarHospital(false);
            $entdsg->setCantHospital(0);
            $entdsg->setLugarClinica(false);
            $entdsg->setCantClinica(0);
            $entdsg->setLugarOtros(false);
            $entdsg->setCantOtros(0);

            switch($dnun) {
                case 1:
                    $entdsg->setLugarCasa(true);
                    break;
                case 2:
                    $entdsg->setLugarCentros(true);
                    break;
                case 3:
                    $entdsg->setLugarClinica(true);
                    break;
                case 4:
                    $entdsg->setLugarHospital(true);
                    break;
                case 5:
                    $entdsg->setLugarClinica(true);
                    break;
                case 6:
                    $entdsg->setLugarOtros(true);
                    break;
                case 7:
                    /* Falta este campo en la base de datos*/
                    break;
            }

            $entdsg->setSemurionino($muen);
            $entdsg->setCantMuerteNinos($cmuh1);
            $entdsg->setCantMuerteNinas($cmum1);
            $entdsg->setCausaMuerte($caum1);

            $entdsg->setMuerteMat($muem);
            $entdsg->setCantMuertem($cmum);
            $entdsg->setMomentoMuertem($momm);
            $entdsg->setCausaMuertem($caum);
            
            /* Guarda los valores por defecto */
            $entdsg->setUsuarioCreacion($usuario);
            $entdsg->setUsuarioUltimaModificacion($usuario);
            $entdsg->setFechaCreacion($fecha);
            $entdsg->setFechaUltimaModificacion($fecha);
        } else {
            $entdsg[0]->setEmbarazos($embr);
            $entdsg[0]->setCantEmbarazos($cemb);
            $entdsg[0]->setEdad1($eem1);
            $entdsg[0]->setEdad2($eem2);
            $entdsg[0]->setEdad3($eem3);

            $entdsg[0]->setLugarCasa(false);
            $entdsg[0]->setCantCasa(0);
            $entdsg[0]->setLugarCentros(false);
            $entdsg[0]->setCantCentros(0);
            $entdsg[0]->setLugarMaterno(false);
            $entdsg[0]->setCantMaterno(0);
            $entdsg[0]->setLugarHospital(false);
            $entdsg[0]->setCantHospital(0);
            $entdsg[0]->setLugarClinica(false);
            $entdsg[0]->setCantClinica(0);
            $entdsg[0]->setLugarOtros(false);
            $entdsg[0]->setCantOtros(0);

            switch($dnun) {
                case 1:
                    $entdsg[0]->setLugarCasa(true);
                    break;
                case 2:
                    $entdsg[0]->setLugarCentros(true);
                    break;
                case 3:
                    $entdsg[0]->setLugarClinica(true);
                    break;
                case 4:
                    $entdsg[0]->setLugarHospital(true);
                    break;
                case 5:
                    $entdsg[0]->setLugarClinica(true);
                    break;
                case 6:
                    $entdsg[0]->setLugarOtros(true);
                    break;
                case 7:
                    /* Falta este campo en la base de datos*/
                    break;
            }

            $entdsg[0]->setSemurionino($muen);
            $entdsg[0]->setCantMuerteNinos($cmuh1);
            $entdsg[0]->setCantMuerteNinas($cmum1);
            $entdsg[0]->setCausaMuerte($caum1);

            $entdsg[0]->setMuerteMat($muem);
            $entdsg[0]->setCantMuertem($cmum);
            $entdsg[0]->setMomentoMuertem($momm);
            $entdsg[0]->setCausaMuertem($caum);
            /* Guarda los valores por defecto */
            $entdsg[0]->setUsuarioUltimaModificacion($usuario);
            $entdsg[0]->setFechaUltimaModificacion($fecha);
        }
        
        $em->persist($entdsg[0]);        
        
/* =================== DATOS FUERZA OTROS ===========================*/
        /***** FALTA INCLUIR EN EL CALCULO LAS REMESAS ******/
            /* Calcula el rango del ingreso familiar segun los valores ingresados en el frontend */
            $ringfam = 0;
            if($tingfam < 1000 ) {
                $ringfam = 1;
            } else if ($tingfam > 1001 && $tingfam <= 2000) {
                $ringfam = 2;
            } else if ($tingfam > 2001 && $tingfam <= 4000) {
                $ringfam = 3;
            } else if ($tingfam > 4001 && $tingfam <= 8000) {
                $ringfam = 4;
            } else if ($tingfam > 8001 && $tingfam <= 12000) {
                $ringfam = 5;
            } else if ($tingfam > 12001 && $tingfam <= 20000) {
                $ringfam = 6;
            } else if ($tingfam > 20001 && $tingfam <= 30000) {
                $ringfam = 7;
            } else if ($tingfam > 30001 && $tingfam <= 50000) {
                $ringfam = 8;
            } else if ($tingfam > 50001 ) {
                $ringfam = 9;
            } 
            
            /* Capturo los datos que viene del frontend por medio del request */
            $iaju = (int)$request->get('qst_83');
            
        /* verifico que la entidad se encuentra si no la encuentra la agrega un nuevo registro */    
        if(!$entdfo) {
            $entdfo = new DatosFuerzaOtros();

            /* Generar el id unico de la tabla seguridad y participacion */
            $sequenceName = 'datos_fuerza_otros_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
            $codCom = (int)$codMuni . $newId;

            $entdfo->setId((int)$codCom);
            $entdfo->setIdEnc($idEnc);
            $entdfo->setCodComunidad($codComunidad);
            $entdfo->setcodMunicipio($codMuni);  
            $entdfo->setcodDepartamento($codDept);
            
            $entdfo->setIngresoAjusta($iaju); /* 1 = un tiempo, 2 = 2 tiempos y 3 = 3 tiempos */
            $entdfo->setCantIngresofam($tingfam);
            $entdfo->setRangoIngresofam($ringfam);

            /* Guarda los valores por defecto */
            $entdfo->setUsuarioCreacion($usuario);
            $entdfo->setUsuarioUltimaModificacion($usuario);
            $entdfo->setFechaCreacion($fecha);
            $entdfo->setFechaUltimaModificacion($fecha);

            $em->persist($entdfo);
        } else {
            $entdfo[0]->setIngresoAjusta($iaju); /* 1 = un tiempo, 2 = 2 tiempos y 3 = 3 tiempos */
            $entdfo[0]->setCantIngresofam($tingfam);
            $entdfo[0]->setRangoIngresofam($ringfam);
            /* Guarda los valores por defecto */
            $entdfo[0]->setUsuarioUltimaModificacion($usuario);
            $entdfo[0]->setFechaUltimaModificacion($fecha);
            
        } //final if !$entdfo 
        
        
        
/* =================== DATOS DEMOGRAFICOS POR RANGOS EDAD ===========================*/
        if(!$entddr) {
            foreach($rangos as $item) {
                if($item["t"] > 0) {
                    $entddr = new DatosdRangos;
                    /* Generar el id unico de la tabla seguridad y participacion */
                    $sequenceName = 'datosd_rangos_id_seq';
                    $dbConnection = $em->getConnection();
                    $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                    $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                    $codCom = (int)$codMuni . $newId;

                    $entddr->setId((int)$codCom);
                    $entddr->setIdEnc($idEnc);
                    $entddr->setCodComunidad($codComunidad);
                    $entddr->setcodMunicipio($codMuni);  
                    $entddr->setcodDepartamento($codDept);

                    $entddr->setRango($item["rng"]);
                    $entddr->setCantPersonas($item["t"]);
                    $entddr->setCantHombres($item["h"]);
                    $entddr->setCantMujeres($item["m"]);
                    $entddr->setCantHombresLeen($item["hl"]);
                    $entddr->setCantMujeresLeen($item["ml"]);

                    /* Guarda los valores por defecto */
                    $entddr->setUsuarioCreacion($usuario);
                    $entddr->setUsuarioUltimaModificacion($usuario);
                    $entddr->setFechaCreacion($fecha);
                    $entddr->setFechaUltimaModificacion($fecha);

                    $em->persist($entddr);
                } // fin del if $item["t"] > 0
            } // fin del foreach $rangos
        } else {
            /* actualizo cada rango en la tabla de datosd_rangos 
             * 
             */
            $numrng = array(1,2,3,4,5,6,7,8,9,10,11);
            $regdel = array();
            foreach($entddr as $rng) { // 1.) recorro los registros actuales de la tabla datosd_rangos
                $num = $rng->getRango();
                /* 2.) Comparo los dos rangos del arreglo ($rangos) contra la tabla datosd_rangos, 
                 * si el arreglo tiene un total mayor que cero, significa que tiene datos y tengo actualizar la tabla
                 * sino significa que ese rango fue borrado y lo tengo que eliminar de la tabla */
                if($rangos[$num-1]["t"] > 0) { 
                    $rng->setCantPersonas($rangos[$num-1]["t"]);
                    $rng->setCantHombres($rangos[$num-1]["h"]);
                    $rng->setCantMujeres($rangos[$num-1]["m"]);
                    $rng->setCantHombresLeen($rangos[$num-1]["hl"]);
                    $rng->setCantMujeresLeen($rangos[$num-1]["ml"]);
                    /*3.) elimino el elemento actualizado del arreglo tempotal ($numrng) 
                     * para buscar despues solo buscar los rangos que se agregaron */
                    unset($numrng[$num-1]); 
                } else { // 4.) guarda un arreglo para borrar los registros borrados en el frontend
                    $regdel[] = $rng->getId();
                }
            }
            /* Recorro el arreglo $rangos para agregar los registros nuevos agregados en el frontend */
            foreach($numrng as $i) {
                /* Si el arreglo $rangos tiene mas elementos con cantidades totales mayores que cero
                 * Significa que se agregaron nuevos rangos en el frontend, 
                 * por lo que se procede a agregar el nuevo registro a la table datosd_rangos 
                 */
                if ($rangos[$i-1]["t"] > 0) {
                    $entddra = new DatosdRangos;
                    /* Generar el id unico de la tabla seguridad y participacion */
                    $sequenceName = 'datosd_rangos_id_seq';
                    $dbConnection = $em->getConnection();
                    $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
                    $newId = (int)$dbConnection->fetchColumn($nextvalQuery);
                    $codCom = (int)$codMuni . $newId;

                    $entddra->setId((int)$codCom);
                    $entddra->setIdEnc($idEnc);
                    $entddra->setCodComunidad($codComunidad);
                    $entddra->setcodMunicipio($codMuni);  
                    $entddra->setcodDepartamento($codDept);

                    $entddra->setRango($rangos[$i-1]["rng"]);
                    $entddra->setCantPersonas($rangos[$i-1]["t"]);
                    $entddra->setCantHombres($rangos[$i-1]["h"]);
                    $entddra->setCantMujeres($rangos[$i-1]["m"]);
                    $entddra->setCantHombresLeen($rangos[$i-1]["hl"]);
                    $entddra->setCantMujeresLeen($rangos[$i-1]["ml"]);

                    /* Guarda los valores por defecto */
                    $entddra->setUsuarioCreacion($usuario);
                    $entddra->setUsuarioUltimaModificacion($usuario);
                    $entddra->setFechaCreacion($fecha);
                    $entddra->setFechaUltimaModificacion($fecha);

                    $em->persist($entddra); 
                }
            }
            
            /*Borrado de los registros en la tabla datosd_rangos que fueron borrados en el frontend*/
            foreach($regdel as $rpd) {
                $entdel = $em->getRepository('FocalAppBundle:DatosdRangos')->find($rpd);
                if($entdel) {
                    $em->remove($entdel);
                }
            }
            
        } // fin del if !$entddr 
        
/* =================== ACTUALIZAR DATOS GENERALES ===========================*/
        
        try {
            $ent = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idEnc, 'codMunicipio' => $codMuni , 'codDepartamento' => $codDept, 'periodo' => $periodo));

            if ($ent) { 
                $ent[0]->setCantPersonas523($per5a23);
                $ent[0]->setCantPersonasm10($perm10);
                $ent[0]->setCantPersonasvivienda($totalper);
                /* Guarda los valores por defecto */
                $ent[0]->setUsuarioUltimaModificacion($usuario);
                $ent[0]->setFechaUltimaModificacion($fecha);
            } 
        } catch (\Exception $e){
            //error_log($e->getMessage());
            $this->addFlash('error',$e->getMessage());
            $this->addFlash('warning_df','No se pudieron encontrar los Datos Generales algunos datos no se actulizarón en la table Datos Generales');
            //throw new NotFoundHttpException('Número de boleta ya existe o esta vacia');
            //throw $this->createAccessDeniedException();
        }
        
        $em->flush();
        
        return $this->redirect($this->generateUrl('datosgeneralesAB'));
    }    
       
    /**
     * Displays a form to create a new DatosGenerales entity.
     *
     */
    public function newABrAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $idEnc = $request->query->get('idEnc');
        $codCom = $request->query->get('codCom');
        
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        
        /* Listado de parentescos */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdParentesco p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $parentescos = $query->getResult();
        
        /* Listado de etnias */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdEtnia e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $etnias = $query->getResult();
        
        /* Listado de ocupaciones */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdOcupaciones p "
        . "order by p.descripcion";
        $query = $em->createQuery($dsql);
        $ocupaciones = $query->getResult();
        
        /* Listado de profesiones */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdProfesiones e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $profesiones = $query->getResult();
        
        return $this->render('FocalAppBundle:DatosGenerales:newABr.html.twig', array(
            'idEnc' => $idEnc,
            'codComunidad' => $codCom,
            'etnias' => $etnias, 
            'parentescos' => $parentescos,
            'ocupaciones' => $ocupaciones,
            'profesiones' => $profesiones,
        ));
    }

    /**
     * Displays a form to editAB an existing DatosGenerales entity.
     *
     */
    public function editABrAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->query->get('idEnc');
        $codCom = $request->query->get('codComunidad');
        
        $entdtg = $em->getRepository('FocalAppBundle:DatosGenerales')->find($id);
        $entdgf = $em->getRepository('FocalAppBundle:DatosGrupoFamiliar')->findBy(array('idEnc' => $id));
        $entddo = $em->getRepository('FocalAppBundle:DatosdOtros')->findBy(array('idEnc' => $id));
        $entdsg = $em->getRepository('FocalAppBundle:DatossGeneral')->findBy(array('idEnc' => $id));
        $entdfo = $em->getRepository('FocalAppBundle:DatosFuerzaOtros')->findBy(array('idEnc' => $id));
        $entddr = $em->getRepository('FocalAppBundle:DatosdRangos')->findBy(array('idEnc' => $id));
        
        /* Listado de parentescos */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdParentesco p "
        . "order by p.id";
        $query = $em->createQuery($dsql);
        $parentescos = $query->getResult();
        
        /* Listado de etnias */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdEtnia e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $etnias = $query->getResult();
        
        /* Listado de ocupaciones */
        $dsql ="select p.id, p.descripcion "
        . "from FocalAppBundle:AdOcupaciones p "
        . "order by p.descripcion";
        $query = $em->createQuery($dsql);
        $ocupaciones = $query->getResult();
        
        /* Listado de profesiones */
        $dsql ="select e.id, e.descripcion "
        . "from FocalAppBundle:AdProfesiones e "
        . "order by e.descripcion";
        $query = $em->createQuery($dsql);
        $profesiones = $query->getResult();
        
        $cntele = count($entddo);
        
        return $this->render('FocalAppBundle:DatosGenerales:editABr.html.twig', array(
            'idEnc' => $id,
            'codComunidad' => $codCom,
            'entdtg' => $entdtg,
            'entdgf' => $entdgf,
            'entddo' => ($cntele==0)?$entddo:$entddo[0],
            'entdsg' => (count($entdsg)==0)?$entdsg:$entdsg[0],
            'entdfo' => (count($entdfo)==0)?$entdsg:$entdfo[0],
            'entddr' => $entddr,
            'etnias' => $etnias,
            'parentescos' => $parentescos,
            'ocupaciones' => $ocupaciones,
            'profesiones' => $profesiones,
        ));
    }

}
