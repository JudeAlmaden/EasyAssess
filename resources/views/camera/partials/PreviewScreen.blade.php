<div id="previewSection" class="section fixed inset-0 hidden bg-white">
    <div class="px-6 py-4 text-center">
        <h2 class="text-xl font-bold text-gray-800">Preview OMR Sheet</h2>
    </div>

    <div class="flex h-[calc(100vh-140px)] w-full items-center justify-center overflow-hidden px-4">
        <img id="imagePreview" class="max-h-[90%] max-w-full object-contain" src="" alt="OMR Sheet Preview">
    </div>

    <div class="fixed inset-x-0 bottom-6 z-40 flex flex-wrap items-center gap-3 px-4">
        <button id="rotateLeft"
            class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-200">
            <span class="text-base">⟲</span>
            <span>Left</span>
        </button>

        <button id="rotateRight"
            class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-200">
            <span class="text-base">⟳</span>
            <span>Right</span>
        </button>

        <button
            class="backToUpload flex flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Back
        </button>

        <div id="processingModal" class="fixed inset-0 z-[9999] hidden bg-black/60 px-4">
            <div class="flex min-h-full w-full items-center justify-center">
                <div class="w-full max-w-sm rounded-2xl bg-white p-8 text-center shadow-xl">
                    <div class="mx-auto mb-4 h-9 w-9 animate-spin rounded-full border-4 border-gray-200 border-t-blue-500"></div>
                    <p id="progressText" class="text-sm text-gray-700">Processing image...</p>
                </div>
            </div>
        </div>

        <button id="processImage"
            class="flex flex-1 items-center justify-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-700">
            <span>Process</span>
        </button>
    </div>
</div>

<script>
    
</script>