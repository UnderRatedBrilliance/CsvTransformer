<?php

namespace CsvTransformer\Transformers;


use Exception;

class ExampleTransformer extends AbstractTransformer implements TransformerInterface {

    /**
     * Required column headers in order for the CSV file to be transformed
     *
     * @var array
     */
    protected $requiredHeaders = [
        'sku',
        'price',
        'name',
    ];
    /**
     * Transform the passed $csv array
     *
     * @param array $csvFileList
     * @return array
     * @throws Exception
     */
    public function transform(array $csvFileList)
    {
        /**
         * the canBeTransformed method should contain your validation logic for your
         * transform process and should return a boolean
         */
        if(!$this->canBeTransformed($csvFileList[0]))
        {
            throw new Exception('Supplied CSV File does not contain the required headers of sku,name, and price ');
        }

        /**
         * transform accepts an array of csv files from the CLI you will always have at least 1 csv file to process
         * which should be available at array[0]. If you would like to pass more than 1 csv to be parsed you can supply
         * checks or pass the csv files in the CLI in the correct order you would like to receive them.
         */
        $csv = array_map(function($line) {
            return [
                'sku' => $line['sku'],
                'name' => $line['name'],
                'price' => $line['price'],
            ];
        },$csvFileList[0]);

        //set column headers
        array_unshift($csv,['sku','name','price']);

        return $csv;
    }

    /**
     * @param array $csv
     * @return bool
     */
    public function canBeTransformed(array $csv)
    {
        return $this->hasHeaders($csv);
    }

    /**
     * Checks if csv contains the correct column header aka array keys
     *
     * @param array $csv
     * @return bool
     */
    public function hasHeaders(array $csv)
    {
        return (0 === count(array_diff($this->requiredHeaders,array_keys($csv[0]))));
    }
}