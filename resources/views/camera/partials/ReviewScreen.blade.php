<div id="resultsSection" class="section fixed inset-0 hidden overflow-y-auto bg-white p-2">
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-md">
        <h2 class="text-2xl font-bold text-gray-800">Detected Answers</h2>
    </div>

    <div class="flex flex-col gap-4 p-2 md:flex-row">
        <div id="canvasWrapper"
            class="sticky top-6 z-50 flex h-[35vh] w-full justify-center rounded-2xl bg-black/5 md:w-1/3">
            <div id="canvas-background"
                class="absolute inset-0 -z-10 rounded-2xl bg-black transition-all duration-300"></div>
            <canvas id="canvasOutput" class="relative max-h-full max-w-full cursor-pointer origin-top"></canvas>
        </div>

        <form method="POST" class="md:w-2/3 w-full max-w-5xl mx-auto px-5 space-y-10 mb-16 overflow-auto md:border-l-2">
            @csrf

            <div class="my-6 rounded border border-red-300 bg-red-50">
                <button type="button" data-toggle="undetectedBubblesContainer"
                    class="flex w-full items-center justify-between px-4 py-3 text-left text-red-700 font-semibold hover:bg-red-100">
                    <span>Undetected Bubbles</span>
                    <svg class="h-5 w-5 text-red-600 transition-transform duration-300" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="undetectedBubblesContainer" class="hidden p-4 text-sm text-red-800">
                    sads
                </div>
            </div>


            <h1 class="text-2xl font-bold text-blue-900">Fill in / Adjust Detected Answers</h1>

            @if(!empty($omrSheet))
                @include('camera.partials.mcq-sections', [
                    'mcqSections' => $mcqSections,
                    'omrSheet' => $omrSheet
                ])
            @endif

            <div
                class="w-full max-w-md mx-auto bg-gradient-to-r from-blue-100 to-blue-200 rounded-2xl py-6 mt-6 shadow-lg px-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="rounded-full bg-blue-600 p-3 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 21h8m-4-4v4m-6-9a6 6 0 0012 0V5H6v7zM4 5h16" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800">Total Score</span>
                </div>
                <span id="totalScoreValue" class="text-3xl font-extrabold text-blue-700">0</span>
            </div>

            @if(!empty($personData))
                <div
                    class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl shadow-lg mb-6 p-6">
                    <div class="mb-5 flex items-center space-x-3">
                        <div class="rounded-full bg-blue-600 p-3 text-white shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5.121 17.804A7 7 0 1118.364 4.56M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Respondent Data</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="personNameInput" class="mb-1 block text-sm font-medium text-gray-700">
                                Name
                            </label>
                            <input list="personSuggestions" id="personNameInput" name="personName"
                                class="w-full rounded-lg border border-blue-300 px-4 py-2 text-sm transition focus:ring-2 focus:ring-blue-500"
                                placeholder="Start typing a name..." autocomplete="off">
                            <datalist id="personSuggestions">
                                @foreach($personData as $person)
                                    <option data-id="{{ $person['Id'] }}" value="{{ $person['Name'] }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <input type="hidden" id="personId" name="personId" value="">
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <button type="button"
                    class="backToUpload rounded-lg border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Back
                </button>
                <button type="button" id="completeScan" onclick="openModal('confirmModal')"
                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white shadow-lg transition hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Complete & Save</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('[data-toggle]');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle');
            const target = document.getElementById(targetId);
            if (!target) return;

            // Toggle visibility
            target.classList.toggle('hidden');

            // Rotate arrow SVG if present
            const icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        });
    });
});
</script>
