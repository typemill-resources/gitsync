# GitSync Plugin for Typemill

The **GitSync** plugin connects your Typemill website to one or more GitHub or GitLab repositories. It lets you pull Markdown content from a repository into Typemill, push changes from Typemill back to the repository, and keep everything in sync automatically or on demand.

This is useful for documentation sites, handbooks, or any content workflow where writers edit files in a Git repository while the website is managed by Typemill.

## What it does

- **Pull** Markdown files from a GitHub or GitLab repository into your `/content` folder.
- **Push** local changes from Typemill back to the repository as commits.
- **Track sync status** per file so you can see what needs to be pulled, pushed, or resolved.
- **Auto-sync** in the background while visitors browse the frontend.
- Support for syncing an entire repository or only a specific subfolder or file pattern, and for excluding local folders from the sync.

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
| **Include Local Folder** | The subfolder under `/content` where files are stored. Leave empty to sync directly into `/content`. |
| **Exclude Local Folders** | Optional comma separated list of local folders to exclude from syncing, e.g. `_de, _en, myfolder/secret`. Files in these folders are ignored by pull and push. |
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

- **Pull** — download remote changes and overwrite local files. Renames and deletions in the repository are applied locally (including the `.yaml`/`.txt` sidecar files), so no duplicates are left behind. If a local file has been changed since the last sync, the pull is aborted to avoid overwriting your work.
- **Push** — upload local changes to the repository. Files that were renamed, moved, or deleted in Typemill (for example after reordering pages, which changes the `01-` prefixes) are deleted remotely, so no duplicates are left behind. If a remote file has been changed since the last sync, the push is aborted.

When a conflict is detected, resolve it manually first, then pull or push again.

## Sync status

Click the status badge or the history icon on a repository card to open a detailed view:

- A list of files and their current status: `sync`, `push`, `pull`, or `conflict`.
- A history of recent pull and push operations.

## Auto-sync

If you enable **Auto-sync on frontend**, the plugin will try to pull remote changes and push local changes in the background during ordinary frontend page requests. The interval setting prevents too many API calls.

Auto-sync is convenient for live sites, but it depends on frontend traffic. For important updates, use a manual pull or push.

## Conflict resolution

The plugin does not merge files automatically. If both the Typemill copy and the Git repository copy of a file changed since the last sync, the file is marked as `conflict`, pull and push are blocked, and the repository card shows a resolve button.

The resolve section inside the repository status view lists all conflicting files. For each file, choose:

- **Keep Typemill version** — the Typemill version is pushed to the repository, overwriting the Git change.
- **Keep Git version** — the Git version is pulled over the Typemill file, overwriting the local change.

The Typemill version is preselected for every conflicting file. Two radio buttons in the section header bulk-select the Typemill version or the Git version for all conflicting files at once. The section also offers:

- **Force pull — Git version wins** — keeps the Git version for all conflicting files, then pulls all other remote changes normally.
- **Force push — Typemill version wins** — keeps the Typemill version for all conflicting files, then pushes all other local changes normally.

All actions require a confirmation that lists the affected files, and all stay inside the sync scope (source path, file pattern, excluded folders). Resolutions are logged in the sync history as `resolve`, `force-pull`, or `force-push`. After resolving the selection, run a normal pull or push to pick up the remaining changes.

If you prefer, you can still resolve a conflict manually: edit the file in Typemill so it matches the remote version (or revert the remote change in the repository), then sync again.

## Security notes

- Keep your personal access token private. Do not commit it to a repository.
- Use fine-grained tokens and restrict them to the repositories that Typemill should access.
- The token is stored in `/settings/secrets.yaml`, which must be protected by your server configuration.

## Changelog

### Version 1.0.0

Initial Release

### Version 1.1.0

* Sync files that got renamed or moved with new prefixes on push and pulls.

### Version 1.1.1

* Fixed PHP 8.4/8.5 deprecation warning for `curl_close()` in the API client.
* Fixed the conflict count in the sync status: it was always reported as `0`, so conflicting files were not marked in the admin interface and the pull/push buttons were not disabled.
* Fixed PHP 8.5 deprecation warnings (passing `null` to string functions) triggered by empty Git API responses and by GitLab diffs without `new_path`/`old_path`.

### Version 1.2.0

* New setting **Exclude Local Folders**: a comma separated list of local folders, e.g. `_de, _en, myfolder/secret`, that is excluded from syncing. Excluded folders are ignored by pull, push, and the sync status.
* Renamed the setting **Local Folder** to **Include Local Folder**.
* Fixed a PHP 8.1+ deprecation warning (automatic conversion of `false` to array) that occurred when the first repository connection was created on a fresh data folder.

### Version 1.3.0

* New conflict resolution section in the repository status view: lists all conflicting files and lets you keep the Typemill version or the Git version per file. The Typemill version is preselected, and radio buttons bulk-select Typemill or Git for all conflicts.
* New **Force pull** (Git version wins) and **Force push** (Typemill version wins) options for conflicting files, with a confirmation step.
* Aborted pulls and pushes now list the affected files directly in the message and expand the status view with the resolve section.