<?php
/**
 * Created by PhpStorm.
 * User: georg_000
 * Date: 3/27/2015
 * Time: 1:46 PM
 */
namespace CsvTransformer\Transformers;

interface TransformerInterface
{
    /**
     * Execute transform process
     *
     * @param array $csvFileList
     * @return array
     */
    public function transform(array $csvFileList);

    /**
     * Put required logic to see if transform can occur with the submitted data
     * @param array $csv
     * @return bool
     */
    public function canBeTransformed(array $csv);
}