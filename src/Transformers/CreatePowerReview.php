<?php
/**
 * Created by PhpStorm.
 * User: georg_000
 * Date: 3/27/2015
 * Time: 12:53 PM
 */

namespace CsvTransformer\Transformers;


use League\Csv\Reader;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use CsvTransformer\Transformers\PrIdMap;

class CreatePowerReview extends AbstractTransformer implements TransformerInterface
{
    protected $orders;

    protected $itemsList;

    /**
     * Power Reviews Sku Map
     * @var
     */
    protected $prIdMap = [];

    protected $bundleSkuRegex = "/((?:E|PATM|PAAR|PAOA|PAB|PACS|PAF|PALC|PAMA|T|ODIA|OFIN|OFIL)-..-.*?)(?:-(?:E|PATM|PAAR|PAOA|PAB|PACS|PAF|PALC|PAMA|T|ODIA|OFIN|OFIL)-)+?/";

    /**
     *  Requires the following CSV FIles
     * @param array $csvFiles
     * @return array
     */
    public function transform(array $csvFiles)
    {
        //Initialize Transform Perform Validation Checks
        $this->transformInit($csvFiles);


        $transform = [];

        //Set Column Headers
        $transform[] = ['order_id','email','first_name','last_name','page_id'];

        foreach($this->orders as $line)
        {
            $matches = [];
            if(preg_match($this->bundleSkuRegex,$line['Item SKU'],$matches))
            {
               $line['Item SKU'] = $matches[1];
            }

            if($this->getPrId($line['Item SKU']) && strpos($line['Customer Email'],'amazon') === false)
            {
                $transform[strtolower($line['Customer Email'].'-'.$this->getPrId($line['Item SKU']))] = [
                    'order_id' => $line['Order Number'],
                    'email' => $line['Customer Email'],
                    'first_name' => $line['Customer Name'],
                    'last_name' => $line['Customer Name'],
                    'page_id' => $this->getPrId($line['Item SKU']),
                ];
            }

        }


        $headers = array_shift($transform);
        ksort($transform);

        array_unshift($transform,$headers);
        return $transform;
    }

    public function transformInit(array $csvFiles)
    {
        $this->orders = $csvFiles[0];
        $this->itemsList = $csvFiles[1];
    }

    public function getPrId($sku)
    {
        $this->issetPrMap();

        return isset($this->prIdMap[$sku]) ? $this->prIdMap[$sku] : false;
    }


    public function issetPrMap()
    {
        if(empty($this->prIdMap))
        {
            $this->prIdMap = $this->generatePrIdMap();
        }
    }

    /**
     * Requires that pr_id_map be available
     *
     * @return array
     */
    public function generatePrIdMap()
    {

        $map = (new PrIdMap)->transform([$this->itemsList]);

        $output = [];
        foreach($map as $item)
        {
            //Empty value continue
            if(!$item['sku']) continue;

            $output[$item['sku']] = $item['pr_id'];
        }
        return $output;

    }
}