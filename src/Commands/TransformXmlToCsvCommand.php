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

class TransformXmlToCsvCommand extends TransformCsvCommand{

    protected $files = [];

    /**
     * Configure transform command to console
     */
    protected function configure()
    {
        $this->setName('transform-xml')
            ->setDescription('Transform and Process XML File(s) to CSV')
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
        $writer->insertAll($this->transformer->transform($this->getFiles()));

    }

    /**
     * Initializes input from CLI
     *
     * @param InputInterface $input
     * @throws Exception
     */
    protected function init(InputInterface $input)
    {
        parent::init($input);

        $this->setFiles($this->getFilePaths());

    }

    /**
     * @param $files
     * @return mixed
     */
    protected function setFiles($files)
    {
        foreach($files as $key => $file)
        {
            $files[$key] = simplexml_load_file($file);
        }
        $this->files = $files;

        return $this;
    }


    /**
     * Returns Read and Parsed CSV Files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }


}