<?php
/**
 * Created by PhpStorm.
 * User: georg_000
 * Date: 3/27/2015
 * Time: 12:53 PM
 */

namespace CsvTransformer\Transformers;


use Exception;

class PrIdMap extends AbstractTransformer implements TransformerInterface{

    protected $requiredHeaders = [
        'ca_pr_id',
        'ca_pr_list',
        'sku',
    ];

    public function transform(array $csv)
    {
        $csv = $csv[0];
        $transform = [];

        if(!$this->canBeTransformed($csv))
        {
           throw new Exception('CSV File does not have the required Headers');
        }

        //Set Column Headers
        $transform[] = ['sku'=> 'sku','pr_id'=>'pr_id'];

        //Set Pr_Id if pr_list is greater than or eq to 3
        $transform = array_merge($transform,array_map(function($v){

            $pr_id = false;

            if($v['ca_pr_id'] && (integer)substr($v['ca_pr_list'],0,1) <= 3 )
            {
                $pr_id = $v['ca_pr_id'];
            }

            return [
                'sku' => $v['sku'],
                'pr_id' => $pr_id,
            ];
        },$csv));

        //Filter out Null Values
        return array_filter($transform, function($v){

            return $v['pr_id'] && $v['sku'] ? true : false;
        });
    }

    public function canBeTransformed(array $csv)
    {
        return (0 === count(array_diff($this->requiredHeaders,array_keys($csv[0]))));
    }

}