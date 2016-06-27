<?php

namespace CsvTransformer\Transformers;


use Exception;

class PowerReviewsXmlTransformer extends AbstractTransformer implements TransformerInterface {


    /**
     * Transform the passed $file array
     *
     * @param array $files
     * @return array
     * @throws Exception
     */
    public function transform(array $files)
    {
        /**
         * the canBeTransformed method should contain your validation logic for your
         * transform process and should return a boolean
         */
        if(!$this->canBeTransformed($files))
        {
            throw new Exception('Xml File cannot be transformed');
        }

        $xmlDump = json_decode(json_encode($files[0]),TRUE);


        //var_dump($xmlDump);die();
        $reviews = [];
        foreach($xmlDump['product'] as $product)
        {

            foreach($product['reviews']['fullreview'] as $review)
            {
                /*foreach((array)$review as $key => $value)
                {
                    echo $key."\n";
                }
                continue;*/

                if(!isset($review['id']))
                {
                    if(isset($product['reviews']['fullreview']['id']))
                    {
                        $review = $product['reviews']['fullreview'];
                    } else {
                        continue;
                    }

                }
                $reviews[$review['id']] = $this->transformReview($review,$product);

            }
        }





        //set column headers
        array_unshift($reviews,['review_id',
            'created_at',
            'status',
            'page_id',
            'name',
            'title',
            'comments',
            'bottom_line',
            'rating',
            'location',
            'nickname',
            'email',
            'helpful_count',
            'not_helpful_count',
            'total_helpful',
            'pros',
            'cons',
            'best_uses',
            'was_gift']
        );

        return $reviews;
    }

    /**
     * @param array $csv
     * @return bool
     */
    public function canBeTransformed(array $csv)
    {
        return true;
    }

    /**
     * Parses taggroup array for a particular tag type
     *
     * @param $tags
     * @param $type
     * @return string
     */
    public function getTags($review,$type)
    {
        //Check if taggroup key exists
        if(!isset($review['taggroup']))
        {
            return '';
        }

        //Set tags
        $tags = (array) $review['taggroup'];

        //Check if tags array is empty return blank value
        if(empty($tags))
        {
            return '';
        }

        // If only one tag is contained in taggroup there should be an attributes key
        if(isset($tags['@attributes']))
        {
            //if not set to type return blank value
            if(!$tags['@attributes']['key'] == $type)
            {
                return '';
            }

            if(!is_array($tags['tag']))
            {
                return $tags['tag'];
            }

            return implode(',',$tags['tag']);
        }


        //Process multiple tags
        foreach($tags as $tag)
        {

           if(!isset($tag['@attributes']))
            {
                var_dump($tags);die();
            }

            if($tag['@attributes']['key'] == $type)
            {
                if(!is_array($tag['tag']))
                {
                    return $tag['tag'];
                }

                return implode(',',$tag['tag']);
            }
        }
        return '';
    }

    public function getReviewerEmail($review)
    {
        if(isset($review['email_address_from_user']))
        {
            return $review['email_address_from_user'];
        }

        if(isset($review['email_address_from_merchant']))
        {
            return $review['email_address_from_merchant'];
        }

        return '';
    }

    public function transformReview($review,$product)
    {
        return [
            'review_id' => $review['id'],
            'created_at' => date_format(new \DateTime($review['createddate'], new \DateTimeZone('America/Los_Angeles')),'Y-m-d') ,
            'status' => $review['status'],
            'page_id' => $product['pageid'],
            'name' => $product['name'],
            'title' => $review['headline'],
            'comments' => $review['comments'],
            'bottom_line' => (isset($review['bottom_line']) && $review['bottom_line'] == 'recommended')? true : false,
            'rating' => (int)$review['overallrating'],
            'location' => $review['location'],
            'nickname' => $review['nickname'],
            'email' => $this->getReviewerEmail($review),
            'helpful_count' => (int) $review['helpfulvotes'],
            'not_helpful_count' => (int) $review['nothelpfulvotes'],
            'total_helpful' => (int) $review['helpfulvotes'] + (int) $review['nothelpfulvotes'],
            'pros' => $this->getTags($review,'pros'),
            'cons'=> $this->getTags($review,'cons'),
            'best_uses' => $this->getTags($review,'bestuses'),
            //'primary_use' => $this->getTags($review['taggroup'],'pros'),
            'was_gift' => ($this->getTags($review,'wasthisagift') == 'Yes')? true : false,
        ];
    }

}