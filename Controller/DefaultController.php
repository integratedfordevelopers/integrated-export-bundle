<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ExportBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ExportBundle\EventListener\ConfigureMenuSubscriber;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DefaultController
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class DefaultController extends Controller
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_EXPORT = 'ROLE_EXPORT';

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
        $this->denyAccessUnlessGranted([self::ROLE_ADMIN, self::ROLE_EXPORT]);

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
        $this->denyAccessUnlessGranted([self::ROLE_ADMIN, self::ROLE_EXPORT]);

        return $this->render("@IntegratedExport/Default/export.html.twig", compact('contentType'));
    }

    /**
     * @param ContentType $contentType
     * @param string      $format
     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function generateAction(ContentType $contentType, $format)
    {
        $this->denyAccessUnlessGranted([self::ROLE_ADMIN, self::ROLE_EXPORT]);

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
