<?php

namespace App\Console\Commands;

use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use SoapBox\Formatter\Formatter;

class UsersReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a users report';

    private $serviceUrl;
    private $folderName;
    private $outputFilename;


    /**
     * Create a new command instance.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $today = new DateTime();
        $this->serviceUrl = 'https://jsonplaceholder.typicode.com/users';
        $this->folderName = 'reports';
        $this->outputFilename = 'users-report_' . $today->format('Ymd') . '.csv';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : void
    {
        $filename = $this->ask('Indicates the file path without the .xml extension');
        $filename = $this->parseFileNameExtension($filename);
        $xml = [];
        $continueWithoutXml = true;

        if (file_exists($filename . '.xml')) {
            $xmlFile = new SimpleXMLElement($filename . '.xml', 0, true);

            $childNodeName = $xmlFile->children()->getName();

            $xml = $this->convertXMLtoArray($xmlFile, $childNodeName);

        } else {
            $continueWithoutXml = $this->confirm('The file does not exists. Do you want to continue anyway?');
        }

        if ($continueWithoutXml){
            $jsonObj = $this->curlConnect($this->serviceUrl);

            $json = $this->convertJSONtoArray($jsonObj);

            $csv = array_merge($xml, $json);

            $formatter = Formatter::make($csv, Formatter::ARR);
            $file = $formatter->toCsv("\r\n", ',');
            $this->saveFile($file);
            $this->table(['Name', 'Email', 'Phone', 'Company'],$csv);
        }
    }

    /**
     * Save file in storage/reports
     * @param $file
     */
    private function saveFile($file)
    {
        $folderExists = Storage::disk('local')->exists($this->folderName);
        $path = $this->folderName . '/' . $this->outputFilename;

        if (!$folderExists)
            Storage::makeDirectory($this->folderName);

        Storage::disk('local')->put($path, $file);
    }

    /**
     * Fix extension for xml file.
     * @param $filename
     * @return mixed
     */
    private function parseFileNameExtension($filename)
    {
        $filename = explode('.xml', $filename);
        return $filename[0];
    }

    /**
     * Create an array from the received json with the necessary nodes for the csv.
     * @param $jsonContent
     * @return array
     */
    public function convertJSONtoArray($jsonContent)
    {
        $jsonArr = [];

        foreach ($jsonContent as $node) {
            $json = [];
            $json['name'] = (string)$node['name'];
            $json['email'] = (string)$node['email'];
            $json['phone'] = (string)$node['phone'];
            $json['company'] = (string)$node['company']['name'];
            $jsonArr[] = $json;
            unset($json);
        }
        return $jsonArr;
    }

    /**
     * Create an array from the xml
     * @param $xmlContent
     * @param $childNode
     * @return array
     */
    private function convertXMLtoArray($xmlContent, $childNode)
    {
        $xml = [];
        foreach ($xmlContent->$childNode as $node) {
            $readingsArr = [];
            $attributesOfReadingNodes = $node->attributes();
            $readingsArr['name'] = (string)$attributesOfReadingNodes->name;
            $readingsArr['email'] = trim((string)$node);
            $readingsArr['phone'] = (string)$attributesOfReadingNodes->phone;
            $readingsArr['company'] = (string)$attributesOfReadingNodes->company;
            $xml[] = $readingsArr;
            unset($readingsArr);
        }
        return $xml;
    }

    /**
     * Function for connect with the webservice
     * @param $url
     * @return mixed
     */
    private function curlConnect($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $obj = json_decode($result, true);

        return $obj;
    }
}
