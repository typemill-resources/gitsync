# GitSync Plugin for Typemill

The **GitSync** plugin connects your Typemill website to one or more GitHub or GitLab repositories. It lets you pull Markdown content from a repository into Typemill, push changes from Typemill back to the repository, and keep everything in sync automatically or on demand.

This is useful for documentation sites, handbooks, or any content workflow where writers edit files in a Git repository while the website is managed by Typemill.

## What it does

- **Pull** Markdown files from a GitHub or GitLab repository into your `/content` folder.
- **Push** local changes from Typemill back to the repository as commits.
- **Track sync status** per file so you can see what needs to be pulled, pushed, or resolved.
- **Auto-sync** in the background while visitors browse the frontend.
- Support for syncing an entire repository or only a specific subfolder or file pattern.

## Requirements

- Typemill with PHP 8.2, 8.3, 8.4, or 8.5.
- A GitHub or GitLab repository with Markdown files.
- A personal access token with read and write access to the repository contents.

## Installation

1. Copy the plugin folder into `/plugins/gitsync`.
2. Activate the plugin in the Typemill admin under **Settings > Plugins**.

## Initial configuration

1. Open the Typemill admin and go to **Settings > Plugins > GitSync**.
2. Enter your **Git Personal Access Token**:
   - For GitHub, create a fine-grained token with **Contents: read and write** for the repository. A classic token with the `repo` scope also works.
   - For GitLab, create a token with the `api` scope.
3. Save the settings.

## Adding a repository connection

1. In the admin, click the **GitSync** item in the left navigation.
2. Click **Add repository**.
3. Fill in the connection details explained below.

| Field | Description |
|-------|-------------|
| **ID** | A unique internal name, e.g. `docs` or `api`. This cannot be changed later. |
| **Local Folder** | The subfolder under `/content` where files are stored. Leave empty to sync directly into `/content`. |
| **File Pattern** | Optional glob pattern, e.g. `*.md` or `index.md`. Leave empty to sync all Markdown files. |
| **Platform** | GitHub or GitLab. |
| **API Base URL** | Only needed for self-hosted GitLab or GitHub Enterprise. |
| **Owner / Organization** | The repository owner. |
| **Repository Name** | The repository name. |
| **Branch** | Usually `main`. |
| **Source Path in Repo** | Sync only a subfolder or file from the repository, e.g. `docs`. Leave empty for the whole repository. |
| **Auto-sync on frontend** | Pull and push automatically while the website is visited. |
| **Auto-sync interval** | Minimum time between automatic syncs, in minutes. |


## Pulling and pushing

Each repository card shows a status badge:

- **Synced** — local and remote files match.
- **Sync now** — there are changes to pull or push.

Use the buttons on each card:

- **Pull** — download remote changes and overwrite local files. If a local file has been changed since the last sync, the pull is aborted to avoid overwriting your work.
- **Push** — upload local changes to the repository. If a remote file has been changed since the last sync, the push is aborted.

When a conflict is detected, resolve it manually first, then pull or push again.

## Sync status

Click the status badge or the history icon on a repository card to open a detailed view:

- A list of files and their current status: `sync`, `push`, `pull`, or `conflict`.
- A history of recent pull and push operations.

## Auto-sync

If you enable **Auto-sync on frontend**, the plugin will try to pull remote changes and push local changes in the background during ordinary frontend page requests. The interval setting prevents too many API calls.

Auto-sync is convenient for live sites, but it depends on frontend traffic. For important updates, use a manual pull or push.

## Conflict resolution

The plugin does not merge files automatically. If both the local copy and the remote copy of a file changed since the last sync:

1. The conflicting file is marked as `conflict`.
2. Pull and push are disabled for that connection.
3. Resolve the conflict manually, either in Typemill or in the repository, and then sync again.

## Security notes

- Keep your personal access token private. Do not commit it to a repository.
- Use fine-grained tokens and restrict them to the repositories that Typemill should access.
- The token is stored in `/settings/secrets.yaml`, which must be protected by your server configuration.
