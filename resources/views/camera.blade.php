<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <title>OMR Scanner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/jquery-preload.js'])
    @vite(['resources/js/blade.js'])
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/favicon.svg">
    
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

        .preview-container {
            width: 100%;
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .preview-image {
            max-width: 100%;
            max-height: 90%;
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

        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            animation: spin 1s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div id="status">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
        <p class="text-lg">Loading OMR Scanner...</p>
    </div>



    <!-- Sidebar (shared across all sections that want to use it) -->
    <div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-40">
        <div class="p-4 border-b font-bold text-gray-700">Menu</div>
        <ul class="p-4 space-y-2">
            <li>
                <a class="backToUpload text-gray-700 hover:underline w-full text-left cursor-pointer">Take Photo</a>
            </li>
            <li>
                <a href={{route('dashboard')}}  class="text-gray-700 hover:underline w-full text-left">Dashboard</a>
            </li>
            <li>
                <button onclick="openModal('tutorialModal')" class="text-gray-700 hover:underline w-full text-left">Tutorial</button>
            </li>
            <li>
                <button onclick="openModal('settingsModal')" class="text-gray-700 hover:underline w-full text-left">Settings</button>
            </li>
        </ul>
    </div>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-30 hidden z-30"></div>

    <!-- Section 1: Upload Image -->
    <div id="uploadSection"
        class="section active py-10 px-6 flex flex-col items-center justify-center min-h-screen bg-gray-50">
        
        <!-- Menu Button (only in section 1) -->
        <button id="menuButton" class="absolute top-5 right-5 z-50 bg-white hover:bg-gray-800 text-gray-800 hover:text-white p-3 rounded-lg shadow-lg transition-all duration-200 flex items-center justify-center border border-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

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
        <a href="{{route('assessment.view', $assessment->id)}}">
            Back
        </a>
    </div>

    <!-- Section 2: Preview Image -->
    <div id="previewSection" class="section next bg-gray-100 flex flex-col min-h-screen">

        <!-- Header -->
        <div class="bg-white border-b border-gray-300 flex justify-between items-center z-10 shadow-sm flex-shrink-0"
            style="height: 10dvh; padding: 0 1.5rem;">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Preview Scan
            </h2>

            <button class="backToUpload text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </button>
        </div>

        <!-- Main Preview Area -->
        <div class="relative bg-gray-200 flex items-center justify-center overflow-hidden p-4"
            style="height: 80dvh; min-height: 80dvh;">
            <!-- Grid Background -->
            <div class="absolute inset-0 opacity-20"
                style="background-image: radial-gradient(#9ca3af 1px, transparent 1px); background-size: 20px 20px;">
            </div>

            <!-- Image Container -->
            <div class="relative w-full h-full flex items-center justify-center">
                <img id="imagePreview" class="max-w-full max-h-full object-contain shadow-2xl"
                    src="" alt="OMR Sheet Preview"
                    style="box-shadow: 0 0 40px rgba(0,0,0,0.2);">
            </div>

            <!-- Floating Rotation Controls -->
            <div class="absolute left-1/2 transform -translate-x-1/2 flex gap-4 bg-white/90 backdrop-blur-sm p-2 rounded-full border border-gray-300 shadow-lg"
                style="bottom: 12dvh;">
                <button id="rotateLeft"
                        class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-all"
                        title="Rotate Left">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </button>

                <div class="w-px bg-gray-300 my-1"></div>

                <button id="rotateRight"
                        class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-all"
                        title="Rotate Right">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="left-0 right-0 bg-white border-t border-gray-300 p-4 z-10 shadow-md"
            style="height: 10dvh;">
            <div class="max-w-4xl mx-auto flex justify-between items-center h-full">
                <div class="text-gray-600 text-sm hidden sm:block">
                    Ensure only the inside of the paper is clearly visible
                </div>

                <button id="processImage"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-lg font-bold shadow-md hover:shadow-blue-500/30 transition-all flex items-center justify-center gap-2 h-full">
                    <span>Process OMR Sheet</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Processing Modal -->
        <div id="processingModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm">
            <div class="text-center" style="margin-top: 20dvh;">
                <div class="relative w-20 h-20 mx-auto mb-6">
                    <div class="absolute inset-0 border-4 border-gray-300 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                    <svg class="absolute inset-0 w-8 h-8 m-auto text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 mb-2">Processing Scan</h3>
                <p id="progressText" class="text-gray-600">Analyzing bubbles and detecting answers...</p>
            </div>
        </div>

    </div>


    <!-- Section 3: Results -->
    <div id="resultsSection" class="section next !overflow-y-auto p-2">
        <!-- Modern Header -->
        <!-- Header -->
        <div class="sticky top-0 h-12 bg-white border-b border-gray-300 px-6 py-4 flex justify-between items-center z-10 shadow-sm flex-shrink-0">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Detected Answers
            </h2>

            <button class="backToUpload text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </button>
        </div>

    <!-- 🧩 Responsive layout wrapper -->
    <div class="flex flex-col md:flex-row gap-2 p-2">
        <!-- Enhanced Canvas Preview (1/3 on md, 1/2 on lg+) -->
        <div id="canvasWrapper" 
            class="md:w-1/2 lg:w-1/3 w-full flex justify-center sticky top-14 z-50 rounded-2xl overflow-hidden border-2 border-gray-200 bg-white shadow-xl hover:shadow-2xl transition-shadow duration-300"
            style="transform-origin: top; transition: all 0.3s ease; height: 35vh;">
            <div id="canvas-background" 
                class="rounded-xl absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-50 to-gray-100 -z-10 transition-all duration-300"></div>

            <canvas id="canvasOutput"
                    class="relative max-h-full max-w-full cursor-pointer rounded-xl"
                    style="transform-origin: top;"
                    title="Click to expand"></canvas>
            
            <!-- Preview Label -->
            <div class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white text-xs px-3 py-1 rounded-full flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>Preview</span>
            </div>
        </div>

        <!-- Detected Answers Form (2/3 on md, 1/2 on lg+, full width on mobile) -->
        <form method="POST" class="md:w-1/2 lg:w-2/3 w-full max-w-5xl mx-auto px-5 space-y-10 mb-16 overflow-auto">
                 @csrf 
                <!-- Y Offset Slider -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="flex justify-between text-sm font-medium text-blue-800 mb-2">
                        <span>Adjust Grid Vertical Offset</span>
                        <span id="gridOffsetYDisplay" class="font-bold">0</span>
                    </label>
                    <input id="gridOffsetYInput" type="range" min="-50" max="50" value="0" class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <p class="text-xs text-blue-600 mt-1">Move the slider to align the grid with the bubbles if they are slightly off.</p>
                </div>

                <!-- Undetected Bubbles Alert (Collapsible) -->
                <div id="undetectedBubblesContainer" class="my-6 border-2 border-red-300 bg-gradient-to-r from-red-50 to-orange-50 rounded-xl shadow-md" style="display: none;">
                    <button type="button" id="toggleUndetectedBtn" class="w-full p-5 flex items-start justify-between gap-3 hover:bg-red-100/50 transition-colors rounded-t-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-left">
                                <h3 class="font-bold text-red-800 text-base mb-1">⚠️ Undetected Answers</h3>
                                <p class="text-sm text-red-700">Click to view items that were not detected</p>
                            </div>
                        </div>
                        <svg id="toggleUndetectedIcon" class="w-5 h-5 text-red-600 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="undetectedBubblesList" class="px-5 pb-5 hidden">
                    </div>
                </div>
        
                <!-- Section Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-2">Fill in / Adjust Detected Answers</h1>
                    <p class="text-sm text-gray-600">Review the automatically detected answers below. You can adjust any incorrect detections by checking/unchecking the boxes.</p>
                </div>

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
                        @php $number = 1; @endphp
                        <div class="bg-white border border-gray-300 rounded-xl shadow-md mb-4">
                            <button type="button" data-toggle="{{ $sectionId }}"
                                class="w-full text-left px-6 py-5 flex justify-between items-center hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-200 rounded-t-2xl">
                                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    MCQ - {{ $sectionName }}
                                </h3>
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="{{ $sectionId }}" class=" px-6 pb-6 space-y-6">
                            @foreach($blocks as $block)
                                @php 
                                    $blockId = 'block_' . \Illuminate\Support\Str::slug($block['id'], '_'); 
                                    $friendlyId = preg_replace('/^mcq_block_/', '', $block['id']);
                                @endphp

                                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-blue-300 transition-colors duration-200">
                                    <!-- Toggle Button -->
                                    <button type="button" data-toggle="{{ $blockId }}"
                                        class="w-full text-left px-5 py-3.5 flex justify-between items-center bg-gradient-to-r from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 transition-all duration-200">
                                        <h4 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">{{ $friendlyId }}</span>
                                            Block {{ $friendlyId }}
                                        </h4>
                                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 transform rotate-0 group-[.open]:rotate-180"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Table Content -->
                                    <div id="{{ $blockId }}" class="hidden px-4 py-4 bg-white">
                                        <div class="flex gap-4 mb-4">
                                            <div class="px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                                                <span class="text-xs text-gray-600">Items:</span>
                                                <strong class="text-sm text-blue-700 ml-1">{{ $block['items'] }}</strong>
                                            </div>
                                            <div class="px-3 py-1.5 bg-purple-50 border border-purple-200 rounded-lg">
                                                <span class="text-xs text-gray-600">Choices:</span>
                                                <strong class="text-sm text-purple-700 ml-1">{{ $block['choices'] }}</strong>
                                            </div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full border-2 border-gray-200 rounded-lg overflow-hidden">
                                                <thead class="bg-gradient-to-r from-blue-50 to-purple-50">
                                                    <tr>
                                                        <th class="px-4 py-3 border border-gray-200 text-left text-sm font-bold text-gray-800 bg-gradient-to-r from-blue-100 to-blue-50">#</th>
                                                        @for($c = 0; $c < $block['choices']; $c++)
                                                            <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800">
                                                                {{ chr(65 + $c) }}
                                                            </th>
                                                        @endfor
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for($i = 1; $i <= $block['items']; $i++, $number++)
                                                        <tr class="{{ $i % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition-colors duration-150">
                                                            <td class="px-4 py-3 border border-gray-200 text-sm font-semibold text-gray-800">{{ $number }}</td>
                                                            @for($c = 0; $c < $block['choices']; $c++)
                                                                <td class="px-4 py-3 border border-gray-200 text-center">
                                                                    <input type="checkbox" id="bubble_{{ $block['id'] }}_{{ $i }}_{{ $c }}"
                                                                        name="mcq[{{ $block['id'] }}][{{ $i }}]" value="{{ $c }}"
                                                                        class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all duration-150 hover:border-blue-400" />
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>
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

                            <div id="blanks_section" class="px-6 pb-6 space-y-4">
                                @foreach($omr['Blanks'] as $blank)
                                    <div class="grid grid-cols-[1fr_auto] items-center gap-4">
                                        <!-- Section Label -->
                                        <div class="text-sm font-medium text-gray-700">
                                            {{ $blank['section'] }}
                                        </div>

                                        <!-- Input + Points -->
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="number" 
                                                name="blanks[{{ $blank['id'] }}]"
                                                class="w-20 border border-gray-300 bg-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-right"
                                                value="0" 
                                                min="0" 
                                                required>
                                            <span class="text-sm text-gray-600">pts</span>
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

                            <div id="freeform_section" class="px-6 pb-6 space-y-4">
                                @foreach($omr['Freeform'] as $field)
                                    <div class="grid grid-cols-[1fr_auto] items-center gap-4 border-b border-gray-200 pb-3">
                                        <!-- Instruction -->
                                        <div class="text-sm font-medium text-gray-700">
                                            {{ $field['Instruction'] }}
                                        </div>

                                        <!-- Input + Points -->
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="number" 
                                                name="freeform[{{ $field['id'] }}]"
                                                class="w-20 border border-gray-300 bg-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-right"
                                                value="0"
                                                min="0" 
                                                required>
                                            <span class="text-sm text-gray-600">pts</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Total Score -->
                <div
                class="w-full max-w-md mx-auto bg-gradient-to-r from-blue-100 to-blue-200 rounded-2xl py-6 mt-6 shadow-lg px-6 flex items-center justify-between">
                
                    <!-- Left side: Icon + Label -->
                    <div class="flex items-center space-x-4">
                        <!-- Trophy Icon -->
                        <div class="p-3 bg-blue-600 rounded-full shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="h-8 w-8 text-white" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor" 
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                d="M8 21h8m-4-4v4m-6-9a6 6 0 0012 0V5H6v7zM4 5h16" />
                        </svg>
                        </div>
                        <!-- Label -->
                        <span class="text-xl font-bold text-gray-800">Total Score</span>
                    </div>

                    <!-- Right side: Score Value -->
                    <span id="totalScoreValue" class="text-3xl font-extrabold text-blue-700">0</span>
                </div>

                @if(!empty($assessment->person_dictionary_snapshot))
                    @php
                        $personData = $assessment->person_dictionary_snapshot['data'] ?? [];
                    @endphp
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl shadow-lg mb-6 p-6">
                        <!-- Header with Icon -->
                        <div class="flex items-center space-x-3 mb-5">
                            <div class="p-3 bg-blue-600 text-white rounded-full shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="h-6 w-6" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor" 
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M5.121 17.804A7 7 0 1118.364 4.56M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Respondent Data</h3>
                        </div>

                        <!-- Form -->
                        <div class="space-y-4">
                            <!-- Name Input -->
                            <div>
                                <label for="personNameInput" class="block text-sm font-medium text-gray-700 mb-1">
                                    Name
                                </label>
                                <input 
                                    list="personSuggestions" 
                                    id="personNameInput" 
                                    name="personName"
                                    class="w-full border border-blue-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                                    placeholder="Start typing a name..." 
                                    autocomplete="off">
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

                <!-- Final Action Button -->
                <div class="flex justify-end grid grid-cols-2">
                    <button type="button" class="backToUpload action-btn secondary-btn">Back</button>
                    <button type="button" id="completeScan" onclick="openModal('confirmModal')"
                        class="bg-blue-600 hover:bg-blue-700 transition text-white font-semibold px-8 py-3 rounded-lg shadow-lg flex items-center space-x-2">
                        <!-- Save Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5 text-white" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor" 
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Complete & Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tutorial Modal -->
    <div id="tutorialModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">How to Use EasyAssess</h2>

            <ol class="list-decimal list-inside text-gray-700 space-y-2 mb-6 text-sm">
                <li>Take a clear photo of your OMR sheet. Make sure the multiple-choice (MCQ) section is visible.</li>
                <li>(Optional) Enter the name of the student or respondent.</li>
                <li>Review the detected bubbles and answers. You can manually adjust scores if needed.</li>
                <li>Fill in scores for essay and blank-type questions.</li>
                <li>Click <strong>Submit</strong> once everything is complete.</li>
            </ol>

            <div class="mb-4">
                <h3 class="text-md font-semibold mb-2">🔍 Bubble Color Meaning</h3>
                <div class="space-y-2 text-sm">

                    <!-- ✅ Correct (Green Check) -->
                    <div class="flex items-center space-x-3">
                        <div class="relative w-5 h-5">
                            <div class="absolute inset-0 rounded-full border-2 border-green-600"></div>
                            <svg class="absolute w-5 h-5 text-green-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 10l3 3 6-6" />
                            </svg>
                        </div>
                        <span>Correct bubble selected</span>
                    </div>

                    <!-- 🟢 Expected but not shaded (Green Circle) -->
                    <div class="flex items-center space-x-3">
                        <div class="w-5 h-5 rounded-full border-2 border-green-500"></div>
                        <span>Expected answer, but not shaded</span>
                    </div>

                    <!-- ❌ Wrong Shaded (Red X) -->
                    <div class="flex items-center space-x-3">
                        <div class="relative w-5 h-5">
                            <div class="absolute inset-0 rounded-full border-2 border-red-600"></div>
                            <svg class="absolute w-5 h-5 text-red-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 6l8 8M14 6l-8 8" />
                            </svg>
                        </div>
                        <span>Wrong bubble shaded</span>
                    </div>

                    <!-- ❓ Ambiguous (Orange Question Mark) -->
                    <div class="flex items-center space-x-3">
                        <div class="relative w-5 h-5">
                            <div class="absolute inset-0 rounded-full bg-orange-400 border border-orange-500"></div>
                            <span class="absolute left-1/2 top-1/2 text-white text-xs font-bold transform -translate-x-1/2 -translate-y-1/2">?</span>
                        </div>
                        <span>Multiple bubbles shaded (ambiguous)</span>
                    </div>

                    <!-- 🔵 Empty Bubble (Blue) -->
                    <div class="flex items-center space-x-3">
                        <div class="w-5 h-5 rounded-full border-2 border-blue-500"></div>
                        <span>Empty bubble (not expected to be shaded)</span>
                    </div>

                </div>
            </div>

            <div class="mt-6 text-right">
                <button onclick="closeModal('tutorialModal')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
            </div>
        </div>
    </div>


    <!-- Settings Modal -->
    <div id="settingsModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Settings</h2>

            <!-- Shade Threshold Control -->
            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-1">
                    Shade Fill Threshold
                    <span class="inline-block relative group ml-1">
                    <span class="bg-blue-600 text-white text-xs font-bold w-4 h-4 inline-flex items-center justify-center rounded-full cursor-pointer">?</span>
                        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 w-64 text-xs text-white bg-gray-800 p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                          Sets how much must to consider a cell to be shaded. Set to higher to reduce false detection, set lower if shades aren't being registered
                        </div>
                    </span>
                </label>
                <input id="fillRatioInput" type="range" min="1" max="100" value="30" class="w-full">
                <p class="text-sm text-gray-600 mt-1">Current: <span id="fillRatioDisplay">30</span>%</p>
            </div>



            <div class="mt-6 text-right">
                <button onclick="closeModal('settingsModal')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
            </div>
        </div>
    </div>

    <div id="errorModal" style="
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;">
        <div style="
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            font-family: sans-serif;
            max-width: 400px;
        ">
            <p style="margin-bottom: 20px;">Not enough OMR boxes detected, possible answer mismatch may occur.
                <br>Please check the image and try again and ensure only the inside of the paper is clearly visible</p>
            <button onclick="closeModal('errorModal'); fullReset(); showSection('uploadSection');" style="
                padding: 8px 20px;
                background: #dc2626;
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
            ">Close</button>
        </div>
    </div>

    <div id="confirmModal" class=" inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden"
        style="position:fixed">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Are you sure?</h2>
            <p class="text-sm text-gray-600 mb-6">Please confirm that you have reviewed all scores before submitting.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal('confirmModal')"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md">Cancel</button>
                <button id="confirmSubmit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Save</button>
            </div>
        </div>
    </div>

    <div id="processingToast" class="fixed bottom-5 right-5 z-50 bg-gray-800 text-white text-sm px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-500 pointer-events-none">
        Time taken for this recording: <span id="processingTimeValue">0</span> seconds
    </div>
    <script async src="/js/opencv.js" onload="onOpenCvReady()" type="text/javascript"></script>
</body>

</html>

{{-- Obfuscated Scripts --}}
<script>
    window.cameraConfig = {
        code: @json($code ?? null),
        recordRoute: "{{ route('assessment.record.create', ['id' => $assessment->id]) }}",
        answerKey: @json($assessment->answer_key),
        omrSheetSnapshot: @json($assessment->omr_sheet_snapshot)
    };
</script>
<script src="{{ asset('js/obfuscated/camera_scripts.js') }}"></script>