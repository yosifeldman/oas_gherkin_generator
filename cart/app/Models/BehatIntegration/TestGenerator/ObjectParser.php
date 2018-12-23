<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 11/28/2018
 * Time: 4:23 PM
 */

namespace App\Models\BehatIntegration\TestGenerator;


class ObjectParser
{
    private $data, $isResponse = false;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @param bool $isResponse
     */
    public function setIsResponse(bool $isResponse): void
    {
        $this->isResponse = $isResponse;
    }

    public function toDataArray($obj = null): array
    {
        $resp = [];
        if(!$obj) {
            $obj = $this->data;
        }
        if(!empty($obj['properties'])) {
            $required = !empty($obj['required']) ? array_flip($obj['required']) : [];
            foreach($obj['properties'] as $property => $details) {

                if($required && !isset($required[$property])) {
                    continue;
                }
                $resp[$property] = $this->schemaToDataArray($details);
            }
        } elseif(!empty($obj['allOf'])) {
            foreach($obj['allOf'] as $sub_ref) {
                $tmp = DocFileHolder::getRef($sub_ref['$ref']);
                $resp = array_merge($resp, $this->toDataArray($tmp));
            }
        }

        return $resp;
    }

    public function schemaToDataArray($schema)
    {
        if(isset($schema['$ref'])) {
            $schema = DocFileHolder::getRef($schema['$ref']);
            if(empty($schema['type'])) {
                return $this->toDataArray($schema);
            }
        }
        switch($schema['type']) {
            case 'string':
            case 'integer':
            case 'number':
                $resp = $this->isResponse ? $schema['type'] : $schema['example'];
                break;
            case 'boolean':
                if($this->isResponse) {
                    $resp = $schema['type'];
                } else {
                    $resp = (bool) array_get($schema,'example', true);
                }
                break;
            case 'object':
                $resp = $this->toDataArray($schema);
                break;
            case 'array':
                $resp = [];
                foreach($schema['items'] as $ind => $item) {
                    if($ind === '$ref') {
                        $item = DocFileHolder::getRef($item);
                    }
                    $resp[] = $this->toDataArray($item);
                }
                break;
            default:
                die('Unsupported object type.');
        }
        return $resp;
    }

    public function parse($obj = null, $phrase = '')
    {
        $resp = [];
        if(!$obj) {
            $obj = $this->data;
        }
        if(!empty($obj['properties'])) {
            $required = !empty($obj['required']) ? array_flip($obj['required']) : [];
            foreach($obj['properties'] as $property => $details) {

                if($required && !isset($required[$property])) {
                    continue;
                }
                $tmp = $this->parseSchema($details, $property, $phrase);
                $resp[] = sprintf($phrase, $property, json_encode($tmp));
            }
        } elseif(!empty($obj['allOf'])) {
            foreach($obj['allOf'] as $sub_ref) {
                $tmp = DocFileHolder::getRef($sub_ref['$ref']);
                $resp = array_merge($resp, $this->parse($tmp, $phrase));
            }
        }
        return $resp;
    }

    public function parseSchema($schema = null, $name = '', $phrase = '')
    {
        $resp = [];
        if(!$schema) {
            $schema = $this->data;
        }
        if(isset($schema['$ref'])) {
            $resp[] =  sprintf($phrase, $name, json_encode($this->parse(DocFileHolder::getRef($schema['$ref']), $phrase)));
            return $resp;
        }
        switch($schema['type']) {
            case 'string':
            case 'integer':
            case 'number':
                $resp[] = sprintf($phrase, $name, $schema['example']);
                break;
            case 'boolean':
                $resp[] = sprintf($phrase, $name, (int) array_get($schema,'example', true));
                break;
            case 'object':
                $resp[] = sprintf($phrase, $name, json_encode($this->parse($schema, $phrase)));
                break;
            case 'array':
                $arr = [];
                foreach($schema['items'] as $ind => $item) {
                    if($ind === '$ref') {
                        $item = DocFileHolder::getRef($item);
                    }
                    $arr[] = $this->parse($item, $phrase);
                }
                $resp[] = sprintf($phrase, $name, json_encode($arr));
                break;
            default:
                die('Unsupported object type.');
        }
        return $resp;
    }
}