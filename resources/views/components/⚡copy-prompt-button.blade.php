<?php

use App\Models\Prompt;
use Livewire\Component;

new class extends Component
{
    public int $promptId;

    public string $body;

    public bool $copied = false;

    public function copy(): void
    {
        Prompt::query()->whereKey($this->promptId)->increment('copy_count');
        $this->copied = true;
        $this->dispatch('prompt-copied', body: $this->body);
    }
};
?>

<div
    x-data="{
        copied: @entangle('copied'),
        async doCopy() {
            try {
                await navigator.clipboard.writeText(@js($body));
                $wire.copy();
                setTimeout(() => $wire.set('copied', false), 1800);
            } catch (e) {
                const area = document.createElement('textarea');
                area.value = @js($body);
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
                $wire.copy();
                setTimeout(() => $wire.set('copied', false), 1800);
            }
        }
    }"
>
    <button type="button" @click="doCopy()" class="btn-primary">
        <span x-text="copied ? 'Copied' : 'Copy prompt'"></span>
    </button>
</div>
