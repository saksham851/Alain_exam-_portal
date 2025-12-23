@props(['name', 'value', 'label', 'selected' => null])

<label class="block relative pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all duration-200">
    <input type="radio" 
           name="{{ $name }}" 
           value="{{ $value }}" 
           class="absolute opacity-0 w-0 h-0 peer"
           {{ $selected == $value ? 'checked' : '' }}
           @change="$dispatch('option-selected', { value: '{{ $value }}' })">
           
    <div class="absolute left-3 top-3.5 w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-white flex items-center justify-center">
        <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
    </div>
    
    <span class="text-gray-700 font-medium peer-checked:text-indigo-900 select-none">
        {{ $label }}
    </span>
    
    <div class="absolute inset-0 border-2 border-indigo-600 rounded-lg opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></div>
</label>
