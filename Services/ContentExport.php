<?php
namespace Integrated\Bundle\ExportBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContentExport
 * @package Integrated\ExportBundle\Services
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class ContentExport
{
    /** @var DocumentManager */
    private $dm;

    /**
     * ContentExport constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param ContentType $contentType
     * @return Response
     */
    public function generateXml(ContentType $contentType)
    {
        $allContent = $this->dm
            ->createQueryBuilder('IntegratedContentBundle:Content\Content')
            ->hydrate(false)
            ->field('contentType')->equals($contentType->getId())
            ->getQuery()->execute();

        $fieldNames = [];
        foreach ($contentType->getFields() as $field) {
            $fieldNames[$field->getName()] = $field->getName();
        }

        $xml = new \SimpleXMLElement('<xml/>');
        $contents = $xml->addChild('content');

        $mainFields = ['_id', 'contentType', 'createdAt', 'updatedAt', 'publishTime', 'channels', 'class'];

        foreach ($allContent as $content) {
            /* main fields*/
            /** @var Content $content */
            $node = $contents->addChild($content['contentType']);
            $node->addAttribute('id', $content['_id']);
            $node->addChild('contentType', $content['contentType']);
            $node->addChild('createdAt', date('Y-m-d H:i:s', $content['createdAt']->sec));
            $node->addChild('updatedAt', date('Y-m-d H:i:s', $content['updatedAt']->sec));

            $publishTime = $node->addChild('publishTime');
            $publishTime->addChild('startTime', date('Y-m-d H:i:s', $content['publishTime']['startDate']->sec));
            $publishTime->addChild('endTime', date('Y-m-d H:i:s', $content['publishTime']['endDate']->sec));

            $channels = $node->addChild('channels');
            foreach ($content['channels'] as $channel) {
                $channels->addChild('channel', $channel['$id']);
            }
            /* main fields end */

            foreach ($content as $name => $value) {
                if (!in_array($name, $mainFields) && is_scalar($value)) {
                    $node->addChild($name, $value);
                }

                /* @TODO add data from Embed and Relation Classes */
                if (is_array($value)) {

                }
            }
        }

        return new Response($xml->asXML(), 200, ['Content-type' => 'text/xml']);
    }

    public function generateCSV(ContentType $contentType)
    {
        $allContent = $this->dm
            ->createQueryBuilder('IntegratedContentBundle:Content\Content')
            ->hydrate(false)
            ->field('contentType')->equals($contentType->getId())
            ->getQuery();

        $fieldNames = [];
        foreach ($contentType->getFields() as $field) {
            $fieldNames[$field->getName()] = $field->getName();
        }
        unset($fieldNames['class']);

        $handle = tmpfile();
        $path = stream_get_meta_data($handle);
        $columnNames = ['id', 'contentType', 'createdAt', 'updatedAt', 'startDate', 'endDate', 'channels'];
        foreach ($allContent as $content) {

            /* main fields*/
            $channelIds = array_map(function($channel) {return $channel['$id'];}, $content['channels']);
            $values = [
                'id' => $content['_id'],
                'contentType' => $content['contentType'],
                'createdAt' => date('Y-m-d H:i:s', $content['createdAt']->sec),
                'updatedAt' => date('Y-m-d H:i:s', $content['updatedAt']->sec),
                'startDate' => date('Y-m-d H:i:s', $content['publishTime']['startDate']->sec),
                'endDate' => date('Y-m-d H:i:s', $content['publishTime']['endDate']->sec),
                'channels' => implode(',', $channelIds)
            ];
            /* end main fields */

            $mainFields = ['_id', 'contentType', 'createdAt', 'updatedAt', 'publishTime', 'channels'];
            foreach ($fieldNames as $name) {
                if (!in_array($name, $mainFields, true)) {
                    if (isset($content[$name]) && !is_array($content[$name]) && !is_object($content[$name])) {
                        $values[] = $content[$name];
                    }
                    else {
                        $values[] = '';
                    }

                    if (isset($columnNames)) {
                        $columnNames[] = $name;
                    }
                }
            }

            if (isset($columnNames)) {
                fputcsv($handle, $columnNames);
                unset($columnNames);
            }

            fputcsv($handle, $values);
        }

        return new Response(file_get_contents($path['uri']), 200, ['Content-type' => 'text/csv']);
    }

    public function generateXLSX(ContentType $contentType)
    {

        return new Response(file_get_contents($path['uri']), 200, ['Content-type' => 'text/csv']);
    }

}
