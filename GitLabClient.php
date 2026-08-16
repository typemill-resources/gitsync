<?php

namespace Plugins\Gitsync;

class GitLabClient extends GitPlatformClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        if (empty($this->apiBaseUrl)) {
            $this->apiBaseUrl = 'https://gitlab.com/api/v4';
        }
    }

    protected function getProjectPath(): string
    {
        # GitLab accepts URL-encoded owner/repo as project path
        return urlencode($this->owner) . '%2F' . urlencode($this->repo);
    }

    protected function getAuthHeaders(): array
    {
        return [
            "PRIVATE-TOKEN: {$this->token}",
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
            $this->lastError = $data['message'];
            return false;
        }
        if (isset($data['error'])) {
            $this->lastError = is_string($data['error']) ? $data['error'] : json_encode($data['error']);
            return false;
        }
        return $data;
    }

    public function getLatestCommitSha()
    {
        $project = $this->getProjectPath();
        $url = "{$this->apiBaseUrl}/projects/{$project}/repository/commits?ref_name={$this->branch}&per_page=1";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        return $data[0]['id'] ?? false;
    }

    public function getTreeRecursive()
    {
        $project = $this->getProjectPath();
        $allItems = [];
        $page = 1;
        $perPage = 100;

        while (true) {
            $url = "{$this->apiBaseUrl}/projects/{$project}/repository/tree?recursive=true&per_page={$perPage}&page={$page}&ref={$this->branch}";
            $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
            if (!$response) {
                return false;
            }

            $data = $this->parseResponse($response);
            if (!$data) {
                return false;
            }

            $items = [];
            foreach ($data as $item) {
                if ($item['type'] === 'blob') {
                    $items[] = [
                        'path' => $item['path'],
                        'sha' => $item['id'] ?? null
                    ];
                }
            }

            $allItems = array_merge($allItems, $items);

            if (count($data) < $perPage) {
                break;
            }
            $page++;
        }

        return $allItems;
    }

    public function compareCommits(string $baseSha, string $headSha)
    {
        $project = $this->getProjectPath();
        $url = "{$this->apiBaseUrl}/projects/{$project}/repository/compare?from={$baseSha}&to={$headSha}&straight=true";
        $response = $this->makeRequest('GET', $url, $this->getAuthHeaders());
        if (!$response) {
            return false;
        }

        $data = $this->parseResponse($response);
        if (!$data) {
            return false;
        }

        $files = [];
        foreach ($data['diffs'] ?? [] as $diff) {
            $status = 'modified';
            if (isset($diff['new_file']) && $diff['new_file']) {
                $status = 'added';
            } elseif (isset($diff['deleted_file']) && $diff['deleted_file']) {
                $status = 'removed';
            } elseif (isset($diff['renamed_file']) && $diff['renamed_file']) {
                $status = 'renamed';
            }

            $files[] = [
                'filename' => $diff['new_path'] ?? $diff['old_path'],
                'status' => $status,
                'sha' => null
            ];
        }
        return $files;
    }

    public function getFile(string $path)
    {
        $project = $this->getProjectPath();
        $encodedPath = urlencode($path);
        $url = "{$this->apiBaseUrl}/projects/{$project}/repository/files/{$encodedPath}?ref={$this->branch}";
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

        # GitLab returns base64 content by default
        $content = base64_decode($data['content']);
        if ($content === false) {
            $this->lastError = 'Failed to decode base64 content';
            return false;
        }

        return [
            'content' => $content,
            'sha' => $data['blob_id'] ?? $data['commit_id'] ?? null
        ];
    }

    public function updateFile(string $path, string $content, string $message, ?string $existingSha = null)
    {
        $project = $this->getProjectPath();
        $encodedPath = urlencode($path);
        $url = "{$this->apiBaseUrl}/projects/{$project}/repository/files/{$encodedPath}";

        $payload = [
            'branch' => $this->branch,
            'content' => $content,
            'commit_message' => $message,
            'encoding' => 'text'
        ];

        # GitLab uses actions array for create/update
        # For single file update, use PUT with the file endpoint
        if ($existingSha) {
            $payload['last_commit_id'] = $existingSha;
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
            'sha' => $data['blob_id'] ?? $data['commit_id'] ?? null,
            'commit_sha' => $data['commit_id'] ?? $data['blob_id'] ?? null
        ];
    }
}
