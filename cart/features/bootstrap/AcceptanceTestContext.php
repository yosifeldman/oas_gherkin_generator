<?php


use \App\Models\BehatIntegration\Helper;
use \Illuminate\Database\Eloquent\Model;

class AcceptanceTestContext extends MicroServiceContext
{
    protected $pathParams = [], $requestData, $requestMethod, $requestUrl, $models;

    /**
     * @When /^request method is (\w+)$/
     * @param $method
     */
    public function requestMethodIs($method): void
    {
        $this->requestMethod = $method;
    }

    /**
     * @When /^request path is (.+)$/
     * @param $url
     */
    public function requestPathIs($url): void
    {
        if(preg_match_all('/{.+}/',$url, $params)) {
            foreach($params as $param) {
                $url = str_replace('{'.$param.'}',$this->pathParams[$param],$url);
            }
        }
        $this->json($this->requestMethod, $url, $this->requestData ?? []);
    }

    /**
     * @When /^request body is ([\[\{].+[\]\}])$/
     * @param $json
     */
    public function requestBodyIs($json): void
    {
        $this->requestData = Helper::decodeJson($json);
    }

    /**
     * @Given /^The "([^"]+)" is "([^"]+)"$/
     * @param $name
     * @param $value
     */
    public function requestParameterIs($name, $value): void
    {
        $this->pathParams[$name] = $value;
    }

    /**
     * @Given /^The model "([^"]+)" exists with data ([\[\{].+[\]\}])$/
     * @param $modelName
     * @param $json
     *
     * @throws Exception
     */
    public function theModelExistsWithData($modelName, $json): void
    {
        $fullName = "App\\Models\\$modelName";
        \Webmozart\Assert\Assert::classExists($fullName);

        /** @var Model $model */
        $model = new $fullName();
        \Webmozart\Assert\Assert::isInstanceOf($model, Model::class);

        // set fields
        $data = Helper::decodeJson($json);
        foreach($data as $key => $val) {
            if((!\is_array($val) && !\is_object($val)) || !method_exists($model, $key)) {
                $model->$key = $val;
            } elseif($model->$key() instanceof \Jenssegers\Mongodb\Relations\EmbedsOneOrMany) {
                $model->$key()->create($val);
            } else {
                throw new Exception("Unsupported relationship type $fullName.$key");
            }
        }

        \Webmozart\Assert\Assert::true($model->save(), "Failed to create model $fullName");

        $this->models[$modelName] = $model;
    }

    /**
     * @Given /^The "([^"]+)" exists with "([^"]+)" = "([^"]+)"$/
     * @param $modelName
     * @param $key
     * @param $value
     */
    public function theModelExistsWithId($modelName, $key, $value): void
    {
        $fullName = "App\\Models\\$modelName";
        \Webmozart\Assert\Assert::classExists($fullName);

        /** @var Model $model */
        $model = new $fullName();
        \Webmozart\Assert\Assert::isInstanceOf($model, Model::class);

        $this->pathParams[$key] = $value;

        // create the new model for testing
        $keyName = $model->getKeyName();


//        $content = $this->response->getContent();
//        $result = Helper::decodeJson($content);
//
//        $keyName = $model->getKeyName();
//        if($id = array_get($result, 'data.'.$keyName)) {
//            $this->$modelName = new $fullName(array_get($result,'data'));
//            $this->$modelName->$keyName = $id;
//        } else {
//            \PHPUnit\Framework\Assert::assertTrue($model->save(), 'Failed to save model '.$fullName);
//            $id = $model->$keyName;
//        }
//        $this->pathParams[$key] = $id;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function prepareUrlForRequest($uri): string
    {
        foreach($this->pathParams as $key => $val) {
            $uri = str_ireplace("\{$key\}", $val, $uri);
        }
        return parent::prepareUrlForRequest($uri);
    }

    /**
     * @Then /^response body is ([\[\{].+[\]\}])$/
     * @param string $json
     */
    public function checkResponseJson($json): void
    {
        $resp = Helper::decodeJson($this->response->content());
        \Webmozart\Assert\Assert::isArray($resp, 'Invalid response');

        $format = Helper::decodeJson($json);
        \Webmozart\Assert\Assert::isArray($format, 'Wrong response format in scenario');

        $this->assertResponseFormat($resp, $format);
    }

    public function assertResponseFormat(array $resp, array $format): void
    {
        foreach($format as $key => $val) {
            if (\is_object($val)) {
                \Webmozart\Assert\Assert::true(\is_object($resp[$key]), "The $key should be an object");
                $this->assertResponseFormat($resp[$key], $val);
            } elseif (\is_array($val)) {
                \Webmozart\Assert\Assert::true(\is_array($resp[$key]), "The $key should be an array");
                $this->assertResponseFormat($resp[$key], $val);
            } elseif ($val === 'string') {
                \Webmozart\Assert\Assert::true(\is_string($resp[$key]), "The $key should be a string");
            } elseif ($val === 'integer') {
                \Webmozart\Assert\Assert::true(\is_int($resp[$key]), "The $key should be an integer");
            } elseif ($val === 'number') {
                \Webmozart\Assert\Assert::true(\is_float($resp[$key]), "The $key should be a number");
            } elseif ($val === 'boolean') {
                \Webmozart\Assert\Assert::true(\is_bool($resp[$key]), "The $key should be boolean");
            }
        }
    }
}