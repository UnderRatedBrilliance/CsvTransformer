<?php
/**
 * Created by PhpStorm.
 * User: georg_000
 * Date: 3/27/2015
 * Time: 12:53 PM
 */

namespace CsvTransformer\Transformers;


class PowerReviewTags implements TransformerInterface {

    protected $tagMap = [
        'Best Uses' => 'best_uses',
        'Primary use' => 'primary_use',
        'Pros' => 'pros',
        'Cons' => 'cons',
        'Was this a gift?' => 'was_gift',
    ];

    public function transform($csv)
    {
        $csv = $csv[0];
        $transform = [];

        //Set Column Headers
        $transform[] = [
            'review_id' => 'review_id',
            'pros' => 'pros',
            'cons' => 'cons',
            'best_uses' => 'best_uses',
            'primary_use' => 'primary_use',
            'was_gift' => 'was_gift',
        ];

        $reviews = [];

        foreach($csv as $line)
        {

            if(!isset($reviews[$line['review_id']]))
            {
                $reviews[$line['review_id']] = [
                    'review_id' => $line['review_id'],
                    'pros' => [],
                    'cons' => [],
                    'best_uses' => [],
                    'primary_use' => [],
                    'was_gift' => [],
                ];
            }

            if($this->mapTagName($line['tag_name']))
            {
                $reviews[$line['review_id']][$this->mapTagName($line['tag_name'])][] = $line['tag_value'];
            }

        }

        foreach($reviews as $review)
        {
            $transform[] = [
                'review_id' => $review['review_id'],
                'pros' => implode(',',$review['pros']),
                'cons' => implode(',',$review['cons']),
                'best_uses' => implode(',',$review['best_uses']),
                'primary_use' => implode(',',$review['primary_use']),
                'was_gift' => $this->wasGift($review['was_gift']),
            ];
        }

        return $transform;
    }

    public function mapTagName($tag)
    {
        return isset($this->tagMap[$tag])? $this->tagMap[$tag] : false;
    }

    public function wasGift($data)
    {
        foreach((array)$data as $item)
        {
            return (bool)($item == 'Yes')? true : false;
        }
    }
}