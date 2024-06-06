<div class="flex flex-col h-full space-y-4">
    @if (auth()->user()->can('create', \Parallax\FilamentComments\Models\FilamentComment::class))
        <div class="space-y-4">
            {{ $this->form }}
            
            <x-filament::button
                wire:click="create"
                color="primary"
            >
                {{ __('filament-comments::filament-comments.comments.add') }}
            </x-filament::button>
        </div>
    @endif

    @if (count($comments))
        <x-filament::grid class="gap-4">
            @foreach ($comments as $comment)
                <div class="fi-in-repeatable-item block rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                    <div class="flex gap-x-3">
                        @if (config('filament-comments.display_avatars'))
                            <x-filament-panels::avatar.user size="md" :user="$comment->user" />
                        @endif

                        <div class="flex-grow space-y-2 pt-[6px]">
                            <div class="flex gap-x-2 items-center justify-between">
                                <div class="flex gap-x-2 items-center">
                                    <div class="text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $comment->user[config('filament-comments.user_name_attribute')] }}
                                    </div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                @if (auth()->user()->can('delete', $comment))
                                    <div class="flex-shrink-0">
                                        <x-filament::icon-button
                                            wire:click="delete({{ $comment->id }})"
                                            icon="{{ config('filament-comments.icons.delete') }}"
                                            color="danger"
                                            tooltip="Delete comment"
                                        />
                                    </div>
                                @endif
                            </div>

                            <div class="prose dark:prose-invert [&>*]:mb-2 [&>*]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
                                @if(config('filament-comments.editor') === 'markdown')
                                    {{ Str::of($comment->comment)->markdown()->toHtmlString() }}
                                @else
                                    {{ Str::of($comment->comment)->toHtmlString() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </x-filament::grid>
    @else
        <div class="flex-grow flex flex-col items-center justify-center space-y-4">
            <x-filament::icon
                icon="{{ config('filament-comments.icons.empty') }}"
                class="h-12 w-12 text-gray-400 dark:text-gray-500"
            />
            
            <div class="text-sm text-gray-400 dark:text-gray-500">
                {{ __('filament-comments::filament-comments.comments.empty') }}
            </div>
        </div>
    @endif
</div>
