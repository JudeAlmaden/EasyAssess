<!DOCTYPE html>
<html>

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="icon" href="Favicon.png" type="image/png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
    </style>
    <title>Answer Sheet Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 p-0 h-screen font-sans">
<div class="pdf-grid-wrapper p-0" style="display: grid; width: 807.5px; height: 1045px; position: relative; padding: 0px; grid-template-rows: repeat(2, 1fr); grid-template-columns: repeat(2, 1fr); box-sizing: border-box;"><div id="canvas" class="bg-white flex w-full h-full leading-tight min-h-50 min-w-50" style="transform-origin: left top; transform: scale(1); margin: 0px 0px 0px -5px; width: 100%; height: 100%; min-width: 403.75px; min-height: 522.5px; padding: 22.5px; box-sizing: border-box; display: block; position: relative; top: 0px; border: 1px dashed rgba(0, 0, 0, 0.1);">
                <!-- Corner black boxes -->

                <div class="grid relative" id="grid" style="display: grid; grid-template-rows: repeat(11, 1fr); grid-template-columns: repeat(4, 1fr); gap: 5px; width: 100%; height: 100%;"><div class="relative rounded bg-white border border-solid border-1 border-gray-400 text-center font-bold grid-item " style="width: 358.75px; height: 477.5px; left: 0px; top: 0px; position: absolute; box-sizing: border-box; user-select: none; transition: 0.1s ease-out; cursor: nwse-resize;"><div class="placeholder-text text-sm text-gray-600 p-2 select-none pointer-events-auto w-full overflow-hidden whitespace-wrap text-ellipsis h-full">
                <span class="font-semibold">Instructions:</span> Click to add a component, click and hold to move, drag the bottom-right to resize.
            </div>

            <!-- Bottom-right reverse L -->
            <div class="absolute bottom-0 right-0 w-5 h-5 resize-icon pointer-events-none">
                <!-- vertical line -->
                <div class="absolute bottom-0 right-0 w-[2px] h-full bg-gray-800 rounded-r"></div>
                <!-- horizontal line -->
                <div class="absolute bottom-0 right-0 w-full h-[2px] bg-gray-800 rounded-b"></div>
            </div></div></div>
            <div class="marker" id="marker-1" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 0px;"></div><div class="marker" id="marker-2" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 381.75px;"></div><div class="marker" id="marker-3" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 0px;"></div><div class="marker" id="marker-4" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 381.75px;"></div></div><div id="canvas" class="bg-white flex w-full h-full leading-tight min-h-50 min-w-50" style="transform-origin: left top; transform: scale(1); margin: 0px 0px 0px -5px; width: 100%; height: 100%; min-width: 403.75px; min-height: 522.5px; padding: 22.5px; box-sizing: border-box; display: block; position: relative; top: 0px; border: 1px dashed rgba(0, 0, 0, 0.1);">
                <!-- Corner black boxes -->

                <div class="grid relative" id="grid" style="display: grid; grid-template-rows: repeat(11, 1fr); grid-template-columns: repeat(4, 1fr); gap: 5px; width: 100%; height: 100%;"><div class="relative rounded bg-white border border-solid border-1 border-gray-400 text-center font-bold grid-item " style="width: 358.75px; height: 477.5px; left: 0px; top: 0px; position: absolute; box-sizing: border-box; user-select: none; transition: 0.1s ease-out; cursor: nwse-resize;"><div class="placeholder-text text-sm text-gray-600 p-2 select-none pointer-events-auto w-full overflow-hidden whitespace-wrap text-ellipsis h-full">
                <span class="font-semibold">Instructions:</span> Click to add a component, click and hold to move, drag the bottom-right to resize.
            </div>

            <!-- Bottom-right reverse L -->
            <div class="absolute bottom-0 right-0 w-5 h-5 resize-icon pointer-events-none">
                <!-- vertical line -->
                <div class="absolute bottom-0 right-0 w-[2px] h-full bg-gray-800 rounded-r"></div>
                <!-- horizontal line -->
                <div class="absolute bottom-0 right-0 w-full h-[2px] bg-gray-800 rounded-b"></div>
            </div></div></div>
            <div class="marker" id="marker-1" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 0px;"></div><div class="marker" id="marker-2" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 381.75px;"></div><div class="marker" id="marker-3" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 0px;"></div><div class="marker" id="marker-4" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 381.75px;"></div></div><div id="canvas" class="bg-white flex w-full h-full leading-tight min-h-50 min-w-50" style="transform-origin: left top; transform: scale(1); margin: 0px 0px 0px -5px; width: 100%; height: 100%; min-width: 403.75px; min-height: 522.5px; padding: 22.5px; box-sizing: border-box; display: block; position: relative; top: 0px; border: 1px dashed rgba(0, 0, 0, 0.1);">
                <!-- Corner black boxes -->

                <div class="grid relative" id="grid" style="display: grid; grid-template-rows: repeat(11, 1fr); grid-template-columns: repeat(4, 1fr); gap: 5px; width: 100%; height: 100%;"><div class="relative rounded bg-white border border-solid border-1 border-gray-400 text-center font-bold grid-item " style="width: 358.75px; height: 477.5px; left: 0px; top: 0px; position: absolute; box-sizing: border-box; user-select: none; transition: 0.1s ease-out; cursor: nwse-resize;"><div class="placeholder-text text-sm text-gray-600 p-2 select-none pointer-events-auto w-full overflow-hidden whitespace-wrap text-ellipsis h-full">
                <span class="font-semibold">Instructions:</span> Click to add a component, click and hold to move, drag the bottom-right to resize.
            </div>

            <!-- Bottom-right reverse L -->
            <div class="absolute bottom-0 right-0 w-5 h-5 resize-icon pointer-events-none">
                <!-- vertical line -->
                <div class="absolute bottom-0 right-0 w-[2px] h-full bg-gray-800 rounded-r"></div>
                <!-- horizontal line -->
                <div class="absolute bottom-0 right-0 w-full h-[2px] bg-gray-800 rounded-b"></div>
            </div></div></div>
            <div class="marker" id="marker-1" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 0px;"></div><div class="marker" id="marker-2" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 381.75px;"></div><div class="marker" id="marker-3" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 0px;"></div><div class="marker" id="marker-4" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 381.75px;"></div></div><div id="canvas" class="bg-white flex w-full h-full leading-tight min-h-50 min-w-50" style="transform-origin: left top; transform: scale(1); margin: 0px 0px 0px -5px; width: 100%; height: 100%; min-width: 403.75px; min-height: 522.5px; padding: 22.5px; box-sizing: border-box; display: block; position: relative; top: 0px; border: 1px dashed rgba(0, 0, 0, 0.1);">
                <!-- Corner black boxes -->

                <div class="grid relative" id="grid" style="display: grid; grid-template-rows: repeat(11, 1fr); grid-template-columns: repeat(4, 1fr); gap: 5px; width: 100%; height: 100%;"><div class="relative rounded bg-white border border-solid border-1 border-gray-400 text-center font-bold grid-item " style="width: 358.75px; height: 477.5px; left: 0px; top: 0px; position: absolute; box-sizing: border-box; user-select: none; transition: 0.1s ease-out; cursor: nwse-resize;"><div class="placeholder-text text-sm text-gray-600 p-2 select-none pointer-events-auto w-full overflow-hidden whitespace-wrap text-ellipsis h-full">
                <span class="font-semibold">Instructions:</span> Click to add a component, click and hold to move, drag the bottom-right to resize.
            </div>

            <!-- Bottom-right reverse L -->
            <div class="absolute bottom-0 right-0 w-5 h-5 resize-icon pointer-events-none">
                <!-- vertical line -->
                <div class="absolute bottom-0 right-0 w-[2px] h-full bg-gray-800 rounded-r"></div>
                <!-- horizontal line -->
                <div class="absolute bottom-0 right-0 w-full h-[2px] bg-gray-800 rounded-b"></div>
            </div></div></div>
            <div class="marker" id="marker-1" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 0px;"></div><div class="marker" id="marker-2" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 0px; left: 381.75px;"></div><div class="marker" id="marker-3" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 0px;"></div><div class="marker" id="marker-4" style="position: absolute; width: 22px; height: 22px; background: black; z-index: 10; top: 500.5px; left: 381.75px;"></div></div></div>
   <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</body>

</html>