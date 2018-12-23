<?php

namespace App\Models\BehatIntegration\Bootstrap;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

/**
 * Class LumenClient
 * @package App\Models\BehatIntegration\Bootstrap
 */
class LumenClient
{

    /**
     * The base path for the application.
     * @var string
     */
    private $basePath;

    /**
     * The application's environment file.
     * @var string
     */
    private $environmentFile;

    /**
     * Create a new Lumen boot instance.
     * @param string $basePath
     * @param string $environmentFile
     */
    public function __construct($basePath, $environmentFile = '.env')
    {
        $this->basePath = $basePath;
        $this->environmentFile = $environmentFile;
    }

    /**
     * Get the application's base path.
     * @return mixed
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Boot the app.
     * @return \Illuminate\Container\Container
     */
    public function boot()
    {
        $bootstrapPath = $this->basePath() . '/bootstrap/app.php';
        $this->assertBootstrapFileExists($bootstrapPath);

        $this->loadEnv();

        return require $bootstrapPath;
    }

    /**
     * Ensure that the provided Lumen bootstrap path exists.
     * @param string $bootstrapPath
     * @throws \RuntimeException
     */
    private function assertBootstrapFileExists($bootstrapPath): void
    {
        if (!file_exists($bootstrapPath)) {
            throw new \RuntimeException('Could not locate the path to the Laravel bootstrap file.');
        }
    }

    private function loadEnv(): void {
        try {
            (new Dotenv(__DIR__ . '/../', $this->environmentFile))->overload();
        } catch (InvalidPathException $e) {
        }
    }
}