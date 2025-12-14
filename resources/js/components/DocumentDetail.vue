<template>
  <div>
    <div v-if="!document" class="text-gray-500">
      {{ __('Select a document to view') }}
    </div>

    <div v-else class="space-y-4">
      <!-- Document details header -->
      <div class="p-4 border rounded bg-white dark:bg-gray-900">
        <div class="flex items-start gap-3">
          <button
              type="button"
              class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
              :aria-label="__('Close detail view')"
              @click="$emit('close')"
          >
            <span class="text-xl leading-none">&times;</span>
          </button>


          <div class="min-w-0 flex-1">
            <div class="text-lg font-semibold truncate">{{ document.name }}</div>
            <div class="text-xs text-gray-500">
              {{ __('Pages') }}: {{ document.pages }}
              <span v-if="document.created_at" class="ml-2">• {{ __('Created at') }}: {{ formatDate(document.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pages list with field overlays -->
      <div class="border rounded bg-white dark:bg-gray-900">
        <div class="px-4 py-2 border-b text-sm font-medium flex items-center justify-between">
          <span>{{ __('Pages') }}</span>
          <span v-if="loadingFields" class="text-gray-500">{{ __('Loading…') }}</span>
        </div>
        <div class="max-h-[70vh] overflow-y-auto p-4 space-y-6">
          <div v-for="n in pageCount" :key="n" class="w-full">
            <PageFieldOverlays
              :image-url="pageUrlBuilder(document.id, n)"
              :page-number="n"
              :document-name="document.name"
              :fields="fieldsByPage[n] || []"
              :placeholders="placeholders"
              :saving-map="savingMap"
              @assign="onAssignPlaceholder"
            />
            <div v-if="!loadingFields && (fieldsByPage[n] || []).length === 0" class="mt-2 text-xs text-gray-500">
              {{ __('No fields on this page') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PageFieldOverlays from './PageFieldOverlays.vue'

export default {
  name: 'DocumentDetail',
  components: { PageFieldOverlays },
  props: {
    document: { type: Object, default: null },
    pageUrlBuilder: { type: Function, required: true },
  },
  data: () => ({
    fieldsByPage: {},
    placeholders: [],
    loadingFields: false,
    errorFields: false,
    savingMap: {}, // { [fieldId]: boolean }
  }),
  computed: {
    pageCount() {
      return this.document?.pages || 0
    },
  },
  watch: {
    document: {
      immediate: true,
      handler(doc) {
        this.fieldsByPage = {}
        if (doc && doc.id) {
          this.loadPlaceholders()
          this.loadFields(doc.id)
        }
      }
    }
  },
  methods: {
    formatDate(iso) {
      try {
        const date = new Date(iso)
        if (isNaN(date.getTime())) return iso
        return date.toLocaleString()
      } catch (e) {
        return iso
      }
    },
    async loadPlaceholders() {
      try {
        const { data } = await Nova.request().get('/nova-vendor/papertrail/placeholders')
        this.placeholders = Array.isArray(data?.data) ? data.data : []
      } catch (e) {
        this.placeholders = []
      }
    },
    async loadFields(docId) {
      this.loadingFields = true
      this.errorFields = false
      try {
        const { data } = await Nova.request().get(`/nova-vendor/papertrail/documents/${docId}/fields`)
        const list = Array.isArray(data?.data) ? data.data : []
        const byPage = {}
        list.forEach(f => {
          const page = f.page_number
          if (!byPage[page]) byPage[page] = []
          byPage[page].push(f)
        })
        this.fieldsByPage = byPage
      } catch (e) {
        this.errorFields = true
      } finally {
        this.loadingFields = false
      }
    },
    async onAssignPlaceholder(payload) {
      const { fieldId, placeholderKey } = payload || {}
      if (!fieldId) return
      this.savingMap = { ...this.savingMap, [fieldId]: true }
      try {
        const res = await Nova.request().put(`/nova-vendor/papertrail/fields/${fieldId}`, {
          assigned_placeholder: placeholderKey ?? null,
        })
        const updated = res?.data?.data
        if (updated && Object.prototype.hasOwnProperty.call(updated, 'assigned_placeholder')) {
          // update local state
          Object.values(this.fieldsByPage).forEach(arr => {
            arr.forEach(f => {
              if (f.id === fieldId) f.assigned_placeholder = updated.assigned_placeholder
            })
          })
        }
        Nova.$toasted.show(this.__('Saved'), { type: 'success' })
      } catch (e) {
        Nova.$toasted.show(this.__('Failed to save'), { type: 'error' })
      } finally {
        const { [fieldId]: _removed, ...rest } = this.savingMap
        this.savingMap = rest
      }
    }
  },
}
</script>

<style scoped>
</style>
