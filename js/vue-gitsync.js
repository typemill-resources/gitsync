const app = Vue.createApp({
	template: `
	<Transition name="initial" appear>
		<div>
			<svg style="display:none;">
				<symbol id="icon-pencil" viewBox="0 0 32 32">
					<path d="M27 0c2.761 0 5 2.239 5 5 0 1.126-0.372 2.164-1 3l-2 2-7-7 2-2c0.836-0.628 1.874-1 3-1zM2 23l-2 9 9-2 18.5-18.5-7-7-18.5 18.5zM22.362 11.362l-14 14-1.724-1.724 14-14 1.724 1.724z"></path>
				</symbol>
				<symbol id="icon-history" viewBox="0 0 34 32">
					<path d="M20 2c7.732 0 14 6.268 14 14s-6.268 14-14 14v-3c2.938 0 5.701-1.144 7.778-3.222s3.222-4.84 3.222-7.778c0-2.938-1.144-5.701-3.222-7.778s-4.84-3.222-7.778-3.222c-2.938 0-5.701 1.144-7.778 3.222-1.598 1.598-2.643 3.601-3.041 5.778h5.819l-7 8-7-8h5.143c0.971-6.784 6.804-12 13.857-12zM26 14v4h-8v-10h4v6z"></path>
				</symbol>
				<symbol id="icon-bin" viewBox="0 0 32 32">
					<path d="M4 10v20c0 1.1 0.9 2 2 2h18c1.1 0 2-0.9 2-2v-20h-22zM10 28h-2v-14h2v14zM14 28h-2v-14h2v14zM18 28h-2v-14h2v14zM22 28h-2v-14h2v14z"></path>
					<path d="M26.5 4h-6.5v-2.5c0-0.825-0.675-1.5-1.5-1.5h-7c-0.825 0-1.5 0.675-1.5 1.5v2.5h-6.5c-0.825 0-1.5 0.675-1.5 1.5v2.5h26v-2.5c0-0.825-0.675-1.5-1.5-1.5zM18 4h-6v-1.975h6v1.975z"></path>
				</symbol>
				<symbol id="icon-arrow-up2" viewBox="0 0 32 32">
					<path d="M27.414 12.586l-10-10c-0.781-0.781-2.047-0.781-2.828 0l-10 10c-0.781 0.781-0.781 2.047 0 2.828s2.047 0.781 2.828 0l6.586-6.586v19.172c0 1.105 0.895 2 2 2s2-0.895 2-2v-19.172l6.586 6.586c0.39 0.39 0.902 0.586 1.414 0.586s1.024-0.195 1.414-0.586c0.781-0.781 0.781-2.047 0-2.828z"></path>
				</symbol>
				<symbol id="icon-arrow-down2" viewBox="0 0 32 32">
					<path d="M27.414 19.414l-10 10c-0.781 0.781-2.047 0.781-2.828 0l-10-10c-0.781-0.781-0.781-2.047 0-2.828s2.047-0.781 2.828 0l6.586 6.586v-19.172c0-1.105 0.895-2 2-2s2 0.895 2 2v19.172l6.586-6.586c0.39-0.39 0.902-0.586 1.414-0.586s1.024 0.195 1.414 0.586c0.781 0.781 0.781 2.047 0 2.828z"></path>
				</symbol>
				<symbol id="icon-git" viewBox="0 0 32 32">
					<path d="M31.397 14.575l-13.972-13.971c-0.804-0.805-2.109-0.805-2.915 0l-3.246 3.246 2.443 2.443c0.392-0.187 0.83-0.293 1.293-0.293 1.657 0 3 1.343 3 3 0 0.463-0.105 0.902-0.293 1.293l3.999 3.999c0.392-0.187 0.83-0.293 1.293-0.293 1.657 0 3 1.343 3 3s-1.343 3-3 3-3-1.343-3-3c0-0.463 0.105-0.902 0.293-1.293l-3.999-3.999c-0.095 0.046-0.193 0.086-0.293 0.121v8.343c1.165 0.412 2 1.522 2 2.829 0 1.657-1.343 3-3 3s-3-1.343-3-3c0-1.306 0.835-2.417 2-2.829v-8.343c-1.165-0.412-2-1.522-2-2.829 0-0.463 0.105-0.902 0.293-1.293l-2.443-2.443-9.247 9.246c-0.805 0.806-0.805 2.11 0 2.915l13.973 13.971c0.804 0.805 2.109 0.805 2.915 0l13.906-13.906c0.805-0.805 0.805-2.111-0-2.915z"></path>
				</symbol>
			</svg>

			<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
				<h1 class="text-3xl font-bold mb-4">Repositories</h1>
				<button
					class="px-3 py-2 bg-stone-700 text-white text-sm font-medium flex items-center gap-2 hover:bg-stone-900 transition shrink-0 whitespace-nowrap"
					@click.prevent="startAddForm"
					:disabled="loading">
					<svg class="icon icon-plus text-xs mr-1"><use xlink:href="#icon-plus"></use></svg> Add repository
				</button>
			</div>

			<div v-if="connections.length === 0" class="text-stone-500 italic mb-4 p-4 border border-stone-200 bg-stone-50">
				No repositories connected yet.
			</div>

			<div v-else class="space-y-4 mb-6">
				<div v-for="conn in connections" :key="conn.id" class="border border-stone-200 bg-white overflow-hidden">

					<!-- Card Header -->
					<div class="p-4 flex justify-between items-start flex-wrap">
						<div class="flex items-start">
							<div>
								<div class="font-medium text-sm">
									{{ conn.owner }}/{{ conn.repo }}
									<span class="text-stone-400 font-normal">({{ conn.branch }})</span>
								</div>
                                <div class="text-xs text-stone-500 mt-1">
                                    {{ conn.platform || 'github' }} → content/{{ conn.local_folder || '(root)' }}
                                    <span v-if="conn.repo_path">/ {{ conn.repo_path }}</span>
                                </div>
                                <div v-if="conn.auto_sync" class="text-xs text-teal-600 mt-1 flex items-center">
                                    <svg class="icon icon-history text-xs mr-1"><use xlink:href="#icon-history"></use></svg>
                                    Auto-sync every {{ conn.auto_sync_interval || 10 }} min
                                </div>
                                <div class="flex items-center mt-2 flex-wrap gap-2">
									<button
										class="text-white text-xs px-2 font-medium cursor-pointer transition hover:opacity-90 mr-2"
										:class="statusBadgeClass(conn.id)"
										@click.prevent="toggleHistory(conn.id)">
										{{ statusBadgeLabel(conn.id) }}
									</button>
									<span class="text-xs text-stone-400">
										{{ conn.last_sync_time ? formatTime(conn.last_sync_time) : 'Never synced' }}
										<span v-if="conn.last_sync_files !== undefined"> · {{ conn.last_sync_files }} files</span>
									</span>
								</div>
								<div v-if="connStatus(conn.id).conflicts > 0" class="text-xs text-rose-600 mt-1">
									{{ connStatus(conn.id).conflicts }} conflicting file{{ connStatus(conn.id).conflicts === 1 ? '' : 's' }} (changed in Typemill and in the Git repository).
									<button class="underline font-medium hover:text-rose-800 transition" @click.prevent="openResolve(conn.id)">Resolve now</button>
								</div>
							</div>
						</div>

							<div class="flex flex-col items-end">
								<div class="flex">
									<button
										class="px-3 py-1 m-1 text-sm font-medium flex items-center transition disabled:cursor-not-allowed"
										:class="(loading || !canPull(conn.id)) ? 'bg-stone-200 text-stone-800' : 'bg-teal-500 text-white hover:bg-teal-600'"
										@click.prevent="pullNow(conn.id)"
										:disabled="loading || !canPull(conn.id)">
										<svg class="icon icon-arrow-down2 text-xs mr-1"><use xlink:href="#icon-arrow-down2"></use></svg> Pull
									</button>
									<button
										class="px-3 py-1 m-1 text-sm font-medium flex items-center transition disabled:cursor-not-allowed"
										:class="(loading || !canPush(conn.id)) ? 'bg-stone-200 text-stone-800' : 'bg-teal-500 text-white hover:bg-teal-600'"
										@click.prevent="pushNow(conn.id)"
										:disabled="loading || !canPush(conn.id)">
										<svg class="icon icon-arrow-up2 text-xs mr-1"><use xlink:href="#icon-arrow-up2"></use></svg> Push
									</button>
								</div>
							<div class="flex justify-end mt-2">
								<button
									title="History"
									class="w-6 h-6 ml-1 mr-1 flex items-center justify-center text-stone-400 hover:text-stone-800 hover:bg-stone-200 transition"
									@click.prevent="toggleHistory(conn.id)">
									<svg class="icon icon-history text-sm"><use xlink:href="#icon-history"></use></svg>
								</button>
								<button
									title="Edit"
									class="w-6 h-6 ml-1 mr-1 flex items-center justify-center text-stone-400 hover:text-stone-800 hover:bg-stone-200 transition"
									@click.prevent="editConnection(conn)"
									:disabled="loading">
									<svg class="icon icon-pencil text-sm"><use xlink:href="#icon-pencil"></use></svg>
								</button>
								<button
									title="Delete"
									class="w-6 h-6 ml-1 mr-1 flex items-center justify-center text-stone-400 hover:text-white hover:bg-rose-500 transition"
									@click.prevent="deleteConnection(conn.id)"
									:disabled="loading">
									<svg class="icon icon-bin text-sm"><use xlink:href="#icon-bin"></use></svg>
								</button>
							</div>
						</div>
					</div>

					<!-- Sync Progress -->
					<div v-if="activeSync[conn.id]" class="mb-0 p-3 text-stone-900 bg-stone-200 text-sm transition flex items-center">
						<div class="text-sm flex items-center">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-4 w-4 animate-spin mr-2"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
							{{ activeSync[conn.id] }}
						</div>
					</div>

					<!-- Per-Card History -->
					<div v-if="historyVisible[conn.id]" class="border-t border-stone-200 bg-stone-50 p-4">
						<div class="flex justify-between items-center mb-3">
							<div class="text-sm font-medium flex items-center gap-2">
								Repository status
							</div>
							<button
								class="text-stone-400 hover:text-stone-700 transition"
								@click.prevent="toggleHistory(conn.id)">
								<svg class="icon icon-close text-sm"><use xlink:href="#icon-close"></use></svg>
							</button>
						</div>

						<!-- Conflict resolution -->
						<div v-if="connStatus(conn.id).conflicts > 0" class="border border-rose-200 bg-white rounded mb-4 p-4">

							<!-- Stage: selection -->
							<div v-if="!resolveConfirm[conn.id]">
								<div class="flex justify-between items-center flex-wrap gap-2 mb-3">
									<div class="text-sm font-medium text-rose-700 flex items-center gap-2">
										Resolve conflicts
									</div>
									<div class="flex items-center gap-4 text-xs text-stone-600">
										<label class="flex items-center gap-1 cursor-pointer">
											<input type="radio" :name="'bulk-' + conn.id" :checked="allDecisionsFor(conn.id, 'local')" @change="setAllDecisions(conn.id, 'local')">
											All Typemill version
										</label>
										<label class="flex items-center gap-1 cursor-pointer">
											<input type="radio" :name="'bulk-' + conn.id" :checked="allDecisionsFor(conn.id, 'remote')" @change="setAllDecisions(conn.id, 'remote')">
											All Git version
										</label>
									</div>
								</div>

								<p class="text-xs text-stone-500 mb-3">
									These files changed in Typemill <strong>and</strong> in the Git repository since the last sync.
									Choose the version to keep per file. "Keep Typemill version" overwrites the Git repository,
									"Keep Git version" overwrites the Typemill file.
								</p>

								<div class="border border-stone-200 overflow-hidden rounded mb-4">
									<div class="overflow-x-auto">
										<table class="w-full text-xs">
											<thead class="bg-stone-100 text-stone-600">
												<tr>
													<th class="p-2 text-left font-medium">File</th>
													<th class="p-2 text-left font-medium whitespace-nowrap">Keep Typemill version</th>
													<th class="p-2 text-left font-medium whitespace-nowrap">Keep Git version</th>
												</tr>
											</thead>
											<tbody class="divide-y divide-stone-200">
												<tr v-for="file in conflictFiles(conn.id)" :key="file.path">
													<td class="p-2 font-mono text-stone-600">{{ file.path }}</td>
													<td class="p-2 whitespace-nowrap"><input type="radio" :name="'keep-' + conn.id + '-' + file.path" value="local" v-model="resolveDecisions[conn.id][file.path]"></td>
													<td class="p-2 whitespace-nowrap"><input type="radio" :name="'keep-' + conn.id + '-' + file.path" value="remote" v-model="resolveDecisions[conn.id][file.path]"></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>

								<div class="flex flex-wrap justify-end gap-2">
									<button type="button" class="px-3 py-2 text-sm text-white bg-stone-700 hover:bg-stone-900 transition disabled:cursor-not-allowed" :disabled="!allDecisionsMade(conn.id) || resolveBusy[conn.id]" @click.prevent="confirmResolveAction(conn.id, 'resolve')">
										Resolve selection
									</button>
									<button type="button" class="px-3 py-2 text-sm text-white bg-rose-500 hover:bg-rose-700 transition disabled:cursor-not-allowed" :disabled="resolveBusy[conn.id]" @click.prevent="confirmResolveAction(conn.id, 'force-pull')">
										Force pull — Git version wins
									</button>
									<button type="button" class="px-3 py-2 text-sm text-white bg-rose-500 hover:bg-rose-700 transition disabled:cursor-not-allowed" :disabled="resolveBusy[conn.id]" @click.prevent="confirmResolveAction(conn.id, 'force-push')">
										Force push — Typemill version wins
									</button>
								</div>

								<p class="text-xs text-stone-400 mt-3">
									Force pull/push apply your choice to all conflicting files and then sync all other changes normally.
									After resolving the selection, run a normal pull or push for remaining changes.
								</p>
							</div>

							<!-- Stage: confirmation -->
							<div v-if="resolveConfirm[conn.id]">
								<div class="text-sm font-medium text-rose-700 mb-2">
									{{ resolveConfirm[conn.id] === 'resolve' ? 'Confirm resolution' : (resolveConfirm[conn.id] === 'force-pull' ? 'Confirm force pull' : 'Confirm force push') }}
								</div>

								<div v-if="resolveConfirm[conn.id] === 'resolve'" class="text-sm text-stone-700 mb-4">
									<div v-if="decisionPathsFor(conn.id, 'local').length > 0">
										<strong>Keep Typemill version</strong> (overwrites the Git repository):
										<span class="font-mono text-xs">{{ decisionPathsFor(conn.id, 'local').join(', ') }}</span>
									</div>
									<div v-if="decisionPathsFor(conn.id, 'remote').length > 0" class="mt-2">
										<strong>Keep Git version</strong> (overwrites the Typemill file):
										<span class="font-mono text-xs">{{ decisionPathsFor(conn.id, 'remote').join(', ') }}</span>
									</div>
								</div>

								<div v-else-if="resolveConfirm[conn.id] === 'force-pull'" class="text-sm text-stone-700 mb-4">
									Force pull keeps the <strong>Git version</strong> for all conflicting files. Your Typemill changes to these files are lost:
									<div class="font-mono text-xs mt-1">{{ conflictPathsList(conn.id).join(', ') }}</div>
									All other remote changes are pulled normally afterwards. This cannot be undone.
								</div>

								<div v-else class="text-sm text-stone-700 mb-4">
									Force push keeps the <strong>Typemill version</strong> for all conflicting files. Git changes to these files are lost:
									<div class="font-mono text-xs mt-1">{{ conflictPathsList(conn.id).join(', ') }}</div>
									All other local changes are pushed normally afterwards. This cannot be undone.
								</div>

								<div class="flex justify-end gap-3">
									<button type="button" class="px-4 p-3 bg-stone-200 hover:bg-stone-300 transition" @click.prevent="backToResolveSelect(conn.id)" :disabled="resolveBusy[conn.id]">Back</button>
									<button type="button" class="px-4 p-3 text-white bg-rose-500 hover:bg-rose-700 transition disabled:cursor-not-allowed" :disabled="resolveBusy[conn.id]" @click.prevent="executeResolveAction(conn.id)">
										{{ resolveBusy[conn.id] ? 'Working...' : (resolveConfirm[conn.id] === 'resolve' ? 'Resolve now' : (resolveConfirm[conn.id] === 'force-pull' ? 'Force pull now' : 'Force push now')) }}
									</button>
								</div>
							</div>

						</div>

						<!-- Unsynced file list -->
						<div v-if="hasUnsyncedFiles(conn.id)" class="border border-stone-200 dark:border-stone-700 overflow-hidden rounded mb-4">
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
						                    v-for="(file, idx) in unsyncedFiles(conn.id)"
						                    :key="idx"
						                    class="transition hover:bg-stone-50 dark:hover:bg-stone-700"
						                    :class="idx % 2 === 0 ? 'bg-white dark:bg-stone-800' : 'bg-stone-50 dark:bg-stone-750'"
						                >
						                    <td class="p-2 font-mono text-stone-600 dark:text-stone-400">{{ file.path }}</td>
						                    <td class="p-2 whitespace-nowrap">
						                        <span v-if="file.status === 'push'" class="text-blue-600">↑ push</span>
						                        <span v-else-if="file.status === 'pull'" class="text-blue-600">↓ pull</span>
						                        <span v-else-if="file.status === 'conflict'" class="text-rose-600">⚠ conflict</span>
						                        <span v-else>{{ file.status }}</span>
						                    </td>
						                </tr>
						            </tbody>
						        </table>
						    </div>
						</div>
						<div v-else-if="connStatusLoaded(conn.id)" class="text-xs text-stone-500 mb-4">
							All files are in sync.
						</div>

						<!-- Sync history log -->
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
						                    v-for="(entry, idx) in connectionHistory(conn.id)"
						                    :key="idx"
						                    class="transition hover:bg-stone-50 dark:hover:bg-stone-700"
						                    :class="idx % 2 === 0 ? 'bg-white dark:bg-stone-800' : 'bg-stone-50 dark:bg-stone-750'"
						                >
						                    <td class="p-2 whitespace-nowrap">
						                        {{ formatTime(entry.time) }}
						                    </td>

						                    <td class="p-2 whitespace-nowrap capitalize">
						                        {{ entry.action }}
						                    </td>

						                    <td class="p-2 whitespace-nowrap">
						                        <span
						                            v-if="entry.status === 'success'"
						                            class="text-teal-500"
						                        >
							                            <svg class="icon icon-check mr-1"><use xlink:href="#icon-check"></use></svg>Success
						                        </span>

						                        <span
						                            v-else
						                            class="text-rose-500"
						                        >
							                            <svg class="icon icon-close mr-1"><use xlink:href="#icon-close"></use></svg>{{ entry.status }}
						                        </span>
						                    </td>

						                    <td class="p-2 whitespace-nowrap">
						                        {{ entry.files || 0 }}
						                    </td>

						                    <td class="p-2 text-stone-500 dark:text-stone-400">
						                        {{ entry.message }}
						                    </td>
						                </tr>

						                <tr v-if="connectionHistory(conn.id).length === 0">
						                    <td
						                        colspan="5"
						                        class="p-2 text-stone-400 dark:text-stone-500"
						                    >
						                        No history for this connection yet.
						                    </td>
						                </tr>
						            </tbody>
						        </table>
						    </div>
						</div>

					</div>
				</div>
			</div>

			<!-- Global Status Message -->
			<div v-if="syncMessage" class="mb-4 p-3 text-white text-sm transition flex items-center" :class="syncMessageClass">
				<svg v-if="syncMessageClass === 'bg-teal-500'" class="icon icon-check h-4 w-4 mr-2"><use xlink:href="#icon-check"></use></svg>
				<svg v-else-if="syncMessageClass === 'bg-rose-500'" class="icon icon-close h-4 w-4 mr-2"><use xlink:href="#icon-close"></use></svg>
				<svg v-else class="icon icon-history h-4 w-4 mr-2 bg-stone-200"><use xlink:href="#icon-history"></use></svg>
				{{ syncMessage }}
			</div>

			<!-- Add/Edit Form -->
			<form v-if="showAddForm || editingConnection" class="w-full my-8">
				<h3 class="font-semibold mb-4 text-lg">{{ editingConnection ? 'Edit Repository' : 'Add Repository' }}</h3>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div v-for="(fieldDefinition, fieldname) in formDefinitions" :key="fieldname">
						<fieldset class="flex flex-wrap justify-between border-2 border-stone-200 bg-white p-4 my-4" v-if="fieldDefinition.type == 'fieldset'">
							<legend class="text-lg font-medium">{{ fieldDefinition.legend }}</legend>
							<component v-for="(subfieldDefinition, subfieldname) in fieldDefinition.fields"
								:is="selectComponent(subfieldDefinition.type)"
								:errors="errors"
								:name="subfieldname"
								:value="formData[subfieldname]" 
								v-bind="subfieldDefinition">
							</component>
						</fieldset>
						<component v-else
							:is="selectComponent(fieldDefinition.type)"
							:errors="errors"
							:name="fieldname"
							:value="formData[fieldname]" 
							v-bind="fieldDefinition">
						</component>						
					</div>
				</div>

				<div v-if="formData.local_folder === '' && !editingConnection" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
					<svg class="icon icon-exclamation-circle mr-2"><use xlink:href="#icon-exclamation-circle"></use></svg>
					<strong>Warning:</strong> Syncing to root will place files directly in /content. Existing files may be overwritten.
				</div>

				<div>
					<div :class="messageClass" class="block w-full h-8 px-3 py-1 my-1 text-white transition">{{ message }}</div>
					<div class="flex gap-3">
						<button type="submit" @click.prevent="saveConnection" class="flex-1 p-3 bg-stone-700 hover:bg-stone-900 text-white text-sm font-medium transition">
							{{ editingConnection ? 'Update' : 'Save' }}
						</button>
						<button type="button" @click.prevent="cancelForm" class="flex-1 p-3 border border-stone-300 hover:bg-stone-100 text-sm transition">
							Cancel
						</button>
					</div>
				</div>
			</form>

			<!-- Delete Confirmation Modal -->
			<div v-if="showDeleteModal" class="fixed w-full h-100 inset-0 z-50 overflow-hidden flex justify-center items-center bg-stone-700 bg-opacity-90">
				<div class="border border-teal-500 dark:border-stone-200 shadow-lg bg-white dark:bg-stone-600 w-11/12 md:max-w-md mx-auto shadow-lg z-50 overflow-y-auto">
					<div class="text-left p-6">
						<div class="text-2xl font-bold text-stone-900 dark:text-stone-200">
							<h3>Delete repository</h3>
						</div>
						<div class="my-5 text-stone-900 dark:text-stone-200">
							<p>Do you really want to delete this repository connection? Local content will not be removed.</p>
						</div>
						<div class="flex justify-end pt-2">
							<button @click.prevent="cancelDelete" class="focus:outline-none px-4 p-3 mr-3 text-black bg-stone-200 hover:bg-stone-300 transition duration-100">Cancel</button>
							<button @click.prevent="confirmDelete" class="focus:outline-none px-4 p-3 mr-3 text-white bg-rose-500 hover:bg-rose-700 transition duration-100">Delete repository</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</Transition>
	`,

	data() {
		return {
			connections: [],
			loading: false,
			statusMessage: '',
			showAddForm: false,
			editingConnection: null,
			showDeleteModal: false,
			deleteTargetId: null,
			resolveDecisions: {},
			resolveConfirm: {},
			resolveBusy: {},
			formDefinitions: data.plugin && data.plugin.connection_form ? data.plugin.connection_form.fields : {},
			formData: {},
			message: '',
			messageClass: '',
			errors: {},
			syncMessage: '',
			syncMessageClass: '',
			historyVisible: {},
			activeSync: {},
			allHistory: [],
			connectionStatus: {}
		}
	},

	mounted() {
		var self = this;
		eventBus.$on('forminput', function(formdata) {
			self.formData[formdata.name] = formdata.value;
		});
		this.fetchConnections();
		this.fetchHistory();
		this.fetchAllStatuses();
	},

	methods: {
		selectComponent: function(type) {
			return 'component-' + type;
		},



		formatTime(time) {
			if (!time) return '';
			const date = new Date(time);
			return date.toLocaleString();
		},

		fetchConnections() {
			var self = this;
			tmaxios.get('/api/v1/gitsync/connections')
				.then(function(response) {
					var conns = response.data.connections || {};
					self.connections = Object.keys(conns).map(function(id) {
						var c = conns[id];
						// Find last sync for this connection
						var lastSync = null;
						for (var i = 0; i < self.allHistory.length; i++) {
							if (self.allHistory[i].connection_id === id) {
								lastSync = self.allHistory[i];
								break;
							}
						}
						return {
							id: id,
							owner: c.owner,
							repo: c.repo,
							branch: c.branch || 'main',
							local_folder: c.local_folder || '',
						exclude_folders: c.exclude_folders || '',
							platform: c.platform || 'github',
							api_base_url: c.api_base_url || '',
							repo_path: c.repo_path || '',
							file_pattern: c.file_pattern || '',
							auto_sync: !!c.auto_sync,
							auto_sync_interval: c.auto_sync_interval || 10,
							last_sync_status: lastSync ? lastSync.status : null,
							last_sync_time: lastSync ? lastSync.time : null,
							last_sync_files: lastSync ? lastSync.files : null
						};
					});
					// Fetch status after connections are loaded
					self.$nextTick(function() {
						self.fetchAllStatuses();
					});
				});
		},

		fetchHistory() {
			var self = this;

			// Fetch all history for the global view; individual panels
			// can load per-connection history via fetchHistoryForConnection.
			tmaxios.get('/api/v1/gitsync/history')
				.then(function(response) {
					self.allHistory = response.data.history || [];
					// Refresh connections to update last sync info
					self.fetchConnections();
				});
		},

		fetchHistoryForConnection(connectionId) {
			var self = this;

			return tmaxios.get('/api/v1/gitsync/history/' + connectionId)
				.then(function(response) {
					var history = response.data.history || [];
					self.allHistory = self.allHistory.filter(function(entry) {
						return entry.connection_id !== connectionId;
					}).concat(history);
					self.$nextTick(function() {
						self.fetchConnections();
					});
				});
		},


		fetchHistory() {
			var self = this;
			tmaxios.get('/api/v1/gitsync/history')
				.then(function(response) {
					self.allHistory = response.data.history || [];
					// Refresh connections to update last sync info
					self.fetchConnections();
				});
		},

		connectionHistory(connectionId) {
			return this.allHistory.filter(function(entry) {
				return entry.connection_id === connectionId;
			}).slice(0, 10);
		},

		connStatus(connectionId) {
			return this.connectionStatus[connectionId] || {
				sync: 0,
				push: 0,
				pull: 0,
				conflicts: 0,
				files: []
			};
		},

		connStatusLoaded(connectionId) {
			return this.connectionStatus[connectionId] !== undefined;
		},

		canPush(connectionId) {
			var status = this.connStatus(connectionId);
			if (status.conflicts > 0) return false;
			// Allow push while status is loading or before first sync
			if (!this.connStatusLoaded(connectionId) || status.first_sync) return true;
			return status.push > 0;
		},

		canPull(connectionId) {
			var status = this.connStatus(connectionId);
			if (status.conflicts > 0) return false;
			// Allow pull while status is loading or before first sync
			if (!this.connStatusLoaded(connectionId) || status.first_sync) return true;
			return status.pull > 0;
		},

		statusBadgeLabel(connectionId) {
			var status = this.connStatus(connectionId);
			if (!this.connStatusLoaded(connectionId)) return 'Loading status...';
			if (this.needsSync(connectionId)) return 'sync now';
			return 'synced';
		},

		statusBadgeClass(connectionId) {
			var status = this.connStatus(connectionId);
			if (!this.connStatusLoaded(connectionId)) return 'bg-stone-100 text-stone-600';
			if (this.needsSync(connectionId)) return 'bg-rose-500';
			return 'bg-teal-500';
		},

		needsSync(connectionId) {
			var status = this.connStatus(connectionId);
			if (status.conflicts > 0) return true;
			if (status.first_sync || !this.connectionLastSyncTime(connectionId)) return true;
			if (status.push > 0 || status.pull > 0) return true;
			return false;
		},

		unsyncedFiles(connectionId) {
			var status = this.connStatus(connectionId);
			if (!status || !status.files) return [];
			return status.files.filter(function(file) {
				return file.status !== 'sync';
			});
		},

		hasUnsyncedFiles(connectionId) {
			return this.unsyncedFiles(connectionId).length > 0;
		},

		connectionLastSyncTime(connectionId) {
			for (var i = 0; i < this.connections.length; i++) {
				if (this.connections[i].id === connectionId) {
					return this.connections[i].last_sync_time;
				}
			}
			return null;
		},

		fetchAllStatuses() {
			var self = this;
			this.connections.forEach(function(conn) {
				self.fetchStatus(conn.id);
			});
		},

		fetchStatus(connectionId) {
			var self = this;
			tmaxios.get('/api/v1/gitsync/status/' + connectionId)
				.then(function(response) {
					self.connectionStatus[connectionId] = response.data;
					// preselect the Typemill version for newly detected conflicts
					self.ensureDecisions(connectionId);
				})
				.catch(function() {
					self.connectionStatus[connectionId] = {
						sync: 0,
						push: 0,
						pull: 0,
						conflicts: 0,
						files: [],
						error: true
					};
				});
		},

		toggleHistory(connectionId) {
			this.historyVisible[connectionId] = !this.historyVisible[connectionId];
			if (this.historyVisible[connectionId]) {
				this.fetchHistoryForConnection(connectionId);
				this.fetchStatus(connectionId);
			}
		},

		startAddForm() {
			if (this.showAddForm && !this.editingConnection) {
				this.cancelForm();
			} else {
				this.showAddForm = true;
				this.editingConnection = null;
				this.formData = { platform: 'github', branch: 'main', auto_sync: false, auto_sync_interval: 10 };
				this.reset();
			}
		},

		editConnection(conn) {
			if (this.editingConnection === conn.id) {
				this.cancelForm();
			} else {
				this.editingConnection = conn.id;
				this.formData = {
					id: conn.id,
					owner: conn.owner,
					repo: conn.repo,
					branch: conn.branch || 'main',
					local_folder: conn.local_folder,
					exclude_folders: conn.exclude_folders || '',
					platform: conn.platform || 'github',
					api_base_url: conn.api_base_url || '',
					repo_path: conn.repo_path || '',
					file_pattern: conn.file_pattern || '',
					auto_sync: !!conn.auto_sync,
					auto_sync_interval: conn.auto_sync_interval || 10
				};
				this.showAddForm = true;
				this.reset();
			}
		},

		cancelForm() {
			this.showAddForm = false;
			this.editingConnection = null;
			this.formData = {};
			this.reset();
		},

		reset() {
			this.errors = {};
			this.message = '';
			this.messageClass = '';
			this.syncMessage = '';
			this.syncMessageClass = '';
		},

		saveConnection() {
			var self = this;
			this.loading = true;
			this.reset();

			var payload = {
				id: this.formData.id,
				owner: this.formData.owner,
				repo: this.formData.repo,
				branch: this.formData.branch,
				local_folder: this.formData.local_folder || '',
				exclude_folders: this.formData.exclude_folders || '',
				platform: this.formData.platform || 'github',
				api_base_url: this.formData.api_base_url || '',
				repo_path: this.formData.repo_path || '',
				file_pattern: this.formData.file_pattern || '',
				auto_sync: !!this.formData.auto_sync,
				auto_sync_interval: parseInt(this.formData.auto_sync_interval) || 10
			};

			if (this.editingConnection) {
				tmaxios.put('/api/v1/gitsync/connections/' + this.editingConnection, payload)
					.then(function(response) {
						self.loading = false;
						self.cancelForm();
						self.syncMessage = 'Connection updated';
						self.syncMessageClass = 'bg-teal-500';
						self.fetchConnections();
					})
					.catch(function(error) {
						self.loading = false;
						if (error.response) {
							self.message = error.response.data.message || 'Failed to update connection';
							self.messageClass = 'bg-rose-500';
							if (error.response.data.errors !== undefined) {
								self.errors = error.response.data.errors;
							}
						}
					});
			} else {
				tmaxios.post('/api/v1/gitsync/connections', payload)
					.then(function(response) {
						self.loading = false;
						self.cancelForm();
						self.syncMessage = 'Connection created';
						self.syncMessageClass = 'bg-teal-500';
						self.fetchConnections();
					})
					.catch(function(error) {
						self.loading = false;
						if (error.response) {
							self.message = error.response.data.message || 'Failed to create connection';
							self.messageClass = 'bg-rose-500';
							if (error.response.data.errors !== undefined) {
								self.errors = error.response.data.errors;
							}
						}
					});
			}
		},

		deleteConnection(id) {
			this.deleteTargetId = id;
			this.showDeleteModal = true;
		},

		cancelDelete() {
			this.deleteTargetId = null;
			this.showDeleteModal = false;
		},

		confirmDelete() {
			var self = this;
			var id = this.deleteTargetId;
			this.showDeleteModal = false;
			this.deleteTargetId = null;
			this.loading = true;
			this.syncMessage = '';

			tmaxios.delete('/api/v1/gitsync/connections/' + id)
				.then(function() {
					self.loading = false;
					self.syncMessage = 'Connection deleted';
					self.syncMessageClass = 'bg-teal-500';
					self.fetchConnections();
				})
				.catch(function(error) {
					self.loading = false;
					if (error.response) {
						self.syncMessage = error.response.data.message || 'Failed to delete connection';
						self.syncMessageClass = 'bg-rose-500';
					}
				});
		},

		conflictFiles(connectionId) {
			var status = this.connStatus(connectionId);
			if (!status || !status.files) return [];
			return status.files.filter(function(file) {
				return file.status === 'conflict';
			});
		},

		conflictPathsList(connectionId) {
			return this.conflictFiles(connectionId).map(function(file) {
				return file.path;
			});
		},

		openResolve(connectionId) {
			// same toggle logic as the history panel: expand the status view
			if (!this.historyVisible[connectionId]) {
				this.historyVisible[connectionId] = true;
				this.fetchHistoryForConnection(connectionId);
				this.fetchStatus(connectionId);
			}
			this.resolveConfirm[connectionId] = null;
		},

		ensureDecisions(connectionId) {
			var current = this.resolveDecisions[connectionId] || {};
			var fresh = {};
			this.conflictFiles(connectionId).forEach(function(file) {
				// keep an existing valid decision, default to the Typemill version
				fresh[file.path] = (current[file.path] === 'local' || current[file.path] === 'remote') ? current[file.path] : 'local';
			});
			this.resolveDecisions[connectionId] = fresh;
		},

		setAllDecisions(connectionId, side) {
			var fresh = {};
			this.conflictFiles(connectionId).forEach(function(file) {
				fresh[file.path] = side;
			});
			this.resolveDecisions[connectionId] = fresh;
		},

		allDecisionsFor(connectionId, side) {
			var decisions = this.resolveDecisions[connectionId];
			var files = this.conflictFiles(connectionId);
			if (!decisions || files.length === 0) return false;
			return files.every(function(file) {
				return decisions[file.path] === side;
			});
		},

		decisionPathsFor(connectionId, side) {
			var decisions = this.resolveDecisions[connectionId] || {};
			return Object.keys(decisions).filter(function(path) {
				return decisions[path] === side;
			});
		},

		allDecisionsMade(connectionId) {
			var decisions = this.resolveDecisions[connectionId];
			var files = this.conflictFiles(connectionId);
			if (!decisions || files.length === 0) return false;
			return files.every(function(file) {
				return decisions[file.path] === 'local' || decisions[file.path] === 'remote';
			});
		},

		confirmResolveAction(connectionId, action) {
			this.resolveConfirm[connectionId] = action;
		},

		backToResolveSelect(connectionId) {
			this.resolveConfirm[connectionId] = null;
		},

		executeResolveAction(connectionId) {
			var self = this;
			var action = this.resolveConfirm[connectionId];
			this.resolveBusy[connectionId] = true;

			var request;
			if (action === 'resolve') {
				request = tmaxios.post('/api/v1/gitsync/resolve/' + connectionId, { decisions: this.resolveDecisions[connectionId] });
			} else if (action === 'force-pull') {
				request = tmaxios.post('/api/v1/gitsync/pull/' + connectionId, { force: true });
			} else {
				request = tmaxios.post('/api/v1/gitsync/push/' + connectionId, { force: true });
			}

			request.then(function(response) {
				self.resolveBusy[connectionId] = false;
				self.resolveConfirm[connectionId] = null;
				self.syncMessage = response.data.detail || response.data.message || 'Conflicts resolved';
				self.syncMessageClass = 'bg-teal-500';
				self.fetchHistoryForConnection(connectionId);
				self.fetchStatus(connectionId);
			}).catch(function(error) {
				if (error.response) {
					self.resolveBusy[connectionId] = false;
					self.resolveConfirm[connectionId] = null;
					self.syncMessage = error.response.data.message || 'Resolve failed';
					self.syncMessageClass = 'bg-rose-500';
					self.fetchHistoryForConnection(connectionId);
					self.fetchStatus(connectionId);
				} else {
					self.resolveBusy[connectionId] = false;
				}
			});
		},

		pushNow(connectionId) {
			var self = this;
			this.loading = true;
			this.activeSync[connectionId] = 'Pushing to remote...';
			this.syncMessage = '';
			this.syncMessageClass = "bg-stone-200";

			tmaxios.post('/api/v1/gitsync/push/' + connectionId)
				.then(function(response) {
					self.loading = false;
					self.activeSync[connectionId] = null;
					self.syncMessage = response.data.detail || response.data.message || 'Push completed';
					self.syncMessageClass = 'bg-teal-500';
					self.fetchHistoryForConnection(connectionId);
					self.fetchStatus(connectionId);
				})
				.catch(function(error) {
					self.loading = false;
					self.activeSync[connectionId] = null;
					if (error.response) {
						var msg = error.response.data.message || 'Push failed';
						if (error.response.data.conflicts && error.response.data.conflicts.length > 0) {
							msg += ' Affected files: ' + error.response.data.conflicts.join(', ');
						}
						self.syncMessage = msg;
						self.syncMessageClass = 'bg-rose-500';
						self.fetchHistoryForConnection(connectionId);
						if (error.response.data.conflicts && error.response.data.conflicts.length > 0) {
							// expand the status view with the resolve section
							self.openResolve(connectionId);
						} else {
							self.fetchStatus(connectionId);
						}
					}
				});
		},

		pullNow(connectionId) {
			var self = this;
			this.loading = true;
			this.activeSync[connectionId] = 'Pulling from remote...';
			this.syncMessage = '';
			this.syncMessageClass = "bg-stone-200";

			tmaxios.post('/api/v1/gitsync/pull/' + connectionId)
				.then(function(response) {
					self.loading = false;
					self.activeSync[connectionId] = null;
					self.syncMessage = response.data.detail || response.data.message || 'Pull completed';
					self.syncMessageClass = 'bg-teal-500';
					self.fetchHistoryForConnection(connectionId);
					self.fetchStatus(connectionId);
				})
				.catch(function(error) {
					self.loading = false;
					self.activeSync[connectionId] = null;
					if (error.response) {
						var msg = error.response.data.message || 'Pull failed';
						if (error.response.data.conflicts && error.response.data.conflicts.length > 0) {
							msg += ' Affected files: ' + error.response.data.conflicts.join(', ');
						}
						self.syncMessage = msg;
						self.syncMessageClass = 'bg-rose-500';
						self.fetchHistoryForConnection(connectionId);
						if (error.response.data.conflicts && error.response.data.conflicts.length > 0) {
							// expand the status view with the resolve section
							self.openResolve(connectionId);
						} else {
							self.fetchStatus(connectionId);
						}
					}
				});
		}
	}
});
