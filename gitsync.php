<?php

namespace Plugins\Gitsync;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Typemill\Plugin;
use Typemill\Models\StorageWrapper;
use Typemill\Models\Settings;
use Typemill\Models\Navigation;

require_once __DIR__ . '/GitPlatformClient.php';
require_once __DIR__ . '/GitHubClient.php';
require_once __DIR__ . '/GitLabClient.php';

class Gitsync extends Plugin
{
    private $storage;
    private $statusError;

    public static function setPremiumLicence()
    {
        return 'BUSINESS';
    }

    public static function getSubscribedEvents()
    {
        return array(
            'onSystemnaviLoaded' => 'onSystemnaviLoaded',
            'onTwigLoaded' => 'onTwigLoaded',
        );
    }

    public static function addNewRoutes()
    {
        return [
            [
                'httpMethod'    => 'get',
                'route'         => '/api/v1/gitsync/history',
                'name'          => 'gitsync.history',
                'class'         => 'Plugins\Gitsync\Gitsync:history',
                'resource'      => 'system',
                'privilege'     => 'read'
            ],
            [
                'httpMethod'    => 'get',
                'route'         => '/api/v1/gitsync/history/{id}',
                'name'          => 'gitsync.history.connection',
                'class'         => 'Plugins\Gitsync\Gitsync:history',
                'resource'      => 'system',
                'privilege'     => 'read'
            ],
            [
                'httpMethod'    => 'get',
                'route'         => '/api/v1/gitsync/connections',
                'name'          => 'gitsync.connections.list',
                'class'         => 'Plugins\Gitsync\Gitsync:listConnections',
                'resource'      => 'system',
                'privilege'     => 'read'
            ],
            [
                'httpMethod'    => 'post',
                'route'         => '/api/v1/gitsync/connections',
                'name'          => 'gitsync.connections.create',
                'class'         => 'Plugins\Gitsync\Gitsync:createConnection',
                'resource'      => 'system',
                'privilege'     => 'update'
            ],
            [
                'httpMethod'    => 'put',
                'route'         => '/api/v1/gitsync/connections/{id}',
                'name'          => 'gitsync.connections.update',
                'class'         => 'Plugins\Gitsync\Gitsync:updateConnection',
                'resource'      => 'system',
                'privilege'     => 'update'
            ],
            [
                'httpMethod'    => 'delete',
                'route'         => '/api/v1/gitsync/connections/{id}',
                'name'          => 'gitsync.connections.delete',
                'class'         => 'Plugins\Gitsync\Gitsync:deleteConnection',
                'resource'      => 'system',
                'privilege'     => 'update'
            ],
            [
                'httpMethod'    => 'post',
                'route'         => '/api/v1/gitsync/pull/{id}',
                'name'          => 'gitsync.pull',
                'class'         => 'Plugins\Gitsync\Gitsync:pull',
                'resource'      => 'system',
                'privilege'     => 'update'
            ],
            [
                'httpMethod'    => 'post',
                'route'         => '/api/v1/gitsync/push/{id}',
                'name'          => 'gitsync.push',
                'class'         => 'Plugins\Gitsync\Gitsync:push',
                'resource'      => 'system',
                'privilege'     => 'update'
            ],
            [
                'httpMethod'    => 'get',
                'route'         => '/api/v1/gitsync/status/{id}',
                'name'          => 'gitsync.status',
                'class'         => 'Plugins\Gitsync\Gitsync:status',
                'resource'      => 'system',
                'privilege'     => 'read'
            ],
            [
                'httpMethod'    => 'get',
                'route'         => '/tm/gitsync',
                'name'          => 'gitsync.show',
                'class'         => 'Typemill\Controllers\ControllerWebSystem:blankSystemPage',
                'resource'      => 'system',
                'privilege'     => 'read'
            ],
        ];
    }

    public function onTwigLoaded($noidea)
    {
        if($this->editorroute)
        {
            $this->addSvgSymbol('<symbol id="icon-history" viewBox="0 0 34 32"><path d="M20 2c7.732 0 14 6.268 14 14s-6.268 14-14 14v-3c2.938 0 5.701-1.144 7.778-3.222s3.222-4.84 3.222-7.778c0-2.938-1.144-5.701-3.222-7.778s-4.84-3.222-7.778-3.222c-2.938 0-5.701 1.144-7.778 3.222-1.598 1.598-2.643 3.601-3.041 5.778h5.819l-7 8-7-8h5.143c0.971-6.784 6.804-12 13.857-12zM26 14v4h-8v-10h4v6z"></path></symbol>');
            $this->addSvgSymbol('<symbol id="icon-check" viewBox="0 0 32 32"><path d="M27 4l-15 15-7-7-5 5 12 12 20-20z"></path></symbol>');
            $this->addSvgSymbol('<symbol id="icon-close" viewBox="0 0 32 32"><path d="M31.708 25.708c-0-0-0-0-0-0l-9.708-9.708 9.708-9.708c0-0 0-0 0-0 0.105-0.105 0.18-0.227 0.229-0.357 0.133-0.356 0.057-0.771-0.229-1.057l-4.586-4.586c-0.286-0.286-0.702-0.361-1.057-0.229-0.13 0.048-0.252 0.124-0.357 0.228 0 0-0 0-0 0l-9.708 9.708-9.708-9.711c-0-0-0-0-0-0-0.105-0.104-0.227-0.18-0.357-0.228-0.356-0.133-0.771-0.057-1.057 0.229l-4.586 4.586c-0.286 0.286-0.361 0.702-0.229 1.057 0.048 0.13 0.124 0.252 0.229 0.357 0 0 0 0 0 0l9.708 9.708-9.708 9.708c-0 0-0 0-0 0-0.104 0.105-0.18 0.227-0.228 0.357-0.133 0.355-0.057 0.771 0.229 1.057l4.586 4.586c0.286 0.286 0.702 0.361 1.057 0.229 0.13-0.048 0.252-0.124 0.357-0.228 0-0 0-0 0-0l9.708-9.708 9.708 9.708c0 0 0 0 0 0 0.105 0.104 0.227 0.18 0.357 0.228 0.356 0.133 0.771 0.057 1.057-0.229l4.586-4.586c0.286-0.286 0.362-0.702 0.229-1.057-0.049-0.13-0.124-0.252-0.229-0.357z"></path></symbol>');
            $this->addJS('/gitsync/js/vue-gitsync-meta.js');
        }
        elseif(!$this->adminroute && !str_starts_with($this->route, 'media/files') && !str_starts_with($this->route, 'api/'))
        {
            $this->autoSync();
        }
    }

    public function onSystemnaviLoaded($navidata)
    {
        $this->addSvgSymbol('<symbol id="icon-github" viewBox="0 0 32 32"><path d="M16 0.395c-8.836 0-16 7.163-16 16 0 7.069 4.585 13.067 10.942 15.182 0.8 0.148 1.094-0.347 1.094-0.77 0-0.381-0.015-1.642-0.022-2.979-4.452 0.968-5.391-1.888-5.391-1.888-0.728-1.849-1.776-2.341-1.776-2.341-1.452-0.993 0.11-0.973 0.11-0.973 1.606 0.113 2.452 1.649 2.452 1.649 1.427 2.446 3.743 1.739 4.656 1.33 0.143-1.034 0.558-1.74 1.016-2.14-3.554-0.404-7.29-1.777-7.29-7.907 0-1.747 0.625-3.174 1.649-4.295-0.166-0.403-0.714-2.030 0.155-4.234 0 0 1.344-0.43 4.401 1.64 1.276-0.355 2.645-0.532 4.005-0.539 1.359 0.006 2.729 0.184 4.008 0.539 3.054-2.070 4.395-1.64 4.395-1.64 0.871 2.204 0.323 3.831 0.157 4.234 1.026 1.12 1.647 2.548 1.647 4.295 0 6.145-3.743 7.498-7.306 7.895 0.574 0.497 1.085 1.47 1.085 2.963 0 2.141-0.019 3.864-0.019 4.391 0 0.426 0.288 0.925 1.099 0.768 6.354-2.118 10.933-8.113 10.933-15.18 0-8.837-7.164-16-16-16z"></path></symbol>');

        $navi = $navidata->getData();

        $navi['Gitsync'] = [
            'title'         => 'Gitsync',
            'routename'     => 'gitsync.show',
            'icon'          => 'icon-github',
            'aclresource'   => 'system',
            'aclprivilege'  => 'view'
        ];

        if (trim($this->route, "/") == 'tm/gitsync')
        {
            $navi['Gitsync']['active'] = true;
            $this->addJS('/gitsync/js/vue-gitsync.js');
        }

        $navidata->setData($navi);
    }

    /* ================================================================
       CONNECTION CRUD
       ================================================================ */

    public function listConnections(Request $request, Response $response, $args)
    {
        $data = $this->getPluginYamlData('gitsync.yaml') ?? [];
        $connections = $data['connections'] ?? [];

        $response->getBody()->write(json_encode([
            'connections' => $connections
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createConnection(Request $request, Response $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params['id'] ?? false;
        $owner = $params['owner'] ?? false;
        $repo = $params['repo'] ?? false;
        $branch = $params['branch'] ?? 'main';
        $localFolder = $params['local_folder'] ?? '';
        $platform = $params['platform'] ?? 'github';
        $apiBaseUrl = $params['api_base_url'] ?? '';
        $repoPath = isset($params['repo_path']) ? $this->sanitizeRepoPath($params['repo_path']) : '';
        $filePattern = isset($params['file_pattern']) ? trim($params['file_pattern']) : '';

        if (!$id || !$owner || !$repo)
        {
            return $this->errorResponse($response, 'Missing required fields: id, owner, repo', 400, []);
        }

        $localFolder = $this->sanitizeLocalFolder($localFolder);
        if ($localFolder === false)
        {
            return $this->errorResponse($response, 'Invalid local folder path', 400, []);
        }

        $data = $this->getPluginYamlData('gitsync.yaml') ?? [];
        if (!isset($data['connections']))
        {
            $data['connections'] = [];
        }

        if (isset($data['connections'][$id]))
        {
            return $this->errorResponse($response, 'Connection ID already exists', 409, []);
        }

        $data['connections'][$id] = [
            'id'                    => $id,
            'owner'                 => $owner,
            'repo'                  => $repo,
            'branch'                => $branch,
            'local_folder'          => $localFolder,
            'platform'              => $platform,
            'api_base_url'          => $apiBaseUrl,
            'repo_path'             => $repoPath,
            'file_pattern'          => $filePattern,
            'auto_sync'             => !empty($params['auto_sync']),
            'auto_sync_interval'    => isset($params['auto_sync_interval']) ? (int) $params['auto_sync_interval'] : 10,
            'last_sync_sha'         => null,
            'files'                 => []
        ];

        $result = $this->storePluginYamlData('gitsync.yaml', $data);
        if ($result !== true)
        {
            return $this->errorResponse($response, 'Failed to save connection', 500, []);
        }

        $response->getBody()->write(json_encode([
            'connection' => $data['connections'][$id]
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateConnection(Request $request, Response $response, $args)
    {
        $id = $args['id'] ?? false;
        $params = $request->getParsedBody();

        if (!$id)
        {
            return $this->errorResponse($response, 'Connection ID required', 400, []);
        }

        $data = $this->getPluginYamlData('gitsync.yaml') ?? [];
        if (!isset($data['connections'][$id]))
        {
            return $this->errorResponse($response, 'Connection not found', 404, []);
        }

        $localFolder = isset($params['local_folder']) ? $this->sanitizeLocalFolder($params['local_folder']) : $data['connections'][$id]['local_folder'];
        if (isset($params['local_folder']) && $localFolder === false)
        {
            return $this->errorResponse($response, 'Invalid local folder path', 400, []);
        }

        $data['connections'][$id]['owner'] = $params['owner'] ?? $data['connections'][$id]['owner'];
        $data['connections'][$id]['repo'] = $params['repo'] ?? $data['connections'][$id]['repo'];
        $data['connections'][$id]['branch'] = $params['branch'] ?? $data['connections'][$id]['branch'];
        $data['connections'][$id]['local_folder'] = $localFolder;
        $data['connections'][$id]['platform'] = $params['platform'] ?? $data['connections'][$id]['platform'];
        $data['connections'][$id]['api_base_url'] = $params['api_base_url'] ?? $data['connections'][$id]['api_base_url'];
        $data['connections'][$id]['repo_path'] = isset($params['repo_path']) ? $this->sanitizeRepoPath($params['repo_path']) : ($data['connections'][$id]['repo_path'] ?? '');
        $data['connections'][$id]['file_pattern'] = isset($params['file_pattern']) ? trim($params['file_pattern']) : ($data['connections'][$id]['file_pattern'] ?? '');
        $data['connections'][$id]['auto_sync'] = isset($params['auto_sync']) ? !empty($params['auto_sync']) : ($data['connections'][$id]['auto_sync'] ?? false);
        $data['connections'][$id]['auto_sync_interval'] = isset($params['auto_sync_interval']) ? (int) $params['auto_sync_interval'] : ($data['connections'][$id]['auto_sync_interval'] ?? 10);

        $result = $this->storePluginYamlData('gitsync.yaml', $data);
        if ($result !== true)
        {
            return $this->errorResponse($response, 'Failed to update connection', 500, []);
        }

        $response->getBody()->write(json_encode([
            'connection' => $data['connections'][$id]
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function deleteConnection(Request $request, Response $response, $args)
    {
        $id = $args['id'] ?? false;

        if (!$id)
        {
            $response->getBody()->write(json_encode([
                'message' => 'Connection ID required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $data = $this->getPluginYamlData('gitsync.yaml') ?? [];
        if (!isset($data['connections'][$id]))
        {
            $response->getBody()->write(json_encode([
                'message' => 'Connection not found'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        unset($data['connections'][$id]);

        $result = $this->storePluginYamlData('gitsync.yaml', $data);
        if ($result !== true)
        {
            $response->getBody()->write(json_encode([
                'message' => 'Failed to delete connection',
                'error'   => is_array($result) ? $result : (string) $result
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'message' => 'Connection deleted'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /* ================================================================
       HISTORY
       ================================================================ */

    public function history(Request $request, Response $response, $args)
    {
        $data = $this->getPluginYamlData('gitsync.yaml') ?? [];
        $history = $data['history'] ?? [];
        $connectionId = $args['id'] ?? null;

        if ($connectionId)
        {
            $history = array_values(array_filter($history, function ($entry) use ($connectionId) {
                return ($entry['connection_id'] ?? null) === $connectionId;
            }));
        }

        $response->getBody()->write(json_encode([
            'history' => $history
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /* ================================================================
       PULL
       ================================================================ */

    public function pull(Request $request, Response $response, $args)
    {
        ini_set('memory_limit', '256M');

        $connectionId = $args['id'] ?? false;

        $pluginData = $this->getPluginYamlData('gitsync.yaml') ?? [];
        $connections = $pluginData['connections'] ?? [];

        if (!$connectionId)
        {
            return $this->errorResponse($response, 'Connection ID required', 400, $pluginData);
        }

        if (!isset($connections[$connectionId]))
        {
            return $this->errorResponse($response, 'Connection not found', 404, $pluginData);
        }

        $connection = $connections[$connectionId];
        $result = $this->performPull($connectionId, $connection, $pluginData);

        $pluginData = $result['pluginData'];
        $this->storePluginYamlData('gitsync.yaml', $pluginData);

        if ($result['success'])
        {
            if ($result['already_up_to_date'] ?? false)
            {
                return $this->errorResponse($response, 'Already up to date', 200, $pluginData);
            }

            $this->clearNavigationCache();

            $response->getBody()->write(json_encode([
                'message'   => $result['message'],
                'files'     => $result['files'],
                'deleted'   => $result['deleted'],
                'skipped'   => $result['skipped'],
                'detail'    => $result['detail'],
                'history'   => $result['history']
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $this->errorResponse($response, $result['message'], $result['status_code'], $pluginData, ['conflicts' => $result['conflicts']]);
    }

    /**
     * Internal pull logic shared by the HTTP endpoint and auto-sync.
     *
     * @return array Pull result with keys: success, status_code, message,
     *               pluginData, files, deleted, skipped, conflicts, detail,
     *               history, already_up_to_date.
     */
    private function performPull(string $connectionId, array $connection, array $pluginData): array
    {
        $localFolder = $connection['local_folder'];

        $pluginSettings = $this->getPluginSettings();
        $timeout = isset($pluginSettings['timeout']) ? (int) $pluginSettings['timeout'] : 60;
        if ($timeout < 5) { $timeout = 5; }

        $settingsModel = new Settings();
        $apikey = $settingsModel->getSecret('github_token', 'plugins', 'gitsync');

        if (!$apikey)
        {
            $pluginData = $this->addHistoryEntry($pluginData, [
                'action'        => 'pull',
                'connection_id' => $connectionId,
                'status'        => 'error',
                'files'         => 0,
                'message'       => 'Token not configured'
            ]);
            return [
                'success'       => false,
                'status_code'   => 400,
                'message'       => 'GitHub token not configured',
                'pluginData'    => $pluginData,
                'files'         => 0,
                'deleted'       => 0,
                'skipped'       => 0,
                'conflicts'     => [],
                'detail'        => '',
                'history'       => $pluginData['history'] ?? [],
                'already_up_to_date' => false
            ];
        }

        $client = GitPlatformClient::factory([
            'platform'      => $connection['platform'] ?? 'github',
            'owner'         => $connection['owner'],
            'repo'          => $connection['repo'],
            'branch'        => $connection['branch'] ?? 'main',
            'token'         => $apikey,
            'timeout'       => $timeout,
            'api_base_url'  => $connection['api_base_url'] ?? ''
        ]);

        $lastRemoteSha = $client->getLatestCommitSha();
        if (!$lastRemoteSha)
        {
            $pluginData = $this->addHistoryEntry($pluginData, [
                'action'        => 'pull',
                'connection_id' => $connectionId,
                'status'        => 'error',
                'files'         => 0,
                'message'       => 'Git API call failed: ' . $client->getLastError()
            ]);
            return [
                'success'       => false,
                'status_code'   => 404,
                'message'       => 'Git API call failed: ' . $client->getLastError(),
                'pluginData'    => $pluginData,
                'files'         => 0,
                'deleted'       => 0,
                'skipped'       => 0,
                'conflicts'     => [],
                'detail'        => '',
                'history'       => $pluginData['history'] ?? [],
                'already_up_to_date' => false
            ];
        }

        $lastKnownSha = $connection['last_sync_sha'] ?? null;

        if ($lastKnownSha === $lastRemoteSha)
        {
            return [
                'success'       => true,
                'status_code'   => 200,
                'message'       => 'Already up to date',
                'pluginData'    => $pluginData,
                'files'         => 0,
                'deleted'       => 0,
                'skipped'       => 0,
                'conflicts'     => [],
                'detail'        => 'Already up to date',
                'history'       => $pluginData['history'] ?? [],
                'already_up_to_date' => true
            ];
        }

        $files = $connection['files'] ?? [];
        $filesProcessed = 0;
        $filesDeleted = 0;
        $filesSkipped = 0;
        $repoPath = $connection['repo_path'] ?? '';
        $filePattern = $connection['file_pattern'] ?? '';

        if (!$lastKnownSha)
        {
            # First-time pull: fetch full tree recursively
            $treeItems = $client->getTreeRecursive();
            if ($treeItems === false)
            {
                $pluginData = $this->addHistoryEntry($pluginData, [
                    'action'        => 'pull',
                    'connection_id' => $connectionId,
                    'status'        => 'error',
                    'files'         => 0,
                    'message'       => 'Git API tree fetch failed: ' . $client->getLastError()
                ]);
                return [
                    'success'       => false,
                    'status_code'   => 404,
                    'message'       => 'Git API tree fetch failed: ' . $client->getLastError(),
                    'pluginData'    => $pluginData,
                    'files'         => 0,
                    'deleted'       => 0,
                    'skipped'       => 0,
                    'conflicts'     => [],
                    'detail'        => '',
                    'history'       => $pluginData['history'] ?? [],
                    'already_up_to_date' => false
                ];
            }

            foreach ($treeItems as $item)
            {
                $filename = $item['path'];
                if (!$this->shouldSyncFile($filename, $repoPath, $filePattern))
                {
                    $filesSkipped++;
                    continue;
                }

                $fileData = $client->getFile($filename);
                if ($fileData === false) continue;

                $localPath = $this->localPathFromRepoPath($repoPath, $filename);
                $this->writeContentFile($localFolder, $localPath, $fileData['content']);
                $files[$filename] = [
                    'remote_sha' => $fileData['sha'],
                    'local_hash' => sha1($fileData['content'])
                ];
                $filesProcessed++;
            }
        }
        else
        {
            # Incremental pull: compare commits
            $changedFiles = $client->compareCommits($lastKnownSha, $lastRemoteSha);
            if ($changedFiles === false)
            {
                $pluginData = $this->addHistoryEntry($pluginData, [
                    'action'        => 'pull',
                    'connection_id' => $connectionId,
                    'status'        => 'error',
                    'files'         => 0,
                    'message'       => 'Git API compare failed: ' . $client->getLastError()
                ]);
                return [
                    'success'       => false,
                    'status_code'   => 404,
                    'message'       => 'Git API compare failed: ' . $client->getLastError(),
                    'pluginData'    => $pluginData,
                    'files'         => 0,
                    'deleted'       => 0,
                    'skipped'       => 0,
                    'conflicts'     => [],
                    'detail'        => '',
                    'history'       => $pluginData['history'] ?? [],
                    'already_up_to_date' => false
                ];
            }

            # Pre-scan for local conflicts before writing anything
            $conflicts = [];
            foreach ($changedFiles as $file)
            {
                $filename = $file['filename'];
                if (!$this->shouldSyncFile($filename, $repoPath, $filePattern))
                {
                    continue;
                }

                $localPath = $this->localPathFromRepoPath($repoPath, $filename);
                $localContent = $this->readContentFile($localFolder, $localPath);

                # File does not exist locally -> no conflict
                if ($localContent === false)
                {
                    continue;
                }

                $localHash = sha1($localContent);

                # If no stored hash, file was added locally after last sync
                if (!isset($files[$filename]['local_hash']))
                {
                    $conflicts[] = $filename;
                    continue;
                }

                # If local hash differs from stored hash, local was modified
                if ($files[$filename]['local_hash'] !== $localHash)
                {
                    $conflicts[] = $filename;
                }
            }

            if (!empty($conflicts))
            {
                $pluginData = $this->addHistoryEntry($pluginData, [
                    'action'        => 'pull',
                    'connection_id' => $connectionId,
                    'status'        => 'error',
                    'files'         => 0,
                    'message'       => 'Pull aborted. Local file(s) have un-pushed changes. Please push first or resolve manually. Conflicts: ' . implode(', ', $conflicts)
                ]);
                return [
                    'success'       => false,
                    'status_code'   => 409,
                    'message'       => 'Pull aborted. Local file(s) have un-pushed changes. Please push first or resolve manually.',
                    'pluginData'    => $pluginData,
                    'files'         => 0,
                    'deleted'       => 0,
                    'skipped'       => 0,
                    'conflicts'     => $conflicts,
                    'detail'        => '',
                    'history'       => $pluginData['history'] ?? [],
                    'already_up_to_date' => false
                ];
            }

            foreach ($changedFiles as $file)
            {
                $filename = $file['filename'];
                if (!$this->shouldSyncFile($filename, $repoPath, $filePattern))
                {
                    $filesSkipped++;
                    continue;
                }

                if ($file['status'] === 'removed')
                {
                    $localPath = $this->localPathFromRepoPath($repoPath, $filename);
                    $this->deleteContentFile($localFolder, $localPath);
                    unset($files[$filename]);
                    $filesDeleted++;
                    continue;
                }

                $fileData = $client->getFile($filename);
                if ($fileData === false) continue;

                $localPath = $this->localPathFromRepoPath($repoPath, $filename);
                $this->writeContentFile($localFolder, $localPath, $fileData['content']);
                $files[$filename] = [
                    'remote_sha' => $file['sha'],
                    'local_hash' => sha1($fileData['content'])
                ];
                $filesProcessed++;
            }
        }

        # Update connection data
        if (isset($pluginData['connections'][$connectionId]))
        {
            $pluginData['connections'][$connectionId]['last_sync_sha'] = $lastRemoteSha;
            $pluginData['connections'][$connectionId]['files'] = $files;
        }

        $detail = 'Pulled ' . $filesProcessed . ' file' . ($filesProcessed !== 1 ? 's' : '');
        if ($filesDeleted > 0)
        {
            $detail .= ', deleted ' . $filesDeleted;
        }
        if ($filesSkipped > 0)
        {
            $detail .= ', skipped ' . $filesSkipped;
        }

        $pluginData = $this->addHistoryEntry($pluginData, [
            'action'        => 'pull',
            'connection_id' => $connectionId,
            'status'        => 'success',
            'files'         => $filesProcessed,
            'message'       => $detail
        ]);

        return [
            'success'       => true,
            'status_code'   => 200,
            'message'       => 'Pull completed',
            'pluginData'    => $pluginData,
            'files'         => $filesProcessed,
            'deleted'       => $filesDeleted,
            'skipped'       => $filesSkipped,
            'conflicts'     => [],
            'detail'        => $detail,
            'history'       => $pluginData['history'] ?? [],
            'already_up_to_date' => false
        ];
    }

    /* ================================================================
       PUSH
       ================================================================ */

    public function push(Request $request, Response $response, $args)
    {
        ini_set('memory_limit', '256M');

        $connectionId = $args['id'] ?? false;
        $params = $request->getParsedBody() ?? [];

        $pluginData = $this->getPluginYamlData('gitsync.yaml') ?? [];
        $connections = $pluginData['connections'] ?? [];

        if (!$connectionId)
        {
            return $this->errorResponse($response, 'Connection ID required', 400, $pluginData);
        }

        if (!isset($connections[$connectionId]))
        {
            return $this->errorResponse($response, 'Connection not found', 404, $pluginData);
        }

        $connection = $connections[$connectionId];
        $commitMessage = $params['message'] ?? null;
        $result = $this->performPush($connectionId, $connection, $pluginData, $commitMessage);

        $pluginData = $result['pluginData'];

        $storeResult = $this->storePluginYamlData('gitsync.yaml', $pluginData);
        if ($storeResult !== true)
        {
            return $this->errorResponse($response, 'Push completed but failed to save sync metadata: ' . (is_array($storeResult) ? json_encode($storeResult) : $storeResult), 500, $pluginData);
        }

        if ($result['success'])
        {
            $this->clearNavigationCache();

            $response->getBody()->write(json_encode([
                'message'   => $result['message'],
                'files'     => $result['files'],
                'skipped'   => $result['skipped'],
                'detail'    => $result['detail'],
                'history'   => $result['history']
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $this->errorResponse($response, $result['message'], $result['status_code'], $pluginData, ['conflicts' => $result['conflicts']]);
    }

    /**
     * Internal push logic shared by the HTTP endpoint and auto-sync.
     *
     * @param string $connectionId
     * @param array $connection
     * @param array $pluginData
     * @param string|null $commitMessage Custom commit message; falls back to 'Updated from Typemill'.
     * @return array Push result with keys: success, status_code, message, pluginData,
     *               files, skipped, conflicts, detail, history.
     */
    private function performPush(string $connectionId, array $connection, array $pluginData, ?string $commitMessage = null): array
    {
        $localFolder = $connection['local_folder'];
        $files = $connection['files'] ?? [];

        $pluginSettings = $this->getPluginSettings();
        $timeout = isset($pluginSettings['timeout']) ? (int) $pluginSettings['timeout'] : 60;
        if ($timeout < 5) { $timeout = 5; }

        $settingsModel = new Settings();
        $apikey = $settingsModel->getSecret('github_token', 'plugins', 'gitsync');

        if (!$apikey)
        {
            $pluginData = $this->addHistoryEntry($pluginData, [
                'action'        => 'push',
                'connection_id' => $connectionId,
                'status'        => 'error',
                'files'         => 0,
                'message'       => 'Token not configured'
            ]);
            return [
                'success'       => false,
                'status_code'   => 400,
                'message'       => 'GitHub token not configured',
                'pluginData'    => $pluginData,
                'files'         => 0,
                'skipped'       => 0,
                'conflicts'     => [],
                'detail'        => '',
                'history'       => $pluginData['history'] ?? []
            ];
        }

        $client = GitPlatformClient::factory([
            'platform'      => $connection['platform'] ?? 'github',
            'owner'         => $connection['owner'],
            'repo'          => $connection['repo'],
            'branch'        => $connection['branch'] ?? 'main',
            'token'         => $apikey,
            'timeout'       => $timeout,
            'api_base_url'  => $connection['api_base_url'] ?? ''
        ]);

        $repoPath = $connection['repo_path'] ?? '';
        $filePattern = $connection['file_pattern'] ?? '';

        $defaultMessage = $commitMessage ?? 'Updated from Typemill';

        # Scan local folder for .md files
        $localFiles = $this->scanLocalMdFiles($localFolder);
        $filesPushed = 0;
        $filesSkipped = 0;
        $conflicts = [];
        $updatedFiles = $files;
        $lastCommitSha = null;

        foreach ($localFiles as $relativePath)
        {
            # Compute remote path
            $remotePath = $relativePath;
            if (!empty($repoPath))
            {
                $remotePath = $repoPath . '/' . $relativePath;
            }

            # Apply file pattern filter
            if (!$this->matchesPattern($remotePath, $filePattern))
            {
                continue;
            }

            # Read local content through StorageWrapper
            $localContent = $this->readContentFile($localFolder, $relativePath);
            if ($localContent === false) continue;

            # Compute local hash for change detection
            $localHash = sha1($localContent);

            # Skip if content hasn't changed since last push/pull
            if (isset($files[$remotePath]['local_hash']) && $files[$remotePath]['local_hash'] === $localHash)
            {
                $filesSkipped++;
                continue;
            }

            # Get current remote file info
            $remoteFile = $client->getFile($remotePath);
            $remoteSha = null;
            $remoteExists = false;

            if ($remoteFile !== false)
            {
                $remoteSha = $remoteFile['sha'];
                $remoteExists = true;
            }
            else
            {
                $error = $client->getLastError();
                if (stripos($error, 'Not Found') !== false)
                {
                    $remoteExists = false;
                }
                else
                {
                    $pluginData = $this->addHistoryEntry($pluginData, [
                        'action'        => 'push',
                        'connection_id' => $connectionId,
                        'status'        => 'error',
                        'files'         => 0,
                        'message'       => 'Git API error for ' . $remotePath . ': ' . $error
                    ]);
                    return [
                        'success'       => false,
                        'status_code'   => 400,
                        'message'       => 'Git API error for ' . $remotePath . ': ' . $error,
                        'pluginData'    => $pluginData,
                        'files'         => 0,
                        'skipped'       => $filesSkipped,
                        'conflicts'     => [],
                        'detail'        => '',
                        'history'       => $pluginData['history'] ?? []
                    ];
                }
            }

            # Conflict check: if remote exists and SHA differs from our stored SHA
            if ($remoteExists && isset($files[$remotePath]['remote_sha']) && $files[$remotePath]['remote_sha'] !== $remoteSha)
            {
                $conflicts[] = $remotePath;
                continue;
            }

            # Push file
            $result = $client->updateFile($remotePath, $localContent, $defaultMessage, $remoteSha);

            if ($result === false)
            {
                $pluginData = $this->addHistoryEntry($pluginData, [
                    'action'        => 'push',
                    'connection_id' => $connectionId,
                    'status'        => 'error',
                    'files'         => 0,
                    'message'       => 'Failed to push ' . $remotePath . ': ' . $client->getLastError()
                ]);
                return [
                    'success'       => false,
                    'status_code'   => 500,
                    'message'       => 'Failed to push ' . $remotePath . ': ' . $client->getLastError(),
                    'pluginData'    => $pluginData,
                    'files'         => 0,
                    'skipped'       => $filesSkipped,
                    'conflicts'     => [],
                    'detail'        => '',
                    'history'       => $pluginData['history'] ?? []
                ];
            }

            # Update stored SHA and hash from response
            $updatedFiles[$remotePath] = [
                'remote_sha' => $result['sha'] ?? ($files[$remotePath]['remote_sha'] ?? null),
                'local_hash' => $localHash
            ];
            if (!empty($result['commit_sha']))
            {
                $lastCommitSha = $result['commit_sha'];
            }

            $filesPushed++;
        }

        if (!empty($conflicts))
        {
            $pluginData = $this->addHistoryEntry($pluginData, [
                'action'        => 'push',
                'connection_id' => $connectionId,
                'status'        => 'error',
                'files'         => 0,
                'message'       => 'Push aborted. Remote file(s) have changed. Please pull the latest version first. Conflicts: ' . implode(', ', $conflicts)
            ]);
            return [
                'success'       => false,
                'status_code'   => 409,
                'message'       => 'Push aborted. Remote file(s) have changed. Please pull the latest version first.',
                'pluginData'    => $pluginData,
                'files'         => 0,
                'skipped'       => $filesSkipped,
                'conflicts'     => $conflicts,
                'detail'        => '',
                'history'       => $pluginData['history'] ?? []
            ];
        }

        # Update connection data
        if (isset($pluginData['connections'][$connectionId]))
        {
            $pluginData['connections'][$connectionId]['files'] = $updatedFiles;

            # Use the commit SHA from the last updateFile response as the
            # authoritative new head, because GitHub's commit list can lag
            # slightly after a push. Fall back to the API only if needed.
            if ($lastCommitSha)
            {
                $pluginData['connections'][$connectionId]['last_sync_sha'] = $lastCommitSha;
            }
            else
            {
                $lastRemoteSha = $client->getLatestCommitSha();
                if ($lastRemoteSha)
                {
                    $pluginData['connections'][$connectionId]['last_sync_sha'] = $lastRemoteSha;
                }
                else
                {
                    $pluginData = $this->addHistoryEntry($pluginData, [
                        'action'        => 'push',
                        'connection_id' => $connectionId,
                        'status'        => 'warning',
                        'files'         => $filesPushed,
                        'message'       => 'Pushed files, but could not determine the latest remote commit. Sync status may be incorrect until the next pull.'
                    ]);
                }
            }
        }

        $detail = 'Pushed ' . $filesPushed . ' file' . ($filesPushed !== 1 ? 's' : '') . ' to remote';
        if ($filesSkipped > 0)
        {
            $detail .= ', skipped ' . $filesSkipped . ' unchanged';
        }

        $pluginData = $this->addHistoryEntry($pluginData, [
            'action'        => 'push',
            'connection_id' => $connectionId,
            'status'        => 'success',
            'files'         => $filesPushed,
            'message'       => $detail
        ]);

        return [
            'success'       => true,
            'status_code'   => 200,
            'message'       => 'Push completed',
            'pluginData'    => $pluginData,
            'files'         => $filesPushed,
            'skipped'       => $filesSkipped,
            'conflicts'     => [],
            'detail'        => $detail,
            'history'       => $pluginData['history'] ?? []
        ];
    }

    /* ================================================================
       STATUS
       ================================================================ */

    public function status(Request $request, Response $response, $args)
    {
        $connectionId = $args['id'] ?? false;
        $pluginData = $this->getPluginYamlData('gitsync.yaml') ?? [];
        $connections = $pluginData['connections'] ?? [];

        if (!$connectionId)
        {
            return $this->errorResponse($response, 'Connection ID required', 400, $pluginData);
        }

        if (!isset($connections[$connectionId]))
        {
            return $this->errorResponse($response, 'Connection not found', 404, $pluginData);
        }

        $connection = $connections[$connectionId];
        $status = $this->getRepositoryStatus($connection);

        if ($status === false)
        {
            return $this->errorResponse($response, 'Failed to compute status: ' . ($this->statusError ?? 'unknown error'), 500, $pluginData);
        }

        $response->getBody()->write(json_encode($status));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /* ================================================================
       AUTO-SYNC
       ================================================================ */

    public function autoSync()
    {
        try
        {
            $pluginData = $this->getPluginYamlData('gitsync.yaml') ?? [];
            $connections = $pluginData['connections'] ?? [];

            $autoSyncConnections = [];
            foreach ($connections as $connectionId => $connection)
            {
                if (!empty($connection['auto_sync']))
                {
                    $autoSyncConnections[$connectionId] = $connection;
                }
            }

            if (empty($autoSyncConnections))
            {
                return;
            }

            $storage = $this->getStorage();
            $anyChanges = false;
            $processedAny = false;

            foreach ($autoSyncConnections as $connectionId => $connection)
            {
                $intervalSeconds = (int) ($connection['auto_sync_interval'] ?? 10) * 60;
                $timerName = 'gitsync_' . $connectionId;

                if (!$storage->timeoutIsOver($timerName, $intervalSeconds))
                {
                    continue;
                }

                $processedAny = true;

                # Pull first
                $pullResult = $this->performPull($connectionId, $connection, $pluginData);
                $pluginData = $pullResult['pluginData'];
                $connection = $pluginData['connections'][$connectionId] ?? $connection;
                $anyChanges = $anyChanges || ($pullResult['files'] > 0) || ($pullResult['deleted'] > 0);

                if (!$pullResult['success'])
                {
                    # Abort this connection; do not push after a failed or conflicted pull
                    continue;
                }

                # Then push local changes
                $pushResult = $this->performPush($connectionId, $connection, $pluginData, 'Auto-sync from Typemill');
                $pluginData = $pushResult['pluginData'];
                $anyChanges = $anyChanges || ($pushResult['files'] > 0);
            }

            if ($processedAny)
            {
                $this->storePluginYamlData('gitsync.yaml', $pluginData);

                if ($anyChanges)
                {
                    $this->clearNavigationCache();
                }
            }
        }
        catch (\Throwable $e)
        {
            error_log('GitSync autoSync error: ' . $e->getMessage());
        }
    }

    /* ================================================================
       HELPERS
       ================================================================ */

    private function getStorage()
    {
        if (!$this->storage)
        {
            $storageClass = $this->container->get('settings')['storage'];
            $this->storage = new StorageWrapper($storageClass);
        }
        return $this->storage;
    }

    private function isSyncableFile($filename)
    {
        return preg_match('/\.(md|markdown)$/i', $filename);
    }

    private function sanitizeLocalFolder($folder)
    {
        $folder = trim($folder, '/\\');
        # Empty string is valid (sync to root /content)
        if ($folder === '')
        {
            return '';
        }
        # Reject path traversal
        if (str_contains($folder, '..'))
        {
            return false;
        }
        # Allow alphanumeric, dashes, underscores, forward slashes
        if (!preg_match('/^[a-zA-Z0-9_\-\/]+$/', $folder))
        {
            return false;
        }
        return $folder;
    }

    private function sanitizeRepoPath($path)
    {
        $path = trim($path, '/\\');
        if ($path === '')
        {
            return '';
        }
        if (str_contains($path, '..'))
        {
            return '';
        }
        if (!preg_match('/^[a-zA-Z0-9_\-\/\.\*\?\+\[\]]+$/', $path))
        {
            return '';
        }
        return $path;
    }

    private function matchesPattern($filename, $pattern)
    {
        if (empty($pattern))
        {
            return $this->isSyncableFile($filename);
        }
        return fnmatch($pattern, $filename, FNM_CASEFOLD);
    }

    private function shouldSyncFile($filename, $repoPath, $filePattern)
    {
        # If repo_path is set, file must be under that path
        if (!empty($repoPath))
        {
            if (!str_starts_with($filename, $repoPath . '/'))
            {
                return false;
            }
        }
        # Apply file pattern filter
        return $this->matchesPattern($filename, $filePattern);
    }

    private function localPathFromRepoPath($repoPath, $repoFilePath)
    {
        if (empty($repoPath))
        {
            return $repoFilePath;
        }
        # Strip repo_path prefix
        $prefix = $repoPath . '/';
        if (str_starts_with($repoFilePath, $prefix))
        {
            return substr($repoFilePath, strlen($prefix));
        }
        return $repoFilePath;
    }

    private function writeContentFile($localFolder, $relativePath, $content)
    {
        $parts = explode('/', $relativePath);
        $filename = array_pop($parts);
        $subFolder = $localFolder;
        if (!empty($parts))
        {
            $subFolder .= '/' . implode('/', $parts);
        }

        $storage = $this->getStorage();
        $result = $storage->writeFile('contentFolder', $subFolder, $filename, $content);

        if ($result !== true)
        {
            error_log('GitSync writeContentFile failed: ' . $storage->getError());
        }

        return $result;
    }

    private function deleteContentFile($localFolder, $relativePath)
    {
        $parts = explode('/', $relativePath);
        $filename = array_pop($parts);
        $subFolder = $localFolder;
        if (!empty($parts))
        {
            $subFolder .= '/' . implode('/', $parts);
        }

        $storage = $this->getStorage();
        $result = $storage->deleteFile('contentFolder', $subFolder, $filename);

        if ($result !== true)
        {
            error_log('GitSync deleteContentFile failed: ' . $storage->getError());
        }

        return $result;
    }

    private function readContentFile($localFolder, $relativePath)
    {
        $parts = explode('/', $relativePath);
        $filename = array_pop($parts);
        $subFolder = $localFolder;
        if (!empty($parts))
        {
            $subFolder .= '/' . implode('/', $parts);
        }

        $storage = $this->getStorage();
        $content = $storage->getFile('contentFolder', $subFolder, $filename);

        if ($content === false)
        {
            return false;
        }

        return $content;
    }

    private function scanLocalMdFiles($localFolder)
    {
        $storage = $this->getStorage();
        $basePath = $storage->getFolderPath('contentFolder', $localFolder);

        if (!$basePath || !is_dir($basePath))
        {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file)
        {
            if ($file->isDir()) continue;
            $ext = strtolower($file->getExtension());
            if ($ext !== 'md' && $ext !== 'markdown') continue;

            $absolutePath = $file->getPathname();
            $relativePath = substr($absolutePath, strlen($basePath));
            $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

            $files[] = $relativePath;
        }

        return $files;
    }

    private function clearNavigationCache()
    {
        $navigation = new Navigation();
        $navigation->clearAllNavigations();
    }

    private function getRepositoryStatus(array $connection)
    {
        $this->statusError = null;

        $localFolder = $connection['local_folder'] ?? '';
        $repoPath = $connection['repo_path'] ?? '';
        $filePattern = $connection['file_pattern'] ?? '';

        $files = $connection['files'] ?? [];
        $lastSyncSha = $connection['last_sync_sha'] ?? null;

        $pluginSettings = $this->getPluginSettings();
        $timeout = isset($pluginSettings['timeout']) ? (int) $pluginSettings['timeout'] : 60;
        if ($timeout < 5) { $timeout = 5; }

        $settingsModel = new Settings();
        $apikey = $settingsModel->getSecret('github_token', 'plugins', 'gitsync');

        if (!$apikey)
        {
            $this->statusError = 'Token not configured';
            return false;
        }

        $client = GitPlatformClient::factory([
            'platform'      => $connection['platform'] ?? 'github',
            'owner'         => $connection['owner'],
            'repo'          => $connection['repo'],
            'branch'        => $connection['branch'] ?? 'main',
            'token'         => $apikey,
            'timeout'       => $timeout,
            'api_base_url'  => $connection['api_base_url'] ?? ''
        ]);

        $latestRemoteSha = $client->getLatestCommitSha();
        if (!$latestRemoteSha)
        {
            $this->statusError = $client->getLastError();
            return false;
        }

        # Build set of all file paths that are tracked or present locally
        $allFiles = [];
        foreach (array_keys($files) as $path)
        {
            $allFiles[$path] = true;
        }

        $localFiles = $this->scanLocalMdFiles($localFolder);
        foreach ($localFiles as $relativePath)
        {
            $repoFilePath = $this->repoFilePathFromLocalPath($repoPath, $relativePath);
            if ($this->matchesPattern($repoFilePath, $filePattern))
            {
                $allFiles[$repoFilePath] = true;
            }
        }

        # Detect local changes
        $localChanged = [];
        foreach (array_keys($allFiles) as $path)
        {
            $localPath = $this->localPathFromRepoPath($repoPath, $path);
            $localContent = $this->readContentFile($localFolder, $localPath);

            if ($localContent === false)
            {
                # File does not exist locally
                $localChanged[$path] = false;
                continue;
            }

            $localHash = sha1($localContent);
            $storedHash = $files[$path]['local_hash'] ?? null;

            if ($storedHash === null)
            {
                # File exists locally but was not tracked -> local change
                $localChanged[$path] = true;
            }
            else
            {
                $localChanged[$path] = ($storedHash !== $localHash);
            }
        }

        # Detect remote changes
        $remoteChanged = [];
        if (!empty($lastSyncSha) && $lastSyncSha !== $latestRemoteSha)
        {
            $changedFiles = $client->compareCommits($lastSyncSha, $latestRemoteSha);
            if ($changedFiles === false)
            {
                $this->statusError = $client->getLastError();
                return false;
            }

            foreach ($changedFiles as $file)
            {
                $filename = $file['filename'];
                if ($this->shouldSyncFile($filename, $repoPath, $filePattern))
                {
                    $remoteChanged[$filename] = true;
                    $allFiles[$filename] = true;
                }
            }
        }

        # Build status table
        $statusList = [];
        $counts = [
            'sync'      => 0,
            'push'      => 0,
            'pull'      => 0,
            'conflicts' => 0
        ];

        foreach (array_keys($allFiles) as $path)
        {
            $local = $localChanged[$path] ?? false;
            $remote = $remoteChanged[$path] ?? false;

            if ($local && $remote)
            {
                $status = 'conflict';
            }
            elseif ($local)
            {
                $status = 'push';
            }
            elseif ($remote)
            {
                $status = 'pull';
            }
            else
            {
                $status = 'sync';
            }

            $statusList[] = [
                'path'   => $path,
                'status' => $status
            ];
            $counts[$status]++;
        }

        return [
            'connection_id' => $connection['id'] ?? null,
            'last_sync_sha' => $lastSyncSha,
            'latest_remote_sha' => $latestRemoteSha,
            'first_sync' => empty($lastSyncSha),
            'sync' => $counts['sync'],
            'push' => $counts['push'],
            'pull' => $counts['pull'],
            'conflicts' => $counts['conflicts'],
            'files' => $statusList
        ];
    }

    private function repoFilePathFromLocalPath(string $repoPath, string $relativePath): string
    {
        if (empty($repoPath))
        {
            return $relativePath;
        }
        return $repoPath . '/' . $relativePath;
    }

    private function addHistoryEntry(array $data, array $entry)
    {
        if (!isset($data['history']) || !is_array($data['history']))
        {
            $data['history'] = [];
        }

        $historyEntry = [
            'time'          => date('c'),
            'connection_id' => $entry['connection_id'] ?? null,
            'action'        => $entry['action'] ?? 'unknown',
            'status'        => $entry['status'] ?? 'success',
            'files'         => $entry['files'] ?? 0,
            'message'       => $entry['message'] ?? ''
        ];

        array_unshift($data['history'], $historyEntry);
        $data['history'] = array_slice($data['history'], 0, 20);

        return $data;
    }

    private function errorResponse(Response $response, string $message, int $status, array $pluginData, array $extra = [])
    {
        $payload = array_merge([
            'message' => $message,
            'history' => $pluginData['history'] ?? []
        ], $extra);

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
