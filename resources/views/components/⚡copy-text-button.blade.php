<?php

use Livewire\Component;

new class extends Component
{
    public string $body;

    public bool $copied = false;

    public function markCopied(): void
    {
        $this->copied = true;
    }
};
?>

<div
    x-data="{
        copied: false,
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
            copied = true;
            $wire.markCopied();
            setTimeout(() => { copied = false; $wire.set('copied', false); }, 1800);
        }
    }"
>
    <button type="button" @click="doCopy()" class="btn-primary">
        <span x-text="copied || @js($copied) ? 'Copied' : 'Copy prompt'"></span>
    </button>
</div>
