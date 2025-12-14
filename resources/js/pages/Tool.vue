<template>
  <div class="papertrail-tool">
    <Head :title="__('Pdf Manager')"/>

    <Heading class="mb-6">{{ __('Pdf Manager') }}</Heading>

    <Card class="p-6">

      <div class="space-y-4">
        <DropZone
            :files="files"
            :accepted-types="'application/pdf'"
            @file-changed="onFileChanged"
            @file-removed="onFileRemoved"
        />

        <Button class="mt-4" :disabled="!file || uploading" @click="upload">
          {{ __('Upload') }}
        </Button>
        <DividerLine class="w-full"/>
      </div>

      <!-- Master–detail layout -->
      <div
          class="mt-6 gap-6"
          :class="selectedDocument ? 'grid grid-cols-1 md:grid-cols-4' : 'grid grid-cols-1'"
      >
        <!-- Left: document list (1/3) -->
        <div :class="selectedDocument ? 'md:col-span-1' : 'md:col-span-4'">
          <Heading class="mb-4 text-base">{{ __('Available Documents') }}</Heading>

          <DocumentList
            ref="docList"
            :selected-id="selectedDocument ? selectedDocument.id : null"
            @select="onSelectDocument"
          />
        </div>

        <!-- Right: detail (2/3) -->
        <div v-if="selectedDocument" class="md:col-span-3 min-h-[300px]">
          <DocumentDetail
            :document="selectedDocument"
            :page-url-builder="buildPageUrl"
            @close="onCloseDetail"
          />
        </div>
      </div>

    </Card>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui'
import DocumentList from '../components/DocumentList.vue'
import DocumentDetail from '../components/DocumentDetail.vue'
export default {
  data: () => ({
    files: [],
    file: null,
    previewFile: null,
    uploading: false,
    removeModalOpen: false,
    selectedDocument: null,
  }),
  components: {
    Button,
    DocumentList,
    DocumentDetail,
  },
  methods: {
    onFileChanged(file) {
      // Check if the file is actually an array. If yes, take only the first element.
      if (Array.isArray(file)) file = file[0]

      this.file = file;
      this.files = [file];
      this.previewFile = {name: file.name, extension: 'pdf'}
    },
    onFileRemoved() {
      this.file = null;
      this.files = [];
      this.previewFile = null
    },
    onSelectDocument(doc) {
      this.selectedDocument = doc || null
    },
    buildPageUrl(docId, pageNumber) {
      if (!docId || !pageNumber) return null
      // Nova tool routes are prefixed with /nova-vendor/papertrail
      return `/nova-vendor/papertrail/documents/${docId}/pages/${pageNumber}`
    },
    onCloseDetail() {
      this.selectedDocument = null
      this.$refs.docList && this.$refs.docList.clearSelection && this.$refs.docList.clearSelection()
    },
    async upload() {
      if (!this.file) return
      this.uploading = true
      try {
        const formData = new FormData()
        formData.append('pdf', this.file, this.file.name)
        const response = await Nova.request().post('/nova-vendor/papertrail/upload', formData)
        const success = response?.data?.success === true
        const message = response?.data?.message ?? (success ? 'File uploaded' : 'Error')
        Nova.$toasted.show(message, {
          type: success ? 'success' : 'error',
        })

        this.onFileRemoved()
        if (success) {
          // Ask the list to refresh itself
          this.$refs.docList && this.$refs.docList.reload && this.$refs.docList.reload()
        }
      } finally {
        this.uploading = false
      }
    },
  },
  computed: {}
}
</script>
