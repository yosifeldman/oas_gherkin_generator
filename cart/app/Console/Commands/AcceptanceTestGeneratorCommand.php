<?php

namespace App\Console\Commands;


use App\Models\BehatIntegration\TestGenerator\DocFileHolder;
use App\Models\BehatIntegration\TestGenerator\ObjectParser;
use Illuminate\Console\Command;
use Illuminate\Http\Response;

class AcceptanceTestGeneratorCommand extends Command
{
    private $outFile, $fullSpec, $result, $objectParser;
    public function __construct()
    {
        $this->signature = 'generate:tests {file : oas json file}';
        $this->description = 'Makes Gherkin scenarios out from the OAS json.';
        $this->objectParser = new ObjectParser();
        parent::__construct();
    }

    public function handle(): void
    {
        // import oas json
        $filename = $this->argument('file');
        DocFileHolder::load($filename);
        $common = DocFileHolder::getCommonFeatures();

        // create .feature file for the tag, or one for entire spec
        if($tags = DocFileHolder::getTags()) {
            foreach ($tags as $tag) {
                $feature = snake_case($tag['name']);
                $this->createFeatureFile($feature);
                $this->emitFeatureHeader($feature, 'use my app','user', array_get($tag, 'description', '[description]'), $common);

                foreach ($this->scenarioIterator(DocFileHolder::getData(), $feature) as $scenario) {
                    $this->emitScenario($feature, $this->makeScenario($common, $scenario));
                }
            }
        }
    }

    private function createFeatureFile($name): void
    {
        $dir_path = base_path().'/features/acceptance/';
        if(!is_dir($dir_path) && !mkdir($dir_path)) {
            die('Cannot create directory: '.$dir_path);
        }
        $this->outFile[$name] = fopen($dir_path."$name.feature",'w+b');
    }

    private function writeToFeatureFile($name, $txt, $blank_line = true): void
    {
        if(!$txt) {
            return;
        }
        if(\is_array($txt)) {
            $txt = implode(PHP_EOL, $txt);
        }
        $txt .= $blank_line ? PHP_EOL.PHP_EOL.PHP_EOL : '';
        fwrite($this->outFile[$name], $txt);
    }

    private function emitFeatureHeader($name, $in_order, $as, $i_want, $common = []): void
    {
        $yml = [
            "Feature: $name",
            "  In order to $in_order",
            "  As a $as",
            "  I want $i_want."
        ];
        if(!empty($common['security'])) {
            $yml = array_merge($yml,[
                PHP_EOL,
                'Background:',
                '  Given: I am authenticated as "test_user"'
            ]);
        }
        $this->writeToFeatureFile($name, implode(PHP_EOL, $yml));
    }

    private function emitScenario($name, $spec): void
    {
        $yml = [
            'Scenario: '.implode(' ', $spec['scenario']),
            '  Given '.implode(PHP_EOL.'    And ',$spec['given']),
            '  When '.implode(PHP_EOL.'    And ',$spec['when']),
            '  Then '.implode(PHP_EOL.'    And ',$spec['then']),
        ];
        $this->writeToFeatureFile($name, $yml);
    }

    private function makeScenario($common, $scenario): array
    {
        $content_type = 'application/json';
        $given = $when = $then = [];

        // given
        if(!empty($scenario['request']['parameters'])) {
            foreach($scenario['request']['parameters'] as $param) {
                if(empty($param['required'])) {
                    continue;
                }
                $given[] = $this->parseRequestParam($param);
            }
        }

        // header, query, body
        $req_header = $req_query = $req_body = $req_body_json = '';
//        if($req_header) {
//            $req_header_json = json_encode($req_header);
//            $when[] = "headers are $req_header_json";
//        }
//        if($req_query) {
//            $req_query_json = json_encode($req_query);
//            $when[] = "query is $req_query_json";
//        }

        // when
        $when[] = "request method is {$scenario['method']}";
        if(!empty($scenario['request']['requestBody']['content'][$content_type])) {
            $obj = $scenario['request']['requestBody']['content'][$content_type];

            if(!empty($obj['schema'])) {
                $parser = new ObjectParser($obj);
                $when[] = sprintf('request body is %s', json_encode($parser->schemaToDataArray($obj['schema'])));
            } else {
                $when[] = sprintf('request body is %s', json_encode($obj));
            }

            //$when = array_merge($when, $this->parseJsonContent($obj,'request body "%s" is "%s"'));
        }
        $when[] = "request path is {$common['basePath']}{$scenario['path']}";

        // then
        $then[] = "response status is {$scenario['status']}";
        if(!empty($scenario['response']['content'][$content_type])) {
            $obj = $scenario['response']['content'][$content_type];
            if(!empty($obj['schema'])) {
                $parser = new ObjectParser();
                $parser->setIsResponse(true);
                $then[] = sprintf('response body is %s', json_encode($parser->schemaToDataArray($obj['schema'])));
            } else {
                $then[] = sprintf('response body is %s', json_encode($obj));
            }
        }

        $scenario = [$scenario['request']['summary']];
        return compact('scenario', 'given', 'when', 'then');
    }

    private function scenarioIterator($spec, $tag = ''): \Iterator
    {
        foreach(array_get($spec, 'paths', []) as $path => $methods) {
            foreach($methods as $method => $request) {
                if(empty($request['tags']) || !\in_array($tag, $request['tags'], true)) {
                    continue;
                }
                $responses = $request['responses'];
                foreach($responses as $status => $response) {
                    if($status >= 400) {
                        continue; // skip un-happy scenarios for now
                    }
                    yield [
                        'path' => $path,
                        'method' => $method,
                        'request' => $request,
                        'status' => $status,
                        'response' => $response
                    ];
                }
            }
        }
    }

    private function parseJsonContent($content, $phrase = 'json body has "%s"'): array
    {
        $resp = [];
        if($content['schema']['$ref'] && ($ref = DocFileHolder::getRef($content['schema']['$ref']))) {
            $resp = array_merge($resp, $this->objectParser->parse($ref, $phrase));
            //$resp = $this->objectParser->toDataArray($ref);
        }
        return $resp;
    }

    private function parseRequestParam($param): string
    {
        if(!$param['explode']) {
            preg_match('/<([^()]+)>/', $param['description'], $matches);
            $model = $matches[1] ?? false;
            if($model) {
                // try to parse full model data from components
                if($ref = DocFileHolder::getRef('#/components/schemas/'.$model)) {
                    $data = $this->objectParser->schemaToDataArray($ref);
                    return sprintf('The model "%s" exists with data %s', $model, json_encode($data));
                }
                return sprintf('The model "%s" exists with "%s" = "%s"', $model, $param['name'], $param['schema']['example']);
            }
            return sprintf('The "%s" is "%s"', $param['name'], $param['schema']['example']);
        }
        die('Unsupported request param type');
    }
}