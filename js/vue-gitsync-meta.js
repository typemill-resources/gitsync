app.component('tab-gitsync', {
	props: ['item', 'formData', 'formDefinitions', 'saved'],
	data: function(){
		return {
			connections: {},
			connection: null,
			status: null,
			history: [],
			loading: false,
			error: false,
			message: false,
			messageClass: '',
			baseurl: typeof data !== 'undefined' && data.urlinfo ? data.urlinfo.baseurl : ''
		}
	},
	template: `<section class="dark:bg-stone-700 dark:text-stone-200">
					<form class="inline-block w-full mb-8">
						<div class="w-full">
							<fieldset class="w-full border border-stone-200 px-4 py-8">
								<legend class="text-lg font-medium">GitSync</legend>

								<div v-if="loading" class="text-stone-500 text-sm">
									Loading repository status...
								</div>

								<div v-else-if="error" class="bg-rose-500 text-white px-3 py-2 text-sm">
									{{ error }}
								</div>

								<div v-else-if="!connection" class="bg-stone-100 p-3 text-sm text-stone-600">
									<p>This page is not connected to a GitSync repository.</p>
								</div>

								<div v-else>
									<div class="flex items-center justify-between flex-wrap gap-2 mb-4">
										<div>
											<div class="font-medium text-sm">
												{{ connection.owner }}/{{ connection.repo }}
												<span class="text-stone-400 font-normal">({{ connection.branch }})</span>
											</div>
											<div class="text-xs text-stone-500 mt-1">
												{{ connection.platform || 'github' }} &rarr; content/{{ connection.local_folder || '(root)' }}
											</div>
										</div>
									</div>

									<div class="mb-4 flex">
										<span
											class="text-white text-xs px-2 py-1 font-medium mr-2"
											:class="statusBadgeClass">
											{{ statusBadgeLabel }}
										</span>
										<a
											:href="baseurl + '/tm/gitsync'"
											class="text-white text-xs px-2 py-1 font-medium bg-teal-500 hover:bg-teal-600 transition">
											Open GitSync admin
										</a>
									</div>

									<div v-if="hasUnsyncedFiles" class="border border-stone-200 dark:border-stone-700 overflow-hidden rounded mb-4">
										<div class="overflow-x-auto">
											<table class="w-full text-xs">
												<thead class="bg-stone-100 dark:bg-stone-900 text-stone-600 dark:text-stone-300">
													<tr>
														<th class="p-2 text-left font-medium">File</th>
														<th class="p-2 text-left font-medium whitespace-nowrap">Status</th>
													</tr>
												</thead>
												<tbody class="divide-y divide-stone-200 dark:divide-stone-700">
													<tr
														v-for="(file, idx) in unsyncedFiles"
														:key="idx"
														class="transition hover:bg-stone-50 dark:hover:bg-stone-700"
														:class="idx % 2 === 0 ? 'bg-white dark:bg-stone-800' : 'bg-stone-50 dark:bg-stone-750'"
													>
														<td class="p-2 font-mono text-stone-600 dark:text-stone-400">{{ file.path }}</td>
														<td class="p-2 whitespace-nowrap">
															<span v-if="file.status === 'push'" class="text-blue-600">&uarr; push</span>
															<span v-else-if="file.status === 'pull'" class="text-blue-600">&darr; pull</span>
															<span v-else-if="file.status === 'conflict'" class="text-rose-600">&#9888; conflict</span>
															<span v-else>{{ file.status }}</span>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
									<div v-else class="text-xs text-stone-500 mb-4">
										All files are in sync.
									</div>

									<div class="text-sm font-medium mb-2 flex items-center gap-2">Sync history</div>
									<div class="border border-stone-200 dark:border-stone-700 overflow-hidden rounded">
										<div class="overflow-x-auto">
											<table class="w-full min-w-[600px] text-xs">
												<thead class="bg-stone-100 dark:bg-stone-900 text-stone-600 dark:text-stone-300">
													<tr>
														<th class="p-2 text-left font-medium whitespace-nowrap">Time</th>
														<th class="p-2 text-left font-medium whitespace-nowrap">Action</th>
														<th class="p-2 text-left font-medium whitespace-nowrap">Status</th>
														<th class="p-2 text-left font-medium whitespace-nowrap">Files</th>
														<th class="p-2 text-left font-medium">Message</th>
													</tr>
												</thead>
												<tbody class="divide-y divide-stone-200 dark:divide-stone-700">
													<tr
														v-for="(entry, idx) in history"
														:key="idx"
														class="transition hover:bg-stone-50 dark:hover:bg-stone-700"
														:class="idx % 2 === 0 ? 'bg-white dark:bg-stone-800' : 'bg-stone-50 dark:bg-stone-750'"
													>
														<td class="p-2 whitespace-nowrap">{{ formatTime(entry.time) }}</td>
														<td class="p-2 whitespace-nowrap capitalize">{{ entry.action }}</td>
														<td class="p-2 whitespace-nowrap">
															<span v-if="entry.status === 'success'" class="text-teal-500">
																<svg class="icon icon-check mr-1"><use xlink:href="#icon-check"></use></svg>Success
															</span>
															<span v-else class="text-rose-500">
																<svg class="icon icon-close mr-1"><use xlink:href="#icon-close"></use></svg>{{ entry.status }}
															</span>
														</td>
														<td class="p-2 whitespace-nowrap">{{ entry.files || 0 }}</td>
														<td class="p-2 text-stone-500 dark:text-stone-400">{{ entry.message }}</td>
													</tr>
													<tr v-if="history.length === 0">
														<td colspan="5" class="p-2 text-stone-400 dark:text-stone-500">No history for this repository yet.</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>

									<div class="block w-full h-8 my-1">
										<transition name="fade">
											<div v-if="message" :class="messageClass" class="text-white px-3 py-1 transition duration-100">{{ message }}</div>
										</transition>
									</div>
								</fieldset>
							</div>
						</form>
					</section>`,
	mounted: function()
	{
		this.loadStatus();
	},
	computed: {
		pagePath: function()
		{
			var path = this.item.path || this.item.pathWithoutType || this.item.urlRelWoF || '';
			return path.replace(/\\/g, '/').replace(/^\//, '').replace(/\.md$/i, '');
		},
		statusBadgeLabel: function()
		{
			if (!this.status) return 'Loading status...';
			if (this.needsSync) return 'sync now';
			return 'synced';
		},
		statusBadgeClass: function()
		{
			if (!this.status) return 'bg-stone-100 text-stone-600';
			if (this.needsSync) return 'bg-rose-500';
			return 'bg-teal-500';
		},
		needsSync: function()
		{
			if (!this.status) return false;
			if (this.status.conflicts > 0) return true;
			if (this.status.first_sync) return true;
			if (this.status.push > 0 || this.status.pull > 0) return true;
			return false;
		},
		unsyncedFiles: function()
		{
			if (!this.status || !this.status.files) return [];
			return this.status.files.filter(function(file) {
				return file.status !== 'sync';
			});
		},
		hasUnsyncedFiles: function()
		{
			return this.unsyncedFiles.length > 0;
		}
	},
	methods: {
		formatTime: function(time)
		{
			if (!time) return '';
			return new Date(time).toLocaleString();
		},
		findConnection: function()
		{
			var pagePath = this.pagePath;
			var matchedId = null;
			var matchedLength = -1;

			for (var id in this.connections)
			{
				if (!this.connections.hasOwnProperty(id)) continue;

				var folder = (this.connections[id].local_folder || '').replace(/\\/g, '/').replace(/\/$/, '');
				if (folder === '')
				{
					// Root connection only matches if no more specific connection matched
					if (matchedLength < 0)
					{
						matchedId = id;
						matchedLength = 0;
					}
					continue;
				}

				if (pagePath === folder || pagePath.indexOf(folder + '/') === 0)
				{
					if (folder.length > matchedLength)
					{
						matchedId = id;
						matchedLength = folder.length;
					}
				}
			}

			if (matchedId)
			{
				this.connection = this.connections[matchedId];
				this.connection.id = matchedId;
			}
			else
			{
				this.connection = null;
			}
		},
		loadStatus: function()
		{
			var self = this;
			this.loading = true;
			this.error = false;
			this.status = null;
			this.history = [];

			tmaxios.get('/api/v1/gitsync/connections')
				.then(function(response) {
					self.connections = response.data.connections || {};
					self.findConnection();

					if (!self.connection)
					{
						self.loading = false;
						return;
					}

					return Promise.all([
						tmaxios.get('/api/v1/gitsync/status/' + self.connection.id),
						tmaxios.get('/api/v1/gitsync/history/' + self.connection.id)
					]);
				})
				.then(function(results) {
					if (!results) return;
					self.status = results[0].data;
					self.history = self.filterHistory(results[1].data.history || []);
					self.loading = false;
				})
				.catch(function(error) {
					self.loading = false;
					if (error.response)
					{
						self.error = error.response.data.message || 'Failed to load repository status';
					}
					else
					{
						self.error = 'Failed to load repository status';
					}
				});
		},
		filterHistory: function(history)
		{
			var connectionId = this.connection ? this.connection.id : null;
			if (!connectionId) return [];
			return history.filter(function(entry) {
				return entry.connection_id === connectionId;
			}).slice(0, 10);
		}
	}
})
