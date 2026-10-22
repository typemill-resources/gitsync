<?php

namespace Plugins\Gitsync;

abstract class GitPlatformClient
{
    protected $owner;
    protected $repo;
    protected $branch;
    protected $token;
    protected $timeout = 60;
    protected $apiBaseUrl;
    protected $lastError = null;
    protected $lastHttpCode = null;

    public function __construct(array $config)
    {
        $this->owner = $config['owner'];
        $this->repo = $config['repo'];
        $this->branch = $config['branch'] ?? 'main';
        $this->token = $config['token'] ?? '';
        $this->timeout = isset($config['timeout']) ? (int) $config['timeout'] : 60;
        $this->apiBaseUrl = $config['api_base_url'] ?? '';
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastHttpCode()
    {
        return $this->lastHttpCode;
    }

    public static function factory(array $config): self
    {
        $platform = $config['platform'] ?? 'github';
        switch ($platform) {
            case 'github':
                return new GitHubClient($config);
            case 'gitlab':
                return new GitLabClient($config);
            default:
                throw new \RuntimeException("Unsupported Git platform: {$platform}");
        }
    }

    protected function makeRequest(string $method, string $url, array $headers, $data = null)
    {
        $curl = curl_init($url);
        if (!$curl) {
            $this->lastError = 'Failed to initialize cURL';
            return false;
        }

        if (defined('CURLSSLOPT_NATIVE_CA') && version_compare(curl_version()['version'], '7.71', '>=')) {
            curl_setopt($curl, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null) {
                $json = json_encode($data);
                if ($json === false) {
                    $this->lastError = 'JSON encoding error: ' . json_last_error_msg();
                    unset($curl);
                    return false;
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
            }
        } elseif ($method === 'GET') {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($curl);
        $this->lastHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $this->lastError = curl_error($curl);
            unset($curl);
            return false;
        }

        unset($curl);
        return $response;
    }

    /**
     * Get the latest commit SHA for the configured branch.
     * @return string|bool
     */
    abstract public function getLatestCommitSha();

    /**
     * Get all files in the repository recursively.
     * @return array [['path' => ..., 'sha' => ...], ...]|bool
     */
    abstract public function getTreeRecursive();

    /**
     * Get files changed between two commits.
     * @return array [['filename' => ..., 'status' => ..., 'sha' => ...], ...]|bool
     */
    abstract public function compareCommits(string $baseSha, string $headSha);

    /**
     * Get a single file's content and metadata.
     * @return array ['content' => string, 'sha' => string]|bool
     */
    abstract public function getFile(string $path);

    /**
     * Update or create a file on the remote.
     * @return array ['sha' => string]|bool
     */
    abstract public function updateFile(string $path, string $content, string $message, ?string $existingSha = null);

    /**
     * Delete a file on the remote.
     * @return array ['commit_sha' => string|null]|bool
     */
    abstract public function deleteFile(string $path, string $message, ?string $existingSha = null);
}
