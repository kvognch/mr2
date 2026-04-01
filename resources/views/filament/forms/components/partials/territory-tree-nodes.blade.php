@foreach ($nodes as $node)
    @php
        $id = (int) ($node['id'] ?? 0);
        $name = (string) ($node['name'] ?? '');
        $children = $node['children'] ?? [];
        $hasChildren = !empty($children);
    @endphp

    <div class="space-y-1">
        <div class="flex items-center gap-2">
            @if ($hasChildren)
                <button
                    type="button"
                    x-on:click="toggleExpand({{ $id }})"
                    class="inline-flex h-5 w-5 items-center justify-center rounded border text-xs"
                >
                    <span x-show="!isExpanded({{ $id }})">+</span>
                    <span x-show="isExpanded({{ $id }})">-</span>
                </button>
            @else
                <span class="inline-block h-5 w-5"></span>
            @endif

            <input
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300"
                :checked="isChecked({{ $id }})"
                x-on:change="toggleChecked({{ $id }}, $event.target.checked)"
            />

            @if ($hasChildren)
                <button
                    type="button"
                    x-on:click="toggleExpand({{ $id }})"
                    class="text-sm text-primary-600 hover:underline"
                >
                    {{ $name }}
                </button>
            @else
                <span class="text-sm">{{ $name }}</span>
            @endif
        </div>

        @if ($hasChildren)
            <div x-show="isExpanded({{ $id }})" class="border-l pl-2" style="margin-left: 20px;">
                @include('filament.forms.components.partials.territory-tree-nodes', ['nodes' => $children, 'level' => $level + 1])
            </div>
        @endif
    </div>
@endforeach
