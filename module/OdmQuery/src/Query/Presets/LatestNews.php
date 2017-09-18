<?php

namespace OdmQuery\Query\Preset;

use Entity\Document\Article;
use Entity\Util\UTC;
use OdmQuery\Query\PresetAbstract;

/**
 * Example preset query
 */
class LatestNews extends PresetAbstract
{
    /**
     * An array of terms to be used for filtering
     * @return mixed
     */
    public function getFilter()
    {
        $aWeekAgo = UTC::dateFromTimestamp(time() - (60*60*24*7));
        $dateStr = $aWeekAgo->format('Y-m-d H:i:s');

        return [
            [
                'field' => 'status',
                'type' => 'eq',
                'value' => Article::STATUS_PUBLISHED,
                'where' => 'and'
            ],
            [
                'field' => 'publishedAt',
                'type' => 'gte',
                'value' => $dateStr,
                'where' => 'and'
            ]
        ];
    }

    /**
     * An an array of fields and ordering for each field
     * @return mixed
     */
    public function getSort()
    {
        return [
            [
                'field' => 'title',
                'type' => 'field',
                'direction' => 'asc'
            ]
        ];
    }

    /**
     * An array of fields to be used in a select
     * @return array
     */
    public function getFields()
    {
        return ['sequence', 'title', 'content'];
    }
}
