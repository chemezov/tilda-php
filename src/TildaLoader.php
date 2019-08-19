<?php

namespace TildaTools\Tilda;

use TildaTools\Tilda\Exceptions\Loader\AssetLoadingException;
use TildaTools\Tilda\Exceptions\Loader\AssetStoringException;
use TildaTools\Tilda\Exceptions\Loader\PageNotLoadedException;
use TildaTools\Tilda\Exceptions\Loader\TildaLoaderInvalidConfigurationException;

function resolvePath(...$pathParts)
{
    return join(DIRECTORY_SEPARATOR, $pathParts);
}

class TildaLoader
{
    const CONFIG_OPTION_PATH = 'path';
    const CONFIG_HTML_FILE_NAME = 'index.html';

    /** @var TildaApi */
    protected $client;
    /** @var string */
    protected $path;
    protected $htmlFileName;

    /**
     * TildaLoader constructor.
     * @param TildaApi $client
     * @param array $config
     * @throws TildaLoaderInvalidConfigurationException
     */
    public function __construct(TildaApi $client, array $config)
    {
        $this->client = $client;
        $this->path = $config[self::CONFIG_OPTION_PATH];
        $this->htmlFileName = $config[self::CONFIG_HTML_FILE_NAME] ?? 'index.html';

        if (!$this->path) {
            throw TildaLoaderInvalidConfigurationException::forConfigOption(self::CONFIG_OPTION_PATH);
        }
    }

    /**
     * @param int $projectId
     * @throws AssetLoadingException
     * @throws AssetStoringException
     * @throws Exceptions\Api\TildaApiException
     * @throws Exceptions\InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     * @throws PageNotLoadedException
     */
    public function project(int $projectId)
    {
        $pages = $this->client->getPagesList($projectId);

        foreach ($pages as $page) {
            $this->page($page->id);
        }
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
        $pageInfo = $this->client->getPageFullExport($pageId);
        if (!$pageInfo) {
            throw new PageNotLoadedException;
        }

        $pagePath = $this->getPagePath($pageInfo->projectid, $pageId);

        $this->loadFiles($pageInfo->css ?? [], $pagePath);
        $this->loadFiles($pageInfo->js ?? [], $pagePath);
        $this->loadFiles($pageInfo->images ?? [], $pagePath);

        $this->store($pageInfo->html, $pagePath, $this->htmlFileName);
        return $pageInfo;
    }

    public function getProjectPath($projectId)
    {
        return resolvePath($this->path, $projectId);
    }

    public function getPagePath($projectId, $pageId)
    {
        return resolvePath($this->getProjectPath($projectId), $pageId);
    }

    /**
     * @param $fileList
     * @param $path
     * @throws AssetLoadingException
     * @throws AssetStoringException
     */
    protected function loadFiles($fileList, $path)
    {
        foreach ($fileList as $file) {
            $this->loadFile($file, $path);
        }
    }

    /**
     * @param $file
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
