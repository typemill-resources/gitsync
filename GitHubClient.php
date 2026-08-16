<?php

namespace Plugins\Gitsync;

class GitHubClient extends GitPlatformClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        if (empty($this->apiBaseUrl)) {
            $this->apiBaseUrl = 'https://api.github.com';
        }
    }

    protected function getAuthHeaders(): array
    {
        return [
            "Authorization: Bearer {$this->token}",
            "Accept: application/vnd.github+json",
            "User-Agent: Typemill-GitSync",
            "X-GitHub-Api-Version: 2022-11-28",
            "Content-Type: application/json"
        ];
    }

    protected function parseResponse(string $response)
    {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = 'Invalid JSON response';
            return false;
        }
        if (isset($data['message'])) {
            $message = $data['message'];
            if (stripos($message, 'Resource not accessible by personal access token') !== false)
            {
                $message .= ' (check token permissions: classic token needs the repo scope, fine-grained token needs Contents "Read and write" for this repository)';
            }
            $this->lastError = $message;
            return false;
        }
        return $data;
    }

    public function getLatestCommitSha()
    {
        $url = "{$this->apiBaseUrl}/repos/{$this->owner}/{$this->repo}/commits?sha={$this->branch}&per_page=1";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        return $data[0]['sha'] ?? false;
    }

    public function getTreeRecursive()
    {
        $url = "{$this->apiBaseUrl}/repos/{$this->owner}/{$this->repo}/git/trees/{$this->branch}?recursive=1";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        $items = [];
        foreach ($data['tree'] ?? [] as $item) {
            if ($item['type'] === 'blob') {
                $items[] = [
                    'path' => $item['path'],
                    'sha' => $item['sha']
                ];
            }
        }
        return $items;
    }

    public function compareCommits(string $baseSha, string $headSha)
    {
        $url = "{$this->apiBaseUrl}/repos/{$this->owner}/{$this->repo}/compare/{$baseSha}...{$headSha}";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        $files = [];
        foreach ($data['files'] ?? [] as $file) {
            $files[] = [
                'filename' => $file['filename'],
                'status' => $file['status'],
                'sha' => $file['sha'] ?? null
            ];
        }
        return $files;
    }

    public function getFile(string $path)
    {
        $url = "{$this->apiBaseUrl}/repos/{$this->owner}/{$this->repo}/contents/{$path}?ref={$this->branch}";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        if (!isset($data['content'])) {
            $this->lastError = 'No content in response';
            return false;
        }

        return [
            'content' => base64_decode($data['content']),
            'sha' => $data['sha'] ?? null
        ];
    }

    public function updateFile(string $path, string $content, string $message, ?string $existingSha = null)
    {
        $url = "{$this->apiBaseUrl}/repos/{$this->owner}/{$this->repo}/contents/{$path}";
        $payload = [
            'message' => $message,
            'content' => base64_encode($content),
            'branch' => $this->branch
        ];
        if ($existingSha) {
            $payload['sha'] = $existingSha;
        }

        $response = $this->makeRequest('PUT', $url, $this->getAuthHeaders(), $payload);
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        return [
            'sha' => $data['content']['sha'] ?? null,
            'commit_sha' => $data['commit']['sha'] ?? null
        ];
    }
}
