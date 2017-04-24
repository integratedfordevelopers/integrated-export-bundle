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
        $return = [
            'job_1_company' => '',
            'job_1_function' => '',
            'job_1_department' => ''
        ];

        if (empty($data['jobs'])) {
            return $return;
        }

        $job = current($data['jobs']);

        if (!empty($job['company']['$id'])) {
            if ($company = $this->dm->getRepository(Company::class)->find($job['company']['$id'])) {
                $return['job_1_company'] = $company->getName();
            }
        }

        $return['job_1_function'] = empty($job['function']) ? '' : $job['function'] ;
        $return['job_1_department'] = empty($job['department']) ? '' : $job['department'];

        return $return;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'Job 1 company',
            'Job 1 function',
            'Job 1 department'
        ];
    }
}
