@props(['duration' => 180])

<div x-data="{ 
    timer: {{ $duration }} * 60, 
    interval: null,
    formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },
    init() {
        this.interval = setInterval(() => {
            if (this.timer > 0) {
                this.timer--;
            } else {
                clearInterval(this.interval);
                this.$dispatch('timer-finished');
                // Auto submit form logic could go here or be caught by listener
            }
        }, 1000);
    }
}" 
class="fixed top-20 right-4 px-4 py-2 rounded-lg shadow-lg font-mono font-bold text-xl z-50"
:class="{
    'bg-gray-800 text-white': timer > 300,
    'bg-orange-500 text-white': timer <= 300 && timer > 60,
    'bg-red-600 text-white animate-pulse': timer <= 60
}">
    <div class="flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span x-text="formatTime(timer)"></span>
    </div>
</div>
