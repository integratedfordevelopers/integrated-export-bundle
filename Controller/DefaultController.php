<?php

namespace Integrated\Bundle\ExportBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package Integrated\ExportBundle\Controller
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class DefaultController extends Controller
{
    /**
     * @var string
     */
    protected $contentTypeClass = 'Integrated\\Bundle\\ContentBundle\\Document\\ContentType\\ContentType';

    /**
     * @var MetadataFactoryInterface
     */
    protected $metadata;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $documents = $dm->getRepository($this->contentTypeClass)->findAll();

        // Get al documentTypes
        $documentTypes = $this->getMetadata()->getAllMetadata();

        return $this->render('IntegratedExportBundle:Default:index.html.twig', array(
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ));
    }

    /**
     * @param ContentType $contentType
     * @return Response
     */
    public function exportAction(ContentType $contentType = null)
    {
        return $this->render("@IntegratedExport/Default/export.html.twig", compact('contentType'));
    }

    /**
     * @param ContentType $contentType
     * @param string      $format
     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function generateAction(ContentType $contentType, $format)
    {
        switch ($format) {
            case "xml":
                return $this->get('integrated.export_service')->generateXml($contentType);
                break;
            case "csv":
            case "xlsx":
                return $this->get('integrated.export_service')->generateTable($contentType, $format);
                break;
        }

        return $this->createNotFoundException();
    }

    /**
     * Get the metadata factory form the service container
     *
     * @return MetadataFactoryInterface
     */
    protected function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = $this->get('integrated_content.metadata.factory');
        }

        return $this->metadata;
    }
}
