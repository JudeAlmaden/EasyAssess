@php
    $questionNumber = 1;
@endphp

@foreach($mcqSections as $sectionName => $blocks)
    @php $sectionId = 'section_' . \Illuminate\Support\Str::slug($sectionName, '_'); @endphp
    <div class="mb-4 rounded-xl border border-gray-300 bg-white shadow-md">
        <button type="button" data-toggle="{{ $sectionId }}"
            class="flex w-full items-center justify-between px-6 py-4 text-left hover:bg-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">MCQ - {{ $sectionName }}</h3>
            <svg class="h-5 w-5 text-gray-500 transition-transform duration-300" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="{{ $sectionId }}" class="space-y-6 px-6 pb-6">
            @foreach($blocks as $block)
                @php
                    $blockId = 'block_' . \Illuminate\Support\Str::slug($block['id'], '_');
                    $friendlyId = preg_replace('/^mcq_block_/', '', $block['id']);
                @endphp

                <div class="rounded-md border border-gray-200">
                    <button type="button" data-toggle="{{ $blockId }}"
                        class="flex w-full items-center justify-between bg-gray-100 px-4 py-3 text-left hover:bg-gray-200">
                        <h4 class="text-sm font-medium text-gray-700">Block {{ $friendlyId }}</h4>
                        <svg class="h-5 w-5 text-gray-500 transition-transform duration-300" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="{{ $blockId }}" class="hidden bg-white px-4 py-4">
                        <p class="mb-4 text-sm text-gray-500">
                            Items: <strong>{{ $block['items'] }}</strong> |
                            Choices: <strong>{{ $block['choices'] }}</strong>
                        </p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700">#</th>
                                        @for($c = 0; $c < $block['choices']; $c++)
                                            <th class="px-3 py-2 border border-gray-300 text-center text-sm font-medium text-gray-700">
                                                {{ chr(65 + $c) }}
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 1; $i <= $block['items']; $i++, $questionNumber++)
                                        <tr class="{{ $i % 2 === 0 ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="px-3 py-2 border border-gray-300 text-sm text-gray-800">{{ $questionNumber }}</td>
                                            @for($c = 0; $c < $block['choices']; $c++)
                                                <td class="px-3 py-2 border border-gray-300 text-center">
                                                    <input type="checkbox" id="bubble_{{ $block['id'] }}_{{ $i }}_{{ $c }}"
                                                        name="mcq[{{ $block['id'] }}][{{ $i }}]" value="{{ $c }}"
                                                        class="h-5 w-5 text-blue-600">
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

@if(!empty($omrSheet['Blanks']))
    <div class="mb-4 rounded-xl border border-gray-300 bg-white shadow-md">
        <button type="button" data-toggle="blanks_section"
            class="flex w-full items-center justify-between px-6 py-4 text-left hover:bg-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Blanks</h3>
            <svg class="h-5 w-5 text-gray-500 transition-transform duration-300" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="blanks_section" class="space-y-4 px-6 pb-6">
            @foreach($omrSheet['Blanks'] as $blank)
                <div class="grid grid-cols-[1fr_auto] items-center gap-4">
                    <div class="text-sm font-medium text-gray-700">
                        {{ $blank['section'] }}
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="blanks[{{ $blank['id'] }}]"
                            class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm text-right focus:ring-2 focus:ring-blue-500"
                            value="0" min="0" required>
                        <span class="text-sm text-gray-600">pts</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($omrSheet['Freeform']))
    <div class="mb-4 rounded-xl border border-gray-300 bg-white shadow-md">
        <button type="button" data-toggle="freeform_section"
            class="flex w-full items-center justify-between px-6 py-4 text-left hover:bg-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Freeform</h3>
            <svg class="h-5 w-5 text-gray-500 transition-transform duration-300" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="freeform_section" class="space-y-4 px-6 pb-6">
            @foreach($omrSheet['Freeform'] as $field)
                <div class="grid grid-cols-[1fr_auto] items-center gap-4 border-b border-gray-200 pb-3">
                    <div class="text-sm font-medium text-gray-700">
                        {{ $field['Instruction'] }}
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="freeform[{{ $field['id'] }}]"
                            class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm text-right focus:ring-2 focus:ring-blue-500"
                            value="0" min="0" required>
                        <span class="text-sm text-gray-600">pts</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Grab all toggle buttons
    const toggleButtons = document.querySelectorAll('[data-toggle]');

    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-toggle');
            const target = document.getElementById(targetId);
            if (!target) return;

            // Toggle hidden class
            target.classList.toggle('hidden');

            // Rotate the arrow icon
            const svg = button.querySelector('svg');
            if (svg) {
                svg.classList.toggle('rotate-180');
            }
        });
    });
});
</script>
