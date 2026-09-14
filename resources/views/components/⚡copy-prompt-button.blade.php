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
            } catch (e) {
                const area = document.createElement('textarea');
                area.value = @js($body);
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
            }
            $wire.copy();
            setTimeout(() => $wire.set('copied', false), 1800);
        }
    }"
>
    <button
        type="button"
        @click="doCopy()"
        class="btn-primary"
        style="color:#10002b;background:#c8f542;border:2px solid #c8f542;"
    >
        <span class="btn-label" style="color:inherit;" x-text="copied ? 'Copied' : 'Copy prompt'">Copy prompt</span>
    </button>
</div>
