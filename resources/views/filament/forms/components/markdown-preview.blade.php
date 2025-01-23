<div class="prose dark:prose-invert max-w-none mt-2">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg min-h-[100px]">
        {!! $content ? Str::markdown($content) : '' !!}
    </div>
</div>
