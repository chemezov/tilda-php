<?php

namespace TildaTools\Tilda;

use TildaTools\Tilda\Exceptions\Loader\AssetLoadingException;
use TildaTools\Tilda\Exceptions\Loader\AssetStoringException;
use TildaTools\Tilda\Exceptions\Loader\PageNotLoadedException;
use TildaTools\Tilda\Exceptions\Loader\TildaLoaderInvalidConfigurationException;
use TildaTools\Tilda\Objects\Asset;

function resolvePath(...$pathParts)
{
    return join(DIRECTORY_SEPARATOR, $pathParts);
}

function fixRelativePath($path, $to)
{
    if (substr($path, 0, 1) === DIRECTORY_SEPARATOR) {
        return $path;
    } else {
        return resolvePath($to, $path);
    }
}

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object))
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }
}

class TildaLoader
{
    const CONFIG_OPTION_PATH = 'path';
    const CONFIG_OPTION_IMAGE_PATH = 'imagesPath';
    const CONFIG_OPTION_CSS_PATH = 'cssPath';
    const CONFIG_OPTION_JS_PATH = 'jsPath';

    /** @var TildaApi */
    protected $client;
    /** @var string */
    protected $path;
    protected $cssPath;
    protected $jsPath;
    protected $imagesPath;

    /**
     * TildaLoader constructor.
     * @param TildaApi $client
     * @param array $config
     * @throws TildaLoaderInvalidConfigurationException
     */
    public function __construct(TildaApi $client, array $config)
    {
        $this->client = $client;
        $this->fillProperties([self::CONFIG_OPTION_PATH], $config, true);
        $this->fillProperties([self::CONFIG_OPTION_CSS_PATH, self::CONFIG_OPTION_IMAGE_PATH, self::CONFIG_OPTION_JS_PATH], $config);
        $this->fixPaths();
    }

    /**
     * @param array $names
     * @param array $config
     * @param bool $required
     * @throws TildaLoaderInvalidConfigurationException
     */
    private function fillProperties(array $names, array $config, bool $required = false)
    {
        foreach ($names as $option) {
            $this->{$option} = $config[$option];
            if ($required && $this->{$option} === null) {
                throw TildaLoaderInvalidConfigurationException::forConfigOption($option);
            }
        }
    }

    private function fixPaths()
    {
        foreach ([self::CONFIG_OPTION_CSS_PATH, self::CONFIG_OPTION_IMAGE_PATH, self::CONFIG_OPTION_JS_PATH] as $property) {
            $this->{$property} = fixRelativePath($this->{$property}, $this->path);
        }
    }

    /**
     * @param int $projectId
     * @param bool $clean
     * @throws AssetLoadingException
     * @throws AssetStoringException
     * @throws Exceptions\Api\TildaApiException
     * @throws Exceptions\InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     * @throws PageNotLoadedException
     */
    public function project(int $projectId, bool $clean = false)
    {
        if ($clean) {
            rrmdir($this->path);
        }

        $project = $this->client->getProjectExport($projectId);
        $this->loadFiles($project->css, $this->cssPath);
        $this->loadFiles($project->js, $this->jsPath);
        $this->loadFiles($project->images, $this->imagesPath);

        $pages = $this->client->getPagesList($projectId);

        foreach ($pages as $page) {
            $this->page($page->id);
        }
    }

    /**
     * @param int $pageId
     * @throws AssetLoadingException
     * @throws AssetStoringException
     * @throws Exceptions\Api\TildaApiException
     * @throws Exceptions\InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     * @throws PageNotLoadedException
     */
    public function page(int $pageId)
    {
        $page = $this->client->getPageFullExport($pageId);
        if (!$page) {
            throw new PageNotLoadedException;
        }

        $this->loadFiles($page->css, $this->cssPath);
        $this->loadFiles($page->js, $this->jsPath);
        $this->loadFiles($page->images, $this->imagesPath);
        $this->store($page->html, $this->path, $page->filename);
    }

    /**
     * @param array|null $files
     * @param $path
     * @throws AssetLoadingException
     * @throws AssetStoringException
     */
    protected function loadFiles($files, $path)
    {
        foreach (($files ?? []) as $file) {
            $this->loadFile($file, $path);
        }
    }

    /**
     * @param Asset $file
     * @param $path
     * @throws AssetLoadingException
     * @throws AssetStoringException
     */
    protected function loadFile($file, $path)
    {
        $loaded = file_get_contents($file->from);
        if ($loaded === false) {
            throw new AssetLoadingException('Unable to load ' . $file->from);
        }
        $this->store($loaded, $path, $file->to);
    }

    /**
     * @param $data
     * @param $assetPath
     * @param $localFilePath
     * @throws AssetStoringException
     */
    protected function store($data, $assetPath, $localFilePath)
    {
        if (!$this->isDirExists($assetPath)) {
            if (!$this->createDir($assetPath)) {
                throw new AssetStoringException('Unable to create assets storage directory at ' . $assetPath);
            }
        }
        if ($assetPath[strlen($assetPath) - 1] !== '/') {
            $assetPath .= '/';
        }
        if (file_put_contents($assetPath . $localFilePath, $data) === false) {
            throw new AssetStoringException('Unable to store asset to ' . $assetPath . $localFilePath);
        }
    }

    protected function isDirExists($path)
    {
        return file_exists($path) && is_dir($path);
    }

    protected function createDir($path)
    {
        return mkdir($path, 0775, true);
    }
}
