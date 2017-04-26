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
            'company' => new ConverterValue('job_1_companny', 'Job 1 company', ''),
            'function' => new ConverterValue('job_1_function', 'Job 1 function', ''),
            'department' => new ConverterValue('job_1_department', 'Job 1 department', '')
        ];

        if (empty($data['jobs'])) {
            return $convert;
        }

        $job = current($data['jobs']);

        if (!empty($job['company']['$id'])) {
            $convert['company']->setValue($this->getCompanyName($job['company']['$id']));
        }

        $convert['function']->setValue(empty($job['function']) ? '' : $job['function']);
        $convert['department']->setValue(empty($job['department']) ? '' : $job['department']);

        return $convert;
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getCompanyName($id)
    {
        $query = $this->dm->createQueryBuilder(Company::class)
            ->select('name')
            ->hydrate(false)
            ->field('id')->equals($id)
            ->getQuery();

        $result = $query->getSingleResult();

        return isset($result['name']) ? $result['name'] : '';
    }
}
