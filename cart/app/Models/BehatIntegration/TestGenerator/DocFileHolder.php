<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 11/28/2018
 * Time: 5:12 PM
 */

namespace App\Models\BehatIntegration\TestGenerator;


class DocFileHolder
{
    private static $json;

    public static function load($filename): void
    {
        if (!is_file(base_path().'/'.$filename)) {
            abort(500, 'Specs file not found.');
        }
        $json = file_get_contents($filename);
        $spec = \GuzzleHttp\json_decode($json, true);
        self::$json = $spec;
    }

    public static function getRef($ref)
    {
        $path = explode('/',$ref);
        $root = array_shift($path);
        $first = array_shift($path);
        if($root !== '#' || !$first) {
            die('Unprocessable $ref = '.$ref);
        }
        $tmp = self::$json[$first];
        $last = $first;
        while($next = array_shift($path)) {
            $tmp = $tmp[$next];
            $last = $next;
        }
        return $tmp;
//        return [
//            'name' => $last,
//            'data' => $tmp
//        ];
    }

    public static function getCommonFeatures(): array
    {
        return [
            'security' => array_get(self::$json, 'security'),
            'basePath' => array_get(self::$json, 'basePath','')
        ];
    }

    public static function getTags(): array
    {
        return self::$json['tags'] ?? [];
    }

    public static function getData(): array
    {
        return self::$json;
    }
}