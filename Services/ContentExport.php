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

    /** @var int */
    private $rowNumb = 1;

    /** @var float */
    private $startTime;

    /**
     * ContentExport constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
        $this->startTime = microtime(1);
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
                    /**/
                }

                /* About 5 seconds until to throwing exception with "execution time exceeded", abort export */
                $executionTime = microtime(1) - $this->startTime;
                $allowedTime = ini_get('max_execution_time');
                if ($allowedTime - $executionTime < 5) {
                    $partialExport = $xml->addChild('PARTIAL_EXPORT');
                    $partialExport->addChild('message', 'THIS IS PARTIAL EXPORT');

                    return new Response($xml->asXML(), 200, ['Content-type' => 'text/xml']);
                }
            }
        }

        return new Response($xml->asXML(), 200, ['Content-type' => 'text/xml']);
    }

    /**
     * @param ContentType $contentType
     * @param string      $format
     * @return Response
     */
    public function generateTable(ContentType $contentType, $format)
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

        $handle = $format == 'csv' ? tmpfile() : new \PHPExcel();
        $columnNames = ['id', 'contentType', 'createdAt', 'updatedAt', 'startDate', 'endDate', 'channels'];
        foreach ($allContent as $content) {
            /* main fields*/
            $channelIds = array_map(function ($channel) { return $channel['$id']; }, $content['channels']);

            $values = [
                'id' => $content['_id'],
                'contentType' => $content['contentType'],
                'createdAt' => date('Y-m-d H:i:s', $content['createdAt']->sec),
                'updatedAt' => date('Y-m-d H:i:s', $content['updatedAt']->sec),
                'startDate' => date('Y-m-d H:i:s', $content['publishTime']['startDate']->sec),
                'endDate' => date('Y-m-d H:i:s', $content['publishTime']['endDate']->sec),
                'channels' => implode(',', $channelIds),
            ];
            /* end main fields */

            $mainFields = ['_id', 'contentType', 'createdAt', 'updatedAt', 'publishTime', 'channels'];
            foreach ($fieldNames as $name) {
                if (!in_array($name, $mainFields, true)) {
                    if (isset($content[$name]) && !is_array($content[$name]) && !is_object($content[$name])) {
                        $values[] = $content[$name];
                    } else {
                        $values[] = '';
                    }

                    if (isset($columnNames)) {
                        $columnNames[] = $name;
                    }
                }
            }

            /* add column names*/
            if (isset($columnNames)) {
                $this->addRow($columnNames, $handle, $format);
                unset($columnNames);
            }

            $this->addRow($values, $handle, $format);

            /* Until 5 seconds to throwing exception with "execution time exceeded", abort export */
            $executionTime = microtime(1) - $this->startTime;
            $allowedTime = ini_get('max_execution_time');
            if ($allowedTime - $executionTime < 5) {
                $partial = array_fill(0, count($values), 'PARTIAL_EXPORT');
                $this->addRow($partial, $handle, $format);

                return $this->getResponse($format, $handle, $contentType);
            }
        }

        return $this->getResponse($format, $handle, $contentType);
    }

    /**
     * @param $values
     * @param $handle
     * @param $format
     */
    private function addRow($values, $handle, $format)
    {
        if ($format == 'csv') {
            fputcsv($handle, $values);
        } else {
            $handle->setActiveSheetIndex(0)->fromArray($values, null, 'A'.$this->rowNumb);
            $this->rowNumb++;
        }
    }

    /**
     *
     * @param $format
     * @param $handle
     * @param $contentType
     * @return Response
     * @throws \PHPExcel_Reader_Exception
     */
    private function getResponse($format, $handle, $contentType)
    {
        if ($format == 'csv') {
            $path = stream_get_meta_data($handle);

            return new Response(file_get_contents($path['uri']), 200, ['Content-type' => 'text/csv']);
        } else {
            $path = stream_get_meta_data(tmpfile());
            $objWriter = \PHPExcel_IOFactory::createWriter($handle, 'Excel2007');
            $objWriter->save($path['uri']);

            return new Response(file_get_contents($path['uri']), 200, [
                'Content-type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment;filename="'.$contentType->getName().'.xlsx"',
            ]);
        }
    }
}
