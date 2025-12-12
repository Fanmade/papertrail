<template>
  <div>
    <Head :title="__('Pdf Manager')"/>

    <Heading class="mb-6">{{ __('Pdf Manager') }}</Heading>

    <Card class="p-6">
      <!--      <h1 class="text-4xl font-light mb-6">{{ __('Upload PDF') }}</h1>-->

      <div class="space-y-4">

        <ConfirmUploadRemovalModal
            :show="removeModalOpen"
            @confirm="confirmRemove"
            @close="removeModalOpen = false"
        />

        <DropZone
            :files="files"
            :accepted-types="'application/pdf'"
            @file-changed="onFileChanged"
            @file-removed="onFileRemoved"
        />

        <Button class="mt-4 p-2 dark:bg-blue-950 bg-blue-700" :disabled="!file || uploading" @click="upload"
                variant="action">
          {{ __('Upload') }}
        </Button>
        <DividerLine class="w-full"/>
      </div>

    </Card>
  </div>
</template>

<script>
export default {
  data: () => ({files: [], file: null, previewFile: null, uploading: false, removeModalOpen: false}),
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

        console.info('Response', {response})
        this.onFileRemoved()
      } finally {
        this.uploading = false
      }
    },
  },
}
</script>

<style>
/* Scoped Styles */
</style>
