<?php

namespace App\Exceptions;

use Exception;
use GreenSmoke\HealthChecks\Exceptions\FailedHealthCheckException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        FailedHealthCheckException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e): void
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return JsonResponse
     */
    public function render($request, Exception $e): JsonResponse
    {
        if ($e instanceof ModelNotFoundException) {
            $modelName = explode('\\',$e->getModel());
            $e = new NotFoundHttpException(array_pop($modelName).' Not Found.');
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage());
        } elseif ($e instanceof ValidationException) {
            $msg = $e->getMessage();
            foreach($errors = $e->errors() as $error) {
                $msg .= ' '.implode(' ',(array)$error);
            }
            $e = new HttpException($e->getResponse()->getStatusCode(), $msg);
        }

        $fe = FlattenException::create($e);
        $message = $fe->getMessage();
        if (!$message || !config('app.debug')) {
            if ($fe->getStatusCode() === 404) {
                $message = 'Sorry, the page you are looking for could not be found.';
            } else {
                $message = 'Whoops, looks like something went wrong.';
            }
        }
        $r = ['error' => $message];
        if(config('app.debug')) {
            $r['trace'] = $fe->getTrace();
            $r['file'] = $fe->getFile();
            $r['line'] = $fe->getLine();
        }

        return response()->json($r, $fe->getStatusCode());
    }
}
