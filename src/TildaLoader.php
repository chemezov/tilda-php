<?php

namespace TildaTools\Tilda;

use BadMethodCallException;
use TildaTools\Tilda\Exceptions\Loader\AssetLoadingException;
use TildaTools\Tilda\Exceptions\Loader\AssetStoringException;
use TildaTools\Tilda\Exceptions\Loader\PageHasNoAssetsException;
use TildaTools\Tilda\Exceptions\Loader\PageNotLoadedException;
use TildaTools\Tilda\Exceptions\Loader\TildaLoaderInvalidConfigurationException;
use TildaTools\Tilda\Objects\Page\ExportedPage;

// TODO: phpdoc update
// TODO: docs
// TODO: test

class TildaLoader
{
    /** @var TildaApi */
    protected $client;
    /** @var array */
    protected $config;

    public function __construct(TildaApi $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param int $pageId
     * @return Objects\Page\ExportedPage|null
     * @throws AssetLoadingException
     * @throws AssetStoringException
     * @throws Exceptions\Api\TildaApiException
     * @throws Exceptions\InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     * @throws PageNotLoadedException
     */
    public function page(int $pageId)
    {
        $pageInfo = $this->client->getPageExport($pageId);
        if (!$pageInfo) {
            throw new PageNotLoadedException;
        }
        $cssList = $pageInfo->css ?? [];
        $jsList = $pageInfo->js ?? [];
        $imgList = $pageInfo->images ?? [];
        $this->load($cssList, $this->config['path']['css'] . '/' . $pageId);
        $this->load($jsList, $this->config['path']['js'] . '/' . $pageId);
        $this->load($imgList, $this->config['path']['img'] . '/' . $pageId);
        return $pageInfo;
    }

    /**
     * @param ExportedPage $page
     * @param string $relPath
     * @return array
     * @throws PageHasNoAssetsException
     */
    public function assets(ExportedPage $page, string $relPath)
    {
        if (!$page->css || !$page->js) {
            throw new PageHasNoAssetsException;
        }
        $cssList = $page->css;
        $jsList = $page->js;
        $files = [];
        $cssPath = substr($this->config['path']['css'], strlen($relPath));
        $jsPath = substr($this->config['path']['js'], strlen($relPath));
        foreach ($cssList as $file) {
            $files['css'][] = $cssPath . '/' . $page->id . '/' . $file->to;
        }
        foreach ($jsList as $file) {
            $files['js'][] = $jsPath . '/' . $page->id . '/' . $file->to;
        }
        return $files;
    }

    /**
     * @param $fileList
     * @param $path
     * @throws AssetLoadingException
     * @throws AssetStoringException
     */
    protected function load($fileList, $path)
    {
        foreach ($fileList as $file) {
            $loaded = file_get_contents($file->from);
            if ($loaded === false) {
                throw new AssetLoadingException('Unable to load ' . $file->from);
            }
            $this->store($loaded, $path, $file->to);
        }
    }

    /**
     * @param $file
     * @param $assetPath
     * @param $localFilePath
     * @throws AssetStoringException
     */
    protected function store($file, $assetPath, $localFilePath)
    {
        if (!$this->isDirExists($assetPath)) {
            if (!$this->createDir($assetPath)) {
                throw new AssetStoringException('Unable to create assets storage directory at ' . $assetPath);
            }
        }
        if ($assetPath[strlen($assetPath) - 1] !== '/') {
            $assetPath .= '/';
        }
        if (file_put_contents($assetPath . $localFilePath, $file) === false) {
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

    /**
     * @throws TildaLoaderInvalidConfigurationException
     */
    protected function validateConfig()
    {
        foreach ($this->config['path'] as $param) {
            if (!$param || !is_dir($param)) {
                throw new TildaLoaderInvalidConfigurationException;
            }
        }
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->client, $method)) {
            throw new BadMethodCallException;
        }
        return call_user_func_array([$this->client, $method], $arguments);
    }
}
