<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">


    <title>OMR Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, html {
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
    <div id="uploadSection" class="section active">
        <div class="header">
            <h1 class="text-2xl font-bold">OMR Sheet Scanner</h1>
        </div>
        
        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="upload-options">
                <input type="file" id="imageUpload" accept="image/*" capture="environment" class="hidden" />
                
                <label for="imageUpload" class="block w-full mb-4">
                    <div class="action-btn primary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Take Photo
                    </div>
                </label>
                
                <p class="text-center text-gray-500 mt-8">
                    Take a clear photo of your OMR sheet with good lighting
                </p>
            </div>
        </div>
    </div>

    <!-- Section 2: Preview Image -->
    <div id="previewSection" class="section next">
        <div class="header">
            <h2 class="text-xl font-bold">Preview OMR Sheet</h2>
        </div>
        
        <div class="preview-container">
            <img id="imagePreview" class="preview-image" src="" alt="OMR Sheet Preview">
        </div>
        
        <div class="btn-container">
            <button id="backToUpload" class="action-btn secondary-btn">
                Back
            </button>
            <button id="processImage" class="action-btn primary-btn">
                Process Image
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

        <!-- Detected Answers Form -->
        <form method="POST" action="" class="max-w-6xl mx-auto space-y-10">
            @csrf

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

                {{-- MCQ Sections Grouped --}}
                @foreach($sections as $sectionName => $blocks)
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ $sectionName }}</h3>

                        @foreach($blocks as $block)
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Block: {{ $block['id'] }}</h4>
                                <p class="text-sm text-gray-500 mb-3">
                                    Items: <strong>{{ $block['items'] }}</strong> |
                                    Choices: <strong>{{ $block['choices'] }}</strong>
                                </p>

                                <div class="space-y-5">
                                    @for($i = 1; $i <= $block['items']; $i++)
                                        <div class="bg-gray-50 p-4 rounded border">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Item {{ $i }}</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                                @for($c = 0; $c < $block['choices']; $c++)
                                                    <label class="flex items-center space-x-2">
                                                    <input
                                                        type="checkbox"
                                                        id="bubble_{{ $block['id'] }}_{{ $i }}_{{ $c }}"
                                                        name="mcq[{{ $block['id'] }}][{{ $i }}][]"
                                                        value="{{ $c }}"
                                                        class="w-5 h-5 text-blue-600 form-checkbox"
                                                    />
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
                @endforeach

                {{-- Blanks --}}
                @if(!empty($omr['Blanks']))
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Blanks</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($omr['Blanks'] as $blank)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $blank['section'] }}</label>
                                    <input
                                        type="number"
                                        name="blanks[{{ $blank['id'] }}]"
                                        class="w-full border border-gray-300 bg-white rounded px-4 py-2 text-sm"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Freeform --}}
                @if(!empty($omr['Freeform']))
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Freeform</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($omr['Freeform'] as $field)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['Instruction'] }}</label>
                                    <input
                                        type="number"
                                        name="freeform[{{ $field['id'] }}]"
                                        class="w-full border border-gray-300 bg-white rounded px-4 py-2 text-sm"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <!-- Final Action Button -->
            <div class="flex justify-end">
                <button type="button" id="completeScan" class="bg-green-600 hover:bg-green-700 transition text-white font-semibold px-8 py-3 rounded-lg shadow">
                    Complete & Save
                </button>
            </div>
        </form>
    </div>



    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script async src="https://cdn.jsdelivr.net/npm/opencv.js" onload="onOpenCvReady()" type="text/javascript"></script>
    
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

            // Handle image upload
            document.getElementById('imageUpload').addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    uploadedImage = e.target.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        document.getElementById('imagePreview').src = event.target.result;
                        showSection('previewSection');
                    };
                    
                    reader.readAsDataURL(uploadedImage);
                }
            });
            
            // Back to upload button
            document.getElementById('backToUpload').addEventListener('click', function() {
                showSection('uploadSection');
            });
            
            // Process image button
            document.getElementById('processImage').addEventListener('click', function () {
                if (uploadedImage) {
                    const img = new Image();
                    img.onload = function () {
                        const canvas = document.getElementById('canvasOutput');
                        const ctx = canvas.getContext('2d');

                        // Calculate dimensions to fit screen while maintaining aspect ratio
                        const maxWidth = window.innerWidth;
                        const maxHeight = window.innerHeight - 140;

                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            const ratio = maxWidth / width;
                            width = maxWidth;
                            height = height * ratio;
                        }

                        if (height > maxHeight) {
                            const ratio = maxHeight / height;
                            height = maxHeight;
                            width = width * ratio;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        // Run image processing
                        const result = processImage();

                        if (result === 1) {
                            // Reset the file input
                            document.getElementById('imageUpload').value = '';
                            uploadedImage = null;

                            // Clear the preview and canvas
                            document.getElementById('imagePreview').src = '';
                            ctx.clearRect(0, 0, canvas.width, canvas.height);

                            // Return to upload section
                            showSection('uploadSection');
                        } else {
                            // Stay on results section
                            showSection('resultsSection');
                        }
                    };
                    img.src = URL.createObjectURL(uploadedImage);
                }
            });

            
            // Complete scan button
            document.getElementById('completeScan').addEventListener('click', function() {
                // Reset the file input
                document.getElementById('imageUpload').value = '';
                uploadedImage = null;
                
                // Clear the preview and canvas
                document.getElementById('imagePreview').src = '';
                const canvas = document.getElementById('canvasOutput');
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                columnResults = [];     //Answers per box
                scoreResults = [];      //Scores per box
                totalScore;             //Total Score

                // Return to upload section
                showSection('uploadSection');
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (currentSection === 'results' && uploadedImage) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.getElementById('canvasOutput');
                        const ctx = canvas.getContext('2d');
                        
                        const maxWidth = window.innerWidth;
                        const maxHeight = window.innerHeight - 140;
                        
                        let width = img.width;
                        let height = img.height;
                        
                        if (width > maxWidth) {
                            const ratio = maxWidth / width;
                            width = maxWidth;
                            height = height * ratio;
                        }
                        
                        if (height > maxHeight) {
                            const ratio = maxHeight / height;
                            height = maxHeight;
                            width = width * ratio;
                        }
                        
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);
                    };
                    img.src = URL.createObjectURL(uploadedImage);
                }
            });

            

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', reevaluateScoring);
        });

        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', reevaluateScoring);
        });
    });
    </script>
</body>
</html>


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


    //Main
    function processImage() {
        let imgElement = document.getElementById('imagePreview');
        let startTime = performance.now();
        // Image prep
        let inpt = cv.imread(imgElement);
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
        let sortedContours = sortContoursByPosition(filteredContours, 30); //Sorted left to right then horizontally

        if (sortedContours.size() < numberOfOmrBoxes) {

            alert("Not enough OMR boxes detected. Please check the image and try again.");
            return 1;
        }

        // Visualize corners
        drawContourCorners(sortedContours, contoursImg, new cv.Scalar(0, 255, 0), numberOfOmrBoxes);
        handleCircles(contoursImg, sortedContours, numberOfOmrBoxes);

        // Display and log
        cv.imshow('canvasOutput', contoursImg);
        console.log(`Processing Time: ${performance.now() - startTime} ms`);

        // Cleanup
        inpt.delete(); preProssedImg.delete(); greyImg.delete(); gaussianBlurImg.delete();
        cannyImg.delete(); contoursImg.delete(); contours.delete(); hierarchy.delete();

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

    function handleScore() {
        const scoredBlocks = {
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
        omrSheetSnapshot.Freeform.forEach(block => {
            scoredBlocks.Freeform.push({
                id: block.id,
                score: 0
            });
        });

        omrSheetSnapshot.Blanks.forEach(block => {
            scoredBlocks.Blanks.push({
                id: block.id,
                score: 0
            });
        });

        console.log("Scored Blocks:", scoredBlocks);
        bindShadedBubbles(scoredBlocks);
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
    }

    function reevaluateScoring() {
        const newScoredBlocks = {
            mcq: scoredBlocks.mcq, // keep MCQ as-is (already scored)
            Freeform: [],
            Blanks: []
        };

        // Reevaluating Freeform
        document.querySelectorAll('input[name^="freeform["]').forEach(input => {
            const idMatch = input.name.match(/^freeform\[(.+)\]$/);
            if (idMatch) {
                const id = idMatch[1];
                const score = parseFloat(input.value) || 0;

                newScoredBlocks.Freeform.push({
                    id,
                    score
                });
            }
        });

        // Reevaluating Blanks
        document.querySelectorAll('input[name^="blanks["]').forEach(input => {
            const idMatch = input.name.match(/^blanks\[(.+)\]$/);
            if (idMatch) {
                const id = idMatch[1];
                const score = parseFloat(input.value) || 0;

                // Get section label (assumes label is just before the input)
                const label = input.closest('div').querySelector('label');
                const section = label ? label.textContent.trim() : '';

                newScoredBlocks.Blanks.push({
                    id,
                    section,
                    score
                });
            }
        });

        // Update global scoredBlocks reference
        scoredBlocks = newScoredBlocks;

        console.log("Updated scoredBlocks:", scoredBlocks);
    }

</script>