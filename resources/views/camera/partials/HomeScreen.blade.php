<div id="uploadSection"
    class="section fixed inset-0 flex min-h-screen flex-col items-center justify-center bg-gray-50 px-6 py-10">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800">OMR Sheet Scanner</h1>
        <p class="mt-2 text-gray-500 text-sm">Take a clear photo of your OMR sheet with good lighting</p>
    </div>

    <input type="file" id="imageUpload" accept="image/*" capture="environment" class="hidden" />

    <label for="imageUpload" class="cursor-pointer">
        <div
            class="flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg transition hover:bg-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Take Photo</span>
        </div>
    </label>

    <a href="{{ route('assessment.view', $assessment->id) }}" class="mt-6 text-sm font-semibold text-blue-600 hover:underline">
        Back to Assessment
    </a>
</div>