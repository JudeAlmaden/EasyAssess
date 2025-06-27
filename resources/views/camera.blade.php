<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">


    <title>OMR Scanner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow: visible;
        }

        .section {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            min-width: 100vw;
            min-height: 100vh;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            background: white;
            overflow: hidden;
            padding-bottom: calc(80px + env(safe-area-inset-bottom, 0));
        }


        .section.active {
            transform: translateX(0);
        }

        .section.next {
            transform: translateX(100vw);

        }

        .section.prev {
            transform: translateX(-100vw);
        }

        #status {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            z-index: 100;
        }

        #canvasOutput {
            width: 100%;
            height: calc(100vh - 80px);
            object-fit: contain;
            background: black;
        }

        .preview-container {
            width: 100%;
            height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .preview-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .action-btn {
            padding: 14px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            margin: 8px;
            font-size: 16px;
            flex: 1;
        }

        .primary-btn {
            background: #3b82f6;
            color: white;
            border: none;
        }

        .secondary-btn {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            position: fixed;
            bottom: 5%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            gap: 12px;
            z-index: 999;
            box-sizing: border-box;
        }


        .upload-options {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            padding: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div id="status">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
        <p class="text-lg">Loading OMR Scanner...</p>
    </div>

    <!-- Section 1: Upload Image -->
    <div id="uploadSection"
        class="section active py-10 px-6 flex flex-col items-center justify-center min-h-screen bg-gray-50">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800">OMR Sheet Scanner</h1>
            <p class="mt-2 text-gray-500 text-sm">Take a clear photo of your OMR sheet with good lighting</p>
        </div>

        <input type="file" id="imageUpload" accept="image/*" capture="environment" class="hidden" />

        <label for="imageUpload" class="cursor-pointer">
            <div
                class="action-btn primary-btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl flex items-center gap-2 shadow-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-semibold">Take Photo</span>
            </div>
        </label>
        <a href="{{route('assessment.view', $assessment->id)}}" >
            Back
        </a>
    </div>

    <!-- Section 2: Preview Image -->
    <div id="previewSection" class="section next">
        <div class="header">
            <h2 class="text-xl font-bold">Preview OMR Sheet</h2>
        </div>

        <div class="preview-container">
            <img id="imagePreview" class="preview-image" src="" alt="OMR Sheet Preview">
        </div>

        <div class="btn-container flex gap-1 flex-wrap mt-2 text-sm">
        <button id="rotateLeft" class="action-btn secondary-btn flex items-center gap-1 px-2 py-1">
            <span class="text-base">⟲</span>
            <span>Left</span>
        </button>

        <button id="rotateRight" class="action-btn secondary-btn flex items-center gap-1 px-2 py-1">
            <span class="text-base">⟳</span>
            <span>Right</span>
        </button>

        <button class="backToUpload action-btn secondary-btn px-2 py-1">
            Back
        </button>

        <button id="processImage" class="action-btn primary-btn flex items-center gap-1 px-2 py-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-6h13M3 6h3m0 0l1.5 4H20M6 6L3 17h18" />
            </svg>
            <span>Process</span>
        </button>
        </div>
    </div>

    <!-- Section 3: Results -->
    <div id="resultsSection" class="section next overflow-y-auto p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Detected Answers</h2>
        </div>

        <!-- Canvas Output -->
        <div class="w-full mb-8">
            <canvas id="canvasOutput" class="w-full h-auto bg-black rounded-lg shadow"></canvas>
        </div>

        <!-- Total Score -->
        <div id="totalScoreDisplay"
            class="w-full max-w-md mx-auto mt-6 bg-white border border-gray-300 rounded-xl shadow px-6 py-4 flex items-center justify-between">
            <span class="text-lg font-semibold text-gray-800">Total Score</span>
            <span id="totalScoreValue" class="text-2xl font-bold text-green-600">0</span>
        </div>

        <!-- Detected Answers Form -->
        <form method="POST" action="" class="w-full max-w-5xl mx-auto px-4 sm:px-6 md:px-8 lg:px-16 py-10 space-y-10">
            @csrf

            @if(!empty($assessment->person_dictionary_snapshot))
                @php
                    $personData = $assessment->person_dictionary_snapshot['data'] ?? [];
                @endphp

                <div class="bg-white border border-gray-300 rounded-xl shadow-md mb-4 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Identify Person</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="personNameInput" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input list="personSuggestions" id="personNameInput" name="personName"
                                class="w-full border border-gray-300 rounded px-4 py-2 text-sm"
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

            @if(!empty($assessment->omr_sheet_snapshot['OMRSheet']))
                @php
                    $omr = $assessment->omr_sheet_snapshot['OMRSheet'];
                    $sections = [];

                    if (!empty($omr['MCQ'])) {
                        foreach ($omr['MCQ'] as $block) {
                            $sections[$block['section'] ?? 'Unlabeled'][] = $block;
                        }
                    }
                @endphp

                {{-- MCQ Sections Grouped (Double Collapsible) --}}
                @foreach($sections as $sectionName => $blocks)
                    @php $sectionId = 'section_' . \Illuminate\Support\Str::slug($sectionName, '_'); @endphp
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md mb-4">
                        <button type="button" data-toggle="{{ $sectionId }}"
                            class="w-full text-left px-6 py-4 flex justify-between items-center hover:bg-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $sectionName }}</h3>
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="{{ $sectionId }}" class="hidden px-6 pb-6 space-y-6">
                            @foreach($blocks as $block)
                                @php $blockId = 'block_' . \Illuminate\Support\Str::slug($block['id'], '_'); @endphp
                                <div class="border border-gray-200 rounded-md">
                                    <button type="button" data-toggle="{{ $blockId }}"
                                        class="w-full text-left px-4 py-3 flex justify-between items-center bg-gray-100 hover:bg-gray-200">
                                        @php
                                            // Use a friendlier label: fallback to index or strip prefix
                                            $friendlyId = preg_replace('/^mcq_block_/', '', $block['id']);
                                        @endphp
                                        <h4 class="text-sm font-medium text-gray-700">Block {{ $friendlyId }}</h4>
                                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div id="{{ $blockId }}" class="hidden px-4 py-4 space-y-4 bg-white">
                                        <p class="text-sm text-gray-500">
                                            Items: <strong>{{ $block['items'] }}</strong> |
                                            Choices: <strong>{{ $block['choices'] }}</strong>
                                        </p>

                                        @for($i = 1; $i <= $block['items']; $i++)
                                            <div class="bg-gray-50 p-4 rounded border">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Item {{ $i }}</label>
                                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                                    @for($c = 0; $c < $block['choices']; $c++)
                                                        <label class="flex items-center space-x-2">
                                                            <input type="checkbox" id="bubble_{{ $block['id'] }}_{{ $i }}_{{ $c }}"
                                                                name="mcq[{{ $block['id'] }}][{{ $i }}]" value="{{ $c }}"
                                                                class="w-5 h-5 text-blue-600 form-checkbox" />
                                                            <span class="text-sm font-medium text-gray-800">{{ chr(65 + $c) }}</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Blanks --}}
                @if(!empty($omr['Blanks']))
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md mb-4">
                        <button type="button" data-toggle="blanks_section"
                            class="w-full text-left px-6 py-4 flex justify-between items-center hover:bg-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">Blanks</h3>
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="blanks_section" class=" px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($omr['Blanks'] as $blank)
                                <div class="flex justify-between items-center border-b border-gray-200 py-3">
                                    <div class="text-sm font-medium text-gray-700 w-1/2">
                                        {{ $blank['section'] }}
                                    </div>
                                    <div class="flex items-center gap-2 w-1/2 justify-end">
                                        <input type="number" name="blanks[{{ $blank['id'] }}]"
                                            class="w-24 border border-gray-300 bg-white rounded px-3 py-2 text-sm" value="0" min="0"
                                            required>
                                        <span class="text-sm text-gray-600">Points</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Freeform --}}
                @if(!empty($omr['Freeform']))
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md mb-4">
                        <button type="button" data-toggle="freeform_section"
                            class="w-full text-left px-6 py-4 flex justify-between items-center hover:bg-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">Freeform</h3>
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="freeform_section" class=" px-6 pb-6 grid grid-cols-1 gap-6">
                            @foreach($omr['Freeform'] as $field)
                                <div class="w-full border-b border-gray-200 py-3">
                                    <div class="flex justify-between items-center w-full">
                                        <div class="text-sm font-medium text-gray-700">
                                            {{ $field['Instruction'] }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="freeform[{{ $field['id'] }}]"
                                                class="w-24 border border-gray-300 bg-white rounded px-3 py-2 text-sm" value="0"
                                                min="0" required>
                                            <span class="text-sm text-gray-600">Points</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <!-- Final Action Button -->
            <div class="flex justify-end grid grid-cols-2">
                <button type="button" class="backToUpload action-btn secondary-btn">Back</button>
                <button type="button" id="completeScan"
                    class="bg-green-600 hover:bg-green-700 transition text-white font-semibold px-8 py-3 rounded-lg shadow">
                    Complete & Save
                </button>
            </div>

            <!-- Confirmation Modal -->
        </form>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script async src="https://cdn.jsdelivr.net/npm/opencv.js" onload="onOpenCvReady()" type="text/javascript"></script>

    <div id="confirmModal" class=" inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden"
        style="position:fixed">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Are you sure?</h2>
            <p class="text-sm text-gray-600 mb-6">Please confirm that you have reviewed all scores before submitting.
            </p>
            <div class="flex justify-end gap-3">
                <button id="cancelConfirm"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md">Cancel</button>
                <button id="confirmSubmit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">Submit</button>
            </div>
        </div>
    </div>

</body>

</html>

<script>
    let opencvReady = false;
    let currentSection = 'upload';
    let uploadedImage = null;

    function onOpenCvReady() {
        opencvReady = true;
        document.getElementById('status').style.display = 'none';
    }

    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active', 'prev', 'next');
            section.classList.add('next');
        });

        // Show selected section
        const section = document.getElementById(sectionId);
        section.classList.remove('next');
        section.classList.add('active');

        currentSection = sectionId.replace('Section', '');
    }

    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById('confirmModal');
        const openBtn = document.getElementById('completeScan');
        const cancelBtn = document.getElementById('cancelConfirm');
        const confirmBtn = document.getElementById('confirmSubmit');
        const backButtons = document.getElementsByClassName('backToUpload');
        const imageInput = document.getElementById('imageUpload');
        const imagePreview = document.getElementById('imagePreview');
        const canvas = document.getElementById('canvasOutput');
        const processImageBtn = document.getElementById('processImage')
        const ctx = canvas.getContext('2d');
        const nameInput = document.getElementById('personNameInput');
        const personIdInput = document.getElementById('personId');
        const datalistOptions = Array.from(document.getElementById('personSuggestions').options);

        let uploadedImage = null;

        //Section toggle
        document.querySelectorAll('[data-toggle]').forEach(button => {
            const targetId = button.getAttribute('data-toggle');
            const target = document.getElementById(targetId);

            button.addEventListener('click', () => {
                target.classList.toggle('hidden');
                const icon = button.querySelector('svg');
                if (icon) icon.classList.toggle('rotate-180');
            });
        });

        // Main back-to-upload handler
        document.querySelectorAll('.backToUpload').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                fullReset();
                showSection('uploadSection');
            });
        });

        function handleImageUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            uploadedImage = file;
            const reader = new FileReader();

            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreview.classList.remove('hidden');
                showSection('previewSection');
                
                // Reset after everything is done
                setTimeout(() => {
                    imageInput.value = '';
                }, 0);
            };

            reader.readAsDataURL(file);
        }


        //ActionListeners
        imageInput.addEventListener('change', handleImageUpload);

        // Process image
        processImageBtn.addEventListener('click', () => {
            if (!uploadedImage) return;

            const img = new Image();
            img.onload = () => {
                const maxWidth = window.innerWidth;
                const maxHeight = window.innerHeight - 140;

                let { width, height } = img;

                if (width > maxWidth) {
                    const ratio = maxWidth / width;
                    width = maxWidth;
                    height *= ratio;
                }

                if (height > maxHeight) {
                    const ratio = maxHeight / height;
                    height = maxHeight;
                    width *= ratio;
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                const result = processImage();

                if (result === 1) {
                    imageInput.value = '';
                    uploadedImage = null;
                    imagePreview.src = '';
                    imagePreview.classList.add('hidden');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    showSection('uploadSection');
                } else {
                    showSection('resultsSection');
                }
            };

            img.src = URL.createObjectURL(uploadedImage);
        });

        // Open confirmation modal
        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        // Cancel modal
        cancelBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Confirm submit and run fetch + reset
        confirmBtn.addEventListener('click', async () => {
            modal.classList.add('hidden');
            let personId = document.querySelector('#personId')?.value || null;
            let personName = document.querySelector('#personNameInput')?.value || null;
            const payload = {
            scoredBlocks,
            personId,
            personName,
            ...(typeof code !== 'undefined' && code !== null ? { code } : {}) // Only include if defined
            };

            console.log("Person id is " + personId)
            try {
              const response = await fetch("{{ route('assessment.record.create', ['id' => $assessment->id]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
                });

                const result = await response.json();
                console.log('Response from Laravel:', result);

                if (response.ok) {
                    showAlert('Successfully saved!', 'green');
                } else {
                    showAlert('Error: ' + (result.message || 'Save failed'), 'red');
                    console.error('Server response:', result);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showAlert('An unexpected error occurred.', 'red');
            }

            fullReset();
            showSection('uploadSection');
        });

        nameInput.addEventListener('input', () => {
            const matchedOption = datalistOptions.find(opt => opt.value === nameInput.value);

            if (matchedOption) {
                // If the input matches a datalist option, set its data-id to the hidden input
                personIdInput.value = matchedOption.dataset.id;
            } else {
                // If it's a new/unknown name, keep personId null
                personIdInput.value = '';
            }
        });

        document.getElementById('rotateLeft').addEventListener('click', () => {
            rotation = (rotation - 90 + 360) % 360; // ensures rotation stays between 0–359
            applyRotation();
        });

        document.getElementById('rotateRight').addEventListener('click', () => {
            rotation = (rotation + 90) % 360;
            applyRotation();
        });

    
        function applyRotation() {
            const img = document.getElementById('imagePreview');
            img.style.transform = `rotate(${rotation}deg)`;
        }

        //utility functions
        function showAlert(message, color = 'green') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `fixed top-5 right-5 z-50 px-4 py-2 rounded shadow-lg bg-${color}-600 text-white text-sm transition-opacity duration-500 opacity-0`;
            alertDiv.innerText = message;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.classList.remove('opacity-0');
                alertDiv.classList.add('opacity-100');
            }, 10); // fade-in

            setTimeout(() => {
                alertDiv.classList.remove('opacity-100');
                alertDiv.classList.add('opacity-0');
            }, 3000); // fade-out after 3 sec

            setTimeout(() => {
                alertDiv.remove();
            }, 3500); // remove after fade
        }

        // Reset function for image upload and related elements
        function resetImageUpload() {
            // 1. Reset file input (clone and replace)
            const imageInput = document.getElementById('imageUpload');
            const newInput = imageInput.cloneNode(true);
            imageInput.parentNode.replaceChild(newInput, imageInput);
            newInput.addEventListener('change', handleImageUpload);
            
            // 2. Reset image preview
            const previewImg = document.getElementById('imagePreview');
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            
            // 3. Reset canvas
            const canvas = document.getElementById('canvasOutput');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            
            // 4. Reset internal image state
            uploadedImage = null;
            rotation = 0; // Reset rotation state
            }

        // Reset function for form fields
        function resetFormFields() {
            document.querySelectorAll('input[type="number"]').forEach(input => input.value = 0);
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }

        // Reset function for scoring data
        function resetScoringData() {
        columnResults = [];
        scoreResults = [];
        totalScore = 0;
        scoredBlocks = {
            mcq: [],
            Freeform: [],
            Blanks: []
        };
        }

        // Reset everything for a fresh start
        function fullReset() {
            resetImageUpload();
            resetFormFields();
            resetScoringData();
        }
});
</script>


{{-- Worse code ever --}}
<script type="text/javascript">
    const rawAnswerKey = @json($assessment->answer_key);
    const omrSheetSnapshot = @json($assessment->omr_sheet_snapshot);

    // Ensure fallback to empty arrays if null
    omrSheetSnapshot.Freeform = omrSheetSnapshot.Freeform || [];
    omrSheetSnapshot.Blanks = omrSheetSnapshot.Blanks || [];


    const numItemsPerBox = [];
    const numChoicesPerBox = [];
    const answers = [];         //Answer key

    let columnResults = [];     //Answers per box
    let scoredBlocks = [];      //Scores per box
    let totalScore;             //Total Score

    for (const blockId in rawAnswerKey) {
        const block = rawAnswerKey[blockId];
        const items = block.answers || {};
        const itemAnswers = [];

        // Push number of items
        numItemsPerBox.push(Object.keys(items).length);

        // Push number of choices (default to 4 if missing)
        numChoicesPerBox.push(block.numberOfChoices ?? 4);

        for (const key of Object.keys(items)) {
            itemAnswers.push(items[key]);
        }
        answers.push(itemAnswers);
    }

    //Debug values for layout
    const numberOfOmrBoxes = numItemsPerBox.length;
    const DESIGN_BUBBLE_DIAMETER = 33;
    const DESIGN_BUBBLE_RADIUS = DESIGN_BUBBLE_DIAMETER / 2;
    const DESIGN_BUBBLE_GAP = 5;
    const DESIGN_BUBBLE_MARGIN = 3;
    const DESIGN_PADDING_LEFT = 20;

    //Debug 
    let shadeThreshold = 120;
    let MIN_RADIUS_RATIO = 0.15;           // Minimum radius = radius * 0.15 (filters out tiny noise)
    let MAX_RADIUS_RATIO = 1.15;           // Maximum radius = radius * 1.15 (allows slight tolerance)
    let MIN_DIST_RATIO = 0.3;              // Minimum distance between centers = (diameter + gap) * 0.3
    let cannyHighThreshold = 50;           // param1: Canny high threshold for edge detection
    let accumulatorThreshold = 25;         // param2: Circle center threshold (lower = more circles)

    let rotation = 0; // Tracks total rotation in degrees (0, 90, 180, 270)

    function processImage() {
        let imgElement = document.getElementById('imagePreview');
        let startTime = performance.now();

        // Image prep
        let inpt = cv.imread(imgElement);

        // Apply rotation based on tracked rotation value
        if (rotation === 90 || rotation === 270 || rotation === 180) {
            let rotated = new cv.Mat();

            if (rotation === 90) {
                cv.transpose(inpt, rotated);
                cv.flip(rotated, rotated, 1); // Flip horizontally
            } else if (rotation === 270) {
                cv.transpose(inpt, rotated);
                cv.flip(rotated, rotated, 0); // Flip vertically
            } else if (rotation === 180) {
                cv.flip(inpt, rotated, -1); // Flip both axes
            }

            inpt.delete();
            inpt = rotated;
        }


        let preProssedImg = new cv.Mat();
        let greyImg = new cv.Mat();
        let gaussianBlurImg = new cv.Mat();
        let cannyImg = new cv.Mat();
        let contoursImg = new cv.Mat();
        let contours = new cv.MatVector();
        let hierarchy = new cv.Mat();

        // Resize & grayscale
        let maxDim = 2500;
        let scale = Math.min(maxDim / inpt.cols, maxDim / inpt.rows, 1);
        cv.resize(inpt, preProssedImg, new cv.Size(), scale, scale, cv.INTER_AREA);
        cv.cvtColor(preProssedImg, contoursImg, cv.COLOR_RGBA2BGR);
        cv.cvtColor(preProssedImg, greyImg, cv.COLOR_RGBA2GRAY);

        // Blur + edge detection
        let ksize = new cv.Size(3, 3);
        cv.GaussianBlur(greyImg, gaussianBlurImg, ksize, 1, cv.BORDER_DEFAULT);
        cv.Canny(gaussianBlurImg, cannyImg, 50, 150);

        // Find contours of squares
        cv.findContours(cannyImg, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
        let areaThreshold = (preProssedImg.cols * preProssedImg.rows) * 0.005;
        let filteredContours = getFilteredContours(contours, areaThreshold);
        let sortedContours = sortContoursByPosition(filteredContours, 30); // Sorted left-right, top-bottom

        if (sortedContours.size() < numberOfOmrBoxes) {
            alert("Not enough OMR boxes detected. Please check the image and try again.");
            return 1;
        }

        // Visualize corners
        drawContourCorners(sortedContours, contoursImg, new cv.Scalar(0, 255, 0), numberOfOmrBoxes);
        handleCircles(contoursImg, sortedContours, numberOfOmrBoxes);

        // Show result and log time
        cv.imshow('canvasOutput', contoursImg);
        console.log(`Processing Time: ${performance.now() - startTime} ms`);

        // Cleanup
        inpt.delete(); preProssedImg.delete(); greyImg.delete(); gaussianBlurImg.delete();
        cannyImg.delete(); contoursImg.delete(); contours.delete(); hierarchy.delete();

        // Scoring logic
        handleScore();
    }

    function getApprox(contour) {
        let perimeter = cv.arcLength(contour, true);
        let approx = new cv.Mat();
        cv.approxPolyDP(contour, approx, 0.02 * perimeter, true);
        return approx;
    }

    function getFilteredContours(contours, areaThreshold) {
        let temp = [];
        for (let i = 0; i < contours.size(); ++i) {
            let contour = contours.get(i);
            let area = cv.contourArea(contour);
            let approx = getApprox(contour);
            if (area > areaThreshold && approx.rows === 4) {
                temp.push({ contour: contour, area: area });
            }
            approx.delete();
        }
        temp.sort((a, b) => b.area - a.area);
        let filtered = new cv.MatVector();
        for (let i = 0; i < temp.length; i++) {
            filtered.push_back(temp[i].contour);
        }
        return filtered;
    }

    function sortContoursByPosition(contoursVec, rowTolerance = 30) {
        let sorted = new cv.MatVector();
        let boxes = [];

        for (let i = 0; i < contoursVec.size(); i++) {
            let cnt = contoursVec.get(i);
            let rect = cv.boundingRect(cnt);
            let centerX = rect.x + rect.width / 2;
            let centerY = rect.y + rect.height / 2;
            boxes.push({ index: i, x: centerX, y: centerY });
        }

        boxes.sort((a, b) => {
            if (Math.abs(a.x - b.x) < rowTolerance) {
                return a.y - b.y;
            }
            return a.x - b.x;
        });

        for (let i = 0; i < boxes.length; i++) {
            sorted.push_back(contoursVec.get(boxes[i].index));
        }

        return sorted;
    }

    function drawContourCorners(contoursVec, drawImg, color, count) {
        for (let c = 0; c < count; c++) {
            let contApprox = getApprox(contoursVec.get(c));
            for (let i = 0; i < contApprox.rows; i++) {
                let point = new cv.Point(contApprox.data32S[i * 2], contApprox.data32S[i * 2 + 1]);
                cv.circle(drawImg, point, 5, color, -1);
            }
            contApprox.delete();
        }
    }

    function warpToRectangle(srcMat, corners, width, height) {
        let dst = new cv.Mat();
        let srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, corners.flatMap(p => [p.x, p.y]));
        let dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
            0, 0,
            width - 1, 0,
            width - 1, height - 1,
            0, height - 1
        ]);
        let M = cv.getPerspectiveTransform(srcTri, dstTri);
        cv.warpPerspective(srcMat, dst, M, new cv.Size(width, height));
        srcTri.delete(); dstTri.delete(); M.delete();
        return dst;
    }


    //Loop through every box and find circles
    function handleCircles(srcMat, boxContours, numBoxes) {
        for (let i = 0; i < Math.min(boxContours.size(), numBoxes); i++) {
            const cnt = boxContours.get(i);
            const rect = cv.boundingRect(cnt);
            const roi = srcMat.roi(rect);

            const {
                gray,
                blurred,
                scaleFactor,
                bubbleDiameter,
                bubbleRadius,
                bubbleGap,
                paddingLeft
            } = preprocessROI(roi, numChoicesPerBox[i]);

            const bubbles = detectBubbles(blurred, bubbleRadius, bubbleDiameter, bubbleGap);

            const rowGroups = groupBubblesIntoRows(
                bubbles,
                numChoicesPerBox[i],
                numItemsPerBox[i],
                bubbleGap
            );

            if (!rowGroups) {
                roi.delete(); gray.delete(); blurred.delete();
                continue;
            }

            annotateBubbles(roi, rowGroups, i);
            columnResults.push(rowGroups);

            roi.delete(); gray.delete(); blurred.delete();
        }
    }

    //Step 1: Prepare the roi, called on the loop of handle circles
    function preprocessROI(roi, numChoices) {
        // 1. Convert ROI to grayscale
        const gray = new cv.Mat();
        cv.cvtColor(roi, gray, cv.COLOR_RGBA2GRAY);

        // 2. Apply Gaussian blur (you can try medianBlur for noisy images)
        const blurred = new cv.Mat();
        cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 1.5, 1.5);

        // 3. Estimate design width and scale based on number of choices (bubble columns)
        const designWidth = DESIGN_PADDING_LEFT +
            (DESIGN_BUBBLE_DIAMETER * numChoices) +
            (DESIGN_BUBBLE_GAP * (numChoices - 1));

        const scaleFactor = roi.cols / designWidth;

        // 4. Return relevant data
        return {
            gray,
            blurred,
            scaleFactor,
            bubbleDiameter: Math.round(DESIGN_BUBBLE_DIAMETER * scaleFactor),
            bubbleRadius: Math.round(DESIGN_BUBBLE_RADIUS * scaleFactor),
            bubbleGap: Math.round(DESIGN_BUBBLE_GAP * scaleFactor),
            paddingLeft: Math.round(DESIGN_PADDING_LEFT * scaleFactor)
        };
    }

    //Step 2: detect the bubbles on a roi and calls is bubble filled for each bubble
    function detectBubbles(blurred, radius, diameter, gap) {
        const circlesMat = new cv.Mat();
        const bubbles = [];

        // Adjustable detection parameters
        const dp = 1;
        const minDist = (diameter + gap) * MIN_DIST_RATIO;
        const minRadius = radius * MIN_RADIUS_RATIO;
        const maxRadius = radius * MAX_RADIUS_RATIO;

        // Hough Circle Detection
        cv.HoughCircles(
            blurred,
            circlesMat,
            cv.HOUGH_GRADIENT,
            dp,
            minDist,
            cannyHighThreshold,
            accumulatorThreshold,
            minRadius,
            maxRadius
        );

        //Check if filled or not
        for (let j = 0; j < circlesMat.cols; j++) {
            const x = circlesMat.data32F[j * 3];
            const y = circlesMat.data32F[j * 3 + 1];
            const r = circlesMat.data32F[j * 3 + 2];

            const shaded = isBubbleFilled(blurred, { cx: x, cy: y, radius: r });

            bubbles.push({
                cx: x,
                cy: y,
                radius: r,
                shaded,
                isCorrect: false
            });
        }

        circlesMat.delete();
        return bubbles;
    }

    //Step 3: Check whether the circle is filled or not
    function isBubbleFilled(blurredMat, bubble) {
        let mask = new cv.Mat.zeros(blurredMat.rows, blurredMat.cols, cv.CV_8UC1);
        cv.circle(mask, new cv.Point(bubble.cx, bubble.cy), bubble.radius, new cv.Scalar(255), -1);
        const mean = cv.mean(blurredMat, mask)[0];
        mask.delete();
        return mean < shadeThreshold;
    }

    //Step 4: Draw onto parent mat the results, used on handleCircles
    function annotateBubbles(roi, rowGroups, answerIndex) {
        for (let rowIndex = 0; rowIndex < rowGroups.length; rowIndex++) {
            let row = rowGroups[rowIndex];
            let shadedCount = row.filter(b => b && b.shaded).length;

            for (let colIndex = 0; colIndex < row.length; colIndex++) {
                const bubble = row[colIndex];
                if (!bubble) continue;

                const center = new cv.Point(bubble.cx, bubble.cy);
                const radius = bubble.radius;

                const drawCircle = (color, thickness) => {
                    cv.circle(roi, center, radius, color, thickness);
                };

                if (shadedCount > 1) {
                    // Orange – Multiple shaded (ambiguous)
                    bubble.isCorrect = false;
                    drawCircle(new cv.Scalar(240, 165, 0, 255), 2);
                } else if (typeof answers !== 'undefined' && answers[answerIndex][rowIndex]?.includes(colIndex + 1)) {
                    if (bubble.shaded) {
                        // Green – Correct and shaded
                        bubble.isCorrect = true;
                        drawCircle(new cv.Scalar(50, 205, 50, 255), 2);
                    } else {
                        // Dashed Green – Correct but missed
                        for (let angle = 0; angle < 360; angle += 30) {
                            cv.ellipse(
                                roi,
                                center,
                                new cv.Size(radius, radius),
                                0,
                                angle,
                                angle + 10,
                                new cv.Scalar(50, 205, 50, 255),
                                2
                            );
                        }
                    }
                } else {
                    if (bubble.shaded) {
                        // Red-Orange – Wrong and shaded
                        drawCircle(new cv.Scalar(255, 69, 0, 255), 2);
                    } else {
                        // Light Blue – Unshaded, not the correct answer
                        drawCircle(new cv.Scalar(100, 150, 255, 255), 2);
                    }
                }
            }
        }
    }

    //Helper: Arrange into rows and cols
    function groupBubblesIntoRows(bubbles, bubblesPerRow, numberOfExpectedRows, rowThreshold = 20) {
        // Step 1: Group into rows based on y-axis (cy)
        let rowGroups = [];
        for (let b of bubbles) {
            let added = false;
            for (let row of rowGroups) {
                if (Math.abs(row[0].cy - b.cy) < rowThreshold) {
                    row.push(b);
                    added = true;
                    break;
                }
            }
            if (!added) rowGroups.push([b]);
        }

        // Step 2: Sort rows top-to-bottom
        rowGroups.sort((a, b) => a[0].cy - b[0].cy);

        // Step 3: Process each row
        const finalRows = [];
        for (let row of rowGroups) {
            // Sort the row left to right
            row.sort((a, b) => a.cx - b.cx);

            // Estimate expected horizontal spacing
            let expectedGap = row.length > 1
                ? (row[row.length - 1].cx - row[0].cx) / (bubblesPerRow - 1)
                : 0;

            // Initialize empty row
            const newRow = new Array(bubblesPerRow).fill(null);

            for (const bubble of row) {
                let colIndex;

                if (expectedGap > 0) {
                    colIndex = Math.round((bubble.cx - row[0].cx) / expectedGap);
                } else {
                    colIndex = Math.round((bubble.cx - row[0].cx) / (bubble.radius * 2));
                }

                // Clamp within bounds
                colIndex = Math.max(0, Math.min(bubblesPerRow - 1, colIndex));

                // Only set if slot is empty
                if (newRow[colIndex] === null) {
                    newRow[colIndex] = bubble;
                } else {
                    // Optional: handle collision — e.g., if two bubbles land in same slot
                    // For now, you can skip or log a warning
                    console.warn('Column collision detected in row:', bubble, 'at column', colIndex);
                }
            }

            finalRows.push(newRow);
        }

        // if (numberOfExpectedRows != finalRows.length) {
        //     alert("Incorrect number of items has been detected, please take another photo and ensure your device has an adequate camera");
        //     return;
        // }

        return finalRows;
    }


    //Helper: Get the score
    function updateTotalScoreUI(score) {
        document.getElementById('totalScoreValue').textContent = score;
    }




    //Thing i need to fix
    function handleScore() {
        scoredBlocks = {
            mcq: [],
            Freeform: [],
            Blanks: []
        };

        // MCQ Scoring (columnResults is aligned with rawAnswerKey order)
        const mcqBlocks = rawAnswerKey; // e.g., {"mcq_block_1": {...}, "mcq_block_2": {...}}
        const mcqKeys = Object.keys(mcqBlocks);

        mcqKeys.forEach((blockId, index) => {
            const bubbles = columnResults[index];
            let score = 0;

            bubbles.forEach(row => {
                row.forEach(bubble => {
                    if (bubble && bubble.isCorrect) score++;
                });
            });

            const cleanedBubbles = bubbles.map(row =>
                row.map(bubble => ({
                    shaded: bubble.shaded,
                    isCorrect: bubble.isCorrect
                }))
            );

            scoredBlocks.mcq.push({
                id: blockId,
                score: score,
                bubbles: cleanedBubbles
            });
        });

        // Freeform and Blanks scoring (default 0 or handle separately if graded)

        omrSheetSnapshot.OMRSheet.Freeform.forEach(block => {
            scoredBlocks.Freeform.push({
                id: block.id,
                score: 0
            });
        });

        omrSheetSnapshot.OMRSheet.Blanks.forEach(block => {
            scoredBlocks.Blanks.push({
                id: block.id,
                score: 0
            });
        });


        calculateTotalScore()
        console.log("Scored Blocks:", scoredBlocks);
        bindShadedBubbles(scoredBlocks);
    }

    function calculateTotalScore() {
        let total = 0;
        // Sum MCQ scores
        if (scoredBlocks.mcq) {
            scoredBlocks.mcq.forEach(block => {
                total += block.score || 0;
            });
        }

        // Sum Freeform scores
        if (scoredBlocks.Freeform) {
            scoredBlocks.Freeform.forEach(field => {
                total += field.score || 0;
            });
        }

        // Sum Blanks scores
        if (scoredBlocks.Blanks) {
            scoredBlocks.Blanks.forEach(blank => {
                total += blank.score || 0;
            });
        }
        updateTotalScoreUI(total); // Optional: Update display if you created a UI element
        return total;
    }

    function bindShadedBubbles(scoredBlocks) {
        scoredBlocks.mcq.forEach(block => {
            const blockId = block.id;
            const bubbles = block.bubbles;

            bubbles.forEach((row, rowIndex) => {
                row.forEach((bubble, colIndex) => {
                    if (bubble && bubble.shaded) {
                        const checkboxId = `bubble_${blockId}_${rowIndex + 1}_${colIndex}`;
                        const checkbox = document.getElementById(checkboxId);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    }
                });
            });
        });
        addChangeListeners(scoredBlocks);
    }


    function addChangeListeners(scoredBlocks) {
        // MCQ Checkbox Updates
        scoredBlocks.mcq.forEach((block, blockIndex) => {
            const blockId = block.id;
            const bubbles = block.bubbles;

            bubbles.forEach((row, rowIndex) => {
                row.forEach((bubble, colIndex) => {
                    const checkboxId = `bubble_${blockId}_${rowIndex + 1}_${colIndex}`;
                    const checkbox = document.getElementById(checkboxId);

                    if (checkbox) {
                        checkbox.addEventListener('change', () => {
                            const shaded = checkbox.checked;
                            const questionNumber = rowIndex + 1; // 1-based
                            const answerKeyBlock = rawAnswerKey[blockId];
                            const correctAnswers = answerKeyBlock.answers?.[questionNumber] || [];

                            // Update shaded state 
                            scoredBlocks.mcq[blockIndex].bubbles[rowIndex][colIndex].shaded = shaded;

                            // MCQ Scoring (columnResults is aligned with rawAnswerKey order)

                            reevaluateScoring();
                        });
                    }
                });
            });
        });

        // Freeform Inputs
        scoredBlocks.Freeform.forEach((block, index) => {
            const input = document.querySelector(`input[name="freeform[${block.id}]"]`);
            if (input) {
                input.addEventListener('input', () => {
                    scoredBlocks.Freeform[index].score = parseFloat(input.value) || 0;
                    reevaluateScoring(scoredBlocks);
                });
            }
        });

        // Blanks Inputs
        scoredBlocks.Blanks.forEach((block, index) => {
            const input = document.querySelector(`input[name="blanks[${block.id}]"]`);
            if (input) {
                input.addEventListener('input', () => {
                    scoredBlocks.Blanks[index].score = parseFloat(input.value) || 0;
                    reevaluateScoring(scoredBlocks);
                });
            }
        });
    }


    function reevaluateScoring() {
        const mcqBlocks = rawAnswerKey;

        scoredBlocks.mcq.forEach((block, blockIndex) => {
            const blockId = block.id;
            const answerKeyBlock = mcqBlocks[blockId];

            let score = 0;

            block.bubbles.forEach((row, rowIndex) => {
                const questionNumber = rowIndex + 1; // 1-based
                const correctAnswers = answerKeyBlock.answers?.[questionNumber] || [];

                // Collect all shaded choices (convert to 1-based)
                const shadedChoices = row
                    .map((bubble, colIndex) => bubble.shaded ? colIndex + 1 : null)
                    .filter(val => val !== null);

                // Mark each bubble's isCorrect status for optional visual feedback
                row.forEach((bubble, colIndex) => {
                    const choiceIndex = colIndex + 1; // 1-based
                    bubble.isCorrect = bubble.shaded && shadedChoices.length === 1 && correctAnswers.includes(choiceIndex);
                });

                // Final scoring logic: only 1 shaded AND it must be correct
                const isRowCorrect = (
                    shadedChoices.length === 1 &&
                    correctAnswers.includes(shadedChoices[0])
                );

                if (isRowCorrect) {
                    score++;
                }
            });

            block.score = score;
        });

        calculateTotalScore();
    }


</script>