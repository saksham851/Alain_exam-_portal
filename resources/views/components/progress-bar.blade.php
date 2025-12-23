@props(['current', 'total'])

@php
    $percentage = $total > 0 ? ($current / $total) * 100 : 0;
@endphp

<div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
    <div class="flex justify-between text-xs text-gray-500 mt-1">
        <span>Question {{ $current }} of {{ $total }}</span>
        <span>{{ round($percentage) }}% Completed</span>
    </div>
</div>
