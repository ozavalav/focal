<?php

namespace Focal\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Focal\AppBundle\Entity\DatosdRangos;
use Focal\AppBundle\Form\DatosdRangosType;

/**
 * DatosdRangos controller.
 *
 */
class DatosdRangosController extends Controller
{

    /**
     * Lists all DatosdRangos entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FocalAppBundle:DatosdRangos')->findAll();

        return $this->render('FocalAppBundle:DatosdRangos:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new DatosdRangos entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new DatosdRangos();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('datosdrangos_show', array('id' => $entity->getId())));
        }

        return $this->render('FocalAppBundle:DatosdRangos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a DatosdRangos entity.
     *
     * @param DatosdRangos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DatosdRangos $entity)
    {
        $form = $this->createForm(new DatosdRangosType(), $entity, array(
            'action' => $this->generateUrl('datosdrangos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new DatosdRangos entity.
     *
     */
    public function newAction()
    {
        $entity = new DatosdRangos();
        $form   = $this->createCreateForm($entity);

        return $this->render('FocalAppBundle:DatosdRangos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a DatosdRangos entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdRangos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdRangos:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing DatosdRangos entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdRangos entity.');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('FocalAppBundle:DatosdRangos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'idenc' => $entity->getIdEnc(),
            'rngsel' => $entity->getRango(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a DatosdRangos entity.
    *
    * @param DatosdRangos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(DatosdRangos $entity)
    {
        $form = $this->createForm(new DatosdRangosType(), $entity, array(
            'action' => $this->generateUrl('datosdrangos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn-success')));

        return $form;
    }
    /**
     * Edits an existing DatosdRangos entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($id);
        $rangoAct = $entity->getRango();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DatosdRangos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        
        $dsql ="select dt.id "
                . "from FocalAppBundle:DatosdRangos dt "
                . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and dt.rango = :rango and dt.rango <> :rangoAct ";
            $query = $em->createQuery($dsql);
            $query ->setParameter('municipio', $entity->getCodMunicipio());
            $query ->setParameter('idenc', $entity->getIdEnc());
            $query ->setParameter('rango', $entity->getRango());
            $query ->setParameter('rangoAct', $rangoAct);
            $entRnge = $query->getResult();
        
        if($entRnge) {
            $this->addFlash('warning','Rango de Edad ya esta ingresado para esta familia');
            return $this->redirect($this->generateUrl('datosdfamilia', array('idenc' => $entity->getIdEnc())));
        }        

        if ($editForm->isValid()) {
            
            if ($entity->getRango() !== null) {
                $rango = $entity->getRango()->getId();
                $entity->setRango($rango);
            }
            /* Guarda los valores por defecto */
            $usr= $this->get('security.context')->getToken()->getUser();
            $usuario = $usr->getUsername();
            $fecha = new \DateTime("now");
            $entity->setUsuarioUltimaModificacion($usuario);
            $entity->setFechaUltimaModificacion($fecha);
            
            /* Validación de las cantidades */
            $idenc = $entity->getIdEnc();
            $codMuni = $entity->getCodMunicipio();
            $cantp = $entity->getCantPersonas();
            $canth = $entity->getCantHombres();
            $cantm = $entity->getCantMujeres();
            /* Cantidad personas en vivienda p.9 */
            $entdatogeneral = $em->getRepository('FocalAppBundle:DatosGenerales')->findBy(array('idEnc' => $idenc, 'codMunicipio' => $codMuni ));
            $cantfamilia = $entdatogeneral[0]->getcantPersonasvivienda();

            /* Cantidad de personas H/M que ingresas en por rango de edad  p.13 */
            $dsql ="select sum(dt.cantPersonas) as cantpersonas, sum(dt.cantHombres) as canthombres, sum(dt.cantMujeres) as cantmujeres "
                    . "from FocalAppBundle:DatosdRangos dt "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and dt.id <> :idactual";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $query ->setParameter('idactual', $entity->getId());
                $entities = $query->getResult();
            $cantingresada = $entities[0]['cantpersonas'];
            $cantHombresingresados = $entities[0]['canthombres'];
            $cantMujeresingresadas = $entities[0]['cantmujeres'];

            /* Cantidad de personas de la familia viven en la vivienda por sexo p.12*/
            $dsql ="select sum(case when dt.sexo=1 then 1 else 0 end) as hombres,"
                    . "sum(case when dt.sexo=2 then 1 else 0 end) as mujeres "
                    . "from FocalAppBundle:DatosdFamilia dt "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc";
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entities = $query->getResult();
            $canthombres = $entities[0]['hombres'];
            $cantmujeres = $entities[0]['mujeres'];  
            
            /* Validación por rango ingresado, que las edades de los familiares sean iguales 
             * a la cantidad en los rangos ingresados */
            // definir el rango
            $valrng = $entity->getRango();
            switch ($valrng) {
                case 1: $strrango = " dt.edad <= 1 "; break;
                case 2: $strrango = " dt.edad >= 1 and dt.edad <= 4 "; break;
                case 3: $strrango = " dt.edad >= 5 and dt.edad <= 6 "; break; 
                case 4: $strrango = " dt.edad >= 7 and dt.edad <= 12 "; break;  
                case 5: $strrango = " dt.edad >= 13 and dt.edad <= 15 "; break;  
                case 6: $strrango = " dt.edad >= 16 and dt.edad <= 18 "; break;  
                case 7: $strrango = " dt.edad >= 19 and dt.edad <= 23 "; break;
                case 8: $strrango = " dt.edad >= 24 and dt.edad <= 30 "; break;
                case 9: $strrango = " dt.edad >= 31 and dt.edad <= 40 "; break;
                case 10: $strrango = " dt.edad >= 41 and dt.edad <= 50 "; break;
                case 11: $strrango = " dt.edad >= 51 and dt.edad <= 64 "; break;
                case 12: $strrango = " dt.edad >= 65 "; break;
            }
            $dsql ="select count(dt.edad) as cantpersonas "
                    . "from FocalAppBundle:DatosdFamilia dt "
                    . "where dt.codMunicipio = :municipio and dt.idEnc = :idenc and "
                    . $strrango;
                $query = $em->createQuery($dsql);
                $query ->setParameter('municipio', $codMuni);
                $query ->setParameter('idenc', $idenc);
                $entities = $query->getResult();
            $cantenrango = $entities[0]['cantpersonas'];
            
            if($cantp != $cantenrango) {
                $this->addFlash('warning','FOCAL: Cantidad familiares en este rango de edad no son iguales '.$cantenrango. ' modifique las cantidades.');
            } else if (($cantingresada + $cantp) <= $cantfamilia && ($cantHombresingresados + $canth) <= $canthombres && ($cantMujeresingresadas + $cantm) <= $cantmujeres) {
                $em->flush();
                return $this->redirect($this->generateUrl('datosdfamilia', array('idenc' => $entity->getIdEnc())));
            } else {
                $this->addFlash('warning','FOCAL: Cantidad familiares debera se menor o igual a '.$cantfamilia. ' modifique las cantidades.');
            }   
            return $this->render('FocalAppBundle:DatosdRangos:edit.html.twig', array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
                'idenc' => $entity->getIdEnc(),
                'rngsel' => $entity->getRango(),
            ));
        }
    }
    /**
     * Deletes a DatosdRangos entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FocalAppBundle:DatosdRangos')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find DatosdRangos entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('datosdrangos'));
    }

    /**
     * Creates a form to delete a DatosdRangos entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('datosdrangos_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
