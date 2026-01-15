<!-- Tutorial Modal -->
<div id="tutorialModal" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-xl.font-bold">How to Use EasyAssess</h2>
            <ol class="mb-6 list-inside list-decimal space-y-2 text-sm text-gray-700">
                <li>Take a clear photo of your OMR sheet. Make sure the MCQ section is visible.</li>
                <li>(Optional) Enter the name of the student or respondent.</li>
                <li>Review the detected bubbles and answers. Adjust if needed.</li>
                <li>Fill in scores for essay and blank-type questions.</li>
                <li>Click <strong>Submit</strong> once everything is complete.</li>
            </ol>
            <div class="mb-4 space-y-2 text-sm">
                <div class="flex items-center space-x-3">
                    <div class="relative h-5 w-5">
                        <div class="absolute inset-0 rounded-full border-2 border-green-600"></div>
                        <svg class="absolute h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 10l3 3 6-6" />
                        </svg>
                    </div>
                    <span>Correct bubble selected</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="h-5 w-5 rounded-full border-2 border-green-500"></div>
                    <span>Expected answer, but not shaded</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative h-5 w-5">
                        <div class="absolute inset-0 rounded-full border-2 border-red-600"></div>
                        <svg class="absolute h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 6l8 8M14 6l-8 8" />
                        </svg>
                    </div>
                    <span>Wrong bubble shaded</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative h-5 w-5">
                        <div class="absolute inset-0 rounded-full border border-orange-500 bg-orange-400"></div>
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-xs font-bold text-white">?</span>
                    </div>
                    <span>Multiple bubbles shaded (ambiguous)</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="h-5 w-5 rounded-full border-2 border-blue-500"></div>
                    <span>Empty bubble (not expected to be shaded)</span>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button onclick="closeModal('tutorialModal')" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div id="settingsModal" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="flex min-h-full w-full items-center justify-center">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-xl font-bold">Settings</h2>
            <div class="mb-4">
                <label class="mb-1 block font-medium text-gray-700">
                    Shade Fill Threshold
                    <span class="group relative ml-1 inline-block">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">?</span>
                        <div class="absolute bottom-full left-1/2 z-10 mb-2 w-64 -translate-x-1/2 rounded bg-gray-800 p-2 text-xs text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                            Sets how much fill is needed to consider a bubble shaded. Increase to reduce false positives.
                        </div>
                    </span>
                </label>
                <input id="fillRatioInput" type="range" min="1" max="100" value="30" class="w-full">
                    <p class="mt-1 text-sm text-gray-600">Current: <span id="fillRatioDisplay">30</span>%</p>
            </div>
            <div class="mt-6 text-right">
                <button onclick="closeModal('settingsModal')" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 z-[10000] hidden bg-black/60 px-4">
    <div class="flex min-h-full w-full items-center justify-center">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <p class="mb-4 text-sm text-gray-700">
                Not enough OMR boxes detected, possible answer mismatch may occur.
                <br> Please check the image and try again ensuring the paper is clearly visible.
            </p>
            <button onclick="hideErrorModal()"
                class="rounded-lg bg-red-600 px-5 py-2 font-medium text-white transition hover:bg-red-700">Close</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden bg-black/40 p-4">
    <div class="flex min-h-full w-full items-center justify-center">
        <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-lg">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Are you sure?</h2>
            <p class="mb-6 text-sm text-gray-600">Please confirm that you have reviewed all scores before submitting.</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal('confirmModal')"
                    class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300">Cancel</button>
                <button id="confirmSubmit"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Submit</button>
            </div>
        </div>
    </div>
</div>

<div id="processingToast"
    class="pointer-events-none fixed bottom-5 right-5 z-50 rounded bg-gray-800 px-4 py-2 text-sm text-white shadow-lg opacity-0 transition-opacity duration-500">
    Time taken for this recording: <span id="processingTimeValue">0</span> seconds
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        // If it uses inline style
        if (modal.style.display === 'none' || getComputedStyle(modal).display === 'none') {
            modal.style.display = 'flex';
        }
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
</script>