<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Focal\AppBundle\Entity\DatosEducacion;
use Focal\AppBundle\Form\DatosEducacionType;

/**
 * DatosEducacion controller.
 *
 */
class DatosEducacionController extends Controller
{

    public function borrarregistroeduAction(Request $request, $param){
        $parametros = explode("&&", $param);
        $idenc = $parametros[0];
        $codDept = $parametros[1];
        $codMuni = $parametros[2];
        
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager(); 
        
        $dsql ="delete "
                    . "from FocalAppBundle:DatosEducacion dt "
                    . "where dt.codDepartamento = :departamento and dt.codMunicipio = :municipio and dt.idEnc = :idenc ";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('departamento', $codDept);
                $query ->setParameter('idenc', $idenc);
                $resultado = $query->getResult();

        if (!$resultado) {
            $response->setData(array('message' => 'false', 'razon' => 'No se encontraron registros'));
        } else {
            
            $em->flush();
            $response->setData(array('message' => 'true'));  
        }
        return $response;      
    }
    /**
     * Lists all DatosEducacion entities.
     *
     */
    public function indexAction(Request $request, $idenc)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        $entdg = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc'=>$idenc));
        $numboleta = $entdg[0]->getNumBoleta();
        $codcomunidad = $entdg[0]->getCodColonia();
        
        $session = $request->getSession();
        $session->set('_num_boleta', $numboleta);
        $session->set('_cod_comunidad', $codcomunidad);
        $codMuni = $session->get('_cod_municipio');
        $codDept = $session->get('_cod_departamento');
        
        $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->findBy(array('idEnc' => $idenc));

        if(!$entity) {
            $dsql ="select dt.id as idFamilia, dt.idEnc, dt.nombre, dt.sexo, dt.edad, 0 as grado, 0 as estudia, 0 as num "
                    . "from FocalAppBundle:DatosdFamilia dt  "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc "
                    . "and dt.edad >= 5 and dt.edad <= 23 "
                    . "order by dt.id";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entedu = $query->getResult();
        } else {
            $dsql ="select dt.idEnc, dt.idFamilia, dt.grado, dt.estudia, dt.num, fa.nombre, fa.sexo, fa.edad  "
                    . "from FocalAppBundle:DatosEducacion dt "
                    . "join FocalAppBundle:DatosdFamilia fa  with (fa.id = dt.idFamilia) "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entedu = $query->getResult();
        } 
        
        return $this->render('FocalAppBundle:DatosEducacion:index.html.twig', array(
            'entities' => $entedu,
            'idenc' => $idenc,
            'coddep' => $codDept,
            'codmun' => $codMuni,
            'numboleta' => $numboleta,
        ));
    }
    /**
     * Creates a new DatosEducacion entity.
     *
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idenc = $request->get('idenced');
        
        $session = $request->getSession();
        $codMuni = $session->get('_cod_municipio');
        $codDep = $session->get('_cod_departamento');
        $codComu = $session->get('_cod_comunidad');
        
        $usr= $this->get('security.context')->getToken()->getUser();
        $usuario = $usr->getUsername();
        $fecha = new \DateTime("now");
        
        $entities = $em->getRepository('FocalAppBundle:DatosEducacion')->findBy(Array('idEnc' => $idenc));
        
        if ($entities) {
            foreach ($entities as $ent) {
                $ent->setGrado($request->get('grado_'.$ent->getNum())); 
                $ent->setEstudia($request->get('estudia_'.$ent->getNum()));
                $ent->setUsuarioUltimaModificacion($usuario);
                $ent->setFechaUltimaModificacion($fecha);
            }
            $em->flush();
            $this->addFlash('success','Datos actualizados con exito');
            return $this->redirect($this->generateUrl('datoseducacion', array('idenc' => $idenc)));
        }
        
        for($i = 1; $i<=$request->get('numtotalreg'); $i++) {
            $entity = new DatosEducacion();
            $sequenceName = 'datos_educacion_id_seq';
            $dbConnection = $em->getConnection();
            $nextvalQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL($sequenceName);
            $newId = (int)$dbConnection->fetchColumn($nextvalQuery);

            $codCom = $codMuni . $newId;            
            $entity->setId($codCom);
            $entity->setIdEnc($idenc);
            $entity->setGrado($request->get('grado_'.$i));
            $entity->setEstudia($request->get('estudia_'.$i)); 
            $entity->setNum($request->get('num_'.$i));
            $entity->setIdFamilia($request->get('idfam_'.$i));
            
            /* Guarda los valores por defecto */
            $entity->setCodDepartamento($codDep);
            $entity->setCodMunicipio($codMuni);
            $entity->setCodComunidad($codComu);
            $entity->setUsuarioCreacion($usuario);
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaCreacion($fecha);
            $entity->setFechaUltimaModificacion($fecha);
            $em->persist($entity);
            $em->flush();
        }
        $this->addFlash('success','Datos generados con exito');
        return $this->redirect($this->generateUrl('datoseducacion', array('idenc' => $idenc)));
        
    }

    /**
     * Creates a form to create a DatosEducacion entity.
     *
     * @param DatosEducacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosEducacion $entity)
    {
        $form = $this->createForm(new DatosEducacionType(), $entity, array(
            'action' => $this->generateUrl('datoseducacion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosEducacion entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosEducacion();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosEducacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosEducacion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosEducacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosEducacion:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosEducacion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosEducacion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosEducacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosEducacion entity.
    *
    * @param DatosEducacion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosEducacion $entity)
    {
        $form = $this->createForm(new DatosEducacionType(), $entity, array(
            'action' => $this->generateUrl('datoseducacion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosEducacion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosEducacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('datoseducacion_edit', array('id' => $id)));
        }

        return $this->render('FocalAppBundle:DatosEducacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a DatosEducacion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosEducacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosEducacion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datoseducacion'));
    }

    /**
     * Creates a form to delete a DatosEducacion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datoseducacion_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
