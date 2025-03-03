<div class="flex flex-col h-full space-y-4">
    @if (auth()->user()->can('create', \Parallax\FilamentComments\Models\FilamentComment::class))
        <div class="space-y-4">
            @if($replyToId)
                <div class="text-sm text-gray-500">
                    {{ __('filament-comments::filament-comments.comments.replying') }}
                    <button wire:click="$set('replyToId', null)" class="text-primary-600 hover:text-primary-500">
                        {{ __('filament-comments::filament-comments.comments.cancel') }}
                    </button>
                </div>
            @endif

            {{ $this->form }}

            <x-filament::button
                wire:click="create"
                color="primary"
            >
                @if($replyToId)
                    {{ __('filament-comments::filament-comments.comments.reply') }}
                @else
                    {{ __('filament-comments::filament-comments.comments.add') }}
                @endif
            </x-filament::button>
        </div>
    @endif

    @if (count($comments))
        <x-filament::grid class="gap-4">
            @foreach ($comments as $comment)
                <div
                    class="flex space-x-3 {{ $comment->isReadByUser($userId) ? 'bg-gray-50' : 'bg-white' }} p-4 rounded-lg">
                    <div class="flex-shrink-0">
                        @if (config('filament-comments.display_avatars'))
                            <x-filament-panels::avatar.user size="md" :user="$comment->user" />
                        @endif
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $comment->user[config('filament-comments.user_name_attribute')] }}
                                </div>
                                <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                    {{ $comment->created_at->diffForHumans() }}
                                </div>
                                @if ($comment->isReadByUser($userId))
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('filament-comments::filament-comments.read') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-x-2">
                                @if (auth()->user()->can('create', \Parallax\FilamentComments\Models\FilamentComment::class))
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        wire:click="startReply({{ $comment->id }})"
                                    >
                                        {{ __('filament-comments::filament-comments.comments.reply') }}
                                    </x-filament::button>
                                @endif

                                @if (auth()->user()->can('delete', $comment))
                                    <x-filament::icon-button
                                        wire:click="delete({{ $comment->id }})"
                                        icon="{{ config('filament-comments.icons.delete') }}"
                                        color="danger"
                                        tooltip="{{ __('filament-comments::filament-comments.comments.delete.tooltip') }}"
                                    />
                                @endif
                            </div>
                        </div>

                        <div
                            class="prose dark:prose-invert [&>*]:mb-2 [&>*]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
                            @if(config('filament-comments.editor') === 'markdown')
                                {{ Str::of($comment->comment)->markdown()->toHtmlString() }}
                            @else
                                {{ Str::of($comment->comment)->toHtmlString() }}
                            @endif
                        </div>

                        @if($comment->replies->count() > 0)
                            <div class="mt-4 space-y-4 pl-6 border-l-2 border-gray-100 dark:border-gray-800">
                                @foreach($comment->replies as $reply)
                                    <div
                                        class="flex space-x-3 {{ $reply->isReadByUser($userId) ? 'bg-gray-50' : 'bg-white' }} p-3 rounded-lg">
                                        <div class="flex-shrink-0">
                                            @if (config('filament-comments.display_avatars'))
                                                <x-filament-panels::avatar.user size="sm" :user="$reply->user" />
                                            @endif
                                        </div>
                                        <div class="flex-grow">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <div class="text-sm font-medium text-gray-950 dark:text-white">
                                                        {{ $reply->user[config('filament-comments.user_name_attribute')] }}
                                                    </div>
                                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                                        {{ $reply->created_at->diffForHumans() }}
                                                    </div>
                                                    @if ($reply->isReadByUser($userId))
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ __('filament-comments::filament-comments.read') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if (auth()->user()->can('delete', $reply))
                                                    <x-filament::icon-button
                                                        wire:click="delete({{ $reply->id }})"
                                                        icon="{{ config('filament-comments.icons.delete') }}"
                                                        color="danger"
                                                        size="sm"
                                                        tooltip="{{ __('filament-comments::filament-comments.comments.delete.tooltip') }}"
                                                    />
                                                @endif
                                            </div>

                                            <div
                                                class="prose dark:prose-invert [&>*]:mb-2 [&>*]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
                                                @if(config('filament-comments.editor') === 'markdown')
                                                    {{ Str::of($reply->comment)->markdown()->toHtmlString() }}
                                                @else
                                                    {{ Str::of($reply->comment)->toHtmlString() }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
