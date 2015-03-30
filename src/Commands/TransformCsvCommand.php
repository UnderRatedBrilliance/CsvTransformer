<?php

namespace CsvTransformer\Commands;

use Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use SplFileObject;

class TransformCsvCommand extends Command{

    protected $delimeter = ",";

    protected $enclosure = '"';

    protected $transformerDefaultNamespace = 'CsvTransformer\\Transformers\\';

    protected $defaultTransformer = 'CsvTransformer\\Transformers\\DefaultTransformer';

    protected $transformer;

    protected $filePaths = [];

    protected $csvFiles = [];

    protected $resultsFilePath = 'results.csv';

    /**
     * Configure transform command to console
     */
    protected function configure()
    {
        $this->setName('transform')
            ->setDescription('Transform and Process CSV Files')
            ->setDefinition([
                new InputOption('delimiter','d', InputOption::VALUE_REQUIRED,' Set Delimiter default is ","'),
                new InputOption('enclosure','e', InputOption::VALUE_REQUIRED,' Set Enclosure default is """ '),
                new InputArgument('new-csv-path', InputArgument::REQUIRED, 'Path to final Transformed CSV'),
                new InputArgument('transformerClass', InputArgument::REQUIRED, 'Path to Transformer'),
                new InputArgument('csv-path', InputArgument::IS_ARRAY, 'Path to CSV(s) to be transformed'),
            ]);

    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Initialize Data from Input
        $this->init($input);

        // Create New Writer to store results in
        $writer = $this->createNewFileAndWriter();

        //Run CSV Files through Transform Process and write results to file
        $writer->insertAll($this->transformer->transform($this->getCsvFiles()));

    }

    /**
     * Initializes input from CLI
     *
     * @param InputInterface $input
     * @throws Exception
     */
    protected function init(InputInterface $input)
    {
        $this->setCsvOptions($input)
            ->setTransformer($input)
            ->setFilePaths($input)
            ->setResultsFilePath($input)
            ->setCsvFiles($this->getFilePaths());
    }

    /**
     * @param $files
     * @return mixed
     */
    protected function setCsvFiles($files)
    {
        foreach($files as $key => $file)
        {
            $files[$key] = $this->setCsvOptionsOnReader(Reader::createFromPath($file))->fetchAssoc();
        }
        $this->csvFiles = $files;

        return $this;
    }

    /**
     * Retrieves transformer class from input data, checks to see if the class can be instantiated if not add
     * default namespace to beginning of class. If still cannot be instantiated throw exception. If supplied classname
     * passes checks set to $this->transformer
     *
     * @param InputInterface $input
     * @return $this
     * @throws Exception
     */
    protected function setTransformer(InputInterface $input)
    {
        $transformer = $input->getArgument('transformerClass');

        /**
         * Checks if supplied class can be instantiated if not add default namespace to supplied class name
         */
        if(!class_exists($transformer))
        {
            $transformer = $this->transformerDefaultNamespace.$transformer;
        }


        if(!class_exists($transformer))
        {
            throw new Exception('Cannot load '. $transformer .' Class.');
        }

        $this->transformer = new $transformer;

        return $this;
    }

    /**
     * @param InputInterface $input
     * @return $this
     */
    protected function setFilePaths(InputInterface $input)
    {
        $this->filePaths = $input->getArgument('csv-path');

        return $this;
    }

    /**
     * @param InputInterface $input
     *
     * @return $this
     */
    protected function setResultsFilePath(InputInterface $input)
    {
        $this->resultsFilePath = $input->getArgument('new-csv-path');
        return $this;
    }

    /**
     * Sets CsvOptions on Reader Object
     *
     * @param Reader $csv
     * @return $this
     */
    protected function setCsvOptionsOnReader(Reader $csv)
    {
        return $csv->setDelimiter($this->delimeter)->setEnclosure($this->enclosure);
    }

    /**
     * Retrieves CsvOptions from CLI input and  sets them to object
     *
     * @param InputInterface $input
     * @return $this
     */
    protected function setCsvOptions(InputInterface $input)
    {
        //Set Delimiter Flag
        if($d = $input->getOption('delimiter'))
        {
            //Check for tab character and transform input
            if($d == '\t')
            {
                $d = "\t";
            }
            $this->delimeter = $d;
        }

        // Set Enclosure flag
        if($e = $input->getOption('enclosure'))
        {
            $this->enclosure = $e;
        }

        return $this;
    }

    /**
     * @return \League\Csv\Writer;
     */
    public function createNewFileAndWriter()
    {
        return Writer::createFromPath(new SplFileObject(getcwd().DIRECTORY_SEPARATOR.$this->resultsFilePath,'a+'),'w');
    }

    /**
     * Returns file paths
     *
     * @return array
     */
    public function getFilePaths()
    {
        return $this->filePaths;
    }

    /**
     * Returns Read and Parsed CSV Files
     *
     * @return array
     */
    public function getCsvFiles()
    {
        return $this->csvFiles;
    }


}