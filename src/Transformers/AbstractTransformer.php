<?php

namespace CsvTransformer\Transformers;


use Exception;

abstract class AbstractTransformer implements TransformerInterface {

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
        if(!$this->canBeTransformed($csvFileList))
        {
            throw new Exception('Supplied CSV File(s) cannot be transformed');
        }

        /**
         * transform accepts an array of csv files from the CLI you will always have at least 1 csv file to process
         * which should be available at array[0]. If you would like to pass more than 1 csv to be parsed you can supply
         * checks or pass the csv files in the CLI in the correct order you would like to receive them.
         */
        $csv = $csvFileList[0];

        return $csv;
    }

    /**
     * @param array $csv
     * @return bool
     */
    public function canBeTransformed(array $csv)
    {
        return true;
    }

}