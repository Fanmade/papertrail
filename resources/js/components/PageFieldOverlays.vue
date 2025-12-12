<template>
  <div class="w-full" ref="container">
    <div class="relative w-full">
      <img
        class="w-full h-auto object-contain rounded shadow-sm bg-gray-50 dark:bg-gray-800 select-none"
        :src="imageUrl"
        :alt="`${documentName} — ${__('Page')} ${pageNumber}`"
        loading="lazy"
        @load="onImageLoad"
      />

      <!-- overlays -->
      <div
        v-for="field in fields"
        :key="field.id"
        class="absolute border-2 rounded cursor-pointer group"
        :class="fieldClass(field)"
        :style="styleFor(field)"
        @click.self="togglePanel(field.id)"
      >
        <!-- assigned placeholder badge -->
        <div v-if="field.assigned_placeholder" class="absolute -top-5 left-0 bg-green-600 text-white text-[10px] leading-tight px-1.5 py-0.5 rounded shadow whitespace-nowrap">
          {{ labelFor(field.assigned_placeholder) }}
        </div>

        <div class="absolute -top-6 left-0 bg-white/90 dark:bg-gray-900/90 text-xs px-1 py-0.5 rounded shadow whitespace-nowrap hidden group-hover:block pointer-events-none">
          {{ field.name }}
        </div>

        <!-- inline panel -->
        <div v-if="openFieldId === field.id" class="absolute z-10 top-full mt-1 left-0 bg-white dark:bg-gray-900 border rounded shadow p-2 w-56" @click.stop>
          <label class="block text-xs text-gray-600 mb-1">{{ __('Placeholder') }}</label>
          <select
            class="w-full form-control form-select"
            :disabled="isSaving(field.id)"
            :value="field.assigned_placeholder || ''"
            @change="onSelect(field.id, $event.target.value)"
          >
            <option value="">{{ __('None') }}</option>
            <option v-for="ph in placeholders" :key="ph.key" :value="ph.key">{{ ph.label }}</option>
          </select>
          <div class="flex justify-end gap-2 mt-2">
            <button type="button" class="btn btn-default btn-sm" :disabled="isSaving(field.id)" @click.stop="closePanel">{{ __('Close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageFieldOverlays',
  props: {
    imageUrl: { type: String, required: true },
    pageNumber: { type: Number, required: true },
    documentName: { type: String, default: '' },
    fields: { type: Array, default: () => [] },
    placeholders: { type: Array, default: () => [] }, // [{key,label}]
    savingMap: { type: Object, default: () => ({}) },
  },
  data: () => ({
    naturalWidth: 0,
    naturalHeight: 0,
    openFieldId: null,
  }),
  computed: {
    placeholderMap() {
      const map = {}
      ;(this.placeholders || []).forEach(p => {
        if (p && p.key != null) map[p.key] = p.label
      })
      return map
    },
  },
  methods: {
    onImageLoad(e) {
      const img = e?.target
      if (img) {
        this.naturalWidth = img.naturalWidth
        this.naturalHeight = img.naturalHeight
      }
    },
    styleFor(field) {
      const pct = field.percent || { left: 0, top: 0, width: 0, height: 0 }
      return {
        left: pct.left + '%',
        top: pct.top + '%',
        width: pct.width + '%',
        height: pct.height + '%',
      }
    },
    fieldClass(field) {
      return [
        field.assigned_placeholder ? 'border-green-500 bg-green-200/10' : 'border-primary-500 bg-primary-200/10',
      ]
    },
    togglePanel(fieldId) {
      this.openFieldId = this.openFieldId === fieldId ? null : fieldId
    },
    closePanel() {
      this.openFieldId = null
    },
    onSelect(fieldId, placeholderKey) {
      const key = placeholderKey || null
      this.$emit('assign', { fieldId, placeholderKey: key })
    },
    labelFor(placeholderKey) {
      if (!placeholderKey) return ''
      return this.placeholderMap[placeholderKey] || placeholderKey
    },
    isSaving(fieldId) {
      return !!this.savingMap[fieldId]
    },
  }
}
</script>

<style scoped>
</style>
