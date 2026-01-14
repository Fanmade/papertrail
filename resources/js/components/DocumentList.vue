<template>
  <div>
    <div v-if="loading" class="text-gray-500">{{ __('Loading…') }}</div>
    <div v-else-if="error" class="text-red-600">{{ __('Failed to load documents') }}</div>
    <div v-else>
      <div v-if="documents.length === 0" class="text-gray-500">{{ __('No documents yet') }}</div>
      <div v-else class="space-y-3 max-h-[70vh] overflow-y-auto pr-1">
        <div
          v-for="doc in documents"
          :key="doc.id"
          class="flex items-center gap-4 p-3 border rounded bg-white dark:bg-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 relative"
          :class="{ 'ring-2 ring-primary-500 border-primary-500': doc.id === effectiveSelectedId }"
          @click="select(doc)"
        >
          <button class="absolute top-2 right-4 hover:bold" @click.prevent="deleteDoc(doc)" :title="__('Delete')">x</button>

          <div class="flex-shrink-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
            <div v-if="!doc.thumb_available" class="w-[100px] h-[140px] animate-pulse bg-gray-200 dark:bg-gray-700 rounded">
              <span class="sr-only">{{ __('Processing…') }}</span>
            </div>
            <img v-else :src="doc._thumb_src || doc.thumb_url" :alt="doc.name" class="object-contain max-w-[100px] h-auto"/>
          </div>
          <div class="min-w-0">
            <div class="font-semibold truncate">{{ doc.name }}</div>
            <div class="text-xs text-gray-500">
              {{ __('Pages') }}: {{ doc.pages }}
            </div>
          </div>
        </div>
      </div>
      <!-- Pagination -->
      <nav v-if="hasPagination" class="flex justify-between items-center">
        <Button class="mt-4" :disabled="pagination.current_page <= 1" @click="prevPage">
          {{ __('Previous') }}
        </Button>
        <span class="text-xs px-4" v-text="currentPageText"></span>
        <Button class="mt-4" :disabled="pagination.current_page >= pagination.last_page" @click="nextPage">
          {{ __('Next') }}
        </Button>
      </nav>

    </div>
  </div>
  
</template>

<script>
export default {
  name: 'DocumentList',
  props: {
    // Optional external selected id for highlighting (Tool can pass it in)
    selectedId: { type: [String, Number, null], default: null },
  },
  data: () => ({
    documents: [],
    pagination: {
      "current_page": 1,
      "per_page": 0,
      "total": 0,
      "last_page": 1
    },
    loading: false,
    error: false,
    thumbPollers: {},
    internalSelectedId: null,
  }),
  computed: {
    effectiveSelectedId() {
      return this.selectedId ?? this.internalSelectedId
    },
    hasPagination() {
      return this.pagination?.last_page > 1
    },
    currentPageText() {
      const from = (this.pagination.current_page - 1) * this.pagination.per_page + 1
      const to = Math.min(from + this.pagination.per_page - 1, this.pagination.total)

      return this.__('Showing :from-:to of :total', {
        from,
        to,
        total: this.pagination.total,
      })
    },
  },
  mounted() {
    this.loadDocuments()
  },
  methods: {
    /**
     * Build a URL for the document API endpoint
     * @param {string|null} documentId - The document ID to include in the URL, optional
     * @param {Object} queryParameters - Query parameters to include in the URL, optional
     * @returns {string} - The constructed URL
     */
    buildUrl(documentId, queryParameters = {}) {
      // The documentId is optional
      let url = '/nova-vendor/papertrail/documents';
      if (documentId) {
        url += `/${documentId}`;
      }
      if (Object.keys(queryParameters).length > 0) {
        url += `?${new URLSearchParams(queryParameters)}`;
      }
      return url;
    },
    async loadDocuments() {
      this.loading = true
      this.error = false
      try {
        let parameters = {}
        if (this.pagination.current_page > 1) {
          parameters.page = this.pagination.current_page
        }
        const { data } = await Nova.request().get(this.buildUrl(null, parameters))
        const docs = Array.isArray(data?.data) ? data.data : []
        // The pagination data should be in "data.meta" and contain a simple object
        const paginationData = data?.meta ? data.meta : {}
        this.setupPagination(paginationData)
        this.documents = docs.map(d => ({ ...d, _thumb_src: null }))
        this.documents.forEach(doc => {
          if (!doc.thumb_available) this.startThumbPolling(doc)
        })
      } catch (e) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    loadPage(page) {
      this.pagination.current_page = page
      this.reload()
    },
    nextPage() {
      if (this.pagination.current_page >= this.pagination.last_page) {
        return
      }
      this.loadPage(this.pagination.current_page + 1)
    },
    prevPage() {
      if (this.pagination.current_page <= 1) {
        return
      }
      this.loadPage(this.pagination.current_page - 1)
    },
    setupPagination(paginationData = {}) {
      const DEFAULT_PAGINATION = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
      }

      const {
        current_page = DEFAULT_PAGINATION.current_page,
        last_page = DEFAULT_PAGINATION.last_page,
        per_page = DEFAULT_PAGINATION.per_page,
        total = DEFAULT_PAGINATION.total,
      } = paginationData ?? DEFAULT_PAGINATION

      this.pagination.current_page = current_page
      this.pagination.last_page = last_page
      this.pagination.per_page = per_page
      this.pagination.total = total
    },
    reload() {
      // Public method callable by parent via ref
      this.cancelAllThumbPolling()
      this.loadDocuments()
    },
    clearSelection() {
      // Public method callable by parent via ref
      this.internalSelectedId = null
    },
    select(doc) {
      this.internalSelectedId = doc?.id ?? null
      this.$emit('select', doc)
    },
    startThumbPolling(doc) {
      const maxMs = 60_000 // stop after 60s
      const firstDelay = 1000
      const maxDelay = 5000

      if (this.thumbPollers[doc.id]) return // already polling

      const state = {
        startedAt: Date.now(),
        tries: 0,
        delayMs: firstDelay,
        timerId: null,
      }
      this.thumbPollers = { ...this.thumbPollers, [doc.id]: state }

      const tick = async () => {
        // Stop if we no longer have this doc
        const current = this.documents.find(d => d.id === doc.id)
        if (!current) return this.clearThumbPolling(doc.id)

        // Give up after maxMs
        if (Date.now() - state.startedAt > maxMs) return this.clearThumbPolling(doc.id)

        try {
          const res = await fetch(current.thumb_url + `?probe=${Date.now()}`, { method: 'HEAD', credentials: 'same-origin' })
          if (res.status === 200) {
            current.thumb_available = true
            current._thumb_src = current.thumb_url + `?v=${Date.now()}`
            this.clearThumbPolling(doc.id)
            return
          }
        } catch (e) {
          // ignore and retry
        }

        state.tries++
        state.delayMs = Math.min(maxDelay, Math.round(state.delayMs * 1.5))
        state.timerId = setTimeout(tick, state.delayMs)
      }

      state.timerId = setTimeout(tick, state.delayMs)
    },

    clearThumbPolling(id) {
      const s = this.thumbPollers[id]
      if (!s) return
      if (s.timerId) clearTimeout(s.timerId)
      const { [id]: _removed, ...rest } = this.thumbPollers
      this.thumbPollers = rest
    },
    async deleteDoc(doc) {
      // TODO: Make this more dynamic, disallow deletion of all or specific documents
      const response = await Nova.request().delete(this.buildUrl(doc.id))
      console.info('Response received', response)
      let isSuccess = response.status === 200
      let message = response?.data?.message ?? (isSuccess ? this.__('Document deleted successfully') : this.__('Failed to delete document'))
      Nova.$toasted.show(message, { type: isSuccess ? 'success' : 'error'})
      if (isSuccess) {
        this.reload()
      }
    },
    cancelAllThumbPolling() {
      Object.keys(this.thumbPollers).forEach(id => this.clearThumbPolling(id))
    },
  },
  beforeUnmount() {
    this.cancelAllThumbPolling()
  },
}
</script>
