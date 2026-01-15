<!DOCTYPE html>
<html>

<head>
    @vite(['resources/js/jquery-preload.js'])
    @vite(['resources/js/app.js', 'resources/css/omr.css'])
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/favicon.svg">

    <style>
        div {
            box-sizing: border-box;
        }

        .selected-element {
            border: 2px solid #3B82F6 !important;
            /* Tailwind's blue-500 */
            border-radius: 4px;
        }

        /* Hide scrollbars for all browsers */
        #canvas-container ::-webkit-scrollbar {
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }

        /* Firefox */
        #canvas-container {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
    </style>
    
    <title>Answer Sheet Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 p-0 h-full font-sans">
    <div class="mx-auto bg-gray-600 flex flex-col md:flex-row items-start w-screen h-screen overflow-visible">
        <div class="h-full bg-gray-600 w-full md:w-3/4 flex justify-center overflow-scroll shadow-lg select-none relative"
            style="transform-origin: top left; transform: scale(1);" id="canvas-container">

            <div id="canvas" class="bg-white flex w-full h-full leading-tight min-h-50 min-w-50" style="transform-origin: top left; transform: scale(1); margin-top: 100px; margin-bottom: 100px; margin-left: 50px;
                   width: 2000px; height: 1500px; /* Make it bigger */
                   min-width: 2000px; min-height: 1500px; /* Ensure it doesn't shrink */">
                <div class="grid relative" id="grid"></div>
            </div>
        </div>

        <!-- Sidebar Toggle Button (visible on small screens) -->
        <button id="sidebarToggle"
            class="bg-white md:hidden fixed top-4 right-4 bg-[#5b9bd5] text-white p-3 rounded-full shadow-lg z-50">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed top-0 right-0 h-full w-full md:w-80 bg-white
                    p-6 md:p-8 shadow-2xl rounded-l-3xl border-l-8 border-[#5b9bd5] overflow-y-auto select-none
                    transform translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50">

            <!-- Close button for mobile -->
            <div class="flex justify-end md:hidden mb-4">
                <button id="sidebarCloseBtn" class="p-2 rounded-full hover:bg-gray-200 ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Section: Paper Size -->
            <div class="mb-6">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2e75b6] mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c1.656 0 3-1.344 3-3S13.656 2 12 2 9 3.344 9 5s1.344 3 3 3zM21 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/>
                    </svg>
                    <span class="text-sm font-bold text-[#2e75b6]">Paper Size</span>
                </div>
                <select id="paper-size-dropdown"
                        class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#5b9bd5] text-sm">
                    <option value="Letter" data-width="8.5" data-height="11">Letter (8.5" x 11")</option>
                    <option value="Legal" data-width="8.5" data-height="14">Legal (8.5" x 14")</option>
                    <option value="A4" data-width="8.3" data-height="11.7">A4 (8.3" x 11.7")</option>
                </select>
            </div>

            <!-- Section: Cutout -->
            <div class="mb-6">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2e75b6] mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M3 14h18M3 18h18"/>
                    </svg>
                    <span class="text-sm font-bold text-[#2e75b6]">Cutout</span>
                </div>
                <div id="cutout-controls" class="grid grid-cols-2 gap-2">
                    <button class="cutout-btn bg-[#5b9bd5] hover:bg-[#4472c4] text-white font-semibold py-2 px-3 rounded shadow text-sm"
                            data-vertical-cuts="0" data-horizontal-cuts="0">Full</button>
                    <button class="cutout-btn bg-white hover:bg-[#dde6f7] text-[#2e75b6] font-semibold py-2 px-3 rounded shadow border border-[#b4c6e7] text-sm"
                            data-vertical-cuts="0" data-horizontal-cuts="1">1/2 Cross</button>
                    <button class="cutout-btn bg-white hover:bg-[#dde6f7] text-[#2e75b6] font-semibold py-2 px-3 rounded shadow border border-[#b4c6e7] text-sm"
                            data-vertical-cuts="1" data-horizontal-cuts="0">1/2 Len</button>
                    <button class="cutout-btn bg-white hover:bg-[#dde6f7] text-[#2e75b6] font-semibold py-2 px-3 rounded shadow border border-[#b4c6e7] text-sm"
                            data-vertical-cuts="1" data-horizontal-cuts="1">1/4</button>
                </div>
            </div>

            <!-- Section: Grid Layout -->
            <div class="mb-6">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2e75b6] mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="text-sm font-bold text-[#2e75b6]">Grid Layout</span>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 w-1/2">
                        <label for="rows" class="text-xs font-medium w-12">Rows:</label>
                        <input type="number" id="rows" min="1" value="22"
                            class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-[#5b9bd5]">
                    </div>
                    <div class="flex items-center gap-2 w-1/2">
                        <label for="cols" class="text-xs font-medium w-12">Cols:</label>
                        <input type="number" id="cols" min="1" value="20"
                            class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-[#5b9bd5]">
                    </div>
                </div>
            </div>

            <!-- Section: Element Controls -->
            <div id="ElementControls" class="mb-6 space-y-3"></div>

            <!-- Section: Actions -->
            <div class="mt-auto space-y-3">
                <button id="downloadPdfBtn"
                        class="w-full bg-gradient-to-r from-[#5b9bd5] to-[#4472c4] hover:from-[#4472c4] hover:to-[#5b9bd5]
                            text-white font-bold py-3 px-4 rounded-xl shadow-lg text-lg tracking-wide transition duration-150
                            outline-none focus:ring-2 focus:ring-[#5b9bd5]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M4 12l8 8 8-8M12 4v12"/>
                    </svg>
                    Download PDF
                </button>

                <div class="relative w-full group">
                    <button id="saveButton"
                            class="w-full bg-gradient-to-r from-[#5b9bd5] to-[#4472c4] hover:from-[#4472c4] hover:to-[#5b9bd5]
                                text-white font-bold py-3 px-4 rounded-xl shadow-lg text-lg tracking-wide transition duration-150
                                outline-none focus:ring-2 focus:ring-[#5b9bd5]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                        Finalize
                    </button>

                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-black text-white text-xs rounded-md
                                whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        Download first
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-black"></div>
                    </div>
                </div>
            </div>
        </aside>


        <ul id="Sections">
        </ul>

        <!-- Android Alert Modal -->
        <div id="androidAlertModal"
            class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[100]">
            <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-sm mx-4">
                <h2 class="text-2xl font-bold text-red-600 mb-4">Device Not Supported</h2>
                <p class="text-gray-700 mb-6">This application requires a PC for optimal performance and full
                    functionality.</p>
                <p class="text-gray-700">Please access this page from a desktop or laptop computer.</p>
            </div>
        </div>

        <!-- Save Modal -->
        <div id="saveModal"
            class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[100]">
            <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-md mx-4 space-y-6">
                <h2 class="text-2xl font-bold text-[#2e75b6] mb-4">Save Your Design</h2>
                <form id="saveForm" class="space-y-4">
                    <div>
                        <label for="saveTitle" class="block text-left text-gray-700 font-semibold mb-2">Title:</label>
                        <input type="text" id="saveTitle"
                            class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b9bd5]"
                            placeholder="Enter a title for your design" required>
                    </div>
                    <div>
                        <label for="saveDescription"
                            class="block text-left text-gray-700 font-semibold mb-2">Description (Optional):</label>
                        <textarea id="saveDescription" rows="4"
                            class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b9bd5]"
                            placeholder="Briefly describe your design"></textarea>
                    </div>
                    <span> NOTE: Test your design first before using it for your assessments</span>
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" id="cancelSaveBtn"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors duration-150">
                            Cancel
                        </button>
                        <button type="submit" id="confirmSaveBtn"
                            class="bg-gradient-to-r from-[#5b9bd5] to-[#4472c4] hover:from-[#4472c4] hover:to-[#5b9bd5] text-white font-bold py-2 px-4 rounded-lg shadow transition-all duration-150">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generic Alert Modal -->
        <div id="genericAlertModal"
            class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[101]">
            <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-sm mx-4 space-y-6">
                <h2 id="alertModalTitle" class="text-2xl font-bold text-gray-800 mb-4"></h2>
                <p id="alertModalMessage" class="text-gray-700 mb-6"></p>
                <button id="alertModalCloseBtn"
                    class="bg-[#5b9bd5] hover:bg-[#4472c4] text-white font-bold py-2 px-4 rounded-lg shadow transition-all duration-150">
                    OK
                </button>
            </div>
        </div>
</body>

</html>

{{-- Obfuscated JavaScript for OMR Create --}}
<script src="{{ asset('js/obfuscated/omr_create_scripts.js') }}"></script>