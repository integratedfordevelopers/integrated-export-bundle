<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ExportBundle\Converter;

use Doctrine\ODM\MongoDB\DocumentManager;

use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Common\ContentType\ContentTypeInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class JobsConverter implements ConverterInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ContentTypeInterface $contentType)
    {
        return is_subclass_of($contentType->getClass(), Person::class);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $data)
    {
        $convert = [
            'portal' => new ConverterValue('Portal', ''),
            'job_1_company' => new ConverterValue('Job 1 company', ''),
            'job_1_function' => new ConverterValue('Job 1 function', ''),
            'job_1_department' => new ConverterValue('Job 1 department', '')
        ];

        if (empty($data['jobs'])) {
            return $convert;
        }

        $job = current($data['jobs']);

        if (!empty($job['company']['$id'])) {
            if ($company = $this->getCompany($job['company']['$id'])) {
                $convert['job_1_company']->setValue($company['name']);
                $convert['portal']->setValue(implode(',', $company['channels']));
            }
        }

        $convert['job_1_function']->setValue(empty($job['function']) ? '' : $job['function']);
        $convert['job_1_department']->setValue(empty($job['department']) ? '' : $job['department']);

        return $convert;
    }

    /**
     * @param string $id
     * @return array
     */
    protected function getCompany($id)
    {
        $query = $this->dm->createQueryBuilder(Company::class)
            ->select('name', 'channels')
            ->hydrate(false)
            ->field('id')->equals($id)
            ->getQuery();

        $result = $query->getSingleResult();

        $return = [
            'name' => isset($result['name']) ? $result['name'] : '',
            'channels' => []
        ];

        if (isset($result['channels'])) {
            foreach ($result['channels'] as $channel) {
                if (isset($channel['$id'])) {
                    $return['channels'][] = $channel['$id'];
                }
            }
        }

        return $return;
    }
}
