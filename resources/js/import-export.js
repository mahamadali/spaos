import { InitApp } from '@/helpers/main'

import ExportModal from './import-export/ExportModal.vue'
import ImportModal from './import-export/ImportModal.vue'

const app = InitApp()

app.component('export-modal', ExportModal)
app.component('import-modal', ImportModal)

// Wait for DOM to be ready before mounting
document.addEventListener('DOMContentLoaded', function() {
  const container = document.querySelector('[data-render="import-export"]');
  if (container) {
    console.log('Mounting import-export Vue app on:', container);
    app.mount('[data-render="import-export"]');
  } else {
    console.warn('Import-export container not found');
  }
});
